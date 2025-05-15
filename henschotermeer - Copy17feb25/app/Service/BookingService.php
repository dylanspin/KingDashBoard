<?php

namespace App\Service;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\Session;
use App\Bookings;
use App\UserlistUsers;
use Exception;
use App\AttendantTransactions;
use App\Attendants;
use App\TommyReservationParents;
use App\DeviceBookings;
use App\GroupDevices;
use App\Barcode;
use App\Customer;
use App\Promo;
use App\EmailNotification;
use App\CustomerVehicleInfo;
use App\BookingPayments;
use App\Products;
use Carbon\Carbon;
use App\User;
use App\LocationDevices;
use App\OpenGateManualTransaction;
use App\TransactionImages;
use GuzzleHttp\Client;
use App\DeviceTicketReaders;

class BookingService
{
    public $url;
    public $key;
    public $setting;
    public $ticketReader;
    public $locationSetting;
    public $locationCreatedAt;
    public $verifyVehicle;
    public $lang_id = FALSE;
    public $disableRules = null;
    public $parkingRules = null;
    public $matching = false;
    public $comfortSecurity = false;
    public $validBookingTypes = array(1, 4, 7, 10, 5);
    public $userListStatuses = array(2, 3);
    public $ticketStatuses = array(6, 11);
    public $validBooking = null;
    public $deviceId = null;
    public $ruleStatus = null;
    public $logCreate = false;
    public $validDeviceSettings = false;
    public $vehicleNumber = false;
    public $barcode = false;
    protected $verifyAccessService;
    public $emailNotificationBooking;
    public $emailNotification = false;
    public $isNotuserListAndPromo = false;
    public $vipBarCode = false;
    public $ticketVehicleNumber;
    public $isNullOrEmptyOrShort = false;
    public $logService = false;
    public $sessionId = false;
    public $barcodeNotUse = false;
    public $request = false;

    public function __construct($url, $matching, $locationSetting, $ticketReader, $setting, $logService, $sessionId)
    {
        $this->matching = $matching;
        $this->locationSetting = $locationSetting;
        $this->ticketReader = $ticketReader;
        $this->setting = $setting;
        $this->logCreate = new LogController();
        $this->logService = $logService;
        $this->sessionId = $sessionId;
        $this->url = $url;
        $user_id = User::first()->live_id;
        $locationId = $this->locationSetting->live_id;
        $this->key = base64_encode($locationId . '_' . $user_id);
    }
    public function getVehicleBooking($request, $key, $validDeviceSettings, $vehicleNumber)
    {
        $this->request = $request;
        $this->key = $key;
        $this->vehicleNumber = $vehicleNumber;
        $this->validDeviceSettings = $validDeviceSettings;
        $this->logService->info($this->sessionId, 'Device Direction is ' . $this->validDeviceSettings['device_direction'], false);
        if ($this->matching) {
            $this->logService->info($this->sessionId, 'Matching is enable system try to get booking from matching plate number', false);
            return $this->filterBookingsByMode($this->matching->access->plate_match_mode);
        }
        $userListBooking = $this->getUserListBooking($this->matching);
        if ($userListBooking) {
            $this->logService->info($this->sessionId, 'Booking found from user list with name (' . $userListBooking->first_name . ') and plate number is (' . $userListBooking->vehicle_num . ').', false);
            $this->logService->info($this->sessionId, 'Booking id is (' . $userListBooking->id . ')', false);
            $this->logService->info($this->sessionId, 'Booking check in time is (' . $userListBooking->checkin_time . ')', false);
            $this->logService->info($this->sessionId, 'Booking check out time is (' . $userListBooking->checkout_time . ')', false);
            $this->ruleStatus['booking_status'] = ['mode' => 'exact', 'type' => 'userlist'];
            return $userListBooking;
        }
        $otherBooking = $this->getOtherBooking();
        if ($otherBooking) {
            $name = $otherBooking->first_name ? $otherBooking->name : 'N/A';
            $this->logService->info($this->sessionId, 'Booking found from online bookings with name (' . $name  . ') and plate number is (' . $otherBooking->vehicle_num . ').', false);
            $this->logService->info($this->sessionId, 'Booking id is (' . $otherBooking->id . ')', false);
            $this->logService->info($this->sessionId, 'Booking check in time is (' . $otherBooking->checkin_time . ')', false);
            $this->logService->info($this->sessionId, 'Booking check out time is (' . $otherBooking->checkout_time . ')', false);
            $this->ruleStatus['booking_status'] = ['mode' => 'exact', 'type' => 'booking'];
            return $otherBooking;
        }
        $this->logService->info($this->sessionId, 'Booking not found against this plate number ' . $this->vehicleNumber, false);
        return false;
    }
    private function filterBookingsByMode($mode)
    {
        try {
            switch ($mode) {
                case strtolower('low'):
                    return $this->filterByLowMode();
                    break;
                case strtolower('medium'):
                    return $this->filterByMediumMode();
                    break;
                case strtolower('high'):
                    return $this->filterByHighMode();
                    break;
                default:
                    return FALSE;
            }
        } catch (Exception $ex) {
            $this->logError('filterBookingsByMode', $ex);
            return FALSE;
        }
    }
    private function filterByLowMode()
    {
        try {
            // Determine if character matching should be applied based on mode and direction
            $this->logService->info($this->sessionId, 'Mode of Matching is ' . $this->matching->access->plate_match_mode, $this->matching['id'], false);
            $shouldApplyMatching = $this->matching && (
                ($this->matching->access->plate_match_mode === 'low' && $this->validDeviceSettings['device_direction'] === 'in')
            );
            $userListBooking = $this->getUserListBooking($shouldApplyMatching);
            if ($userListBooking) {
                $this->ruleStatus['booking_status'] = ['mode' => $this->matching->access->plate_match_mode, 'type' => 'userlist'];
                return $userListBooking;
            }
            $booking_details = $this->getOtherBooking();
            $this->ruleStatus['booking_status'] = ['mode' => $this->matching->access->plate_match_mode];
            return $booking_details;
        } catch (Exception $ex) {
            return $ex;
        }
    }
    private function filterByMediumMode()
    {
        try {
            // Determine if character matching should be applied based on mode and direction
            $this->logService->info($this->sessionId, 'Mode of Matching is ' . $this->matching->access->plate_match_mode, $this->matching['id'], false);
            $shouldApplyMatching = $this->matching && (
                ($this->matching->access->plate_match_mode === 'medium' && $this->validDeviceSettings['device_direction'] === 'out')
            );
            $userListBooking = $this->getUserListBooking($shouldApplyMatching);
            if ($userListBooking) {
                $this->ruleStatus['booking_status'] = ['mode' => $this->matching->access->plate_match_mode, 'type' => 'userlist'];
                return $userListBooking;
            }
            $booking = $this->getOtherBooking();
            $this->ruleStatus['booking_status'] = ['mode' => $this->matching->access->plate_match_mode, 'type' => 'booking'];
            return $booking;
        } catch (Exception $ex) {
            return $ex;
        }
    }
    private function filterByHighMode()
    {
        try {
            $this->logService->info($this->sessionId, 'Mode of Matching is ' . $this->matching->access->plate_match_mode, $this->matching['id'], false);
            $shouldApplyMatching = $this->matching && (
                ($this->matching->access->plate_match_mode === 'high' && ($this->validDeviceSettings['device_direction'] === 'out' || $this->validDeviceSettings['device_direction'] === 'in'))
            );
            $userListBooking = $this->getUserListBooking($shouldApplyMatching);
            if ($userListBooking) {
                $this->ruleStatus['booking_status'] = ['mode' => $this->matching->access->plate_match_mode, 'type' => 'userlist'];
                return $userListBooking;
            }
            $booking = $this->getOtherBooking();
            $this->ruleStatus['booking_status'] = ['mode' => $this->matching->access->plate_match_mode, 'type' => 'booking'];
            return $booking;
        } catch (Exception $ex) {
            return $ex;
        }
    }
    private function getUserListBooking($shouldApplyMatching)
    {
        try {
            if ($shouldApplyMatching) {
                $this->logService->info($this->sessionId, 'Matching is enable upto character limit ' . $this->validDeviceSettings['character_match_limit'], false);
                $userlist_booking_details = Bookings::with('attendant_transactions')
                    ->selectRaw("bookings.*, levenshtein('$this->vehicleNumber', bookings.vehicle_num) as distance")
                    ->having('distance', '<=', (int)$this->validDeviceSettings['matching_distance'])
                    ->whereIn('type', $this->userListStatuses)
                    ->orderBy('distance', 'asc')
                    ->first();
            } else {
                $this->logService->info($this->sessionId, 'Matching is not enable system try to find booking from userlist users booking', false);
                $userlist_booking_details = Bookings::with('attendant_transactions')->where('vehicle_num', $this->vehicleNumber)
                    ->whereIn('type', $this->userListStatuses)
                    ->first();
            }
            if ($userlist_booking_details) {
                if ($userlist_booking_details->customer_id > 0) {
                    $userlist_user = UserlistUsers::where('customer_id', $userlist_booking_details->customer_id)->first();
                    if ($userlist_user) {
                        $this->lang_id = $userlist_user->language_id;
                    }
                }
                return $userlist_booking_details;
            }
            return false;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    private function getOtherBooking()
    {
        try {
            $bookingDetails = null;
            $deviceDirection = $this->validDeviceSettings['device_direction'];
            $this->logService->info($this->sessionId, 'Booking not found from userList user. Now system is trying to find booking from online bookings.', false);
            if ($this->matching) {
                $bookingDetails = ($deviceDirection === 'in')
                    ? $this->findBookingIn($this->matching)
                    : $this->findMatchingBooking();
            } else {
                if ($deviceDirection === 'in') {
                    $bookingDetails = $this->findBookingIn(false);
                } elseif ($deviceDirection === 'out') {
                    $bookingDetails = $this->findBookingOut();
                } else {
                }
            }
            if ($bookingDetails) {
                return $bookingDetails;
            }
            return false;
        } catch (Exception $ex) {
            return $this->logError('getBooking', $ex);
        }
    }
    private function findBookingIn($matching)
    {
        $booking = false;
        if ($matching) {
            $this->logService->info($this->sessionId, 'Matching is enable and device Direction is ' . $this->validDeviceSettings['device_direction'], false);
            $booking = Bookings::with('attendant_transactions')->selectRaw("bookings.*, levenshtein('$this->vehicleNumber',bookings.vehicle_num) as distance")
                ->having('distance', '<=', (int) $this->validDeviceSettings['matching_distance'])
                ->whereIn('type', $this->validBookingTypes)
                ->where('checkout_time', '>', date('Y-m-d H:i:s'))
                ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
                ->orderBy('distance', 'asc')
                ->first();
        }
        $this->logService->info($this->sessionId, 'Matching not enable and device direction is ' . $this->validDeviceSettings['device_direction'], false);
        $booking = Bookings::with('attendant_transactions')->where('vehicle_num', $this->vehicleNumber)
            ->whereIn('type', $this->validBookingTypes)
            ->where('checkout_time', '>', date('Y-m-d H:i:s'))
            ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
            ->orderBy('created_at', 'DESC')
            ->first();
        if ($booking) {
            return $booking;
        }
    }

    private function findBookingOut()
    {
        $this->logService->info($this->sessionId, 'Finding Booking for Exit against plate Number ' . $this->vehicleNumber, false);
        $booking_details = null;
        $booking_details = Bookings::with('attendant_transactions')->where('vehicle_num', $this->vehicleNumber)
            ->whereIn('type', $this->validBookingTypes)
            ->where('checkout_time', '>', date('Y-m-d H:i:s'))
            ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
            ->orderBy('is_paid', 'DESC')
            ->first();
        if ($booking_details) {
            return $booking_details;
        }
        $booking_details = Bookings::whereHas('attendant_transactions', function ($query) {
            $query->whereNull('check_out');
        })->whereIn('type', $this->validBookingTypes)
            ->where('vehicle_num', $this->vehicleNumber)->orderBy('is_paid', 'DESC')->first();
        return $booking_details;
    }

    private function findMatchingBooking()
    {
        $this->logService->info($this->sessionId, 'Matching is enable and device Direction is ' . $this->validDeviceSettings['device_direction'], false);
        $booking_detail = Bookings::with('attendant_transactions')->selectRaw("bookings.*, levenshtein('$this->vehicleNumber',bookings.vehicle_num) as distance")
            ->having('distance', '<=', (int) $this->validDeviceSettings['matching_distance'])
            ->whereIn('type', $this->validBookingTypes)
            ->where('checkout_time', '>', date('Y-m-d H:i:s'))
            ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
           ->orderBy('distance', 'asc')
            ->first();
        if ($booking_detail) {
            return $booking_detail;
        }
        $booking_details = Bookings::with('attendant_transactions')->selectRaw("bookings.*,levenshtein('$this->vehicleNumber',bookings.vehicle_num) as distance")
            ->having('distance', '<=', (int) $this->validDeviceSettings['matching_distance'])->whereHas('attendant_transactions', function ($query) {
                $query->whereNull('check_out');
            })->whereIn('type', $this->validBookingTypes)
            ->orderBy('distance', 'asc')->first();
        return $booking_details;
    }
    // Barcode Related Function // 
    public function getPersonBooking($request, $key, $validDeviceSettings, $barcode)
    {
        $this->request = $request;
        $this->key = $key;
        $this->barcode = $barcode;
        $this->validDeviceSettings = $validDeviceSettings;
        $personBooking = $this->personBooking($this->barcode);
        if ($personBooking) {
            return $personBooking;
        }
        $vipPersonBarcode = $this->checkBarcodeType($this->barcode);
        if(empty($vipPersonBarcode)){
            return false;
        }
        if (isset($vipPersonBarcode->type) && $vipPersonBarcode->type != "person") {
            $this->logService->info($this->sessionId, 'Barcode is not valid', false);
        }
        $vipPersonBooking = $this->vipPersonBarcodeBooking($vipPersonBarcode, $this->barcode);
        if ($vipPersonBooking) {
            return $vipPersonBooking;
        }
        return false;
    }
    public function getParkingBooking($request, $key, $validDeviceSettings, $barcode, $ticketVehicleNumber = null, $isNullOrEmptyOrShort = false)
    {

        $this->request = $request;
        $this->barcode = $barcode;
        $this->key = $key;
        $this->validDeviceSettings = $validDeviceSettings;
        $this->isNullOrEmptyOrShort = $isNullOrEmptyOrShort;
        $this->ticketVehicleNumber = $ticketVehicleNumber;
        $vipParkingBooking = false;
        //$barcodeNotUse = false
        $bookingStatus = "";
        $parkingBooking = $this->parkingBooking($this->barcode);
        if ($parkingBooking) {
            return $parkingBooking;
        }
        $this->logService->info($this->sessionId, 'Booking not found from against (' . $barcode . ')', false);
        $vipParkingBarcode = $this->checkBarcodeType($this->barcode);
        if ($vipParkingBarcode) {
            if (isset($vipParkingBarcode) && $vipParkingBarcode->type == "parking") {
                if (!$this->ticketVehicleNumber) {
                    $this->logService->info($this->sessionId, 'Now system try to find booking against vip barcode without vehicle number against barcode (' . $barcode . ')', false);
                    $vipParkingBooking = $this->getBarcodeBooking($this->barcode);
                    if ($vipParkingBooking && $vipParkingBarcode->use_barcode_multiple_time) {
                        $this->logService->info($this->sessionId, 'Booking found and mutiple time usage is enable against this barcode (' . $barcode . ')', false);
                        $this->logService->info($this->sessionId, 'Multiple booking are allowed against this barcode (' . $barcode . ')', false);
                        $this->barcodeNotUse = true;
                    }
                    if ($vipParkingBooking && (!$vipParkingBarcode->use_barcode_multiple_time)) {
                        $this->logService->info($this->sessionId, 'Booking found and mutiple time usage is disable against this barcode ' . $barcode, false);
                        $this->logService->info($this->sessionId, 'Multiple booking are not allowed against this barcode (' . $barcode . ')', false);
                        return $vipParkingBooking;
                    }
                    $bookingStatus = "barcode_without_vehicle";
                    $this->barcodeNotUse = true;
                }
                if ($this->ticketVehicleNumber && !$this->isNullOrEmptyOrShort) {
                    $this->logService->info($this->sessionId, 'Now system try to find booking using vip barcode and vehicle number against barcode (' . $barcode . ')', false);
                    $vipVehicleBooking = $this->getVehicleBarcodeBooking($this->ticketVehicleNumber, false);
                    if ($vipVehicleBooking && $vipParkingBarcode->use_barcode_multiple_time) {
                        $this->logService->info($this->sessionId, 'Booking found and mutiple time usage is enable against this barcode (' . $barcode . ')', false);
                        $this->logService->info($this->sessionId, 'Multiple booking are allowed against this barcode (' . $barcode . ')', false);
                        $this->barcodeNotUse = true;
                    }
                    if ($vipVehicleBooking && (!$vipParkingBarcode->use_barcode_multiple_time)) {
                        $this->logService->info($this->sessionId, 'Booking found and mutiple time usage is disable against this barcode ' . $barcode, false);
                        $this->logService->info($this->sessionId, 'Multiple booking are not allowed against this barcode (' . $barcode . ')', false);
                        return $vipVehicleBooking;
                    }
                    $bookingStatus = "barcode_with_vehicle";
                    $this->barcodeNotUse = true;
                }
                if (!$this->barcodeNotUse) {
                    return false;
                }
                switch ($bookingStatus) {
                    case "barcode_without_vehicle":
                        $this->logService->info($this->sessionId, 'Barcode Multiple enable is true so system create new booking without vehicle against this barcode (' . $barcode . ')', false);
                        return $this->addBarcodeBooking($this->barcode, $vipParkingBarcode);
                        break;
                    case "barcode_with_vehicle":
                        $this->logService->info($this->sessionId, 'Barcode Multiple enable is true so system create new booking with vehicle against this barcode (' . $barcode . ')', false);
                        return $this->addBarcodeBooking($this->barcode, $vipParkingBarcode, $this->ticketVehicleNumber);
                        break;
                    default:
                        return false;
                        break;
                }
            }
        }
        $this->logService->info($this->sessionId, 'Booking not found from vip barcode also (' . $barcode . ')', false);
        $emailNotificationBooking = $this->emailNotificationBooking($this->barcode);
        if ($emailNotificationBooking) {
            return $emailNotificationBooking;
        }
        $this->logService->info($this->sessionId, 'Booking not found from email notification  also (' . $barcode . ')', false);
        $posBooking = Bookings::where(['type' => 4, 'pos_barcode' => $this->barcode])->first();
        if ($posBooking) {
            $this->logService->info($this->sessionId, 'Now system try to find pos booking against (' . $this->barcode . ')', false);
            return $this->isValidPosBooking($posBooking, $this->deviceId, $this->validDeviceSettings, $this->vehicleNumber);
        }
        return false;
    }
    public function checkBarcodeType($barcode)
    {
        $this->logService->info($this->sessionId, 'Checking Vip Barcode' . $barcode, false);
        $parkingBarcode = Barcode::where(['barcode' => $barcode, 'type' => 'parking'])
            ->orderBy('created_at', 'desc')->first();
        if ($parkingBarcode) {
            return $parkingBarcode;
        }
        $personBarCode = Barcode::where(['barcode' => $barcode, 'type' => 'person'])
            ->orderBy('created_at', 'desc')->first();
        if ($personBarCode) {
            return $personBarCode;
        }
        $posBarCode = Barcode::where(['barcode' => $barcode, 'type' => 'pos_barcode'])
            ->orderBy('created_at', 'desc')->first();
        if ($posBarCode) {
            return $posBarCode;
        }
        return false;
    }
    public function isPersonValidBooking($barcode)
    {
        $booking_details = Bookings::where([['live_id', intval($barcode)]])
            ->whereIn('type', array(6, 11))
            ->whereDate('checkin_time', '<=', date('Y-m-d H:i'))
            ->whereDate('checkout_time', '>=', date('Y-m-d H:i'))
            ->orderBy('created_at', 'DESC')->orWhere([['id', intval($barcode)]])
            ->first();
        if ($booking_details) {
            return $booking_details;
        }
        $vipBarcode = $this->checkBarcodeType($barcode);
        if ($vipBarcode && $this->validDeviceSettings['device_direction'] == "out") {
            $booking_details = Bookings::where('barcode', $barcode)
                ->where('checkin_time', '<=', date('Y-m-d H:i'))
                ->whereNull('checkout_time')
                ->where('type', 5)
                ->orderBy('created_at', 'desc')
                ->first();
            return $booking_details;
        }
    }
    private function isValidPosBooking($valid_booking, $id, $device, $vehicle_num = FALSE)
    {
        $product = Products::where('pos_type', $valid_booking->pos_type)->first();
        $valid_until = $valid_booking->checkout_time;
        $timeout_message = $this->verifyVehicle->getMessage('unauthorized', "", $valid_booking->lang_id);
        $booking = $valid_booking;
        $verified_message = $device->device_direction == 'in' ? $this->verifyVehicle->getMessage('welcome_entrance', $valid_booking->first_name, $valid_booking->lang_id) : $this->verifyVehicle->getMessage->get_error_message('goodbye_exit', $valid_booking->first_name, $valid_booking->lang_id);
        if (date('Y-m-d H:i:s') > $valid_until) {
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => FALSE,
                'message' => $timeout_message,
                'validity' => $valid_until,
                'current_time' => date('Y-m-d H:i:s'),
                'data' => FALSE,
                'is_expired'  =>  true,
            );
        } else {
            if ($vehicle_num && strtolower($valid_booking->vehicle_num) != strtolower($vehicle_num)) {
                $booking = new Bookings();
                $dataArray = array(
                    'first_name' => $valid_booking->first_name,
                    'type' => 4,
                );
                $booking->type = $dataArray['type'];
                $booking->first_name = $dataArray['first_name'];
                $booking->checkin_time = $valid_booking->checkin_time;
                $booking->sender_name = $valid_booking->sender_name;
                $booking->checkout_time = $valid_booking->checkout_time;
                $booking->vehicle_num = $vehicle_num;
                $booking->pos_barcode = $valid_booking->pos_barcode;
                $booking->pos_type = $valid_booking->pos_type;
                $booking->is_local_updated = 1;
                $booking->is_live_updated = 0;
                $booking->is_paid = 1;
                $booking->save();
            } else {
                if ($vehicle_num) {
                    $valid_booking->vehicle_num = $vehicle_num;
                    $valid_booking->save();
                }
            }
            return array(
                'status' => 1,
                'access_status' => 'allow',
                'od_sent' => FALSE,
                'message' => $verified_message,
                'pos_ticket_type' => $valid_booking->pos_type,
                'check_in' => $valid_booking->checkin_time,
                'check_out' => $valid_booking->checkout_time,
                'validity' => $valid_until,
                'current_time' => date('Y-m-d H:i:s'),
                'is_expired' => false,
                'data' => $booking->id
            );
        }
    }
    private function personBooking($barcode)
    {
        $personBarcodeBooking = null;
        $isInteger = is_numeric($barcode);
        if ($isInteger) {
            $this->logService->info($this->sessionId, 'Trying to Find Person Booking Against this Barcode', false);
            switch ($this->validDeviceSettings['device_direction']) {
                case "in":
                    $this->logService->info($this->sessionId, 'Device Direction is ' . $this->validDeviceSettings['device_direction'], false);
                    $personBarcodeBooking = Bookings::where(function ($query) use ($barcode) {
                        $query->where('id', $barcode)
                            ->orWhere('live_id', $barcode);
                    })->whereIn('type', $this->ticketStatuses)
                        ->where('checkout_time', '>', date('Y-m-d H:i:s'))
                        ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
                        ->orderBy('created_at', 'DESC')
                        ->first();
                    if ($personBarcodeBooking) {
                        return $personBarcodeBooking;
                    }
                    break;
                case "out":
                    $this->logService->info($this->sessionId, 'Device Direction is ' . $this->validDeviceSettings['device_direction'], false);
                    $booking_details = Bookings::whereHas('attendant_transactions', function ($query) {
                        $query->whereNull('check_out');
                    })->where(function ($query) use ($barcode) {
                        $query->where('id', intval($barcode))
                            ->orWhere('live_id', intval($barcode));
                    })->where(function ($query) {
                        $query->whereNull('vehicle_num')->orWhereNotNull('vehicle_num');
                    })->whereIn('type', $this->ticketStatuses)->whereNull('barcode')->orderBy('created_at', 'desc')->first();
                    if ($booking_details) {
                        return $booking_details;
                    }
                default:
                    $this->logService->info($this->sessionId, 'No Booking Found Against Barcode ' . $barcode, false);
                    return false;
                    break;
            }
        }
        return false;
    }
    private function parkingBooking($barcode)
    {
        $parkingBarcodeBooking = false;
        $isInteger = is_numeric($barcode);
        if ($isInteger) {
            switch ($this->validDeviceSettings['device_direction']) {
                case "in":
                    $this->logService->info($this->sessionId, 'Device Direction is ' . $this->validDeviceSettings['device_direction'], false);
                    $parkingBarcodeBooking = Bookings::with('attendant_transactions')->where(function ($query) use ($barcode) {
                        $query->where('id', $barcode)
                            ->orWhere('live_id', $barcode);
                    })->whereNotIn('type', $this->ticketStatuses)
                        ->where('checkout_time', '>', date('Y-m-d H:i:s'))
                        ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
                        ->orderBy('created_at', 'DESC')
                        ->first();
                    if ($parkingBarcodeBooking) {
                        return $parkingBarcodeBooking;
                    }
                    $userListBooking = Bookings::with('attendant_transactions')->where(function ($query) use ($barcode) {
                        $query->where('id', intval($barcode))
                            ->orWhere('live_id', intval($barcode));
                    })
                        ->whereIn('type', $this->userListStatuses)
                        ->first();
                    if ($userListBooking) {
                        return $userListBooking;
                    }
                    return false;
                    break;
                case "out":
                    $this->logService->info($this->sessionId, 'Device Direction is ' . $this->validDeviceSettings['device_direction'], false);
                    $booking_details = Bookings::whereHas('attendant_transactions', function ($query) {
                        $query->whereNull('check_out');
                    })->where(function ($query) use ($barcode) {
                        $query->where('id', intval($barcode))
                            ->orWhere('live_id', intval($barcode));
                    })
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if (!$booking_details) {
                        return false;
                    }
                    // if ($booking_details->customer_id > 0) {
                    //     $userlist_user = UserlistUsers::where('customer_id', $booking_details->customer_id)
                    //         ->where('is_blocked', 0)
                    //         ->first();
                    //     return $userlist_user ? $booking_details : false;
                    // } elseif (isset($booking_details->customer_vehicle_info_id)) {
                    //     $customer_vehicle_info = $booking_details->customer_vehicle_info;
                    //     $userlist_user = UserlistUsers::where('id', $customer_vehicle_info->userlist_user_id)
                    //         ->where('is_blocked', 0)
                    //         ->first();
                    //     return $userlist_user ? $booking_details : false;
                    // }

                    return $booking_details;

                default:
                    return false;
                    break;
            }
        }

        return false;
    }
    private function vipPersonBarcodeBooking($vipBarcode, $barcode)
    {
        $barcodeNotUse = false;
        if ($this->validDeviceSettings['device_direction'] == "out") {
            $vipPersonBarcodeBooking = Bookings::where('barcode', $barcode)
                ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
                ->whereNull('checkout_time')
                ->where('type', 5)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($vipPersonBarcodeBooking) {
                return $vipPersonBarcodeBooking;
            }
        }
        $booking = $this->getBarcodeBooking($barcode);
        if (!empty($booking) && (!$vipBarcode->use_barcode_multiple_time)) {
            return $booking;
        }
        if ($booking && $vipBarcode->use_barcode_multiple_time) {
            $barcodeNotUse = true;
        }
        if (!$booking) {
            $barcodeNotUse = true;
        }
        if (!$barcodeNotUse) {
            return false;
        }
        $personBarcodeBooking = $this->addBarcodeBooking($barcode, $vipBarcode);
        return $personBarcodeBooking;
    }
    public function getBarcodeBooking($barcode)
    {
        $barcodeExits = Barcode::where('barcode', $barcode)->first();
        if (!$barcodeExits) {
            return false;
        }
        $booking = Bookings::where('type', 5)
            ->where('barcode', $barcode)
            ->where(function ($query) {
                $query->whereNotNull('vehicle_num')
                    ->orWhereNull('vehicle_num');
            })
            ->orderBy('created_at', 'desc')
            ->first();
        if ($booking) {
            return $booking;
        }
        return false;
    }
    public function locationBarcodeSeries($barcode)
    {
        if ($this->locationSetting->barcode_series == NULL) {
            Session::put('error_message', 'Sorry, You do not have access!');
            $message = $this->ticketReader->getMessage('unauthorized');
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => $this->setting->send_message_od($this->deviceId, $message, 'rejected'),
                'message' => $message,
                'data' => FALSE,
            );
        }
        $barcode_range = explode('-', $this->locationSetting->barcode_series);

        if (!is_array($barcode_range) || count($barcode_range) != 2) {

            Session::put('error_message', 'Sorry, You do not have access!');
            $message = $this->ticketReader->getMessage('unauthorized');
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => $this->setting->send_message_od($this->deviceId, $message, 'rejected'),
                'message' => $message,
                'data' => FALSE,
            );
        }
        if ($barcode >= $barcode_range[0] && $barcode <= $barcode_range[1]) {

            $is_barcode_at_location = $this->isBarcodeAtLocation($barcode);
            if ($is_barcode_at_location) {
                Session::put('error_message', 'You are already on location!');
                $message = $this->ticketReader->getMessage('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $this->setting->send_message_od($this->deviceId, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
        }
    }
    private function getVehicleBarcodeBooking($vehicleNumber, $barcode_booking = FALSE)
    {
        try {
            $booking_details = Bookings::where('vehicle_num', $vehicleNumber)->where('type', 5)->first();
            if ($booking_details) {
                return $booking_details;
            } else {
                if ($barcode_booking) {
                    $new_booking = $this->addVehicleBarcodeBooking($vehicleNumber);
                    if ($new_booking) {
                        return $new_booking;
                    }
                }
            }
            return FALSE;
        } catch (Exception $ex) {
            return FALSE;
        }
    }
    private function isBarcodeAtLocation($barcode)
    {
        $booking = Bookings::where('type', 5)
            ->where('barcode', $barcode)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$booking) {
            return FALSE;
        }
        // return $this->bookingAlreadyOnLocation($booking);
    }
    private function getPersonBarcodeBooking($barcode)
    {
        return
            $booking = Bookings::where('type', 5)->whereNull('vehicle_num')
            ->where('barcode', $barcode)->orWhereNotNull('vehicle_num')
            ->orderBy('created_at', 'desc')
            ->first();
    }
    // email Notification //
    private function emailNotificationBooking($barcode)
    {
        $this->logService->info($this->sessionId, 'Now system try find booking using identifier (' . $barcode . ') against the email notification', false);
        $emailNotificationBooking = EmailNotification::where('ticket_token', $barcode)->first();
        if ($emailNotificationBooking && $this->validDeviceSettings['device_direction'] == "in") {
            $this->logService->info($this->sessionId, 'Device Direction is ' . $this->validDeviceSettings['device_direction'], false);
            if ($emailNotificationBooking->type != "promo") {
                if ($emailNotificationBooking->type != "user_list") {
                    $this->logService->info($this->sessionId, 'Type of identifier used (' . $barcode . ') against the email notification (' . $emailNotificationBooking->type . ').', false);
                    $notificationTime = $this->notificationTime($emailNotificationBooking);
                    if ($notificationTime) {
                        $this->logService->info($this->sessionId, 'identifier (' . $barcode . ') used against email notification is expired.', false);
                        return false;
                    }
                }
                $type = $emailNotificationBooking->type == 'user_list' ? 3 : 4;
                $booking = $this->getCustomerVehicleBooking($emailNotificationBooking->customer_id, $this->ticketVehicleNumber, 'in', $type);
                if ($booking) {
                    $this->logService->info($this->sessionId, 'Booking found identifier (' . $barcode . ') against the email notification.', false);
                    return $booking;
                }
                $this->emailNotification = $emailNotificationBooking;
                $this->logService->info($this->sessionId, 'Booking not found but identifier used (' . $barcode . ') is valid against the email notification', false);
                return false;
            } else {
                $notificationTime = $this->notificationTime($emailNotificationBooking);
                if ($notificationTime) {
                    $this->logService->info($this->sessionId, 'Identifier used (' . $barcode . ') against email notification is expired', false);
                    return false;
                }
                $booking = $this->getVehiclePromoBooking($emailNotificationBooking->customer_id, $this->ticketVehicleNumber, 'in', $emailNotificationBooking->type_id);
                if (!$booking) {
                    $this->emailNotification = $emailNotificationBooking;
                    $this->logService->info(
                        $this->sessionId,
                        'Booking not found but identifier used (' . $barcode . ') is valid against the email Notification',
                        false
                    );
                    return false;
                }
                $this->logService->info($this->sessionId, 'Booking found using identifier (' . $barcode . ') against the email notification', false);
                return $booking;
            }
        } elseif ($emailNotificationBooking && $this->validDeviceSettings['device_direction'] == "out") {
            $this->logService->info($this->sessionId, 'Device Direction is ' . $this->validDeviceSettings['device_direction'], false);
            if ($emailNotificationBooking->type == 'customer') {
                $notificationTime = $this->notificationTime($emailNotificationBooking);
                if ($notificationTime) {
                    $this->logService->info($this->sessionId, 'identifier (' . $barcode . ') used against the email notification is expired', false);
                    return false;
                }
            }
            $type = $emailNotificationBooking->type == 'user_list' ? 3 : 4;
            $booking = $this->getCustomerVehicleBooking($emailNotificationBooking->customer_id, $this->ticketVehicleNumber, 'out', $type);
            if (!$booking) {
                $this->emailNotification = $emailNotificationBooking;
                $this->logService->info($this->sessionId, 'Booking not found but identifier used (' . $barcode . ') is valid against the email notification', false);
                return false;
            }
            $customer = $booking->customer;
            if (isset($customer) && $customer->language_id > 0) {
                $this->lang_id = $customer->language_id;
            }
            //$user_name = $this->get_user_name($booking);
            $at_location = $this->setting->is_booking_at_location($booking->id);
            if (!$at_location && !($booking->type == 3 || $booking->type == 2)) {
                return false;
            }
            $is_paid = $this->isbookingPaid($booking);
            if ($is_paid['status']) {
                $this->logService->info($this->sessionId, 'Booking found using identifier (' . $barcode . ') against This email notification', false);
                return $booking;
            }
            return false;
        }
    }
    private function getCustomerVehicleBooking($customer_id, $vehicle, $status, $type = 4)
    {
        if ($status == 'in') {
            $booking_details = Bookings::where('customer_id', $customer_id)
                ->where('vehicle_num', $vehicle)
                ->where('type', $type)
                ->orderBy('created_at', 'desc')->first();
            if ($booking_details) {
                return $booking_details;
            }
            return FALSE;
        } else if ($status == 'out') {
            $booking_details = Bookings::whereHas('attendant_transactions', function ($query) {
                $query->whereNull('check_out');
            })
                ->where('type', $type)
                ->where('customer_id', $customer_id)
                ->where('vehicle_num', $vehicle)
                ->first();
            if ($booking_details) {
                return $booking_details;
            }

            if ($type == 3) {
                $booking_details = Bookings::whereHas('attendant_transactions', function ($query) {
                    $query->whereNull('check_out');
                })
                    ->where('type', 4)
                    ->where('customer_id', $customer_id)
                    ->where('vehicle_num', $vehicle)
                    ->first();
                if ($booking_details) {
                    return $booking_details;
                }
            }
            return FALSE;
        } else {
            return FALSE;
        }
    }
    function createCustomerVehicleBooking($customer_id, $vehicle, $check_in, $check_out, $type)
    {
        $customer = Customer::find($customer_id);
        $booking = null;
        if ($customer) {
            $booking = new Bookings();
            $booking->customer_id = $customer_id;
            if ($type == 3) {
                $type = 4;
                $booking->checkout_time = date('Y-m-d H:i:s', strtotime('+1 minutes', strtotime(date('Y-m-d H:i:s'))));
                $booking->checkin_time = date('Y-m-d H:i:s');
            } else {
                $booking->checkin_time = $check_in;
                $booking->checkout_time = $check_out;
            }

            $booking->type = $type;
            if (!isset($vehicle)) {
                $booking->vehicle_num = 'No Plate';
            }
            if ($vehicle) {
                $booking->vehicle_num = $vehicle;
            } else {
                $booking->vehicle_num = 'No Plate';
            }
            $booking->first_name = $customer->name;
            $booking->email = $customer->email;
            $booking->is_paid = 1;
            $booking->save();
        }
        return $booking;
    }
    public function getVehiclePromoBooking($customer_id, $vehicle, $status, $promo_id)
    {

        $promo = Promo::where('live_id', $promo_id)
            ->first();
        if ($promo) {
            if ($status == 'in') {
                $booking_details = Bookings::with('promo')->where('customer_id', $customer_id)
                    ->where('vehicle_num', $vehicle)
                    ->where('promo_code', $promo->code)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($booking_details) {
                    return $booking_details;
                }
                return FALSE;
            } else if ($status == 'out') {
                $booking_details = Bookings::whereHas('attendant_transactions', function ($query) {
                    $query->whereNull('check_out');
                })
                    ->where('customer_id', $customer_id)
                    ->where('vehicle_num', $vehicle)
                    ->where('promo_code', $promo->code)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($booking_details) {
                    return $booking_details;
                }
                return FALSE;
            } else {
                return FALSE;
            }
        }
        return false;
    }
    public function createVehiclePromoBooking($customer_id, $vehicle, $check_in, $check_out, $promo_id)
    {
        $promo = Promo::where('live_id', $promo_id)
            ->first();
        $customer = Customer::find($customer_id);
        if (!$promo) {
            return false;
        }
        $booking = null;
        if ($customer) {
            $customer_vehicle_info = CustomerVehicleInfo::where('num_plate', $vehicle)
                ->where('customer_id', $customer_id)
                ->first();
            if (!$customer_vehicle_info) {
                $customer_vehicle_info = new CustomerVehicleInfo();
                $customer_vehicle_info->customer_id = $customer_id;
                $customer_vehicle_info->num_plate = $vehicle;
                $customer_vehicle_info->save();
            }
            $booking = new Bookings();
            $booking->customer_id = $customer_id;
            $booking->customer_vehicle_info_id = $customer_vehicle_info->id;
            $booking->checkin_time = $check_in;
            $booking->checkout_time = $check_out;
            $booking->promo_code = $promo->code;
            $booking->vehicle_num = $vehicle;
            $booking->first_name = $customer->name;
            $booking->email = $customer->email;
            $booking->type = 4;
            $booking->is_paid = 1;
            $booking->save();
        }
        return $booking;
    }
    public function addBarcodeBooking($barcode, $vipBarcode = false, $vehicle = false)
    {
        try {
            if ($barcode == NULL) {
                return FALSE;
            }
            $payment_id = 'Parking Ticket';
            $booking = new Bookings();
            if ($vipBarcode) {
                $booking->first_name = $vipBarcode->name;
                if ($vipBarcode->type == 'parking') {
                    $booking->vehicle_num = 'No Plate';
                } else {
                    $payment_id = 'Person Ticket';
                }
            }
            if ($vehicle !== FALSE) {
                $booking->vehicle_num = $vehicle;
            }
            $booking->type = 5;
            $booking->checkin_time = date('Y-m-d H:i:s');
            $booking->barcode = $barcode;
            $booking->save();
            $booking_id = $booking->id;
            $locationId = $this->locationSetting->live_id;
            $user_id = User::first()->live_id;
            $Key = base64_encode($locationId . '_' . $user_id);
            $data = array(
                'barcode' => $barcode,
                'ticket_type' => 'barcode',
                'type' => 5,
                'amount' => 0,
                'payment_id' => $payment_id
            );
            return $booking;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function get_user_name($booking_details)
    {
        $user = '';
        if (!empty($booking_details->first_name)) {
            $user = $booking_details->first_name;
        }
        if (!empty($booking_details->last_name)) {
            if ($user != '') {
                $user = $user . ' ' . $booking_details->last_name;
            } else {
                $user = $booking_details->last_name;
            }
        }
        return ($user == 'Paid Vehicle' || $user == 'Paid Person') ? 'paid_string' : $user;
    }
    private function addVehicleBarcodeBooking($vehicleNumber)
    {
        try {
            $checkin_time = date('Y-m-d H:i:s');
            $deviceBooking = DeviceBookings::where([
                ['vehicle_num', $vehicleNumber]
            ])
                ->whereIn('device_id', [5, 25])
                ->whereDate('created_at', Carbon::today()->toDateString())
                ->orderBy('created_at', 'desc')
                ->first();
            if ($deviceBooking) {
                $checkin_time = date('Y-m-d H:i:s', strtotime($deviceBooking->created_at));
            }
            $dataArray = array(
                'first_name' => 'Paid Vehicle',
                'vehicle_num' => $vehicleNumber,
                'type' => 8,
                'is_paid' => 0,
                'checkin_time' => $checkin_time,
                'amount' => 0,
                'payment_id' => 'Paid Vehicle'
            );

            $booking = new Bookings();
            $booking->type = $dataArray['type'];
            $booking->first_name = $dataArray['first_name'];
            $booking->vehicle_num = $dataArray['vehicle_num'];
            $booking->checkin_time = $dataArray['checkin_time'];
            $booking->save();
            $bookingId = $booking->id;
            $booking_payment = new BookingPayments();
            $booking_payment->booking_id = $bookingId;
            $booking_payment->amount = $dataArray['amount'];
            $booking_payment->payment_id = $dataArray['payment_id'];
            $booking_payment->checkin_time = $dataArray['checkin_time'];
            $booking_payment->save();
            $bookingPaymentId = $booking_payment->id;
            $attendant = Attendants::where('booking_id', $bookingId)->first();
            if (!$attendant) {
                $attendant = new Attendants();
            }
            $attendant->booking_id = $bookingId;
            $attendant->save();
            $attendant_id = $attendant->id;
            $attendant_transaction = new AttendantTransactions();
            $attendant_transaction->attendant_id = $attendant_id;
            $attendant_transaction->check_in = $checkin_time;
            $attendant_transaction->save();
            return $booking;
        } catch (Exception $ex) {
            return FALSE;
        }
    }
    public function createBooking($vehicle, $device_id, $check_in_vehicle = True, $type = 4)
    {
        $dataArray = array(
            'first_name' => 'Paid Vehicle',
            'vehicle_num' => $vehicle ?  $vehicle : 'No Plate',
            'type' => $type,
            'is_paid' => 0,
            'checkin_time' => date('Y-m-d H:i:s'),
            'amount' => 0,
            'payment_id' => 'Paid Vehicle',
            'attendant' => 1
        );
        $checkout_time = date('Y-m-d H:i:s', strtotime('+15 minutes', strtotime($dataArray['checkin_time'])));
        $booking = new Bookings();
        $booking->type = $dataArray['type'];
        $booking->first_name = $dataArray['first_name'];
        $booking->vehicle_num = $dataArray['vehicle_num'];
        $booking->checkin_time = $dataArray['checkin_time'];
        $booking->checkout_time = $checkout_time;
        $booking->is_paid = $dataArray['is_paid'];
        $booking->save();
        $bookingId = $booking->id;
        $booking_payment = new BookingPayments();
        $booking_payment->booking_id = $bookingId;
        $booking_payment->amount = $dataArray['amount'];
        $booking_payment->payment_id = $dataArray['payment_id'];
        $booking_payment->checkin_time = $dataArray['checkin_time'];
        $booking_payment->checkout_time = $checkout_time;
        $booking_payment->save();
        return $booking;
    }
    public function getBookingById($bookingId)
    {
        return Bookings::find($bookingId);
    }
    public function push_booking_cloud($booking)
    {
        if ($booking->live_id > 0) {
            return $booking;
        }
        $dataArray = array(
            'first_name' => 'Paid Vehicle',
            'vehicle_num' => $booking->vehicle_num,
            'type' => $booking->type,
            'is_paid' => 0,
            'checkin_time' => $booking->checkin_time,
            'checkout_time' => '',
            'amount' => 0,
            'payment_id' => 'Paid Vehicle',
            'attendant' => 1
        );
        try {
            if (!$this->key) {
                $error_log = new LogController();
                $error_log->log_create('import-key', 'custom: Import key not found');
                return FALSE;
            }
            $Key = $this->key;
            $http = new Client();
            $response = $http->post($this->url . '/api/store-booking-info', [
                'form_params' => [
                    'token' => $Key,
                    'data' => $dataArray
                ],
                'connect_timeout' => 5
            ]);
            $responseData = json_decode((string) $response->getBody(), true);
            if (is_array($responseData) && array_key_exists('booking_info_live_id', $responseData['data'])) {
                $booking->live_id = $responseData['data']['booking_info_live_id'];
                $booking->save();
            }
            if (is_array($responseData) && array_key_exists('booking_payment_live_id', $responseData['data'])) {
                $booking_payment = $booking->booking_payments;
                if ($booking_payment) {
                    $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                    $booking_payment->save();
                }
            }
        } catch (Exception $ex) {
            $this->logCreate->log_create('push_booking_cloud', $ex->getMessage() . '-' . $ex->getFile() . '-' . $ex->getLine(), $ex->getTraceAsString());
        }
    }
    public function set_booking_entry($booking, $device)
    {

        $attendants = [];
        $attendant = Attendants::where('booking_id', $booking->id)->first();
        if (!$attendant) {
            $attendant = new Attendants();
        }
        $attendant->booking_id = $booking->id;
        $attendant->save();
        $attendant_id = $attendant->id;
        $attendants = array();
        $existing_checked_in_bookings = Bookings::whereHas('attendant_transactions', function ($query) {
            $query->whereNull('check_out');
        })
            ->where('vehicle_num', $booking->vehicle_num)->get();
        if ($existing_checked_in_bookings) {
            foreach ($existing_checked_in_bookings as $booking_close) {
                $attendants[] = $booking_close->attendants->id;
                if ($booking_close->checkout_time == null) {
                    $booking_close->checkout_time = date('Y-m-d H:i:s');
                    $booking_close->save();
                }
            }
            $setCheckOutForExistingBookings = AttendantTransactions::whereIn('attendant_id', $attendants)
                ->whereNull('check_out')->update(['check_out' => date('Y-m-d H:i:s')]);
        }
        $attendant_transaction = new AttendantTransactions();
        $attendant_transaction->attendant_id = $attendant_id;
        $attendant_transaction->check_in = date('Y-m-d H:i:s');
        $attendant_transaction->in_going_device_id = $device->id;
        $attendant_transaction->save();
        $this->update_transaction_table($device, $attendant_transaction->id, 'in', $booking);
        Session::forget('vehicle_image');
        DeviceBookings::where('device_id', $device->id)->update(array('is_operator' => 0));
        $this->update_booking_from_temporary_booking($device, $booking);
        try {
            if (!$this->key) {
                $this->logCreate->log_create('import-key', 'custom: Import key not found');
                return FALSE;
            }
            $Key = $this->key;
            $dataArray = array(
                'booking_info_live_id' => $booking->live_id,
                'checkin_time' => date('Y-m-d H:i:s'),
                'vehicle_num' => $booking->vehicle_num,
                'attendant' => 1
            );
            $http = new Client();
            $response = $http->post($this->url . '/api/store-booking-info', [
                'form_params' => [
                    'token' => $Key,
                    'data' => $dataArray
                ],
                'connect_timeout' => 5
            ]);
            $responseData = json_decode((string) $response->getBody(), true);
            return $responseData;
        } catch (Exception $ex) {
            $this->logCreate->log_create('set_booking_entry', $ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile(), $ex->getTraceAsString());
        }
        return TRUE;
    }
    public function set_vehicle_booking_checkout($vehicle_booking, $booking, $status)
    {
        try {
            if ($status == 'out') {
                $valid_bookings_types = array(1, 2, 3, 4, 7);
                $booking_details = Bookings::where([
                    ['live_id', $booking]
                ])
                    ->whereIn('type', $valid_bookings_types)
                    ->first();
                $promoCode = NULL;
                if ($booking_details) {
                    if ($booking_details->type == 4) {
                        $promoCode = $booking_details->promo_code;
                        if ($booking_details->promo_code != NULL) {
                            $promo = Promo::where('code', $booking_details->promo_code)->first();
                            if ($promo) {
                                if ($promo->end_date != Null && $promo->coupon_number_limit == Null) {
                                    if (strtotime($promo->end_date) < strtotime(date("Y-m-d h:i:s"))) {
                                        return $vehicle_booking;
                                    } else if (strtotime($promo->start_date) > strtotime(date("Y-m-d h:i:s"))) {
                                        return $vehicle_booking;
                                    }
                                } else if ($promo->end_date == Null && $promo->coupon_number_limit != Null) {
                                    if ($promo->coupon_number_limit <= $promo->coupon_used) {
                                        return $vehicle_booking;
                                    }
                                }
                            }
                        }
                    }
                    $vehicle_booking_details = Bookings::find($vehicle_booking->id);
                    if ($vehicle_booking_details) {
                        $data = array(
                            'booking_info_live_id' => $vehicle_booking_details->live_id,
                            'is_paid' => 1,
                            'checkout_time' => date('Y-m-d H:i:s'),
                            'promo_code' => $promoCode,
                            'attendant' => 1
                        );
                        if (!$this->key) {
                            $this->logCreate->log_create('import-key', 'custom: Import key not found');
                            return FALSE;
                        }
                        $Key = $this->key;
                        $http = new Client();
                        $response = $http->post($this->url . '/api/store-booking-info', [
                            'form_params' => [
                                'token' => $Key,
                                'data' => $data
                            ],
                        ]);
                        $responseData = json_decode((string) $response->getBody(), true);
                        if (array_key_exists('booking_info_live_id', $responseData['data'])) {
                            $vehicle_booking_details->live_id = $responseData['data']['booking_info_live_id'];
                        }
                        $vehicle_booking_details->checkout_time = $data['checkout_time'];
                        $vehicle_booking_details->is_paid = $data['is_paid'];
                        if ($booking_details->type == 4 && $data['promo_code'] != NULL) {
                            $vehicle_booking_details->promo_code = $data['promo_code'];
                        }
                        $vehicle_booking_details->save();

                        $booking_payment = BookingPayments::where('booking_id', $vehicle_booking_details->id)->first();
                        if ($booking_payment) {
                            if (array_key_exists('booking_payment_live_id', $responseData['data'])) {
                                $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                            }
                            $booking_payment->checkout_time = $data['checkout_time'];
                            $booking_payment->save();
                        }

                        $vehicle_booking->checkout_time = $vehicle_booking_details->checkout_time;
                        $vehicle_booking->is_paid = $vehicle_booking_details->is_paid;
                        if ($vehicle_booking_details->promo_code != NULL) {
                            $vehicle_booking->promo_code = $vehicle_booking_details->promo_code;
                        }

                        return $vehicle_booking;
                    }
                    return $vehicle_booking;
                }
                return $vehicle_booking;
            } else {
                return $vehicle_booking;
            }
        } catch (Exception $ex) {
            $this->logCreate->log_create('get_vehicle_booking', $ex->getMessage(), $ex->getTraceAsString());
            return $vehicle_booking;
        }
    }
    public function set_booking_exit($booking_details, $device)
    {
        if (empty($booking_details->checkout_time)) {
            $booking_details->checkout_time = date('Y-m-d H:i:s');
            $booking_details->save();
        }
        $attendant = Attendants::where('booking_id', $booking_details->id)->first();
        if (!$attendant) {
            $attendant = new Attendants();
        }
        $attendant->booking_id = $booking_details->id;
        $attendant->save();
        $attendant_id = $attendant->id;
        $attendant_transaction = AttendantTransactions::where('attendant_id', $attendant_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$attendant_transaction) {
            $attendant_transaction = new AttendantTransactions();
            $attendant_transaction->attendant_id = $attendant_id;
            $attendant_transaction->check_in = date('Y-m-d H:i:s');
            $attendant_transaction->check_out = date('Y-m-d H:i:s');
            $attendant_transaction->out_going_device_id = $device->id;
            $attendant_transaction->save();
        } else {
            $attendant_transaction->check_out = date('Y-m-d H:i:s');
            $attendant_transaction->out_going_device_id = $device->id;
            $attendant_transaction->save();
        }
        $this->update_transaction_table($device, $attendant_transaction->id, 'out', $booking_details);
        Session::forget('vehicle_image');
        DeviceBookings::where('device_id', $device->id)->update(array('is_operator' => 0));
        $this->update_booking_from_temporary_booking($device, $booking_details);
        $attendants = array();
        $existing_checked_in_bookings = Bookings::whereHas('attendant_transactions', function ($query) {
            $query->whereNull('check_out');
        })
            ->where('vehicle_num', $booking_details->vehicle_num)->get();
        foreach ($existing_checked_in_bookings as $booking_close) {
            $attendants[] = $booking_close->attendants->id;
            if ($booking_close->checkout_time == null) {
                $booking_close->checkout_time = date('Y-m-d H:i:s');
                $booking_close->save();
            }
        }
        AttendantTransactions::whereIn('attendant_id', $attendants)
            ->whereNull('check_out')
            ->update(['check_out' => date('Y-m-d H:i:s')]);
        if ($booking_details->live_id > 0) {
            try {
                if (!$this->key) {
                    $this->logCreate->log_create('import-key', 'custom: Import key not found');
                    return FALSE;
                }
                $Key = $this->key;
                $dataArray = array(
                    'booking_info_live_id' => $booking_details->live_id,
                    'checkout_time' => date('Y-m-d H:i:s'),
                    'vehicle_num' => $booking_details->vehicle_num,
                    'attendant' => 1
                );
                $http = new Client();
                $response = $http->post($this->url . '/api/store-booking-info', [
                    'form_params' => [
                        'token' => $Key,
                        'data' => $dataArray
                    ],
                    'connect_timeout' => 5
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
            } catch (Exception $ex) {
                $this->logCreate->log_create('set_booking_exit', $ex->getMessage(), $ex->getTraceAsString());
            }
        }
        return TRUE;
    }
    public function update_booking_from_temporary_booking($device, $booking)
    {
        $related_plate_reader = $device->device_ticket_reader;
        if (!$related_plate_reader) {
            return FALSE;
        }
        $low_confidence = 0;
        $temporary_booking = DeviceBookings::where('device_id', $device->id)->where('booking_id', $booking->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$temporary_booking) {
            $temporary_booking = DeviceBookings::where('device_id', $device->id)->where('vehicle_num', $booking->vehicle_num)
                ->orderBy('created_at', 'DESC')
                ->first();
            if (!$temporary_booking) {
                return FALSE;
            }
        }
        if (!empty($device->confidence)) {
            if ($temporary_booking->confidence < $device->confidence) {
                $low_confidence = 1;
            }
        }
        $booking->confidence = $temporary_booking->confidence;
        $booking->low_confidence = $low_confidence;
        $booking->country_code = $temporary_booking->country_code;
        $booking->image_path = $temporary_booking->file_path;
        $booking->save();
        //\App\DeviceBookings::where('device_id', $related_plate_reader_id)->where('booking_id', $booking->id)->delete();
        DeviceBookings::where('device_id', $device->id)
            ->where('is_operator', '1')
            ->delete();

        return TRUE;
    }

    public function update_transaction_table($device, $attendant_id, $status, $bookingDetails = null)
    {

        $related_plate_reader = null;
        $related_ticket_reader = null;
        // related plate reader in case if ticket reader has related plated reader vice versa. 
        switch ($device->available_device_id) {
            case 1:
                $related_plate_reader = $device->related_plate_reader;
                if ($related_plate_reader) {
                    $transaction_images = $this->saveDeviceTransaction($related_plate_reader, $bookingDetails, $attendant_id, $status);
                }
                break;
            case 2:
                $transaction_images = $this->saveDeviceTransaction($device, $bookingDetails, $attendant_id, $status);
                break;
            case 3:
                $related_ticket_reader = $device->device_ticket_reader;
                $transaction_images = $this->saveDeviceTransaction($device, $bookingDetails, $attendant_id, $status);
                break;
            default:
                $transaction_images = $this->saveDeviceTransaction($device, $bookingDetails, $attendant_id, $status);
                break;
        }
        if (Session::has('open_gate_reason')) {
            $open_gate_reason = Session::get('open_gate_reason');
            $manual_open_gate = new OpenGateManualTransaction();
            $manual_open_gate->transaction_images_id = $transaction_images->id;
            $manual_open_gate->attendant_transaction_id = $attendant_id;
            if ($open_gate_reason == 'always_access') {
                $manual_open_gate->reason = 'Always Access';
                $manual_open_gate->user_id = $device->user_id ?: 1;
                $manual_open_gate->type = 'AA';
            } else {
                $manual_open_gate->reason = $open_gate_reason;
                $manual_open_gate->user_id = Auth::id();
            }
            $manual_open_gate->location_device_id = $device->id;
            $manual_open_gate->save();

            Session::forget('open_gate_reason');
        }
    }
    public function createEmergencyBooking($vehicle, $device_id)
    {
        try {
            $dataArray = array(
                'first_name' => 'Paid Vehicle',
                'vehicle_num' => $vehicle,
                'type' => 8,
                'is_paid' => 0,
                'checkin_time' => date('Y-m-d H:i:s'),
                'amount' => 0,
                'payment_id' => 'Paid Vehicle',
                'attendant' => 1
            );
            $booking = new Bookings();
            $booking->type = $dataArray['type'];
            $booking->first_name = $dataArray['first_name'];
            $booking->vehicle_num = $dataArray['vehicle_num'];
            $booking->checkin_time = $dataArray['checkin_time'];
            $booking->is_paid = $dataArray['is_paid'];
            $booking->save();
            $bookingId = $booking->id;
            $booking_payment = new BookingPayments();
            $booking_payment->booking_id = $bookingId;
            $booking_payment->amount = $dataArray['amount'];
            $booking_payment->payment_id = $dataArray['payment_id'];
            $booking_payment->checkin_time = $dataArray['checkin_time'];
            $booking_payment->save();
            return $booking;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    public function user_arrival_notification($vehicle_booking, $status)
    {
        try {
            $type = NULL;
            $type_id = NULL;
            if (!$vehicle_booking->customer_vehicle_info_id) {
                return false;
            }
            $customer_vehicle_info = \App\CustomerVehicleInfo::find($vehicle_booking->customer_vehicle_info_id);
            if (!$customer_vehicle_info) {
                return FALSE;
            }
            if ($customer_vehicle_info->userlist_user_id != NULL) {
                $type = 'user_list';
                $type_id = $customer_vehicle_info->userlist_user_id;
            } else {
                $type = 'customer';
                $type_id = $customer_vehicle_info->customer_id;
            }
            $vehicle = $customer_vehicle_info->num_plate;
            if ($vehicle_booking->vehicle_num) {
                $vehicle = $vehicle_booking->vehicle_num;
            }
            $in_out_notification = new \App\InOutNotification();
            if ($type) {
                $in_out_notification->type = $type;
            }
            if ($type_id) {
                $in_out_notification->type_id = $type_id;
            }
            $in_out_notification->type_id = $type_id;
            $in_out_notification->status = $status;
            $in_out_notification->vehicle_no = $vehicle;
            $in_out_notification->checkin_time = $vehicle_booking->checkin_time;
            $in_out_notification->checkout_time = $vehicle_booking->checkout_time;
            $in_out_notification->save();
            return TRUE;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }
    public function saveDeviceTransaction($device, $booking, $attendant_id, $status)
    {

        $twoMinutesAgo = Carbon::now()->subMinutes(2);
        $temporary_booking = False;
        $temporary_booking = DeviceBookings::where('device_id', $device->id)
            //->where('vehicle_num', $booking->vehicle_num)
            ->where('booking_id',$booking->id)
            ->whereNotNull('file_path')
            ->orderBy('created_at', 'DESC')
            ->first();
        if (!$temporary_booking) {
            $temporary_booking = DeviceBookings::withTrashed()->where('device_id', $device->id)->where('confidence', '<', 80)
                ->where('created_at', '>', $twoMinutesAgo->toDateTimeString())
                ->first();
            if (!$temporary_booking) {
                return false;
            }
        }
        $transaction_images = new TransactionImages();
        $transaction_images->image_path = $temporary_booking->file_path;
        $transaction_images->device_id = $temporary_booking->device_id;
        $transaction_images->transaction_id = $attendant_id;
        $transaction_images->type = $status;
        $transaction_images->save();

       // $temporary_booking->booking_id = $booking->id;
       // $temporary_booking->save();
        return $transaction_images;
    }
    public function syncBookingToCloud($booking_details)
    {
        $Key = base64_encode($locationId . '_' . $user_id);
        $http = new Client();
        $response = $http->post($this->url . '/api/store-booking-info', [
            'form_params' => [
                'token' => $Key,
                'data' => $booking_details
            ],
        ]);
        $responseData = json_decode((string) $response->getBody(), true);
        if ($responseData['success'] && count($responseData['data']) > 0) {
            if (array_key_exists('booking_info_live_id', $responseData['data'])) {
                $booking_details = Bookings::find($booking_details->id);

                $booking_details->live_id = $responseData['data']['booking_info_live_id'];
                $booking_details->save();
            }
            if (array_key_exists('booking_payment_live_id', $responseData['data'])) {
                $booking_payments = new BookingPayments();
                $booking_payments->live_id = $responseData['data']['booking_payment_live_id'];
                $booking_payments->booking_id = $booking_details->id;
                $booking_payments->amount = 0;
                $booking_payments->save();
            } else {
                $booking_payments = new BookingPayments();
                $booking_payments->live_id = 0;
                $booking_payments->booking_id = $booking_details->id;
                $booking_payments->amount = 0;
                $booking_payments->save();
            }
        }
        return $responseData;
    }
    public function saveVipBarcodeTransaction($booking, $vipBarcode)
    {
        $booking->barcode = $vipBarcode->id;
        $booking->is_paid = 1;
        //$booking_details->checkout_time = date('Y-m-d H:i:s');
        $booking->save();
    }
    public function isbookingPaid($booking)
    {
        if ($booking->is_paid) {
            return array(
                'status' => 1,
                'message' => 'Thanks',
            );
        }
        return array(
            'status' => 0,
            'message' => $this->ticketReader->getMessage('goto_nearby_payment_terminal', '', $this->lang_id)
        );
    }
    private function notificationTime($notificationTime)
    {
        return (!($notificationTime->checkin_time <= date('Y-m-d H:i') && $notificationTime->checkout_time > date('Y-m-d H:i')));
    }
    private function isNotUserlistOrPromo($type)
    {
        $this->logService->info($this->sessionId, 'Type of barcode is ' . $type . ' against emailnotification ', false);
        return ($type !== "user_list" && $type !== "promo");
    }
    private function logError($functionName, $ex)
    {
        $errorLog = new LogController(); // Replace with your log controller
        $errorLog->log_create($functionName, $ex->getMessage() . '-' . $ex->getLine(), $ex->getTraceAsString());
    }
    private function isInteger($number)
    {
        if (is_numeric($number)) {
            return true;
        } else {
            return false;
        }
    }
}
