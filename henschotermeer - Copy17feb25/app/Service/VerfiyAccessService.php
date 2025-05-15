<?php

namespace App\Service;

// Include Models
use App\ParkingRulesName;
use App\UnwantedCharacter;
use App\LocationDevices;
use App\DeviceTicketReaders;
use App\Bookings;
use App\UserlistUsers;
use App\LocationTimings;
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
use DB;

//use App\Http\Controllers\AccessCheckController;
// Include Other Class
use App\Http\Controllers\Settings\LocationSettings;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\LogController;
use App\Http\Controllers\Connection\Client;
use App\ParkingAccessRule;
use Carbon\Carbon;

class VerfiyAccessService
{
    public $url;
    public $setting;
    public $ticketReader;
    public $locationSetting;
    public $locationCreatedAt;
    public $lang_id = FALSE;
    public $disableRules = null;
    public $parkingRules = null;
    public $matching = false;
    public $comfortSecurity = false;
    public $ruleStatus = null;
    public $logCreate = false;
    public $validDeviceSettings = false;
    public $vehicleNumber = null;
    public $validBookingTypes = array(1, 4, 7, 10);
    public $userListStatuses = array(2, 3);
    public $ticketStatuses = array(6, 11);
    public $validBooking = null;
    public $deviceId = null;
    public $plateCorrection = false;
    public $confidenceStatus = false;
    public $attendant_transactions = null;
    public $accessController;
    public $barcode = null;
    public $verifyVehicle = false;
    public $ticketVehicleNumber = false;
    public $isNullOrEmptyOrShort = false;
    public $customer = false;
    public $emailNotification = false;
    public $isNotuserListAndPromo = false;
    public $vipBarCode = false;
    public $booking = false;
    public $key = false;
    public $bookingId = false;
    public $logService = false;
    public $request = false;
    public $testingSessionId = false;
    public $testingRules = false;
    public $status = [];
    public $not_apply = "not_applicable";
    public $access = "success";
    public $denied = "denied";
    public $data = array();
    public function __construct($url, $ticketReader, $setting, $verifyVehicle, $locationSetting, $locationCreatedAt, $logService, $testingSessionId)
    {
        $this->url = $url;
        $this->ticketReader = $ticketReader;
        $this->setting = $setting;
        $this->locationSetting = $locationSetting;
        $this->locationCreatedAt = $locationCreatedAt;
        $this->verifyVehicle = $verifyVehicle;
        $this->disableRules = ParkingRulesName::whereHas('access', function ($query) {
            $query->where('enable', 0);
        })->whereIn('slug', ['comfort_security_check', 'has_always_access', 'pre_booking'])->orderBy('rule_sorting', 'ASC')->pluck('id')->all();
        $this->parkingRules  = ParkingRulesName::whereHas('access', function ($query) {
            $query->where('enable', 1)->where('slug', '!=', 'matching_enable');
        })->whereNull('is_imported')->orWhere(function ($query) {
            $query->whereIn('id', $this->disableRules);
        })->orderBy('rule_sorting', 'ASC')->get();
        $this->matching = $this->matchEnable($request = false);
        $this->comfortSecurity = $this->comfortSecurityCheckEnable();
        $this->logCreate = new LogController();
        $this->logService = $logService;
        $this->testingSessionId = $testingSessionId;
        $this->booking = new BookingService($this->url, $this->matching, $this->locationSetting, $this->ticketReader, $this->setting, $this->logService, $this->testingSessionId);
    }
    public function isValidBooking($request, $key, $deviceId, $vehiceNumber = null, $barcode = null, $ticketVehicleNumber = null, $lang_id = null, $confidenceStatus = false, $plateCorrection = false, $isNullOrEmptyOrShort = false)
    {
        $this->request = $request;
        if (isset($this->request->regression_testing)) {
            $this->testingRules = $this->testingParkingRules($this->request);
            $this->matching = $this->matchEnable($request);
        }
        $this->key = $key;
        $this->deviceId = $deviceId;
        $this->vehicleNumber = $vehiceNumber;
        $this->barcode = $barcode;
        $this->ticketVehicleNumber = $ticketVehicleNumber;
        $this->lang_id = $lang_id;
        $this->confidenceStatus = $confidenceStatus;
        $this->plateCorrection = $plateCorrection;
        $this->isNullOrEmptyOrShort = $isNullOrEmptyOrShort;
        if ($this->vehicleNumber) {
            $this->logService->info($this->testingSessionId, 'System is trying to find a valid booking against (' . $this->vehicleNumber . ')', false);
        } elseif ($this->barcode) {
            $this->logService->info($this->testingSessionId, 'System is trying to find a valid booking against (' . $this->barcode . ')', false);
        }
        switch ($this->validDeviceSettings['available_device_id']) {
            case 1:
                $this->validBooking = $this->booking->getParkingBooking($request, $this->key, $this->validDeviceSettings, $this->barcode, $this->ticketVehicleNumber, $this->isNullOrEmptyOrShort);
                if ($this->validBooking) {
                    $name = $this->validBooking->name ? $this->validBooking->name : 'N/A';
                    $this->logService->info($this->testingSessionId, 'Booking found with name (' . $name . ') and plate number is (' . $this->validBooking->vehicle_num . ').', false);
                    $this->logService->info($this->testingSessionId, 'Booking id is (' . $this->validBooking->id . ')', false);
                    $this->logService->info($this->testingSessionId, 'Booking check in time is (' . $this->validBooking->checkin_time . ')', false);
                    $this->logService->info($this->testingSessionId, 'Booking check out time is (' . $this->validBooking->checkout_time . ')', false);
                    return $this->validBooking;
                }
                break;
            case 2:
                $this->validBooking = $this->booking->getPersonBooking($request, $this->key, $this->validDeviceSettings, $this->barcode);
                if ($this->validBooking) {
                    $name = $this->validBooking->name ? $this->validBooking->name : 'N/A';
                    $this->logService->info($this->testingSessionId, 'Booking found with name (' . $name . ') and plate number is (' . $this->validBooking->vehicle_num . ').', false);
                    $this->logService->info($this->testingSessionId, 'Booking id is (' . $this->validBooking->id . ')', false);
                    $this->logService->info($this->testingSessionId, 'Booking check in time is (' . $this->validBooking->checkin_time . ')', false);
                    $this->logService->info($this->testingSessionId, 'Booking check out time is (' . $this->validBooking->checkout_time . ')', false);
                    return $this->validBooking;
                }
                break;
            case 3:
                $this->validBooking = $this->booking->getVehicleBooking($request, $this->key, $this->validDeviceSettings, $this->vehicleNumber);
                return $this->validBooking;
                break;
            default:
                return FALSE;
        }
    }
    public function validateRule()
    {
        $this->bookingAccess();
        $rules = false;
        $vehiceNumber = $this->validDeviceSettings['available_device_id'] == 3 ? $this->vehicleNumber : $this->ticketVehicleNumber;
        if ($this->testingRules) {
            $rules = $this->testingRules;
        } else {
            $rules = $this->parkingRules;
        }
        foreach ($rules  as $rule) {
            $this->logService->info($this->testingSessionId, $rule->name, $rule->id, false);
            switch ($rule->slug) {
                case "blocked":
                    if ($this->validDeviceSettings['available_device_id'] == 1 && ($this->validBooking)) {
                        $response = $this->isVehicleBlocked($this->validBooking['vehicle_num'], $rule);
                        $this->ruleStatus[$rule->slug] = $response;
                        if ($this->checkRuleResponse($response)) {
                            return $this->ruleStatus;
                            break;
                        }
                        break;
                    } elseif (in_array($this->validDeviceSettings['available_device_id'], [1, 3]) && isset($vehiceNumber)) {
                        $response = $this->isVehicleBlocked($vehiceNumber, $rule);
                        $this->ruleStatus[$rule->slug] = $response;
                        if ($this->checkRuleResponse($response)) {
                            return $this->ruleStatus;
                            break;
                        }
                        break;
                    } elseif ($this->validDeviceSettings['available_device_id'] == 2) {
                        $response = $this->isPersonBlocked($this->barcode, $rule);
                        $this->ruleStatus[$rule->slug] = $response;
                        if ($this->checkRuleResponse($response)) {
                            return $this->ruleStatus;
                            break;
                        }
                        break;
                    } else {
                        $this->logService->info($this->testingSessionId, 'This Rule is not Applicable becuase given plate number is empty or null', $rule->id, $this->not_apply, false);
                        $data = array(
                            'status' => $this->not_apply,
                            $rule->slug => false
                        );
                        $this->ruleStatus[$rule->slug] = $data;
                    }
                    break;
                case 'parking_hours':
                    $response = $this->parkingHours($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'full_zone':
                    $response = $this->checkFullZone($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'has_always_access':
                    $response = $this->hasAlwasyAccess($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'license_plate_no_recognize':
                    $response = $this->licensePlateNoRecognize($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    break;
                case 'license_plate_not_issued':
                    $response = $this->licensePlateNotIssued($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    break;
                case 'vehicle_unknown':
                    $response = $this->checkUnkownVehicle($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    break;
                case 'exceeded_max_limit':
                    $response = $this->exceedMaxLimit($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'valid_subscription':
                    $response = $this->validSubscription($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    break;
                case 'drive_out_too_late':
                    $response = $this->checkLateDriveBooking($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'drive_in_too_fast':
                    $response = $this->checkFastDriveInBooking();
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'zone_full_for_category':
                    $response = $this->checkCategoryZone($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'too_many_vehicle_per_contact':
                    $response = $this->checkContactVehicle($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'max_number_runin_exceeded':
                    $response = $this->checkMaxLimit($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'user_access_right_and_group':
                    $response = $this->userAccessRightAndGroup($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'plate_entry_exit_difference':
                    $response = $this->checkPlateDifference($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'ticket_no_entry_exit_difference':
                    $response = $this->checkTicketDifference($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'comfort_security_check':
                    $response = $this->comfortSecurity($rule);
                    $data = array(
                        $rule->slug => $response,
                        'status' => $this->comfortSecurity ? "comfort" : "security"
                    );
                    $this->ruleStatus[$rule->slug] = $data;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'ticket_free_ride':
                    $response = $this->checkFreeRide($this->validBooking);
                    $this->ruleStatus[$rule->slug] = $response;
                    break;
                case 'pre_booking':
                    $response = $this->preBooking($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'post_booking':
                    $response = $this->postBooking($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    break;
                case 'valid_anti_passback':
                    $response = $this->validateAntiPassBack($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'emergency_entry_exit':
                    $response = $this->emergencyEntryExit($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                case 'multiple_subscription':
                    $response = $this->multipleSubscription($rule);
                    $this->ruleStatus[$rule->slug] = $response;
                    if ($this->checkRuleResponse($response)) {
                        return $this->ruleStatus;
                        break;
                    }
                    break;
                default:
                    $message = $this->notActiveRule();
                    $this->ruleStatus[$rule->slug] = $message;
                    break;
            }
        }
        return $this->ruleStatus;
    }
    private function isVehicleBlocked($vehicle_num, $rule)
    {
        $vehicle_blocked = FALSE;
        if (!isset($vehicle_num)) {
            $this->logService->info($this->testingSessionId, 'Rule (' . $rule->name . ') is not applicable beacuse plate number is empty', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if (!in_array($this->validDeviceSettings['available_device_id'], [1, 3])) {
            $this->logService->info($this->testingSessionId, 'Rule (' . $rule->name . ') is not applicable beacuse device is not a plate reader', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        $userlist_users = UserlistUsers::whereHas('customer_vehicle_info', function ($query) use ($vehicle_num) {
            $query->where('num_plate', $vehicle_num);
        })->where('is_blocked', 1)->first();
        if ($userlist_users) {
            $vehicle_blocked = TRUE;
            $message = $this->ticketReader->getMessage('user_blocked', '', $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'user_blocked'));
            $this->logService->info($this->testingSessionId, 'Given Plate Number is Blocked ' . $this->vehicleNumber, $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Denied against this plate number ' . $vehicle_num, $rule->id, $this->denied, false);
            $data = array(
                'status' => $this->denied,
                $rule->slug => $vehicle_blocked
            );
            return $data;
        }
        $day_ticket_vehicle = Bookings::where('vehicle_num', $vehicle_num)->where('is_blocked', 1)->first();
        if ($day_ticket_vehicle) {
            $vehicle_blocked = TRUE;
            $message = $this->ticketReader->getMessage('user_blocked', '', $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'user_blocked'));
            $this->logService->info($this->testingSessionId, 'Given Plate Number is Blocked ' . $this->vehicleNumber, $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Denied against this plate number ' . $vehicle_num, $rule->id, $this->denied, false);
            $data = array(
                'status' => $this->denied,
                $rule->slug => $vehicle_blocked
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Incoming plate number  is not Blocked ' . $vehicle_num, $rule->id, false);
        $this->logService->info($this->testingSessionId, 'Access granted against this plate number ' . $vehicle_num, $rule->id, $this->access, false);
        $data = array(
            'status' => $this->access,
            $rule->slug => $vehicle_blocked
        );
        return $data;
    }
    private function isPersonBlocked($barcode, $rule)
    {
        $person_blocked = FALSE;
        if (!isset($barcode)) {
            $this->logService->info($this->testingSessionId, 'Rule (' . $rule->name . ') is not applicable beacuse plate number is empty', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        $person_day_ticket = Bookings::whereIn('type', [6, 11])->where(function ($query) use ($barcode) {
            $query->where('id', $barcode)
                ->orWhere('live_id', $barcode);
        })->where('is_blocked', 1)->first();
        if ($person_day_ticket) {
            $person_blocked = TRUE;
            $message = $this->ticketReader->getMessage('user_blocked', '', $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'user_blocked'));
            $this->logService->info($this->testingSessionId, 'Given Barcode Number is Blocked ' . $this->barcode, $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Denied against this barcode number ' . $barcode, $rule->id, $this->denied, false);
            $data = array(
                'status' => $this->denied,
                $rule->slug => $person_blocked
            );
            return $data;
        }
        return $person_blocked;
    }
    private function parkingHours($rule)
    {
        $parkingHours = false;
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'This device direction is out so the rule (' . $rule->name . '). is not applicable.', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        $location_timings = FALSE;
        if ($this->validDeviceSettings['available_device_id'] == 2) {
            $location_timings = $this->getTodayPersonTimings();
        } else {
            $location_timings = $this->getTodayParkingHours();
        }
        if (!empty($location_timings->opening_time) && !empty($location_timings->closing_time)) {
            $now = date('H:i');
            $start = date('H:i', strtotime($location_timings->opening_time));
            $end = date('H:i', strtotime($location_timings->closing_time));
            if ($now >= $start && $now <= $end) {
                $this->logService->info($this->testingSessionId, 'Booking is on valid Parking Time ', $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access granted against this booking ', $rule->id, $this->access, false);
                $data = array(
                    'status' => $this->access,
                    $rule->slug => false
                );
                return $data;
            }
            if ($now < $start) {
                $parkingHours = true;
                $message = $this->ticketReader->getMessage('too_early', '', $this->lang_id);
                $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'too_early'));
                $this->logService->info($this->testingSessionId, 'Parking station is not open yet.', $rule->id, $this->denied, false);
                $this->logService->info($this->testingSessionId, 'Access denied.', $rule->id, false);
                $data = array(
                    'status' => $this->denied,
                    $rule->slug => $parkingHours
                );
                return $data;
            }
            if ($now > $end) {
                $parkingHours = true;
                $message = $this->ticketReader->getMessage('too_late', '', $this->lang_id);
                $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'too_late'));
                $this->logService->info($this->testingSessionId, 'Parking station is closed.', $rule->id, $this->denied, false);
                $this->logService->info($this->testingSessionId, 'Access denied.', $rule->id, false);
                $data = array(
                    'status' => $this->denied,
                    $rule->slug => $parkingHours
                );
                return $data;
            }
        }
        $this->logService->info($this->testingSessionId, 'Parking timing slots are not found.', $rule->id, $this->not_apply, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => $parkingHours
        );
        return $data;
    }
    private function checkFullZone($rule)
    {
        $zoneFull = false;
        if ($this->ruleStatus['access']['access_status'] == "denied") {
            $this->logService->info($this->testingSessionId, 'As Access status is denied so this rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $zoneFull
            );
            return $data;
        }
        if (!in_array($this->validDeviceSettings['available_device_id'], array(1, 3))) {
            $this->logService->info($this->testingSessionId, 'Incoming device is ' . $this->validDeviceSettings['device_name'] . '. This Rule is not applicable against this device direction (' . $this->validDeviceSettings['device_direction'] . ')', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $zoneFull
            );
            return $data;
        }
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'Incoming device direction is ' . $this->validDeviceSettings['device_direction'] . '. This rule is not applicable when device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $zoneFull
            );
            return $data;
        }
        $totalParkingSpots = $this->locationSetting->total_spots;
        $this->logService->info($this->testingSessionId, 'No of available parking spots are ' . $totalParkingSpots, $rule->id, false);
        $type = [1, 2, 3, 4, 5];
        $on_location = AttendantTransactions::whereHas(
            'attendants.bookings',
            function ($query) use ($type) {
                $query->whereIn('type', $type)->whereNotNull('vehicle_num');
            }
        )->whereNull('check_out')
            ->count();
        $this->logService->info($this->testingSessionId, 'No of occupied parking spots are' . $on_location, $rule->id, false);
        $resul = $totalParkingSpots - $on_location;
        if ($on_location > $totalParkingSpots) {
            $zoneFull = true;
            $message = $this->ticketReader->getMessage('zone_full', '', $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'zone_full'));
            $this->logService->info($this->testingSessionId, 'On location, parking spots are greater than total parking spots', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access denied', $rule->id, $this->denied, false);
            $data = array(
                'status' => $this->denied,
                $rule->slug => $zoneFull
            );
            return $data;
        } else {
            $this->logService->info($this->testingSessionId, ' On location, parking spots are less than total parking spots', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, $this->access, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => $zoneFull
            );
            return $data;
        }
    }
    private function hasAlwasyAccess($rule)
    {
        $hasAlwayAtDevice = $this->checkAlwaysAccessAtDeviceLevel($this->validDeviceSettings);
        if (!$hasAlwayAtDevice) {
            $this->logService->info($this->testingSessionId, 'Always access is disable on device', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not Applicable', $rule->id, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if ($this->validDeviceSettings['available_device_id'] == 3) {
            $this->logService->info($this->testingSessionId, 'Checking for related ticket reader against given plate reader', $rule->id, false);
            $response = $this->hasRelatedTicketReader($this->validDeviceSettings, $rule);
            if (!$response) {
                $data = array(
                    'status' => $this->not_apply,
                    $rule->slug => false
                );
                return $data;
            }
            $message = $this->bookingMessage($this->validDeviceSettings['device_direction']);
            $key = $this->validDeviceSettings['device_direction'] == "in" ? "welcome_entrance" : "goodbye_exit";
            $this->accessMessage(1, 'success', $message, $this->setting->send_message_od($this->deviceId, $message, 'welcome_entrance'), $this->validBooking);
            $data = array(
                'status' => $this->access,
                $rule->slug => true
            );
            return $data;
        }
        if (in_array($this->validDeviceSettings['available_device_id'], array(1, 2))) {
            if ($this->validDeviceSettings['is_synched'] && $this->validDeviceSettings['barrier_status'] == 3) {
                if ($this->validBooking || $this->emailNotification) {
                    $key = $this->validDeviceSettings['available_device_id'] == 2 ? 'welcome_entrance_person' : 'welcome_entrance';
                    $user_name = $this->getUserName($this->validBooking);
                    $message = $this->ticketReader->getMessage($key, $user_name, $this->lang_id);
                    $this->accessMessage(1, 'success', $message, $this->setting->send_message_od($this->deviceId, $message, $key), $this->validBooking);
                    $this->logService->info($this->testingSessionId, 'Always access is enabled.', $rule->id, false);
                    $this->logService->info($this->testingSessionId, 'Access granted.', $rule->id, $this->access, false);
                    $data = array(
                        'status' => $this->access,
                        $rule->slug => true
                    );
                    return $data;
                }
            }
        }
        $this->logService->info($this->testingSessionId, $this->validDeviceSettings['device_name'] . ' has no alway access.', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'This rule is not Applicable', $rule->id, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => false
        );
        return $data;
    }
    private function licensePlateNoRecognize($rule)
    {
        $licensePlateNoRecognize = false;
        if ($this->validDeviceSettings['available_device_id'] != 3) {
            $this->logService->info($this->testingSessionId, 'Given device is' . $this->validDeviceSettings['device_name'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against this ' . $this->validDeviceSettings['device_name'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $licensePlateNoRecognize
            );
            return $data;
        }
        if ($this->plateCorrection) {
            $this->logService->info($this->testingSessionId, 'Plate correction has allowed  against related ticket reader.', $rule->id, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => true
            );
            return $data;
        }
        if (!$this->confidenceStatus && !$this->plateCorrection) {
            $this->logService->info($this->testingSessionId, 'Given confidence is less than thresh hold confidence and also plate correction is not enable', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against this ' . $this->validDeviceSettings['device_name'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Given confidence is greater than thresh hold confidence', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'This rule is not applicable against this ' . $this->validDeviceSettings['device_name'], $rule->id, $this->not_apply, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => false
        );
        return $data;
    }
    private function licensePlateNotIssued($rule)
    {
        $licensePlateNotIssued = false;
        if ($this->validDeviceSettings['available_device_id'] != 3) {
            $this->logService->info($this->testingSessionId, 'Given Device is ' . $this->validDeviceSettings['device_name'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against ' . $this->validDeviceSettings['device_name'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $licensePlateNotIssued
            );
            return $data;
        }
        if (!$this->validBooking || $this->validBooking == null) {
            $this->logService->info($this->testingSessionId, 'plate number is empty or booking not found Against this ' . $this->vehicleNumber, $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Denied against this ' . $this->validDeviceSettings['device_name'], $rule->id, $this->denied, false);
            $licensePlateNotIssued = true;
            $data = array(
                'status' => $this->denied,
                $rule->slug => $licensePlateNotIssued
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Valid booking found against this device ' . $this->validDeviceSettings['device_name'], $rule->id, false);
        $this->logService->info($this->testingSessionId, 'Access granted against this ' . $this->validDeviceSettings['device_name'], $rule->id, $this->access, false);
        $data = array(
            'status' => $this->access,
            $rule->slug => $licensePlateNotIssued
        );
        return $data;
    }
    private function checkUnkownVehicle($rule)
    {
        $checkUnknownVehicle = false;
        if ($this->validDeviceSettings['available_device_id'] != 3) {
            $this->logService->info($this->testingSessionId, 'Given device is ' . $this->validDeviceSettings['device_name'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against ' . $this->validDeviceSettings['device_name'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $checkUnknownVehicle
            );
            return $data;
        }
        if (!$this->validBooking && !$this->confidenceStatus) {
            $this->logService->info($this->testingSessionId, 'Booking is not found against this plate number ' . $this->vehicleNumber . ' and also device confidence is also less than threshold confidence.', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Denied against this ' . $this->validDeviceSettings['device_name'], $rule->id, $this->denied, false);
            $checkUnknownVehicle = true;
            $data = array(
                'status' => $this->denied,
                $rule->slug => $checkUnknownVehicle
            );
            return $data;
        }
        if ($this->validBooking && $this->confidenceStatus) {
            $this->logService->info($this->testingSessionId, 'Valid booking found against this device ' . $this->validDeviceSettings['device_name'] . ' and plate confidence is greater thresh hold.', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access granted ' . $this->validDeviceSettings['device_name'], $rule->id, $this->access, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => $checkUnknownVehicle
            );
            return $data;
        }
        if ($this->validBooking) {
            $this->logService->info($this->testingSessionId, 'Valid Booking found ', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => $checkUnknownVehicle
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Valid Booking not found ', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => $checkUnknownVehicle
        );
        return $data;
    }
    private function exceedMaxLimit($rule)
    {
        $exceedMaxLimit = false;
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against ' . $this->validDeviceSettings['device_direction'] . ' direction.', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $exceedMaxLimit
            );
            return $data;
        }
        if ($this->validDeviceSettings['available_device_id'] == 2) {
            $this->logService->info($this->testingSessionId, 'Given device is' . $this->validDeviceSettings['device_name'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against ' . $this->validDeviceSettings['device_name'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $exceedMaxLimit
            );
            return $data;
        }
        if (!$this->validBooking) {
            $this->logService->info($this->testingSessionId, 'Valid booking not Found', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $exceedMaxLimit
            );
            return $data;
        }
        $tommyBooking = $this->checkTommyBooking($this->validBooking);
        if (!$tommyBooking) {
            $this->logService->info($this->testingSessionId, 'Tommy user not found against this booking', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $exceedMaxLimit
            );
            return $data;
        }
        $bookingIds = Bookings::where('id', '<>', $this->validBooking['id'])->where('tommy_parent_id', $tommyBooking->id)->pluck('id');
        $this->logService->info($this->testingSessionId, 'No of booking are ' . count($bookingIds), $rule->id, false);
        $bookingCount = Bookings::whereHas('attendant_transactions', function ($query) {
            $query->whereNull('check_out');
        })->whereIn('id', $bookingIds)->where('id', '<>', $this->validBooking['id'])->count();
        if ($bookingCount >= $tommyBooking->total_vehicles) {
            $message = $this->ticketReader->getMessage('exceed_max_limit', '', $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'exceed_max_limit'));
            $this->logService->info($this->testingSessionId, 'No of checked in booking are greater allowed bookings.', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access denied ', $rule->id, $this->denied, false);
            $exceedMaxLimit = true;
            $data = array(
                'status' => $this->denied,
                $rule->slug => $exceedMaxLimit
            );
            return $data;;
        } else {
            $this->logService->info($this->testingSessionId, 'No of allowed bookings are available');
            $this->logService->info($this->testingSessionId, 'Access Granted ', $rule->id, $this->access, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => $exceedMaxLimit
            );
            return $data;
        }
    }
    private function validSubscription($rule)
    {

        $date = date('Y-12-31 23:59:59');
        if ($this->validBooking->checkout_time < date('Y-12-31 23:59:59')) {
            $this->logService->info($this->testingSessionId, 'Subscription is valid', $rule->id, $this->access, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => true
            );
            return $data;
        }
        return TRUE;
    }
    private function checkLateDriveBooking($rule)
    {
        $checkLateDriveBooking = false;
        if (!$this->validBooking) {
            $this->logService->info($this->testingSessionId, 'Valid booking is  not found', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $checkLateDriveBooking
            );
            return $data;
        }
        if ($this->validBooking->type == 5) {
            $this->logService->info($this->testingSessionId, 'Valid booking found but Booking type is 5.', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against booking type 5.', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $checkLateDriveBooking
            );
            return $data;
        }
        $now = date('Y-m-d H:i:s');
        if ($this->validBooking && $this->validDeviceSettings['device_direction'] == "out") {
            if ($now > $this->validBooking['checkout_time']) {
                $message = $this->ticketReader->getMessage('drive_out_late', '', $this->lang_id);
                $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'drive_out_late'));
                $this->logService->info($this->testingSessionId, $message, $rule->id);
                $this->logService->info($this->testingSessionId, $now . 'Check out time has been passed.' . $this->validBooking['checkout_time'], $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
                $checkLateDriveBooking = true;
                $data = array(
                    'status' => $this->denied,
                    $rule->slug => $checkLateDriveBooking
                );
                return $data;
            }
            $this->logService->info($this->testingSessionId, 'Checkout time is valid', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, $this->access, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => $checkLateDriveBooking
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, false);
        $this->logService->info($this->testingSessionId, 'This rule is not applicable when device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, $this->not_apply, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => $checkLateDriveBooking
        );
        return $data;
    }
    private function checkFastDriveInBooking()
    {
    }
    private function  checkCategoryZone($rule)
    {
        $categoryZoneFull = false;
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable when device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $categoryZoneFull
            );
            return $data;
        }
        $onLocationVehicle = AttendantTransactions::whereNull('check_out')->where('in_going_device_id', $this->validDeviceSettings['id'])->count();
        $this->logService->info($this->testingSessionId, 'No of in coming bookings are ' . $onLocationVehicle, $rule->id, false);
        $device = GroupDevices::with('group')->where('device_id', $this->validDeviceSettings['id'])->orderBy('created_at', 'DESC')->first();
        if ($device) {
            if ($device->group->group_max) {
                if ($onLocationVehicle >= $device->group->group_max) {
                    $message = $this->ticketReader->getMessage('category_zone', '', $this->lang_id);
                    $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'category_zone'));
                    $this->logService->info($this->testingSessionId, 'Allowed bookings against ' . $this->validDeviceSettings['device_name'] . ' are exceeded', $rule->id, false);
                    $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
                    $categoryZoneFull = true;
                    $data = array(
                        'status' => $this->denied,
                        $rule->slug => $categoryZoneFull
                    );
                    return $data;
                }
            }
        }
        $this->logService->info($this->testingSessionId, 'Group not found against this device', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'Rule not applicable against this booking', $rule->id, $this->not_apply, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => $categoryZoneFull
        );
        return $data;
    }
    private function checkContactVehicle($rule)
    {
        $type = false;
        $contactVehicle = false;
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable when device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $contactVehicle
            );
            return $data;
        }
        if (!$this->validBooking) {
            $this->logService->info($this->testingSessionId, 'Valid booking not Found', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $contactVehicle
            );
            return $data;
        }
        if (isset($this->validBooking->customer_id)) {
            $checkCars = $this->checkCustomerCar($this->validBooking->customer, $rule);
            return $checkCars;
        }
        if ($this->validBooking->type == 3) {
            return $this->checkUserCar($this->validBooking, $rule);
        }
        $this->logService->info($this->testingSessionId, 'No customer/userlist user found against booking', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => $contactVehicle
        );
        return $data;
    }
    private function checkMaxLimit($rule)
    {
        $checkMaxLimit = false;
        $count = false;
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable when device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $checkMaxLimit
            );
            return $data;
        }
        // based on ticket type which ticket type is associated with it.
        if (!$this->validBooking) {
            $this->logService->info($this->testingSessionId, 'Valid booking not Found', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $checkMaxLimit
            );
            return $data;
        }
        if ($this->validBooking['type'] == 3) {
            $this->logService->info($this->testingSessionId, 'Valid booking found but Booking type is 3.', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against booking type 3.', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $checkMaxLimit
            );
            return $data;
        }
        if (!isset($this->validBooking['product_id'])) {
            $this->logService->info($this->testingSessionId, 'As Valid Booking Type is ' . $this->validBooking['type'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This Rule is Not Applicable when booking type is ' . $this->validBooking['type'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $checkMaxLimit
            );
            return $data;
        }
        if (!$this->validBooking->attendants) {
            $this->logService->info($this->testingSessionId, 'Not Checked Out Transaction Found against this booking ', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable when booking checked in transaction found against', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $checkMaxLimit
            );
            return $data;
        }
        $ticketType = $this->getTicketType($this->validBooking['product_id']);
        $departureCount = AttendantTransactions::where('attendant_id', $this->validBooking->attendants->id)->Departure();
        $this->logService->info($this->testingSessionId, 'No of checked out transaction against this booking is ' . $departureCount, $rule->id, false);
        if ($ticketType) {
            if ($departureCount >= $ticketType->no_of_time) {
                $message = $this->ticketReader->getMessage('max_number_exceeded', '', $this->lang_id);
                $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'max_number_exceeded'));
                $this->logService->info($this->testingSessionId, 'No checked out transaction found against this booking are greater than max limit ', $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
                $checkMaxLimit = true;
                $data = array(
                    'status' => $this->denied,
                    $rule->slug => $checkMaxLimit
                );
                return $data;
            }
        }
        $this->logService->info($this->testingSessionId, 'no checked out transaction found against this booking are less than max limit ', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, $this->access, false);
        $data = array(
            'status' => $this->access,
            $rule->slug => $checkMaxLimit
        );
        return $data;
    }
    private function bookingOnLocation($rule)
    {
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        try {
            if ($this->validBooking['attendant_transactions'] && count($this->validBooking['attendant_transactions']) > 0) {
                $this->attendant_transactions = $this->validBooking['attendant_transactions'];
                foreach ($this->attendant_transactions as $attendant_trans) {

                    if ($attendant_trans->check_in != NULL && $attendant_trans->check_out != NULL) {
                        continue;
                    } elseif ($attendant_trans->check_in != NULL && $attendant_trans->check_out == NULL) {
                        $message = $this->ticketReader->getMessage('already_at_location', '', $this->lang_id);
                        $this->logService->info($this->testingSessionId, 'Booking is Already on Location', $rule->id, false);
                        $this->logService->info($this->testingSessionId, 'Access denied', $rule->id, false);
                        $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'already_at_location'));
                        $data = array(
                            'status' => $this->denied,
                            $rule->slug => true
                        );
                        return $data;
                    }
                    $this->logService->info($this->testingSessionId, 'Booking is not of Location', $rule->id, false);
                    $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, false);
                }
            }
            $this->logService->info($this->testingSessionId, 'Booking have no attendant transaction', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        } catch (Exception $ex) {
            return FALSE;
        }
    }
    private function checkPlateDifference($rule)
    {
        $platedifference = false;
        if ($this->validDeviceSettings['available_device_id'] == 2) {
            $this->logService->info($this->testingSessionId, 'As Device is ' . $this->validDeviceSettings['device_name'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable when device ' . $this->validDeviceSettings['device_name'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $platedifference
            );
            return $data;
        }
        if (!$this->validBooking) {
            $this->logService->info($this->testingSessionId, 'As valid booking not found', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $platedifference
            );
            return $data;
        }
        if ($this->validBooking && $this->validBooking->type == 5) {
            $this->logService->info($this->testingSessionId, 'Valid booking found but booking Type is 5', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against booking type 5', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $platedifference
            );
            return $data;
        }
        if ($this->isNullOrEmptyOrShort) {
            $this->logService->info($this->testingSessionId, 'Plate number may null short', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $platedifference
            );
            return $data;
        }
        if (!$this->ticketVehicleNumber) {
            $this->logService->info($this->testingSessionId, 'Plate number not found ', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This Rule is Not Applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $platedifference
            );
            return $data;
        }
        if ($this->validBooking['vehicle_num'] != $this->ticketVehicleNumber) {
            $message = $this->ticketReader->getMessage('registration_number_error', '', $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'registration_number_error'));
            $this->logService->info($this->testingSessionId, 'Incoming vehicle number is not equal to vehicle number against which booking found ', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
            $platedifference = true;
            $data = array(
                'status' => $this->denied,
                $rule->slug => $platedifference
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Plate number and ticket vehicle number are valid', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'Access Granted', $rule->id, $this->access, false);
        $data = array(
            'status' => $this->access,
            $rule->slug => $platedifference
        );
        return $data;
    }
    private function checkTicketDifference($rule)
    {
        $ticketDifference = false;
        if (!in_array($this->validDeviceSettings['available_device_id'], array(1, 2))) {
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against device ' . $this->validDeviceSettings['device_name'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $ticketDifference
            );
            return $data;
        }
        if (!$this->validBooking && $this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against direction ' . $this->validDeviceSettings['device_direction'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $ticketDifference
            );
            return $data;
        }
        if ($this->validDeviceSettings['device_direction'] != "out") {
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against direction ' . $this->validDeviceSettings['device_direction'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $ticketDifference
            );
            return $data;
        }
        if ($this->validBooking && $this->validBooking->type == 5) {
            $this->logService->info($this->testingSessionId, 'Valid booking found but booking Type is 5', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable against booking type 5', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $ticketDifference
            );
            return $data;
        }
        if (($this->validBooking['id'] != $this->barcode)) {
            $message = $this->ticketReader->getMessage('ticket_used', '', $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'ticket_used'));
            $this->logService->info($this->testingSessionId, 'Incoming barcode number is not equal to barcode number against which booking found ', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
            $ticketDifference = true;
            $data = array(
                'status' => $this->denied,
                $rule->slug => $ticketDifference
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Barcode number and ticket vehicle number are valid', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'Access Granted', $rule->id, $this->access, false);
        $data = array(
            'status' => $this->access,
            $rule->slug => $ticketDifference
        );
        return $data;
    }
    private function comfortSecurity($rule)
    {
        try {
            $comfortSecurityOnVehicleBooking = false;
            if ($this->validDeviceSettings['available_device_id'] == 3) {
                if (array_key_exists('booking_status', $this->ruleStatus)) {
                    $comfortSecurityOnVehicleBooking = $this->validBooking && (($this->ruleStatus['booking_status']['mode'] == "medium" && $this->validDeviceSettings['device_direction'] == "out") || ($this->ruleStatus['booking_status']['mode'] == "high" && ($this->validDeviceSettings['device_direction'] == "in" || $this->validDeviceSettings['device_direction'] == "out")));
                }
            }
            if ($this->comfortSecurity) {
                return $this->verifyComfort($comfortSecurityOnVehicleBooking, $rule);
            } else {
                return $this->verifySecurity($comfortSecurityOnVehicleBooking, $rule);
            }
        } catch (Exception $ex) {
            return FALSE;
        }
    }
    private function checkFreeRide($booking)
    {
    }
    private function preBooking($rule)
    {
        $postBooking = ParkingRulesName::whereHas('access', function ($query) {
            $query->where('enable', 1);
        })->where('slug', 'post_booking')->first();
        if ($rule->access->enable == 0 && $postBooking) {
            $this->logService->info($this->testingSessionId, 'This Rule is not Applicable beacuse(' . $postBooking->name . ') is enable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if ($rule->access->enable == 0 && (!$postBooking)) {
            if ($this->booking->emailNotification) {
                $this->logService->info($this->testingSessionId, 'As Email Notification Barcode Found so System will create booking against this barcode', $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, false);
                $data = array(
                    'status' => $this->access,
                    $rule->slug => false
                );
                return $data;
            }
            if (!$this->validBooking) {
                $message = $this->ticketReader->getMessage('denied_access', "", $this->lang_id);
                $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'denied_access'));
                $this->logService->info($this->testingSessionId, 'Pre Booking applied because post booking is disable', $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access denied', $rule->id, false);
                $this->logService->info($this->testingSessionId, $message, $rule->id, $this->denied, false);
                $data = array(
                    'status' => $this->denied,
                    $rule->slug => false
                );
                return $data;
            }
        }
        if ($this->booking->emailNotification) {
            $this->logService->info($this->testingSessionId, 'As Email Notification Barcode Found so System will create booking against this barcode', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => false
            );
            return $data;
        }
        if (!$this->validBooking) {

            $message = $this->ticketReader->getMessage('denied_access', "", $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'denied_access'));
            $this->logService->info($this->testingSessionId, 'Access denied', $rule->id, false);
            $this->logService->info($this->testingSessionId, $message, $rule->id, $this->denied, false);
            $data = array(
                'status' => $this->denied,
                $rule->slug => false
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Valid Booking Found', $rule->id, $this->access, false);
        $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, false);
        $data = array(
            'status' => $this->access,
            $rule->slug => true
        );
        return $data;
    }
    private function postBooking($rule)
    {
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'As Device Direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable when device direction = ' . $this->validDeviceSettings['device_direction'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if (!$this->validBooking && $this->plateCorrection) {
            $this->logService->info($this->testingSessionId, 'Plate correction is enable', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable when booking found', $rule->id, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if ($this->validBooking) {
            $this->logService->info($this->testingSessionId, 'Valid booking found', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable when booking found', $rule->id, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if ($this->validDeviceSettings['available_device_id'] == 3) {
            $name = $this->checkName($this->validBooking);
            $message = $this->ticketReader->getMessage('welcome_entrance', $name, $this->lang_id);
            $this->accessMessage(1, 'success', $message, $this->setting->send_message_od($this->deviceId, $message, 'welcome_entrance'));
            $this->logService->info($this->testingSessionId, 'Post Booking is Enable ', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Granted', $rule->id, $this->access, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => true
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'This rule is not applicable when barcode is not valid', $rule->id, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => false
        );
        return $data;
    }
    private function checkTommyBooking($booking)
    {
        if (isset($booking->tommy_parent_id)) {
            $this->logService->info($this->testingSessionId, 'Checking Tommy User Reservation Against this booking');
            $tommyBooking = TommyReservationParents::where('id', $booking->tommy_parent_id)->first();
            return $tommyBooking;
        }
        return FALSE;
    }
    private function verifyComfort($comfortOnVehicle, $rule)
    {
        if ($comfortOnVehicle) {
            $key = $this->validDeviceSettings['available_device_id'] == 2 ? 'welcome_entrance_person' : 'welcome_entrance';
            $message = $this->ticketReader->getMessage($key, "", $this->lang_id);
            $this->accessMessage(1, 'success', $message, $this->setting->send_message_od($this->deviceId, $message, $key), $this->validBooking);
            $this->logService->info($this->testingSessionId, 'Due to comfort security system has given access.', $rule->id, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => true
            );
            return $data;
        }
        if ($this->validBooking) {
            if (array_key_exists('valid_anti_passback', $this->ruleStatus) && ($this->ruleStatus['valid_anti_passback'] && $this->validDeviceSettings['device_direction'] == "in")) {
                $key = $this->validDeviceSettings['available_device_id'] == 2 ? 'welcome_entrance_person' : 'welcome_entrance';
                $name = $this->checkName($this->validBooking);
                $message = $this->ticketReader->getMessage($key, $name, $this->lang_id);
                $this->accessMessage(1, 'success', $message, $this->setting->send_message_od($this->deviceId, $message, $key), $this->validBooking);
                $this->logService->info($this->testingSessionId, 'Due to comfort security system has given access.', $rule->id, false);
                $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                $data = array(
                    'status' => $this->access,
                    $rule->slug => true
                );
                return $data;
            }
            if (array_key_exists('validBooking', $this->ruleStatus) && (!$this->validBooking['attendant_transactions'] && $this->validDeviceSettings['device_direction'] == "out")) {
                $key = $this->validDeviceSettings['available_device_id'] == 2 ? 'welcome_entrance_person' : 'welcome_entrance';
                $name = $this->checkName($this->validBooking);
                $message = $this->ticketReader->getMessage($key, $name, $this->lang_id);
                $this->accessMessage(1, 'success', $message, $this->setting->send_message_od($this->deviceId, $message, $key), $this->validBooking);
                $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                $data = array(
                    'status' => $this->access,
                    $rule->slug => true
                );
                return $data;
            }
            if (!$this->isNullOrEmptyOrShort && (array_key_exists('plate_entry_exit_difference', $this->ruleStatus) && ($this->ruleStatus['plate_entry_exit_difference']['plate_entry_exit_difference']))) {
                $ticketUsed = $this->ticketUsedOrMismatch($rule);
                if ($ticketUsed) {
                    $key = $this->validDeviceSettings['device_direction'] == "in" ? "welcome_entrance" : "goodbye_exit";
                    $name = $this->checkName($this->validBooking);
                    $message = $this->ticketReader->getMessage($key, $name, $this->lang_id);
                    $this->accessMessage(1, 'success', $message, $this->setting->send_message_od($this->deviceId, $message, $key), $this->validBooking);
                    $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                    $data = array(
                        'status' => $this->access,
                        $rule->slug => true
                    );
                    $this->logService->info($this->testingSessionId, 'Plate Number is not valid', $rule->id, false);
                    return $data;
                }
                $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, false);
                $data = array(
                    'status' => $this->not_apply,
                    $rule->slug => true
                );
                return $data;
            }
        }
        $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => true
        );
        return $data;
    }
    private function verifySecurity($security, $rule)
    {
        if ($security) {
            $message = $this->ticketReader->getMessage('comfort_security', '', $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'comfort_security'));
            $this->logService->info($this->testingSessionId, $message, $rule->id, false);
            $data = array(
                'status' => $this->denied,
                $rule->slug => true
            );
            return $data;
        }
        if ($this->validBooking) {
            if (array_key_exists('valid_anti_passback', $this->ruleStatus) && ($this->ruleStatus['valid_anti_passback'] && $this->validDeviceSettings['device_direction'] == "in")) {
                $message = $this->ticketReader->getMessage('already_at_location', '', $this->lang_id);
                $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'already_at_location'));
                $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                $data = array(
                    'status' => $this->denied,
                    $rule->slug => true
                );
                return $data;
            }
            if (array_key_exists('validBooking', $this->ruleStatus) && (!$this->validBooking['attendant_transactions'] && $this->validDeviceSettings['device_direction'] == "out")) {
                $message = $this->ticketReader->getMessage('unauthorized', '', $this->lang_id);
                $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'unauthorized'));
                $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                $data = array(
                    'status' => $this->denied,
                    $rule->slug => true
                );
                return $data;
            }
            if (array_key_exists('plate_entry_exit_difference', $this->ruleStatus) && ($this->ruleStatus['plate_entry_exit_difference']['plate_entry_exit_difference'])) {
                $ticketUsed = $this->ticketUsedOrMismatch($rule);
                if ($ticketUsed) {
                    $message = $this->ticketReader->getMessage('ticket_used', '', $this->lang_id);
                    $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'ticket_used'));
                    $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                    $data = array(
                        'status' => $this->denied,
                        $rule->slug => true
                    );
                    return $data;
                }
            }
        }
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => false
        );
        return $data;
    }
    private function getTodayPersonTimings()
    {
        $day_num = date('w');
        $location_timings = LocationTimings::where([
            ['is_whitelist', 0],
            ['is_person', 1],
            ['week_day_num', $day_num],
        ])->first();
        if ($location_timings) {
            return $location_timings;
        }
        return FALSE;
    }
    private function getTodayParkingHours()
    {
        $day_num = date('w');
        $location_timings = LocationTimings::where([
            ['is_whitelist', 1],
            ['is_person', 0],
            ['week_day_num', $day_num],
        ])->first();
        if ($location_timings) {
            return $location_timings;
        }
        return FALSE;
    }
    public function checkLicensePlate($filePath, $confidence = NULL)
    {

        $input_showed = $this->showInputTicketReader($filePath);
        if (!$input_showed) {
            $this->logService->info($this->testingSessionId, 'Plate Correction is not enable on ticket reader .', false);
            return false;
        }
        $this->logService->info($this->testingSessionId, 'Plate Correction is enable on ticket reader', false);
        return true;
    }
    function showInputTicketReader($filePath)
    {

        if ($this->validDeviceSettings['device_ticket_reader']) {
            if (!$this->validDeviceSettings['device_ticket_reader']['is_synched']) {
                return FALSE;
            }
            $ip = $this->validDeviceSettings['device_ticket_reader']['device_ip'];
            $port = $this->validDeviceSettings['device_ticket_reader']['device_port'];
            $plate_correction_enabled = $this->validDeviceSettings['device_ticket_reader']['plate_correction_enabled'];
            if (empty($ip) || empty($port)) {
                return FALSE;
            }
            if (!$plate_correction_enabled) {
                return FALSE;
            }
            $client = new Client($ip, $port);
            $key = $this->locationCreatedAt . '-' . $this->validDeviceSettings['device_ticket_reader']['id'];
            $command = 'ShowTicketReaderInput';
            $data = '32|' . $key . '|' . '1' . '|' . $filePath;
            $client->send($command, $data);
            return true;
        }
        return FALSE;
    }
    public function accessMessage($status, $accessStatus, $message, $od_sent = null, $data = null)
    {
        return $this->ruleStatus['access'] = array(
            'status' => $status,
            'access_status' => $accessStatus,
            'message' => $message,
            'od_sent' => $od_sent,
            'data' => isset($data->id) ? $data->id : false
        );
    }
    public function bookingMessage($direction)
    {
        $message = "";
        //$name = isset($this->validBooking->first_name) ? $this->validBooking->first_name : '';
        $name = $this->getUserName($this->validBooking);
        if ($direction == "in") {
            $key = $this->validDeviceSettings['available_device_id'] == 2 ? 'welcome_entrance_person' : 'welcome_entrance';
            $message = $this->ticketReader->getMessage($key, $name, $this->lang_id);
            return $message;
        }
        return $message = $this->ticketReader->getMessage('goodbye_exit', $name, $this->lang_id);
    }
    public function getUserName($booking)
    {
        $user = '';
        if (!empty($booking->first_name)) {
            $user = $booking->first_name;
        }
        if (!empty($booking->last_name)) {
            if ($user != '') {
                $user = $user . ' ' . $booking->last_name;
            } else {
                $user = $booking->last_name;
            }
        }
        return ($user == 'Paid Vehicle' || $user == 'Paid Person') ? 'paid_string' : $user;
    }
    // private function isValidAntiPassBack($booking)
    // {
    //     if (!$this->validDeviceSettings) {
    //         return false;
    //     }
    //     $time_passback = 0;
    //     if ($this->isEmptyOrZeroTimePassBack($this->validDeviceSettings['time_passback'])) {
    //         $time_passback = $this->validDeviceSettings['available_device_id'] == 3 ? $this->validDeviceSettings['device_ticket_reader']['time_passback'] :  $this->validDeviceSettings['time_passback'];
    //     }
    //     Log::channel('verifyPlateNum')->info('attendant_transactions');
    //     if (isset($booking->attendant_transactions) && count($booking->attendant_transactions) > 0) {
    //         foreach ($booking->attendant_transactions as $attendant_transactions) {
    //             Log::channel('verifyPlateNum')->info(date('Y-m-d', strtotime($attendant_transactions->check_out)) . ' < ' . date('Y-m-d'));
    //             Log::channel('verifyPlateNum')->info('Attendant ' . $attendant_transactions);
    //             if ($attendant_transactions->check_in != NULL && $attendant_transactions->check_out != NULL) {
    //                 Log::channel('verifyPlateNum')->info('*** No Attendant Transaction  *** ' . $attendant_transactions->check_in . ' ' . $attendant_transactions->check_out);
    //                 continue;
    //             }
    //             Log::channel('verifyPlateNum')->info('Checkin =' . date('Y-m-d', strtotime($attendant_transactions->check_in)) . ' Current Date =' . date('Y-m-d'));
    //             if (date('Y-m-d', strtotime($attendant_transactions->check_in)) < date('Y-m-d')) {
    //                 continue;
    //             } elseif (date('Y-m-d', strtotime($attendant_transactions->check_in)) == date('Y-m-d')) {
    //                 $checkout_time = date('H:i', strtotime($attendant_transactions->check_in));
    //                 $current_time = date('H:i');
    //                 $valid_time = date("H:i", strtotime('+ ' . $time_passback . ' minutes', strtotime($checkout_time)));
    //                 if ($current_time < $valid_time) {
    //                     if ($booking->type == 5) {
    //                         Log::channel('verifyPlateNum')->info('Booking type is ' . $booking->type);
    //                         return true;
    //                     }
    //                     Log::channel('verifyPlateNum')->info('Booking type is ' . $booking->type);
    //                     return true;
    //                 }
    //             }
    //         }
    //     }
    //     return false;
    // }
    private function isValidTimePassBack($rule)
    {
        $emptyOrZeroPassBack = false;
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if (!$this->validBooking) {
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if ($this->validDeviceSettings['available_device_id'] != 3) {
            if (!$this->validDeviceSettings['anti_passback']) {
                $data = array(
                    'status' => $this->not_apply,
                    $rule->slug => false
                );
                return $data;
            }
        } else {
            if (!$this->validDeviceSettings['device_ticket_reader']['anti_passback']) {
                $data = array(
                    'status' => $this->not_apply,
                    $rule->slug => false
                );
                return $data;
            }
        }
        if ($this->validDeviceSettings['available_device_id'] == 3) {
            $emptyOrZeroPassBack =  $this->isEmptyOrZeroTimePassBack($this->validDeviceSettings['device_ticket_reader']['time_passback']);
        } else {
            $emptyOrZeroPassBack = $this->isEmptyOrZeroTimePassBack($this->validDeviceSettings['time_passback']);
        }
        $time_passback = 0;
        if ($emptyOrZeroPassBack) {
            $time_passback = $this->validDeviceSettings['available_device_id'] == 3 ? $this->validDeviceSettings['device_ticket_reader']['time_passback'] :  $this->validDeviceSettings['time_passback'];
        }
        if (isset($this->validBooking['attendant_transactions']) && count($this->validBooking['attendant_transactions']) > 0) {
            foreach ($this->validBooking['attendant_transactions'] as $attedant_transaction) {
                if ($attedant_transaction->check_in != NULL && $attedant_transaction->check_out != NULL) {
                    continue;
                }
                if (date('Y-m-d', strtotime($attedant_transaction->check_in)) < date('Y-m-d')) {
                    return
                        $data = array(
                            'status' => $this->not_apply,
                            $rule->slug => false
                        );
                } else if (date('Y-m-d', strtotime($attedant_transaction->check_in)) == date('Y-m-d')) {
                    $checkin_time = date('H:i', strtotime($attedant_transaction->check_in));
                    $current_time = date('H:i');
                    $valid_time = date("H:i", strtotime('+ ' . $time_passback . ' minutes', strtotime($checkin_time)));
                    if ($current_time < $valid_time) {
                        if ($this->validBooking['type'] == 5) {
                            return
                                $data = array(
                                    'status' => $this->denied,
                                    $rule->slug => true
                                );
                        }
                        return
                            $data = array(
                                'status' => $this->denied,
                                $rule->slug => true
                            );
                    }
                }
                return
                    $data = array(
                        'status' => $this->not_apply,
                        $rule->slug => false
                    );
            }
        }
        return
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
    }
    private function validateAntiPassBack($rule)
    {
        $validAntiPassBack = false;
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'As device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable when device direction = ' . $this->validDeviceSettings['device_direction'], $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $validAntiPassBack
            );
            return $data;
        }
        if (!$this->validBooking) {
            $this->logService->info($this->testingSessionId, 'Booking is not found ', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $validAntiPassBack
            );
            return $data;
        }

        if ($this->validDeviceSettings['available_device_id'] == 3 && !isset($this->validDeviceSettings['device_ticket_reader'])) {
            $this->logService->info($this->testingSessionId, 'As plate reader has related ticket reader so system check already booking on location', $rule->id, $this->not_apply, false);
            return $this->bookingOnLocation($rule);
        }
        $antiPassBack = $this->validDeviceSettings['available_device_id'] == 3 ? $this->validDeviceSettings['device_ticket_reader']['anti_passback'] :  $this->validDeviceSettings['anti_passback'];
        if ($antiPassBack != 1) {
            $this->logService->info($this->testingSessionId, 'Ticket has no anti pass back', $rule->id, false);
            return false;
        }
        $timePassBack = $this->validDeviceSettings['available_device_id'] == 3 ? $this->validDeviceSettings['device_ticket_reader']['time_passback'] :  $this->validDeviceSettings['time_passback'];
        $emptyOrZeroPassBack = $this->isEmptyOrZeroTimePassBack($timePassBack);
        if ($emptyOrZeroPassBack) {
            $this->logService->info($this->testingSessionId, 'Time passback is zero or empty check on location', $rule->id, false);
            return $this->bookingOnLocation($rule);
        }
        if ($timePassBack <= 0) {
            $this->logService->info($this->testingSessionId, 'Time passback less than 0 or equal to 0', $rule->id, false);
            return true;
        }
        $isValidTimePassBack = $this->isValidTimePassBack($rule);
        if ($isValidTimePassBack['valid_anti_passback']) {
            $key = ($this->validDeviceSettings['available_device_id'] == 3) ? "already_at_location" : (($this->validDeviceSettings['available_device_id'] == 1) ? "anti_passback_message" : "anti_passback_message_barcode");
            $message = $this->ticketReader->getMessage($key, "", $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, $key));
            $this->logService->info($this->testingSessionId, 'Valid Time Passback found so booking. ' . $message, $rule->id, $this->denied, false);
            $validAntiPassBack = true;
            $data = array(
                'status' => $this->denied,
                $rule->slug => $validAntiPassBack
            );
            return $data;
        }
        $alreadyOnLocation = $this->bookingOnLocation($rule);
        if ($alreadyOnLocation['valid_anti_passback']) {
            $this->logService->info($this->testingSessionId, 'Booking are already on location', $rule->id, $this->denied, false);
            $validAntiPassBack = true;
            $data = array(
                'status' => $this->denied,
                $rule->slug => $validAntiPassBack
            );
            return $data;
        }
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => $validAntiPassBack
        );
        return $data;
    }
    private function emergencyEntryExit($rule)
    {
        if ($this->validDeviceSettings['available_device_id'] != 3 && !$this->validDeviceSettings['has_emergency']) {
            $this->logService->info($this->testingSessionId, 'Available device is not plate reader or emergency is not enable against this device' . $this->validDeviceSettings['device_name'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if (!$this->validBooking) {
            $this->logService->info($this->testingSessionId, 'Valid Booking Not Found', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        switch ($this->validDeviceSettings['device_direction']) {
            case "in":
                if ($this->validBooking &&  $this->validBooking['type'] == 3 && $this->validDeviceSettings['emergency_entry_exit']) {
                    $this->booking->set_booking_entry($this->validBooking, $this->validDeviceSettings);
                    $this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'] . ' booking type is ' . $this->validBooking['type'] . ' system create set trnasaction entry against this booking', $rule->id, false);
                    $this->logService->info($this->testingSessionId, 'Access is granted', $rule->id, $this->access, false);
                    $data = array(
                        'status' => $this->access,
                        $rule->slug => false
                    );
                    return $data;
                }
                //$this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'] . ' and emergency  ' . $this->validBooking->type, $rule->id, false);
                $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
                $message = $this->ticketReader->getMessage('emergency_entry_exit', "", $this->lang_id);
                // $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'booking_not_valid'));
                $data = array(
                    'status' => $this->not_apply,
                    $rule->slug => false
                );
                return $data;
                break;
            case "out":
                if (!$this->validBooking) {
                    $this->booking->createEmergencyBooking($this->validDeviceSettings, $this->vehicleNumber);
                    $this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'] . 'Booking not Found system create booking entry against this booking and also set trnasaction entry as well', $rule->id, false);
                    $this->logService->info($this->testingSessionId, 'Access Granted', $rule->id, $this->access, false);
                    $data = array(
                        'status' => $this->access,
                        $rule->slug => true
                    );
                    return $data;
                }
                $this->booking->set_booking_exit($this->validBooking, $this->validDeviceSettings);
                $this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'] . ' Booking type is ' . $this->validBooking['type'] . ' system create trnasaction entry against this booking', $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access Granted', $rule->id, $this->access, false);
                $data = array(
                    'status' => $this->access,
                    $rule->slug => true
                );
                return $data;
                break;
            default:
                $data = array(
                    'status' => $this->not_apply,
                    $rule->slug => false
                );
                return $data;
                break;
        }
    }
    private function multipleSubscription($rule)
    {
        if ($this->validDeviceSettings['device_direction'] != "in") {
            $this->logService->info($this->testingSessionId, 'Device direction is ' . $this->validDeviceSettings['device_direction'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not Applicable', $this->access, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => true
            );
            return $data;
        }
        if (!$this->validBooking) {
            $this->logService->info($this->testingSessionId, 'Booking not found' . $this->validDeviceSettings['device_direction'], $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not Applicable', $this->access, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => true
            );
            return $data;
        }
        $booking = $this->validBooking;
        if ($this->validBooking->parent_id != null) {
            $this->logService->info($this->testingSessionId, 'Booking has parent id.', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Checking for parent booking if already on location.', $this->access, false);
            $parent_on_location = AttendantTransactions::whereHas(
                'attendants.bookings',
                function ($query) use ($booking) {
                    $query->where('id', $booking->parent_id);
                }
            )->whereNull('check_out')->first();
            if ($parent_on_location) {
                $this->logService->info($this->testingSessionId, 'Parent booking has already on location.', $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access denied', $rule->id, false);
                $data = array(
                    'status' => $this->denied,
                    $rule->slug => true
                );
                return $data;
            }
            $this->logService->info($this->testingSessionId, 'Parent booking is not on location', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, false);
            $data = array(
                'status' => $this->denied,
                $rule->slug => true
            );
            return $data;
        }
        if ($booking->parent_id == null) {
            $this->logService->info($this->testingSessionId, 'Booking have no parent. So checking for child booking if exits', $rule->id, false);
            $childBooking = Bookings::where('parent_id', $booking->id)->first();
            if (!$childBooking) {
                $this->logService->info($this->testingSessionId, 'Child not exits.', $rule->id, false);
                return true;
            }
            $on_location = AttendantTransactions::whereHas(
                'attendants.bookings',
                function ($query) use ($booking) {
                    $query->where('vehicle_num', $booking->vehicle_num);
                }
            )->whereNull('check_out')->first();
            if ($on_location) {
                return false;
            }
            if ($childBooking && !$on_location) {
                return true;
            }
        }
    }
    public function saveVipBarcodeTransaction($booking, $vipBarcode)
    {
        $booking->barcode = $vipBarcode->id;
        $booking->is_paid = 1;
        //$booking_details->checkout_time = date('Y-m-d H:i:s');
        $booking->save();
    }

    private function isEmptyOrZeroTimePassBack($timePassBack)
    {
        return (empty($timePassBack) || $timePassBack == 0);
    }
    private function matchEnable($request)
    {
        try {
            if ($request->location == "import") {
                $matching = ParkingRulesName::whereHas('access', function ($query) {
                    $query->where('enable', 1);
                })->where('is_imported', 1)->where('slug', 'matching_enable')->first();
                if ($matching) {
                    return $matching;
                }
            }
            $matching = ParkingRulesName::whereHas('access', function ($query) {
                $query->where('enable', 1);
            })->where('slug', 'matching_enable')->first();
            if ($matching) {
                return $matching;
            }
            return FALSE;
        } catch (Exception $ex) {
            return FALSE;
        }
    }
    private function comfortSecurityCheckEnable()
    {
        try {
            $comfort_security_check = ParkingRulesName::whereHas('access', function ($query) {
                $query->where('enable', 1);
            })->where('slug', 'comfort_security_check')->first();
            if ($comfort_security_check) {
                return $comfort_security_check;
            }
            return FALSE;
        } catch (Exception $ex) {
            return FALSE;
        }
    }
    public function isValidDevice($request, $key, $deviceId)
    {
        $keyArray = explode('-', $key);
        if (count($keyArray) != 2) {
            return FALSE;
        }
        $locationSettings = new LocationSettings();
        $location = $locationSettings->get_location();
        if (strtotime($location->created_at) != $keyArray[0]) {
            return FALSE;
        }
        if ($deviceId == null) {
            $deviceId = $keyArray[1];
        }
        $locationDevice = LocationDevices::find($deviceId);
        if (!$locationDevice) {
            return FALSE;
        }
        if ($locationDevice->available_device_id == 3) {
            $relatedTicketReader = DeviceTicketReaders::where('device_id', $deviceId)->first();
            if ($relatedTicketReader) {
                $ticketReader = LocationDevices::find($relatedTicketReader->ticket_reader_id);
                if ($ticketReader) {
                    $locationDevice->device_ticket_reader = $ticketReader;
                }
            }
        } elseif ($locationDevice->available_device_id == 1) {
            $relatedPlatedReader = DeviceTicketReaders::where('ticket_reader_id', $deviceId)->first();
            if ($relatedPlatedReader) {
                $plateReader = LocationDevices::find($relatedPlatedReader->device_id);
                if ($plateReader) {
                    $locationDevice->related_plate_reader = $plateReader;
                }
            }
        }
        if (isset($request->identifier_type) && $request->identifier_type == "license_plate") {
            $this->validDeviceSettings = $plateReader;
        } else {
            $this->validDeviceSettings = $locationDevice;
        }
        
        return $this->validDeviceSettings;
    }
    // email Notification //
    private function emailNotificationBooking($barcode)
    {
        $emailNotificationBooking = EmailNotification::where('ticket_token', $barcode)->first();
        if ($emailNotificationBooking && $this->validDeviceSettings['device_direction'] == "in") {
            $this->emailNotification = $emailNotificationBooking;
            $this->isNotuserListAndPromo = $this->isNotUserlistOrPromo($emailNotificationBooking->type);
            if ($this->isNotuserListAndPromo) {
                $notificationTime = $this->notificationTime($emailNotificationBooking);
                if ($notificationTime) {
                    return false;
                }
                $type = $emailNotificationBooking->type == 'user_list' ? 3 : 4;
                $booking = $this->getCustomerVehicleBooking($emailNotificationBooking->customer_id, $this->ticketVehicleNumber, 'in', $type);
                if (!$booking) {
                    $this->ruleStatus['email_notification_booking'] = false;
                    return false;
                }
                return $booking;
            } else {
                $notificationTime = $this->notificationTime($emailNotificationBooking);
                if ($notificationTime) {
                    return false;
                }
                $booking = $this->getVehiclePromoBooking($emailNotificationBooking->customer_id, $this->ticketVehicleNumber, 'in', $emailNotificationBooking->type_id);
                if (!$booking) {
                    return false;
                }
                return $booking;
            }
        } elseif ($emailNotificationBooking && $this->validDeviceSettings['device_direction'] == "out") {
            $this->emailNotification = $emailNotificationBooking;
            if ($emailNotificationBooking->type == 'customer') {
                $notificationTime = $this->notificationTime($emailNotificationBooking);
                if ($notificationTime) {
                    return false;
                }
            }
            $type = $emailNotificationBooking->type == 'user_list' ? 3 : 4;
            $booking = $this->getCustomerVehicleBooking($emailNotificationBooking->customer_id, FALSE, 'out', $type);
            if (!$booking) {
                $this->ruleStatus['email_notification_booking'] = $emailNotificationBooking;
                return false;
            }
            $customer = $booking->customer;
            if (isset($customer) && $customer->language_id > 0) {
                $this->lang_id = $customer->language_id;
            }
            $user_name = $this->ticketReader->get_user_name($booking);
            $at_location = $this->setting->is_booking_at_location($booking->id);
            if (!$at_location && !($booking->type == 3 || $booking->type == 2)) {
                return false;
            }
            $is_paid = $this->isbookingPaid($booking);
            if ($is_paid['status']) {
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
    private function createCustomerVehicleBooking($customer_id, $vehicle, $check_in, $check_out, $type)
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
            $booking->vehicle_num = $vehicle;
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
        Log::channel('verifyPlateNum')->info($promo);
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
    public function checkCustomerCar($customer, $rule)
    {
        if ($customer != null) {
            $this->logService->info($this->testingSessionId, 'Customer Found Against this Booking', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Checking Customer Max Cars', $rule->id, false);
            if (!$customer->max_cars > 0) {
                $this->logService->info($this->testingSessionId, 'customer has no max cars', $rule->id, false);
                $this->logService->info($this->testingSessionId, 'This Rule is not applicable against this booking', $rule->id, $this->not_apply, false);
                $data = array(
                    'status' => $this->not_apply,
                    $rule->slug => false
                );
                return $data;
            }
            $booking_count = Bookings::whereHas('attendant_transactions', function ($query) {
                $query->whereNull('check_out');
            })->where('customer_id', $customer->id)->count();
            if ($booking_count < $customer->max_cars) {
                $this->logService->info($this->testingSessionId, 'Customer Max cars are less than given ' . $booking_count, $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access Granted', $rule->id, $this->access, false);
                $data = array(
                    'status' => $this->access,
                    $rule->slug => false
                );
                return $data;
            }
            $message = $this->ticketReader->getMessage('max_allowed_vehicles', "", $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'max_allowed_vehicles'));
            $this->logService->info($this->testingSessionId, $message, $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
            $data = array(
                'status' => $this->denied,
                $rule->slug => false
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Customer not found against this booking', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'This rule is not applicable against this booking', $rule->id, $this->not_apply, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => false
        );
        return $data;
    }
    public function checkUserCar($booking, $rule)
    {
        $user_vehicle = $booking->customer_vehicle_info;
        if (!$user_vehicle) {
            $this->logService->info($this->testingSessionId, 'Vehicle Not Found against User', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This Rule is not Applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        $userlist_user = isset($user_vehicle->user_list_users) ? $user_vehicle->user_list_users : false;
        $user_group = isset($userlist_user->group) ? $userlist_user->group : false;
        if (!$user_group) {
            $this->logService->info($this->testingSessionId, 'Group not found against user', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This Rule is not Applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        $userlist_group_users = UserlistUsers::where('group_id', $user_group->id)->get();
        if (!$userlist_group_users) {
            $this->logService->info($this->testingSessionId, 'Group not found against user', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This Rule is not Applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        $vehicle_ids = array();
        foreach ($userlist_group_users as $user) {
            $ids = $user->customer_vehicle_info()->pluck('id')->toArray();
            $vehicle_ids = array_merge($vehicle_ids, $ids);
        }
        if (isset($booking->customer_vehicle_info_id)) {
            $booking_count = Bookings::whereHas('attendant_transactions', function ($query) {
                $query->whereNull('check_out');
            })->whereIn('customer_vehicle_info_id', $vehicle_ids)->where('customer_vehicle_info_id', '<>', $booking->customer_vehicle_info_id)->count();
            if (isset($user_group->group_max)) {
                if (($booking_count >= $user_group->group_max)) {
                    $name = $this->ticketReader->get_user_name($booking);
                    $message = $this->ticketReader->getMessage('max_allowed_vehicles_group', $name, $this->lang_id);
                    $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'max_allowed_vehicles_group'));
                    $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                    $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
                    $data = array(
                        'status' => $this->denied,
                        $rule->slug => true
                    );
                    return $data;
                }
            }
        }
        $this->logService->info($this->testingSessionId, 'No of Parking Space are avaiable against this user group', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'Access Granted', $rule->id, $this->access, false);
        $data = array(
            'status' => $this->access,
            $rule->slug => false
        );
        return $data;
    }
    public function userAccessRightAndGroup($rule)
    {
        $userRights = false;
        if (!$this->validBooking) {
            $this->logService->info($this->testingSessionId, 'As valid booking not found', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $userRights
            );
            return $data;
        }
        if ($this->validBooking['type'] != 3) {
            $this->logService->info($this->testingSessionId, 'Booking type is' . $this->validBooking['type'] . ' booking other than type 3 have no premssion to access group and acces groups rights.', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $userRights
            );
            return $data;
        }
        $device_group = $this->ticketReader->device_has_group($this->validDeviceSettings['id']);
        if (!$device_group) {
            $this->logService->info($this->testingSessionId, 'Given device has no group', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $userRights
            );
            return $data;
        }
        $customer = $this->validBooking->customer;
        $access_right = null;
        $user_group = null;
        $access_right = null;
        $userlist_user = null;
        if ($customer && isset($customer->user_list_users)) {
            $userlist_user = $customer->user_list_users;
            $user_group = isset($userlist_user->group) ?  $userlist_user->group : false;
            $access_right = $customer->user_list_users->group_access;
        } else {
            $user_vehicle = $this->validBooking['customer_vehicle_info'];
            $userlist_user = $user_vehicle->user_list_users;
            if (isset($userlist_user->group)) {
                $user_group = $userlist_user->group;
            }
            if (isset($userlist_user->group_access)) {
                $access_right = $userlist_user->group_access;
            }
        }
        if (!$user_group && !$access_right) {
            $this->logService->info($this->testingSessionId, 'user have no user group and access right', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => $userRights
            );
            return $data;
        }
        if ($access_right) {
            return $this->accessRightGroup($access_right, $this->validBooking, $userlist_user, $user_group, $rule);
        }
        if (isset($device_group)) {
            return $this->deviceGroup($user_group, $this->validDeviceSettings, $device_group, $this->validBooking->type, $rule);
        }
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => $userRights
        );
        return $data;
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
    public function accessRightGroup($access_right, $booking, $userlist_user, $user_group, $rule)
    {
        if (!$user_group && !$access_right) {
            $this->logService->info($this->testingSessionId, 'user have no user group and access right', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
            $data = array(
                'status' => $this->not_apply,
                $rule->slug => false
            );
            return $data;
        }
        if ($access_right) {
            if ($access_right->number_of_times) {
                $vehicle_ids = $userlist_user->customer_vehicle_info()->pluck('id')->toArray();
                $booking_count = Bookings::whereHas('attendant_transactions', function ($query) {
                    $query->whereNotNull('check_out');
                })->whereIn('customer_vehicle_info_id', $vehicle_ids)->count();
                if (!($booking_count < $access_right->number_of_times)) {
                    $name = $this->ticketReader->get_user_name($booking);
                    $message = $this->ticketReader->getMessage('access_number_of_time_expired', $name, $this->lang_id);
                    $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'access_number_of_time_expired'));
                    $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                    $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
                    $data = array(
                        'status' => $this->denied,
                        $rule->slug => true
                    );
                    return $data;
                }
            }
            if (!empty($access_right->start_date) && !empty($access_right->end_date)) {
                if (!(date('Y-m-d H:i:s') >= $access_right->start_date && date('Y-m-d H:i:s') <= $access_right->end_date)) {
                    $name = $this->ticketReader->get_user_name($booking);
                    $message = $this->ticketReader->getMessage('access_expired', $name, $this->lang_id);
                    $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'access_expired'));
                    $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                    $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
                    $data = array(
                        'status' => $this->denied,
                        $rule->slug => true
                    );
                    return $data;
                }
            }
            if (isset($booking->customer_vehicle_info_id)) {
                if ($access_right->allowed_no_of_vehicles) {
                    $vehicle_ids = $userlist_user->customer_vehicle_info()->pluck('id')->toArray();
                    $booking_count = Bookings::whereHas('attendant_transactions', function ($query) {
                        $query->whereNull('check_out');
                    })->whereIn('customer_vehicle_info_id', $vehicle_ids)->where('customer_vehicle_info_id', '<>', $booking->customer_vehicle_info_id)->count();
                    if (!($booking_count < $access_right->allowed_no_of_vehicles)) {
                        $name = $this->ticketReader->get_user_name($booking);
                        $message = $this->ticketReader->getMessage('access_vehicle_max', $name, $this->lang_id);
                        $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'access_expired'));
                        $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                        $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
                        $data = array(
                            'status' => $this->denied,
                            $rule->slug => true
                        );
                        return $data;
                    }
                }
            }
        }
        $this->logService->info($this->testingSessionId, 'User Have no access group', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => false
        );
        return $data;
    }
    public function deviceGroup($user_group, $device, $device_group, $booking, $rule)
    {
        $has_group_access = $this->ticketReader->is_valid_group_device($user_group->id, $device->id);
        if (!$has_group_access) {
            $message = $this->ticketReader->getMessage('group_device_access_denied', '', $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'group_device_access_denied'));
            $this->logService->info($this->testingSessionId, $message, $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access Denied', $rule->id, $this->denied, false);
            $data = array(
                'status' => $this->denied,
                $rule->slug => true
            );
            return $data;
        }
        if ($device_group->group->has_anti_pass_back) {
            //group_anti_passback
            if (isset($booking->customer_vehicle_info_id)) {
                $booking_count = Bookings::whereHas('attendant_transactions', function ($query) {
                    $query->whereNull('check_out');
                })->where('customer_vehicle_info_id', $booking->customer_vehicle_info_id)->count();
                if ($booking_count) {
                    $name = $this->ticketReader->get_user_name($booking);
                    $message = $this->ticketReader->getMessage('group_anti_passback', $name, $this->lang_id);
                    $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'group_device_access_denied'));
                    $this->logService->info($this->testingSessionId, $message, $rule->id, false);
                    $this->logService->info($this->testingSessionId, 'Access Granted', $rule->id, $this->access, false);
                    $data = array(
                        'status' => $this->access,
                        $rule->slug => false
                    );
                    return $data;
                }
            }
        }
        $this->logService->info($this->testingSessionId, 'Device Has no access group', $rule->id, false);
        $this->logService->info($this->testingSessionId, 'This rule is not applicable', $rule->id, $this->not_apply, false);
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => false
        );
        return $data;
    }
    public function userMaxGroup($user_group, $booking)
    {

        $userlist_group_users = UserlistUsers::where('group_id', $user_group->id)->get();
        $vehicle_ids = array();
        foreach ($userlist_group_users as $user) {
            $ids = $user->customer_vehicle_info()->pluck('id')->toArray();
            $vehicle_ids = array_merge($vehicle_ids, $ids);
        }
        $booking_count = Bookings::whereHas('attendant_transactions', function ($query) {
            $query->whereNull('check_out');
        })->whereIn('customer_vehicle_info_id', $vehicle_ids)->where('customer_vehicle_info_id', '<>', $booking->customer_vehicle_info_id)->count();
        if (!($booking_count < $user_group->group_max)) {
            $name = $this->ticketReader->get_user_name($booking);
            $message = $this->ticketReader->get_error_message('max_allowed_vehicles_group', $name, $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'group_device_access_denied'));
            return true;
        }
    }
    public function ticketUsedOrMismatch($rule)
    {
        if ($this->validBooking['type'] != 6) {
            if ($this->validBooking['vehicle_num'] != $this->ticketVehicleNumber) {
                return true;
            }
        }
        $data = array(
            'status' => $this->not_apply,
            $rule->slug => true
        );
        return $data;
    }
    public function hasRelatedTicketReader($device, $rule)
    {
        if ($device->has_related_ticket_reader != 0) {
            $this->logService->info($this->testingSessionId, 'Related Ticket Reader Found. Now checking always access against related ticket reader', $rule->id, false);
            if ((!$device->has_gate && isset($device->device_ticket_reader) && $device->device_ticket_reader->barrier_status == 3)) {
                if (!$this->validBooking) {
                    $this->logService->info($this->testingSessionId, 'Always access is enable on related ticket reader so system has given access (' . $this->vehicleNumber . ').', $rule->id, false);
                    // $this->validBooking = $this->booking->createBooking($this->vehicleNumber, $this->validDeviceSettings['id'], false);
                    // $this->ruleStatus['validBooking'] = $this->validBooking;
                    $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, $this->access, false);
                    $data = array(
                        'status' => $this->access,
                        $rule->slug => true
                    );
                    return $data;
                }
                $this->logService->info($this->testingSessionId, 'Always Access is open and valid booking found against this plate number ' . $this->vehicleNumber, $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access is granted', $rule->id, $this->access, false);
                $data = array(
                    'status' => $this->access,
                    $rule->slug => true
                );
                return $data;
            } else {
                $this->logService->info($this->testingSessionId, 'Related Ticket Has no always access', $rule->id, false);
                $this->logService->info($this->testingSessionId, 'Access is Denied', $rule->id, $this->denied, false);
                $data = array(
                    'status' => $this->denied,
                    $rule->slug => false
                );
                return $data;
            }
        } elseif ($device->has_gate) {
            $this->logService->info($this->testingSessionId, 'Related ticket reader not found', $rule->id, false);
            if (!$this->validBooking) {
                $this->logService->info($this->testingSessionId, 'Plate reader has always access enable so system has given access (' . $this->vehicleNumber . ')', $rule->id, false);
                // $this->validBooking = $this->booking->createBooking($this->vehicleNumber, $this->validDeviceSettings['id'], false);
                // $this->ruleStatus['validBooking'] = $this->validBooking;
                $this->logService->info($this->testingSessionId, 'Access is granted due to always access', $rule->id, $this->access, false);
                $data = array(
                    'status' => $this->access,
                    $rule->slug => true
                );
                return $data;
            }
            $this->logService->info($this->testingSessionId, 'Plate reader has always enable and also booking is also found against this plate number (' . $this->vehicleNumber . ').', $rule->id, false);
            $this->logService->info($this->testingSessionId, 'Access granted', $rule->id, $this->access, false);
            $data = array(
                'status' => $this->access,
                $rule->slug => true
            );
            return $data;
        }
        $this->logService->info($this->testingSessionId, 'Neither related ticket reader nor plate reader has always access enable', $rule->id, $this->not_apply, false);
        return false;
    }
    public function getTicketType($productId)
    {
        $product = Products::where('id', $productId)->first();
        if ($product) {
            return $product;
        }
        return false;
    }
    public function totalNumberOfTime($ticket)
    {

        if (isset($ticket->no_of_time)) {
            Log::channel('verifyPlateNum')->info('No of Time');
            return $ticket->no_of_time;
        }
        return false;
    }
    private function getCustomerName($customerId)
    {
        $customer = Customer::find($customerId);
        if (isset($customer)) {
            return $customer;
        }
        return false;
    }
    private function notificationTime($notificationTime)
    {
        return (!($notificationTime->checkin_time <= date('Y-m-d H:i') && $notificationTime->checkout_time > date('Y-m-d H:i')));
    }
    public function bookingAccess($booking = false)
    {
        if ($this->booking->emailNotification) {
            $message = $this->bookingMessage($this->validDeviceSettings['device_direction']);
            $key = $this->validDeviceSettings['device_direction'] == "in" ? "welcome_entrance" : "goodbye_exit";
            $this->accessMessage(1, 'success', $message, $this->setting->send_message_od($this->deviceId, $message, $key));
            $this->logService->info($this->testingSessionId, 'Valid email notification found', false, $this->access, false);
        } elseif (!$this->validBooking) {
            $message = $this->ticketReader->getMessage('booking_not_valid', "", $this->lang_id);
            $this->accessMessage(1, 'denied', $message, $this->setting->send_message_od($this->deviceId, $message, 'booking_not_valid'));
            $this->ruleStatus['validBooking'] = $this->validBooking;
            $this->logService->info($this->testingSessionId, 'Booking not found', false, $this->access, false);
        } else {
            $name = $this->validBooking->first_name ? $this->validBooking->first_name : '';
            $key = '';
            $message = $this->bookingMessage($this->validDeviceSettings['device_direction']);
            if (in_array($this->validDeviceSettings['available_device_id'], [1, 3])) {
                $key = $this->validDeviceSettings['device_direction'] == "in" ? "welcome_entrance" : "goodbye_exit";
            } elseif ($this->validDeviceSettings['available_device_id'] == 2) {
                $key = $this->validDeviceSettings['device_direction'] == "in" ? "welcome_entrance_person" : "goodbye_exit";
            }
            $res = $this->accessMessage(1, 'success', $message, $this->setting->send_message_od($this->deviceId, $message, $key), $this->validBooking);
            $this->ruleStatus['validBooking'] = $this->validBooking;
            if ($this->validDeviceSettings['available_device_id'] == 3) {
                if (array_key_exists('booking_status', $this->booking->ruleStatus)) {
                    $data = $this->booking->ruleStatus['booking_status'];
                    $this->ruleStatus['booking_status'] = $data;
                }
            }
        }
    }
    public function open_gate_plate_reader($ticket_reader_details, $vehicle_num, $message, $type)
    {

        try {

            $open_gate_call_start = microtime(true);
            if ($ticket_reader_details->available_device_id == 3) {
                $related_ticket_reader = DeviceTicketReaders::where([
                    ['device_id', $ticket_reader_details->id]
                ])->first();

                if (!$related_ticket_reader) {
                    if ($ticket_reader_details->has_sdl || $ticket_reader_details->gate_close_transaction_enabled) {
                        return True;
                    }
                    return FALSE;
                }
                $related_ticket_reader_id = $related_ticket_reader->ticket_reader_id;
                $ticket_reader_details = LocationDevices::find($related_ticket_reader_id);
            }
            if (!$ticket_reader_details) {
                return FALSE;
            }
            if (!$ticket_reader_details->is_synched) {
                return FALSE;
            }
            $ip = $ticket_reader_details->device_ip;
            $port = $ticket_reader_details->device_port;
            if (empty($ip) || empty($port)) {
                $ticket_reader_details->is_synched = 0;
                $ticket_reader_details->save();
                return FALSE;
            }
            $name = $this->checkName($this->validBooking);
            if ($type == 'entry') {
                if (empty($message)) {
                    $message = $this->ticketReader->getMessage('welcome_entrance', $name, $this->lang_id);
                }
                if ($ticket_reader_details->barrier_status != 1 && $ticket_reader_details->barrier_status != 2) {

                    $client = new Client($ip, $port);

                    $key = $this->locationCreatedAt . '-' . $ticket_reader_details->id;
                    $command = 'open_gate';
                    $data = '31|' . $key . '|' . $vehicle_num . '|' . $this->validBooking->id . '|' . $message;
                    $open_gate_call_total_time_start = (round(microtime(true) - $open_gate_call_start, 3) * 1000);

                    $connection = $client->send($command, $data);
                    $open_gate_call_total_time_after = (round(microtime(true) - $open_gate_call_start, 3) * 1000);
                    if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                        if ($connection['status'] >= 3) {
                            $ticket_reader_details->is_synched = 1;
                            $ticket_reader_details->is_opened = 1;
                            $ticket_reader_details->save();
                        }
                    }
                }
            } elseif ($type == 'exit') {
                if (empty($message)) {
                    $message = $this->ticketReader->getMessage('goodbye_exit', $name, $this->lang_id);
                }
                if ($ticket_reader_details->barrier_status != 1 && $ticket_reader_details->barrier_status != 2) {
                    $client = new Client($ip, $port);
                    $key = $this->locationCreatedAt . '-' . $ticket_reader_details->id;
                    $command = 'open_gate_Exit';
                    $data = '35|' . $key . '|' . $vehicle_num . '|' . $this->validBooking->id . '|' . $message;
                    $connection = $client->send($command, $data);
                    if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                        if ($connection['status'] >= 3) {
                            $ticket_reader_details->is_synched = 1;
                            $ticket_reader_details->is_opened = 1;
                            $ticket_reader_details->save();
                        }
                    }
                }
            }
            return TRUE;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function send_denied_access_socket($device, $message, $vehicle_num)
    {
        if ($device) {
            if ($device->available_device_id == 1) {
                $related_ticket_reader_id = $device->id;
            } elseif ($device->available_device_id == 3) {
                $related_ticket_reader = DeviceTicketReaders::where([
                    ['device_id', $device->id]
                ])->first();

                if (!$related_ticket_reader) {
                    return FALSE;
                }
                $related_ticket_reader_id = $related_ticket_reader->ticket_reader_id;
                $device = LocationDevices::find($related_ticket_reader_id);
            } else {
                return FALSE;
            }

            $ip = $device->device_ip;
            $port = $device->device_port;
            $client = new Client($ip, $port);

            $key = $this->locationCreatedAt . '-' . $device->id;
            $command = 'Plate Reader Message';
            $data = '33|' . $key . '|' . $message;
            if ($vehicle_num) {
                $data .= "|tentave_vehicle_" . $vehicle_num;
            }
            $client->send($command, $data);
            return;
        }
    }
    public function getDeviceStatus($device, $bookingId)
    {
        $this->validDeviceSettings = $device;
        $this->bookingId = $bookingId;
        $open_gate_reason = "always_access";
        $accessMessage = "Transaction registered";
        $deviceObject = LocationDevices::find($device->id);
        if ($this->validDeviceSettings->has_related_ticket_reader) {
            $this->sendCommandToRelatedTicketReader($this->validDeviceSettings->device_ticket_reader);
        }
        switch ($deviceObject->is_opened) {
            case 1:
                $deviceObject->is_opened = 0;
                $deviceObject->save();
                break;
            case 2:
                $deviceObject->is_opened = 0;
                $deviceObject->save();
                break;
            case 3:
                Session::put('open_gate_reason', $open_gate_reason);
                break;
        }
        switch ($this->validDeviceSettings->device_direction) {
            case "in":
                $this->validBooking = $this->booking->getBookingById($this->bookingId);
                if (!$this->validBooking) {
                    $message = $this->ticketReader->getMessage('booking_not_valid', '', $this->lang_id);
                    return $this->verifyStatusMessage(1, 'denied', $accessMessage);
                }
                $userName = $this->getUserName($this->validBooking);
                $pushingBookingOnCloud = $this->booking->push_booking_cloud($this->validBooking);
                $setBookingEntry = $this->booking->set_booking_entry($this->validBooking, $this->validDeviceSettings);
                if ($this->validBooking->user_arrival_notification) {
                    $this->booking->user_arrival_notification($this->validBooking, 'in');
                }
                $message = $this->ticketReader->getMessage('welcome_entrance', $userName, $this->lang_id);
                return $this->verifyStatusMessage(1, 'success', $accessMessage);
                break;
            case "out":
                $this->validBooking = $this->booking->getBookingById($this->bookingId);
                if (!$this->validBooking) {
                    $message = $this->ticketReader->getMessage('booking_not_valid', '', $this->lang_id);
                    return $this->verifyStatusMessage(1, 'denied', $accessMessage);
                }
                $setBookingCheckOut = $this->booking->set_vehicle_booking_checkout($this->validBooking, $this->bookingId, 'out');
                $userName = $this->getUserName($this->validBooking);
                if (!$this->validDeviceSettings->has_gate && isset($this->validDeviceSettings->device_ticket_reader)) {
                    $device_ticket_reader = $this->validDeviceSettings->device_ticket_reader;
                    if ($device_ticket_reader->barrier_status == 3) {
                        Session::put('open_gate_reason', $open_gate_reason);
                    }
                }
                $res = $this->booking->set_booking_exit($this->validBooking, $this->validDeviceSettings);
                $message = $this->ticketReader->getMessage('goodbye_exit', $userName, $this->lang_id);
                if ($this->validBooking->user_arrival_notification) {
                    $this->booking->user_arrival_notification($this->validBooking, 'out');
                }
                if ($this->validDeviceSettings->barrier_status != 1 && $this->validBooking->barrier_status != 2) {
                }
                return $this->verifyStatusMessage(1, 'success', $accessMessage);
                break;
            default:
                return $this->accessMessage(1, 'error', 'Bidirectional Devices is not supported', 0);
        }
    }
    public function sendCommandToRelatedTicketReader($readerTickerReader)
    {
        $ip = $readerTickerReader->device_ip;
        $port = $readerTickerReader->device_port;
        $client = new Client($ip, $port);
        $key = $this->locationCreatedAt . '-' . $readerTickerReader->id;
        $command = 'ready_recognition';
        $data = '36|' . $key;
        $client->send($command, $data);
        return true;
    }
    public function testingParkingRules($request)
    {
        $rules = [];
        if ($request->regression_testing == "yes" && $request->location != "import") {
            $parkingRules = ParkingRulesName::whereIn('id', $request->rules)
                ->where('slug', '!=', 'matching_enable')->orWhere(function ($query) {
                    $query->whereIn('id', $this->disableRules);
                })->whereNull('is_imported')->orderByRaw(("FIELD(id, $request->sorted_rules)"))->get();
            if ($parkingRules) {
                return $parkingRules;
            }
        }
        if ($request->location == "import") {
            $importParkingRules = ParkingRulesName::whereHas('access', function ($query) {
                $query->where('enable', 1)->where('slug', '!=', 'matching_enable')
                    ->orWhere(function ($query) {
                        $query->whereIn('rule_id', $this->disableRules)
                            ->where('enable', 0);
                    });
            })->whereIn('id', $request->rules)->where('is_imported', 1)->orderByRaw(("FIELD(id, $request->sorted_rules)"))->get();
            return $importParkingRules;
        }
        return false;
    }
    private function isNotUserlistOrPromo($type)
    {
        return ($type !== "user_list" && $type !== "promo");
    }
    private function logError($functionName, $ex)
    {
        $errorLog = new LogController(); // Replace with your log controller
        $errorLog->log_create($functionName, $ex->getMessage() . '-' . $ex->getLine(), $ex->getTraceAsString());
    }
    private function notActiveRule()
    {
        $message = $this->ticketReader->getMessage('not_active_rule', '', $this->lang_id);
        return $message;
    }
    public function verifyStatusMessage($status, $accessstatus, $message)
    {

        return $this->ruleStatus['access'] = array(
            'status' => $status,
            'access_status' => $accessstatus,
            'message' => $message
        );
    }
    private function checkAlwaysAccessAtDeviceLevel($device)
    {
        if ($device->available_device_id == 3 && $device->has_related_ticket_reader != 1) {
            if ($device->has_gate) {
                return true;
            }
        } elseif ($device->available_device_id == 3 && $device->has_related_ticket_reader != 0) {
            if (!$device->has_gate && isset($device->device_ticket_reader) && $device->device_ticket_reader->barrier_status == 3) {
                return true;
            }
        } elseif (in_array($device->available_device_id, array(1, 2)) && $device->barrier_status == 3) {
            return true;
        }
        return false;
    }
    private function checkRuleResponse($response)
    {
        if (isset($response['status']) && $response['status'] == "denied") {
            return true;
        }
    }
    function moveItemsToEnd($rules)
    {
        $enableRules = array();
        $disableRules = array();
        foreach ($rules as $rule) {
            if (in_array($rule->id, $this->disableRules)) {
                $disableRules[] = $rule;
            } else {
                if ($rule->access->enable == 1)
                    $enableRules[] = $rule;
            }
        }
        return array_merge($enableRules, $disableRules);
    }
    public function checkName($booking)
    {
        if (isset($booking)) {
            if (isset($booking->first_name)) {
                return $this->getUserName($booking);
            }
        }
    }
    // public function calculateTime($data)
    // {
    //     $file = 'verify_access_request_time.txt';
    //     // Specify the file path within the public directory
    //     $filePath = public_path('verify_access/' . $file);
    //     if (!file_exists($filePath)) {
    //         // Create the file if it doesn't exist
    //         touch($filePath);
    //     }
    //     // Use the File facade to write data to the file
    //     $myfile = fopen($filePath, "w") or die("Unable to open file!");
    //     fwrite($myfile, $data);
    //     fclose($myfile);
    // }

    public function setBooking($booking)
    {
        $this->validBooking = $booking;
    }
}
