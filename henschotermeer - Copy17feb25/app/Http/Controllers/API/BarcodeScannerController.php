<?php

namespace App\Http\Controllers\API;

use App\Barcode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DeviceAuthorizationToken;
use App\LocationOptions;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Bookings;
use Carbon\Carbon;
use App;
use App\Attendants;
use App\AttendantTransactions;
use App\Http\Resources\BarcodeJsonResource;
use App\Http\Controllers\Settings\Settings;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PlateReaderController\VerifyVehicle;
use App\EmailNotification;
use App\Http\Controllers\Settings\BarcodeSettings;
use App\Promo;
use App\TransactionImages;
use App\LocationDevices;

class BarcodeScannerController extends Controller
{
    public $settings = false;
    public $verfiyAccess = false;
    public $deviceSetting = false;
    public $barcodeSetting = false;
    public $data = false;
    public $device_id = null;
    public function __construct()
    {
        $this->settings = new Settings();
        $this->verfiyAccess = new VerifyVehicle();
        $this->barcodeSetting = new BarcodeSettings();
    }
    public function authorizeDevice(Request $request)
    {
        try {
            $this->requestInfo($request);
            $validator = Validator::make(
                $request->all(),
                [
                    'identifire' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json($validator->errors(), Response::HTTP_NOT_FOUND);
            }
            $data = $request->all();
            $location = LocationOptions::first();
            $deviceToken = DeviceAuthorizationToken::where('identifire', $data['identifire'])->first();
            if (!$deviceToken) {
                $deviceToken = new DeviceAuthorizationToken();
                $token = str_random(8) . '-' . base64_encode($location->created_at) . '-' . base64_encode($location->live_id) . '-' . str_random(8);
                $deviceToken->token = $token;
            }
            $deviceToken->identifire = $request->identifire;
            $deviceToken->save();
            $locationDevice = LocationDevices::where('fcm_token', $deviceToken->identifire)->first();
            if (!$locationDevice) {
                $locationDevice = $this->addDevice($deviceToken);
            }
            $message = 'Token generated successfully';
            return response()->json(['token' => $deviceToken->token, 'device_id' => $locationDevice->id, 'message' => $message], Response::HTTP_OK);
        } catch (Exception $ex) {
            return response()->json($ex->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }
    public function verifyBooking(Request $request)
    {
        try {
            $this->requestInfo($request);
            $message = "";
            $validator = Validator::make(
                $request->all(),
                [
                    'barcode' => 'required',
                    'status' => 'required',
                    'lang' => 'required'
                ]
            );
            $response = array();
            if ($validator->fails()) {
                return response()->json($validator->errors(), Response::HTTP_NOT_FOUND);
            }
            $token = explode('-', $request->bearerToken());
            $this->deviceSetting = DeviceAuthorizationToken::where('token', $request->bearerToken())->first();
            if (!$this->deviceSetting || count($token) < 4) {
                $message = "Invalid Token";
                return response()->json(['message' => $message], Response::HTTP_BAD_REQUEST);
            }
            $data = $request->all();
            if ($data['device_id']) {
                $this->device_id = $data['device_id'];
            }
            if ($data['lang'] == "en") {
                App::setLocale("en");
            } else {
                App::setLocale("nl");
            }
            $location_id = base64_decode($token[2]);
            $location = LocationOptions::where('live_id', $location_id)->first();
            if (!$location) {
                $message = "Invalid Token";
                return response()->json(['message' => $message], Response::HTTP_BAD_REQUEST);
            }
            if (strtolower($data['status']) == "in") {
                $res = $this->getCheckInBooking($data);
                if (!$res) {
                    $response['code'] = 404;
                    $response['status'] = trans('device-authorize.invalid_booking');
                    $response['message'] = trans('device-authorize.not_found');
                    return response()->json($response, Response::HTTP_NOT_FOUND);
                }
                return response()->json($res, Response::HTTP_OK);
            } elseif (strtolower($data['status']) == "out") {
                $res = $this->getCheckOutBooking($data);
                if (!$res) {
                    $response['code'] = 404;
                    $response['status'] = trans('device-authorize.invalid_booking');
                    $response['message'] = trans('device-authorize.not_found');
                    return response()->json($response, Response::HTTP_NOT_FOUND);
                }
                return response()->json($res, Response::HTTP_OK);
            }
        } catch (Exception $ex) {
            $res['message'] = $ex->getMessage();
            $res['line'] = $ex->getLine();
            $res['file'] = $ex->getFile();
            return response()->json($res, Response::HTTP_BAD_REQUEST);
        }
    }
    function getCheckInBooking($data)
    {
        $currentTime = date('Y-m-d H:i:s');
        $validBooking = Bookings::with([
            'attendant_transactions' => function ($query) {
                $query->where('auto_check_out', 0)->where('check_in', '!=', Carbon::today())->orderBy('updated_at', 'desc')
                    ->with(['transaction_images.location_device']);
            }
        ])->where(function ($query) use ($data) {
            $query->where('id', $data['barcode'])->orWhere('live_id', $data['barcode']);
        })->where('checkin_time', '<=', date('Y-m-d H:i:s'))
            ->where('checkout_time', '>', date('Y-m-d H:i:s'))->orderBy('created_at', 'DESC')->first();
        if ($validBooking) {
            return $this->checkBookingStatus($data['status'], $validBooking);
        }
        $validBooking = Bookings::with([
            'attendant_transactions' => function ($query) {
                $query->where('auto_check_out', 0)->orderBy('updated_at', 'desc')
                    ->with(['transaction_images.location_device']);
            }
        ])->where(function ($query) use ($data) {
            $query->where('id', $data['barcode'])
                ->orWhere('live_id', $data['barcode']);
        })->orderBy('created_at', 'DESC')->first();
        if ($validBooking) {
            return $this->checkBookingStatus($data['status'], $validBooking);
        }
        $vipBarcode = $this->checkBarcodeType($data['barcode']);
        if ($vipBarcode) {
            return $this->checkBookingStatus($data['status'], null, $vipBarcode);
        }
        $emailNotificationBarcode = EmailNotification::where('ticket_token', $data['barcode'])->first();
        if ($emailNotificationBarcode) {
            $validBooking = $this->verifyEmailBarcode($emailNotificationBarcode, $data);
            return $this->checkBookingStatus($data['status'], $validBooking);
        }
        return false;
    }
    function getCheckOutBooking($data)
    {
        $validBooking = Bookings::with([
            'attendant_transactions' => function ($query) {
                $query->where('auto_check_out', 0)->orderBy('updated_at', 'desc')
                    ->with(['transaction_images.location_device']);
            }
        ])->whereHas('attendant_transactions', function ($query) {
            $query->whereDate('check_in', '=', Carbon::today())->whereNull('check_out');
        })->where('checkin_time', '<=', date('Y-m-d H:i:s'))->where('checkout_time', '>', date('Y-m-d H:i:s'))->where(function ($query) use ($data) {
            $query->where('id', $data['barcode'])
                ->orWhere('live_id', $data['barcode']);
        })->orderBy('created_at', 'DESC')->first();
        if ($validBooking) {
            return $this->checkBookingStatus($data['status'], $validBooking);
        }
        $validBooking = Bookings::with([
            'attendant_transactions' => function ($query) {
                $query->where('auto_check_out', 0)->orderBy('updated_at', 'desc')
                    ->with(['transaction_images.location_device']);
            }
        ])->where(function ($query) use ($data) {
            $query->where('id', $data['barcode'])
                ->orWhere('live_id', $data['barcode']);
        })->orderBy('created_at', 'DESC')->first();
        if ($validBooking) {
            return $this->checkBookingStatus($data['status'], $validBooking);
        }
        $vipBarcode = $this->checkBarcodeType($data['barcode']);
        if ($vipBarcode) {
            return $this->checkBookingStatus($data['status'], null, $vipBarcode);
        }
        return false;
    }
    function checkBookingStatus($status, $booking = null, $barcode = null)
    {
        if ($booking == null) {
            switch (strtolower($status)) {
                case "in":
                    if ($barcode->type == "person") {
                        $booking_details = $this->barcodeSetting->add_barcode_booking($barcode->barcode);
                        if ($booking_details) {
                            $this->setBookingEntry($booking_details);
                            return BarcodeJsonResource::make('checked_in', $booking_details);
                        }
                        return false;
                    }
                    return BarcodeJsonResource::make('checked_in', null, $barcode);
                    break;
                case "out":
                    if ($barcode->type == "person") {
                        $booking_details = Bookings::where('barcode', $barcode->barcode)
                            ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
                            ->whereNull('checkout_time')
                            ->where('type', 5)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        if ($booking_details) {
                            $this->setBookingExit($booking_details);
                            return BarcodeJsonResource::make('checked_out', $booking_details);
                        }
                        return false;
                    }
                    return BarcodeJsonResource::make('checked_out', null, $barcode);
                    break;
                default:
                    return false;
            }
        }
        $currentTime = date('Y-m-d H:i:s');
        $at_location = false;
        $res = [];

        if ($status == "out" && empty($booking)) {
            $res['code'] = 404;
            $res['status'] = trans('device-authorize.invalid_booking');
            $res['message'] = trans('device-authorize.not_found');
            return $res;
        }
        $at_location = $this->settings->is_booking_at_location($booking->id);
        $last_transaction = $this->settings->lastTransaction($booking->id);
        switch (strtolower($status)) {
            case "in":
                if ($booking->type == 4 || $booking->type == 3) {
                    return BarcodeJsonResource::make('in_valid', $booking);
                } elseif ($booking->is_blocked) {
                    return BarcodeJsonResource::make('blocked', $booking);
                } elseif ($booking->checkin_time <= $currentTime && $booking->checkout_time > $currentTime && !$at_location) {
                    if ($this->deviceSetting->allow_transaction && $booking->type == 6) {
                        $this->setBookingEntry($booking);
                    }
                    return BarcodeJsonResource::make('checked_in', $booking);
                } elseif ($booking->checkout_time >  $currentTime  && $at_location) {
                    if ($this->deviceSetting->allow_transaction && $booking->type == 6) {
                        $this->setBookingEntry($booking);
                    }
                    return BarcodeJsonResource::make("on_location", $booking);
                } elseif ($booking->checkout_time < $currentTime && $booking->type != 3) {
                    return BarcodeJsonResource::make("expired", $booking);
                } elseif ($booking->checkout_time > $currentTime) {
                    return BarcodeJsonResource::make("not_valid", $booking);
                } else {
                    return false;
                }
                break;
            case "out":

                if ($booking->type == 4 || $booking->type == 3) {
                    return BarcodeJsonResource::make('in_valid', $booking);
                } elseif ($booking->is_blocked) {
                    return BarcodeJsonResource::make('blocked', $booking);
                } elseif ($booking->checkout_time > $currentTime && $at_location) {
                    if ($this->deviceSetting->allow_transaction && $booking->type == 6) {
                        $this->setBookingExit($booking);
                    }
                    return BarcodeJsonResource::make("checked_out", $booking);
                } elseif ($booking->checkin_time <= $currentTime && $booking->checkout_time > $currentTime && !$last_transaction) {
                    if ($this->deviceSetting->allow_transaction && $booking->type == 6) {
                        dd('ee');
                        $this->setBookingExit($booking);
                    }
                    return BarcodeJsonResource::make("checked_out", $booking);
                } elseif ($booking->checkout_time > $currentTime && ($last_transaction && $last_transaction->check_in != null && $last_transaction->check_out != null)) {
                    return BarcodeJsonResource::make("already_checkout", $booking);
                } else if ($booking->checkout_time > $currentTime) {
                    return BarcodeJsonResource::make("not_valid", $booking);
                } elseif ($booking->checkout_time < $currentTime) {
                    return BarcodeJsonResource::make("expired", $booking);
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }
    }
    public function setBookingEntry($booking)
    {
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
            ->where('id', $booking->id)->get();
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
        $attendant_transaction = new AttendantTransactions();
        $attendant_transaction->attendant_id = $attendant_id;
        $attendant_transaction->check_in = date('Y-m-d H:i:s');
        $attendant_transaction->save();
        $imageTransaction = TransactionImages::where('transaction_id', $attendant_transaction->id)->first();
        if (!$imageTransaction) {
            $imageTransaction = new TransactionImages();
            $imageTransaction->transaction_id = $attendant_transaction->id;
            $imageTransaction->device_id = $this->device_id;
            $imageTransaction->type = "in";
            $imageTransaction->save();
        }
        $imageTransaction->device_id = $this->device_id;
        $imageTransaction->type = "in";
        $imageTransaction->save();
    }
    public function setBookingExit($booking)
    {
        $attendant = Attendants::where('booking_id', $booking->id)->first();
        if (!$attendant) {
            $attendant = new Attendants();
        }
        $attendant->booking_id = $booking->id;
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
            $attendant_transaction->save();
        } else {
            $attendant_transaction->check_out = date('Y-m-d H:i:s');
            $attendant_transaction->save();
        }
        $attendants = array();
        $existing_checked_in_bookings = Bookings::whereHas('attendant_transactions', function ($query) {
            $query->whereNull('check_out');
        })->where('id', $booking->id)->get();
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
        $imageTransaction = TransactionImages::where('transaction_id', $attendant_transaction->id)->first();
        if (!$imageTransaction) {
            $imageTransaction = new TransactionImages();
            $imageTransaction->transaction_id = $attendant_transaction->id;
            $imageTransaction->device_id = $this->device_id;
            $imageTransaction->type = "out";
        }
        $imageTransaction->device_id = $this->device_id;
        $imageTransaction->type = "out";
        $imageTransaction->save();

        return TRUE;
    }
    public function requestInfo(Request $request)
    {
        $requestData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'parameters' => $request->all(),
        ];

        Log::info('Request Data:', $requestData);
    }
    public function checkBarcodeType($barcode)
    {
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
    public function verifyEmailBarcode($barcode, $data)
    {
        $type = $barcode->type == 'user_list' ? 3 : 4;
        if ($data['status'] == "in") {
            if ($barcode->type != "promo") {
                if ($barcode->type != 'user_list') {
                    return $this->notificationTime($barcode);
                }
                $booking_details = Bookings::where('customer_id', $barcode->customer_id)
                    ->where('type', $type)
                    ->orderBy('created_at', 'desc')->first();
                if ($booking_details) {
                    return $booking_details;
                }
            } else {
                $promo = Promo::where('live_id', $barcode->type_id)
                    ->first();
                if ($promo) {
                    $booking_details = Bookings::with('promo')->where('customer_id', $barcode->customer_id)
                        ->where('promo_code', $promo->type_id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($booking_details) {
                        return $booking_details;
                    }
                }
                return false;
            }
        } else if ($data['status'] == "out") {
            if ($barcode->type == 'customer') {
                return $this->notificationTime($barcode);
            }
            $booking_details = Bookings::whereHas('attendant_transactions', function ($query) {
                $query->whereNull('check_out');
            })
                ->where('type', $type)
                ->where('customer_id', $barcode->customer_id)
                ->first();
            if ($booking_details) {
                return $booking_details;
            }
        }
    }
    function notificationTime($notificationTime)
    {
        return (!($notificationTime->checkin_time <= date('Y-m-d H:i') && $notificationTime->checkout_time > date('Y-m-d H:i')));
    }
    public function addDevice($deviceToken)
    {
        $locationOption = LocationOptions::first();
        $locationId = $locationOption->live_id;
        $device = LocationDevices::where('fcm_token', $deviceToken->identifire)->first();
        if (!$device) {
            $device = new LocationDevices();
        }
        $device->device_name = "Zebra Scanner";
        $device->available_device_id = 13;
        $device->device_direction = "bi-directional";
        $device->is_synched = 0;
        $device->enable_log = 0;
        $device->enable_idle_screen = 0;
        $device->opacity_input = 0;
        $device->camera_enabled = 0;
        $device->has_gate = 0;
        $device->has_barrier = 0;
        $device->barrier_close_time = 0;
        $device->qr_code_type = 0;
        $device->confidence_level_lowest = 0;
        $device->character_match_limit = 0;
        $device->is_opened = 0;
        $device->has_related_ticket_reader = 0;
        $device->has_sdl = 0;
        $device->gate_close_transaction_enabled = 0;
        $device->has_pdl = 0;
        $device->plate_correction_enabled = 0;
        $device->barrier_status = 0;
        $device->has_always_access = 0;
        $device->has_enable_person_ticket = 0;
        $device->has_enable_parking_ticket = 0;
        $device->popup_time = 0;
        $device->printer_name = 0;
        $device->is_imported = 0;
        $device->disable_night_mode = 0;
        $device->light_condition = 0;
        $device->emergency_entry_exit = 0;
        $device->fcm_token = $deviceToken->identifire;;
        $device->save();
        return $device;
    }
}
