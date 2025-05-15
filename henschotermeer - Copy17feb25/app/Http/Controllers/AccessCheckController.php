<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\VerfiyAccessService;
use App\Service\LogService;
use App\Http\Controllers\Settings\VerifyBookings;
use App\Http\Controllers\PlateReaderController\VerifyVehicle;
use App\Http\Controllers\Settings\Settings;
use App\Http\Controllers\Settings\LocationSettings;
use App\LocationOptions;
use App\DeviceBookings;
use App\User;
use App\LocationDevices;
use Illuminate\Support\Facades\Session;
use Exception;
use App\Language;
use App\ParkingAccessRule;
use App\ParkingRulesName;
use App\TommyReservationParents;
use App\UnwantedCharacter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\DeviceLog;
use Lang;

class AccessCheckController extends Controller
{
    //
    public $confidence_val = 80;
    public $lang_id = FALSE;
    public $location_created_at = '1552661741';
    public $ticket_reader;
    public $lag_time = 30;
    public $settings;
    public $key = 'MTk3Nl8yODI=';
    public $url = "";
    public $location_setting = FALSE;
    public $parkingRules = "";
    public $rulePass = false;
    public $unwanted_array = [];
    public $validDeviceSettings = false;
    public $validBooking = false;
    public $matching = false;
    public $comfortSecurity = false;
    public $vehicleNumber = false;
    public $barcode = false;
    public $confidence_status = false;
    public $deviceBooking = null;
    public $deviceId = false;
    public $validBookingTypes = array(1, 4, 7, 10);
    public $userListStatuses = array(2, 3);
    public $attendant_transactions = null;
    public $disableRules = null;
    public $plateCorrection = false;
    public $isNullOrEmptyOrShort = false;
    public $lastTrueRule = null;
    public $verifyAccessService = null;
    public $parkingLocationSetting;
    public $ticketVehicleNumber = false;
    public $deviceBookingId = false;
    public $ticketType = false;
    public $verifyVehicle = false;
    public $verifyBooking = false;
    public $deviceConfidence = false;
    public $bookingId = false;
    public $logService = false;
    public $testingSessionId = false;
    public $info = "info";
    public $debug = "debug";
    public $error = "error";
    public $locale = false;
    public $regressionTesting = false;
    public $testingRequest = false;
    public function __construct($key = NULL)
    {
        $this->url = env('API_BASE_URL');
        $this->ticket_reader = new VerifyBookings();
        $this->settings = new Settings();
        $this->parkingLocationSetting = new LocationSettings();
        $this->verifyVehicle = new VerifyVehicle();
        $location_setting = LocationOptions::first();
        $this->comfortSecurity = $this->comfortSecurityCheckEnable();
        $this->unwanted_array = UnwantedCharacter::pluck('valid_character', 'unwanted_character')->all();
        if ($key !== NULL) {
            $this->key = $key;
        } else {
            $user = User::first();
            if ($user) {

                if ($location_setting) {
                    $key = $location_setting->live_id . '_' . $user->live_id;
                    $this->key = base64_encode($key);
                }
            }
        }
        $this->location_setting = $location_setting;
        if (!empty($location_setting->time_lag)) {
            $this->lag_time = $location_setting->time_lag;
        }
        $this->testingSessionId = $this->generateRandomString(10);
        $this->location_created_at = strtotime($location_setting->created_at);
        $this->logService = new LogService($this->testingSessionId, 'Start Booking', false, false);
        $this->verifyAccessService = new VerfiyAccessService($this->url, $this->ticket_reader, $this->settings, $this->verifyVehicle, $this->location_setting, $this->location_created_at, $this->logService, $this->testingSessionId);
    }

    public function verifyAccessRequest(Request $request, $key, $deviceId, $identifier, $confidence = null, $country_code = false)
    {
        // setting request variable  //
        $filePath = NULL;
        //$this->deviceBookingId = $request->device_booking_id;
        $this->ticketVehicleNumber = $request->vehicle_number;
        $this->ticketType = $request->ticket_type;
        $this->deviceId = $deviceId;
        $this->deviceConfidence = $confidence;
        if ($this->deviceConfidence) {
            $request['device_confidence'] = $this->deviceConfidence;
        }
        if ($request->regression_testing == "yes") {
            Session::put('testing_session_id', $this->testingSessionId);
            $this->regressionTesting = true;
        }
        // setting request variable  //
        $this->set_lang_id($country_code);
        $this->logService->info($this->testingSessionId, 'Country locale (' . $this->locale . ') has been set.', false, false);
        try {
            if ($request->hasFile('file')) {
                $filePath = $this->saveImage($request);
                $this->logService->info($this->testingSessionId, 'Received and saved file with path (' . $filePath . ')', $filePath);
            } elseif ($request->file) {
                $filePath = $this->saveBaseEncodedImage($request->file);
                $this->logService->info($this->testingSessionId, 'Received and saved file with path(' . $filePath . ')', $filePath);
            }
            $this->validDeviceSettings = $this->verifyAccessService->isValidDevice($request,$key, $deviceId);
            if (!$this->validDeviceSettings) {
                $message = $this->ticket_reader->getMessage('unknown', '', $this->lang_id);
                $this->logService->error($this->testingSessionId, $message, false);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $this->settings->send_message_od($deviceId, $message, 'unknown'),
                    'data' => FALSE,
                );
            }
            $this->logService->info($this->testingSessionId, 'A valid device (' . $this->validDeviceSettings['device_name'] . ') found');
            if ($this->validDeviceSettings->available_device_id != 3) {
                $this->logService->info($this->testingSessionId, 'If device is not plate reader than identifier is barcode.', false);
                $this->barcode = $identifier;
            }
            if ($this->validDeviceSettings->available_device_id == 3) {
                $this->logService->info($this->testingSessionId, 'Replacing UnWanted Character from given identifier.(' . $identifier . ')', false);
                $this->vehicleNumber = strtr($identifier, $this->unwanted_array);
                $this->confidence_status = $this->validDeviceSettings->available_device_id == 3 ? $this->check_confidence($this->deviceConfidence) : false;
                if (!$request->regression_testing) {
                    if (!$this->confidence_status) {
                        $this->logService->info($this->testingSessionId, 'Given confidence is less than threshold (' . $this->confidence_val . ' )', false);
                        $this->plateCorrection = $this->verifyAccessService->checkLicensePlate($filePath, $this->deviceConfidence);
                        if ($this->plateCorrection) {
                            $this->logService->info($this->testingSessionId, 'Plate Correction is enabled', false);
                        }
                    } else {
                        $this->logService->info($this->testingSessionId, 'Given confidence of device is (' . $this->deviceConfidence . '). As given confiedence is greater than threshold (' . $this->confidence_val . ').', false);
                    }
                }
            }
            $this->logService->info($this->testingSessionId, 'Checking incoming idenitifier (' . $identifier . ')', false);
            if (in_array($this->validDeviceSettings->available_device_id, array(1, 3))) {
                if ($this->validDeviceSettings->available_device_id == 1 && (isset($this->ticketVehicleNumber))) {
                    $this->isNullOrEmptyOrShort = $this->isNullOrEmptyOrShort($this->ticketVehicleNumber);
                } elseif ($this->validDeviceSettings->available_device_id == 3) {
                    $this->isNullOrEmptyOrShort = $this->isNullOrEmptyOrShort($this->vehicleNumber);
                }
            }
            $this->logService->info($this->testingSessionId, 'Checked given identifier: (' . $identifier . ') is valid', false);
            if ($this->validDeviceSettings->available_device_id == 3) {
                $this->logService->info($this->testingSessionId, 'Setting up a temporary booking entry for device (' . $this->validDeviceSettings['device_name'] . ')', false);
                $this->deviceBookingId = $this->set_temporary_booking_entry($this->validDeviceSettings->id, $this->vehicleNumber, $this->deviceConfidence, $filePath, $country_code);
            }
            switch ($this->validDeviceSettings->available_device_id) {
                case 1:
                    $status = $this->ticketReader($request);
                    break;
                case 2:
                    $status = $this->personTicketReader($request);
                    break;
                case 3:
                    $status = $this->plateReaderBooking($request, $filePath);
                    break;
                default:
                    break;
            }
            return $this->processAccessStatus($request, $status, $this->verifyAccessService, $this->ticket_reader, $this->lang_id);
        } catch (Exception $ex) {
            $error_log = new LogController();
            $message = $ex->getMessage() . $ex->getFile() . $ex->getLine();
            $this->logService->error($this->testingSessionId, $message . $this->locale, false);
            $error_log->log_create('vehicle-verify', $ex->getMessage() . '' . $ex->getFile() . $ex->getLine(), $ex->getTraceAsString());
            $message = $this->ticket_reader->getMessage('unknown', '', $this->lang_id);
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'od_sent' => $this->settings->send_message_od($deviceId, $message, 'unknown'),
                'data' => FALSE,
            );
        }
    }
    public function plateReaderBooking($request, $filePath = null)
    {
        $this->logService->info($this->testingSessionId, 'Received a booking request against the plate reader.', false);
        $this->validBooking = $this->verifyAccessService->isValidBooking($request, $this->key, $this->deviceId, $this->vehicleNumber, false, false, $this->lang_id, $this->confidence_status, $this->plateCorrection, $this->isNullOrEmptyOrShort, $this->testingRequest);
		return $ruleStatus = $this->verifyAccessService->validateRule($this->validBooking);
    }
    public function ticketReader($request)
    {
        $this->logService->info($this->testingSessionId, 'Received a booking request against the ticket reader.', false);
        $this->validBooking = $this->verifyAccessService->isValidBooking($request, $this->key, $this->deviceId, false, $this->barcode, $this->ticketVehicleNumber, $this->lang_id, false, false, $this->isNullOrEmptyOrShort, $this->testingRequest);
        $this->rulePass = $this->verifyAccessService->validateRule($this->validBooking);
        if ($this->verifyAccessService->booking->emailNotification) {
            $type = $this->verifyAccessService->booking->emailNotification['type'] == 'user_list' ? 3 : 4;
            $this->validBooking = $this->verifyAccessService->booking->createCustomerVehicleBooking($this->verifyAccessService->booking->emailNotification['customer_id'], $this->ticketVehicleNumber, $this->verifyAccessService->booking->emailNotification['checkin_time'], $this->verifyAccessService->booking->emailNotification['checkout_time'], $type);
            if ($this->validBooking) {
                $this->verifyAccessService->ruleStatus['validBooking'] = $this->validBooking;
                $this->verifyAccessService->setBooking($this->validBooking);
                $message = $this->bookingMessage($this->validDeviceSettings['device_direction']);
                $key = $this->validDeviceSettings['device_direction'] == "in" ? "welcome_entrance" : "goodbye_exit";
                $this->verifyAccessService->accessMessage(1, 'success', $message, $this->settings->send_message_od($this->deviceId, $message, $key), $booking);
                $this->logService->info($this->testingSessionId, 'System found a valid email barcode notification, created a booking against email barcode notification', false);
            }
        }
        return $this->verifyAccessService->ruleStatus;
    }
    public function personTicketReader($request)
    {
        $this->validBooking = $this->verifyAccessService->isValidBooking($request, $this->key, $this->deviceId, false, $this->barcode, $this->ticketVehicleNumber, $this->lang_id, false, false, $this->isNullOrEmptyOrShort, $this->testingRequest);
        $ruleStatus = $this->verifyAccessService->validateRule($this->validBooking);
        return $ruleStatus;
    }
    public function checkDeviceBooking($deviceBookingId)
    {
        if ($this->validBooking) {
            $deviceBooking = DeviceBookings::where('id', $deviceBookingId)->orderBy('created_at', 'DESC')->first();
            if ($deviceBooking) {
                $deviceBooking->booking_id = $this->validBooking['id'];
                $deviceBooking->save();
                //$deviceLogs = DeviceLog::where('vehicle_number', $this->vehicleNumber)->whereNull('device_booking_id')->update(['device_booking_id' => $deviceBooking->id]);
            }
            return False;
        }
    }
    public function verifyAccessRequestStatus(Request $request, $key, $deviceId, $identifier)
    {
        $this->deviceId = $deviceId;
        $open_gate_reason = "Always Access";
        $this->bookingId = $identifier;
        try {
            $this->validDeviceSettings = $this->verifyAccessService->isValidDevice($request,$key, $deviceId);
            if (!$this->validDeviceSettings) {
                $message = $this->ticket_reader->getMessage('unknown', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $this->settings->send_message_od($deviceId, $message, 'unknown'),
                    'data' => FALSE,
                );
            }
            switch ($this->validDeviceSettings->available_device_id) {
                case 1:
                    return $this->verifyAccessService->getDeviceStatus($this->validDeviceSettings, $this->bookingId);
                    break;
                case 2:
                    return $this->verifyAccessService->getDeviceStatus($this->validDeviceSettings, $this->bookingId);
                    break;
                case 3:
                    return $this->verifyAccessService->getDeviceStatus($this->validDeviceSettings, $this->bookingId);
                    break;
                default:
                    return false;
                    break;
            }
        } catch (Exception $ex) {
            $error_log = new LogController();
            $error_log->log_create('vehicle-verify-status', $ex->getMessage(), $ex->getTraceAsString());
            $message = $this->ticket_reader->get_error_message('unknown');
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'od_sent' => $this->settings->send_message_od($deviceId, $message, 'unknown'),
                'data' => FALSE,
            );
        }
    }
    function processAccessStatus($request, $status, $verifyAccessService, $ticket_reader, $lang_id)
    {
        if (!$request->regression_testing) {
            if (in_array($this->validDeviceSettings['available_device_id'], array(1, 3))) {
                if (array_key_exists('access', $status) && ($status['access']['status'] == 1 && $status['access']['access_status'] == "success")) {
                    if (!$this->validBooking && $this->validDeviceSettings['available_device_id'] == 3) {
                        $this->validBooking = $verifyAccessService->booking->createBooking($this->vehicleNumber, $this->deviceId, false);
                        $this->verifyAccessService->ruleStatus['validBooking'] = $this->validBooking;
                        $this->verifyAccessService->setBooking($this->validBooking);
                        $key = $this->validDeviceSettings['available_device_id'] == 2 ? 'welcome_entrance_person' : 'welcome_entrance';
                        $user_name = $this->verifyAccessService->getUserName($this->validBooking);
                        $message = $this->ticket_reader->getMessage($key, $user_name, $this->lang_id);
                        $status['access']['data'] = $this->validBooking->id;
                        $status['access']['message'] = $message;
                    }
                    $type = false;
                    if ($this->validDeviceSettings['device_direction'] == "in") {
                        $type = "entry";
                    } else {
                        $type = "exit";
                    }
                    $name = isset($status['validBooking']['first_name']) ? $status['validBooking']['first_name'] : '';
                    $message = $status['access']['message'];
					
                    $gateOpen = $this->verifyAccessService->open_gate_plate_reader($this->validDeviceSettings, $this->vehicleNumber, $message, $type);
                    
					if (!$gateOpen && !$this->validDeviceSettings['has_gate']) {
                        $message = $ticket_reader->getMessage('system_error', '', $lang_id);
                        $access = array(
                            'status' => 1,
                            'access_status' => 'denied',
                            'message' => $message . ' ' . $name,
                            'od_sent' => $this->settings->send_message_od($this->deviceId, $message, 'system_error'),
                            'data' => FALSE,
                        );
                        $status['access'] = $access;
                        return $status['access'];
                    }
                    if ($this->validDeviceSettings['available_device_id'] == 3) {
                    if (isset($this->deviceBookingId)) {
                        $this->checkDeviceBooking($this->deviceBookingId);
                    }
                    }

                    return $status['access'];
                } else {
                    $message = $status['access']['message'];
                    if ((array_key_exists('access', $status) && ($status['access']['access_status'] == "denied")) && !$this->plateCorrection) {
                        $sendDeniedCommand = $verifyAccessService->send_denied_access_socket($this->validDeviceSettings, $message, $this->vehicleNumber);
                        return $status['access'];
                    }
                    $message = $ticket_reader->getMessage('key_board_enable', '', $lang_id);
                    $status['access']['message'] = $message;
                    return $status['access'];
                }
            } elseif ($this->validDeviceSettings['available_device_id'] == 2) {
                return $status['access'];
            }
        }
        if (array_key_exists('access', $status) && ($status['access']['status'] == 1 && $status['access']['access_status'] == "success")) {
            if (!$this->validBooking && $this->validDeviceSettings['available_device_id'] == 3) {
                $this->validBooking = $verifyAccessService->booking->createBooking($this->vehicleNumber, $this->deviceId, false);
            }
        }
        if ($this->validDeviceSettings['available_device_id'] == 3) {
            if (isset($this->deviceBookingId)) {
                $this->checkDeviceBooking($this->deviceBookingId);
            }
        }
        
        return $status;
    }
    private function logError($functionName, $ex)
    {
        $errorLog = new LogController(); // Replace with your log controller
        $errorLog->log_create($functionName, $ex->getMessage() . '-' . $ex->getLine(), $ex->getTraceAsString());
    }
    function isNullOrEmptyOrShort($value)
    {
        $this->logService->debug($this->testingSessionId, 'Checking incoming identifier: if its empty, null or less than 4', false);
        if (empty($value) && $value == 0) {
            return true;
        }
        if ($value == "0") {
            return true;
        }
        if (strtolower($value) == "none") {
            return true;
        }
        if ($this->validDeviceSettings->available_device_id == 3) {
            if (is_string($value) && strlen($value) < 4) {
                return true;
            }
        }
        return false;
    }
    public function check_confidence($confidence)
    {
        if ((int)$confidence >= $this->confidence_val) {
            return TRUE;
        }
        return FALSE;
    }
    function saveImage(Request $request)
    {
        $file = $request->file('file');
        $extension = $file->extension() ?: 'png';
        $destinationPath = public_path('/uploads/vehicles');
        $safeName = str_random(10) . '.' . $extension;
        $file->move($destinationPath, $safeName);
        $request['pic'] = $safeName;
        $filePath = '/uploads/vehicles' . '/' . $safeName;
        $request->session()->put('vehicle_image', '/uploads/vehicles' . '/' . $safeName);
        return $filePath;
    }
    function saveBaseEncodedImage($file)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $file, $extension)) {
            $data = substr($file, strpos($file, ',') + 1);
            $extension = strtolower($extension[1]) ?: "png"; // jpg, png, gif
            if (!in_array($extension, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $safeName = str_random(10) . '.' . $extension;
            $data = str_replace(' ', '+', $data);
            $data = base64_decode($data);
            if ($data === false) {
                throw new Exception('base64_decode failed');
            }
        } else {
            return FALSE;
        }
        $path = '/uploads/vehicles/' . $safeName;
        file_put_contents(public_path($path), $data);
        return $path;
    }
    public function set_lang_id($country_code)
    {
        if ($country_code == NULL) {
        }
        $lang_details = Language::where('code', $country_code)->first();
        if ($lang_details) {
            $this->lang_id = $lang_details->id;
            $this->locale = $lang_details->code;
        }
    }
    public function set_temporary_booking_entry($device_id, $vehicle_num, $confidence, $file_path, $country_code, $deviceBookingId = NULL)
    {
        $booking = DeviceBookings::where('id', $deviceBookingId)->first();
        if (!$booking) {
            $booking = new DeviceBookings();
        }
        $booking->device_id = $device_id;
        if (isset($vehicle_num)) {
            $booking->vehicle_num = $vehicle_num;
        }
        $booking->confidence = $confidence;
        $booking->file_path = $file_path;
        $booking->country_code = $country_code;
        if ($booking->exists()) {
            $booking->update();
        }
        $booking->save();
        if ($this->validDeviceSettings['has_related_ticket_reader']) {
            Artisan::call('command:download_related_ticket_reader_logs', [
                'device_booking_id' => $booking->id,
                'has_related_ticket_reader' => $this->validDeviceSettings['device_ticket_reader']
            ]);
        }
        return $booking->id;
    }
    public function set_device_confidence($device_id)
    {
        $device_details = LocationDevices::find($device_id);
        if ($device_details) {
            if (!empty($device_details->confidence) && is_numeric($device_details->confidence)) {
                $this->confidence_val = $device_details->confidence;
            }
        }
    }
    public function checkConfidence($confidence)
    {
        if ($confidence >= $this->confidence_val) {
            return TRUE;
        }
        return FALSE;
    }
    private function accessMessage($status, $accessStatus, $message, $od_sent, $data = FALSE)
    {
        return $this->rulePass['access'] = array(
            'status' => $status,
            'access_status' => $accessStatus,
            'message' => $message,
            'od_sent' => $od_sent,
            'data' => $data,
        );
    }
    private function bookingMessage($direction)
    {
        $message = "";
        if ($direction == "in") {
            $message = $this->ticket_reader->getMessage('welcome_entrance', "", $this->lang_id);
            return $message;
        }
        return $message = $this->ticket_reader->getMessage('goodbye_exit', "", $this->lang_id);
    }
    private function matchEnable()
    {
        try {
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
    function generateRandomString($length = 25)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
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
}
