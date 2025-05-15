<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Artisan;
use GuzzleHttp\Client;

class Settings extends Controller {

    public $confidence_val = 80;
    public $lang_id = FALSE;
    public $location_created_at = '1552661741';
    public $ticket_reader;
    public $lag_time = 30;
    public $settings;
    public $key = 'MTk3Nl8yODI=';
    public $url = "";

    public function __construct($key = NULL) {
        $this->url = env('API_BASE_URL');
        $location_setting = \App\LocationOptions::first();
        if ($key !== NULL) {
            $this->key = $key;
        } else {
            $user = \App\User::first();
            if ($user) {

                if ($location_setting) {
                    $key = $location_setting->live_id . '_' . $user->live_id;
                    $this->key = base64_encode($key);
                }
            }
        }

        if (!empty($location_setting->time_lag)) {
            $this->lag_time = $location_setting->time_lag;
        }
        $this->location_created_at = strtotime($location_setting->created_at);
    }

	function get_endpoints_v1(){
			return array(
            'initialize_settings_status' => 'initialize_settings_status',
            'location_settings' => 'location_settings',
            'location_timings' => 'location_timings',
            'location_whitelist_timings' => 'location_whitelist_timings',
            'whitelist_settings' => 'whitelist_settings',
            'userlist_settings' => 'userlist_settings',
            'device_settings' => 'device_settings',
            'location_settings_status' => 'location_settings_status',
            'location_timings_status' => 'location_timings_status',
            'location_whitelist_timings_status' => 'location_whitelist_timings_status',
            'whitelist_settings_status' => 'whitelist_settings_status',
            'userlist_settings_status' => 'userlist_settings_status',
            'device_settings_status' => 'device_settings_status',
            'other_settings_status' => 'other_settings_status',
            'verify_access' => 'verify_access',
            'verify_access_status' => 'verify_access_status',
            'health_check' => 'health_check',
            'check_status' => 'check_status',
            'bar_code' => 'bar_code',
            'verify_plate_num' => 'verify_plate_num',
            'verify_plate_num_status' => 'verify_plate_num_status',
            'remove_prending_transaction' => 'remove_prending_transaction',
            'release_plate_reader' => 'release_plate_reader',
            'device_alerts' => 'device_alerts',
            'door_close' => 'door_close',
            'get_related_plate_reader_state' => 'get_related_plate_reader_state',
            'open_gate_status' => 'open_gate_status',
        );
	}
	
    function get_endpoints() {
        return array(
            'initialize_settings_status' => 'initialize_settings_status',
            'location_settings' => 'location_settings',
            'location_timings' => 'location_timings',
            'location_whitelist_timings' => 'location_whitelist_timings',
            'whitelist_settings' => 'whitelist_settings',
            'userlist_settings' => 'userlist_settings',
            'device_settings' => 'device_settings',
            'location_settings_status' => 'location_settings_status',
            'location_timings_status' => 'location_timings_status',
            'location_whitelist_timings_status' => 'location_whitelist_timings_status',
            'whitelist_settings_status' => 'whitelist_settings_status',
            'userlist_settings_status' => 'userlist_settings_status',
            'device_settings_status' => 'device_settings_status',
            'other_settings_status' => 'other_settings_status',
            'verify_access' => 'verify-access-request',
            'verify_access_status' => 'verify-access-request-status',
            'health_check' => 'health_check',
            'check_status' => 'check_status',
            'bar_code' => 'bar_code',
            'verify_plate_num' => 'verify-access-request',
            'verify_plate_num_status' => 'verify-access-request-status',
            'remove_prending_transaction' => 'remove_prending_transaction',
            'release_plate_reader' => 'release_plate_reader',
            'device_alerts' => 'device_alerts',
            'door_close' => 'door_close',
            'get_related_plate_reader_state' => 'get_related_plate_reader_state',
            'open_gate_status' => 'open_gate_status',
			'store_device_logs'=> 'store_device_logs'
        );
    }

    function get_od_endpoints() {
        return array();
    }

    function get_payment_terminal_endpoints() {
        return array(
            'get_products' => 'get_products',
            'get_ticket_price' => 'get_ticket_price',
            'get_ticket_price_status' => 'get_ticket_price_status',
            'get_vehicle_ticket_price' => 'get_vehicle_ticket_price',
            'get_vehicle_ticket_price_status' => 'get_vehicle_ticket_price_status',
            'get_person_ticket_price_transaction' => 'get_person_ticket_price_transaction',
            'get_person_ticket_price' => 'get_person_ticket_price',
            'get_person_ticket_price_status' => 'get_person_ticket_price_status',
            'get_license_plate_transactions' => 'search_plate',
            'get_messages' => 'get_messages'
        );
    }

    function get_plate_reader_endpoints() {
        return array(
            'verify-access-request' => 'verify-access-request',
            'verify-access-request-status'=> 'verify-access-request-status',
            'other_settings_status' => 'other_settings_status',
			'store_device_logs' => 'store_device_logs',
			'verify_low_confidence_vehicle' => 'verify_low_confidence_vehicle',
			'sendCancelManualAccessControl' => 'sendCancelManualAccessControl'
        );
    }

    function is_valid_call($key, $id) {
        $key_array = explode('-', $key);
        if (count($key_array) != 2) {
            return FALSE;
        }
        $location_settings = new LocationSettings();
        $location = $location_settings->get_location();
        if (strtotime($location->created_at) != $key_array[0]) {
            return FALSE;
        }
        if ($id == null) {
            $id = $key_array[1];
        }
        $location_device = \App\LocationDevices::find($id);
        if (!$location_device) {
            return FALSE;
        }
        \Illuminate\Support\Facades\Session::put('error_message', 'Invalid Access');
        return $location_device;
    }

    function initialize_settings_status(Request $request, $key, $id = null, $status) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Access ',
                    'data' => FALSE,
                );
            }
            $statuses = explode(':', $status);
            if (count($statuses) != 6) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Status',
                    'data' => FALSE,
                );
            }
            $error_message = 'Unable to connect';
            if (!empty($request->error_message)) {
                $error_message = $request->error_message;
            }

            $device_id = $valid_settings->id;
            $device_settings = \App\DeviceSettings::where('device_id', $device_id)->first();
            if (!$device_settings) {
                $device_settings = new \App\DeviceSettings();
            }
            if ($statuses[0]) {
                $device_settings->location_settings = 1;
                $device_settings->location_settings_details = NULL;
            } else {
                $device_settings->location_settings = 0;
                $device_settings->location_settings_details = $error_message;
            }
            if ($statuses[1]) {
                $device_settings->location_timings_settings = 1;
                $device_settings->location_timings_settings_details = NULL;
            } else {
                $device_settings->location_timings_settings = 0;
                $device_settings->location_timings_settings_details = $error_message;
            }
            if ($statuses[2]) {
                $device_settings->location_whitelist_timings_settings = 1;
                $device_settings->location_whitelist_timings_settings_details = NULL;
            } else {
                $device_settings->location_whitelist_timings_settings = 0;
                $device_settings->location_whitelist_timings_settings_details = $error_message;
            }
            if ($statuses[3]) {
                $device_settings->whitelist_settings = 1;
                $device_settings->whitelist_settings_details = NULL;
            } else {
                $device_settings->whitelist_settings = 0;
                $device_settings->whitelist_settings_details = $error_message;
            }
            if ($statuses[4]) {
                $device_settings->userlist_settings = 1;
                $device_settings->userlist_settings_details = NULL;
            } else {
                $device_settings->userlist_settings = 0;
                $device_settings->userlist_settings_details = $error_message;
            }
            if ($statuses[5]) {
                $device_settings->device_settings = 1;
                $device_settings->device_settings_details = NULL;
            } else {
                $device_settings->device_settings = 0;
                $device_settings->device_settings_details = $error_message;
            }

            $device_settings->save();
            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $device_settings,
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => 'Error',
                'data' => $ex->getMessage(),
            );
        }
    }

    public function settings_updated($status) {
        $devices = \App\LocationDevices::get();
        if ($devices->count() > 0) {
            foreach ($devices as $device) {
                if (!$device->is_synched) {
                    continue;
                }
                if ($status == 'location_setting') {

                    Artisan::call('command:InitializeDevice', [
                        'device' => $device->id, 'status' => 'location_setting'
                    ]);
                    Artisan::call('command:InitializeDevice', [
                        'device' => $device->id, 'status' => 'timings'
                    ]);
                    Artisan::call('command:InitializeDevice', [
                        'device' => $device->id, 'status' => 'whitelist_tiings'
                    ]);
                } elseif ($status == 'whitelist_users') {
                    Artisan::call('command:InitializeDevice', [
                        'device' => $device->id, 'status' => 'whitelist_users'
                    ]);
                } elseif ($status == 'userlist_users') {
                    Artisan::call('command:InitializeDevice', [
                        'device' => $device->id, 'status' => 'userlist_users'
                    ]);
                } elseif ($status == 'od') {
                    Artisan::call('command:InitializeDevice', [
                        'device' => $device->id, 'status' => 'od'
                    ]);
                }
            }
        }
    }

    public function run_socket_connection_command($device_id, $status) {
        Artisan::call('command:InitializeDevice', [
            'device' => $device_id, 'status' => $status
        ]);
    }

    public function run_keep_alive_command($device_id) {
        Artisan::call('command:KeepAliveDevice', [
            'device' => $device_id
        ]);
    }

    public function health_check(Request $request, $key, $id) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return 1;
            }
            $response = 1; //$this->check_system_live_status();
            if ($response) {
                return 1;
            }
        } catch (\Exception $ex) {
            return 1;
        }
    }

    public function check_system_live_status() {
        \Illuminate\Support\Facades\Session::put('error_message', 'System is not live');
        try {
            $http = new Client();
            $response = $http->post(env('API_BASE_URL') . '/api/health-check');
            if ($response->getStatusCode() == 200) {

                \Illuminate\Support\Facades\Session::put('error_message', 'System is live');
                return 1;
            }
            return 0;
        } catch (\Exception $ex) {
            return 0;
        }
    }

    public function is_booking_at_location($booking_id) {
        $attendant = \App\Attendants::where('booking_id', $booking_id)->first();
        if (!$attendant) {
            return FALSE;
        }
        $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendant->id)
                ->orderBy('created_at', 'desc')
                ->first();

        if (!$attendant_transaction) {
            return FALSE;
        }
        if ($attendant_transaction->check_in != NULL && $attendant_transaction->check_out != NULL) {
            return FALSE;
        }
        if ($attendant_transaction->check_in != NULL && $attendant_transaction->check_out == NULL) {
            return TRUE;
        }
        return FALSE;
    }
	public function lastTransaction($booking_id)
    {
        $attendant = \App\Attendants::where('booking_id', $booking_id)->first();
        if (!$attendant) {
            return FALSE;
        }
        $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendant->id)
            ->whereDate('check_in', '=', \Carbon\Carbon::today())->orderBy('created_at', 'desc')
            ->first();
        if ($attendant_transaction) {
            return $attendant_transaction;
        }
        return false;
    }

    public function send_message_od($device_id, $message_text, $message_key) {
        $message_code = $this->get_od_message_codes($message_key);
        if (!$message_code) {
            return FALSE;
        }
        $message = $message_code . ':' . $message_text;
        $device = \App\LocationDevices::find($device_id);
        if (!$device) {
            return FALSE;
        }
        if (!$this->is_device_synched($device_id)) {
            return FALSE;
        }
        if ($device->available_device_id == 1 || $device->available_device_id == 2) {
            $device_ods = $this->get_device_ods($device_id);
            if (count($device_ods) > 0) {
                foreach ($device_ods as $device_od) {
                    Artisan::call('command:OdSendMessage', [
                        'device' => $device_od->id, 'message' => $message
                    ]);
                }
            }
        } elseif ($device->available_device_id == 3) {
            $ticket_readers = $this->get_device_ticket_readers($device_id);
            if (count($ticket_readers) > 0) {
                foreach ($ticket_readers as $ticket_reader) {
                    if (count($ticket_reader) > 0) {
                        foreach ($ticket_reader as $device_od) {
                            Artisan::call('command:OdSendMessage', [
                                'device' => $device_od->id, 'message' => $message
                            ]);
                        }
                    }
                }
            }
        } elseif ($device->available_device_id == 4) {
            Artisan::call('command:OdSendMessage', [
                'device' => $device->id, 'message' => $message
            ]);
        } else {
            return FALSE;
        }
        return TRUE;
    }

    public function is_device_synched($device_id) {
        $device = \App\LocationDevices::find($device_id);
        if ($device) {
            if ($device->is_synched) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function get_device_ods($device_id) {
        $ods = array();
        $device_details = \App\LocationDevices::find($device_id);
        if (!$device_details) {
            return $ods;
        }
        if (!$device_details) {
            return $ods;
        }
        if ($device_details->od_enabled) {
            return $ods;
        }
        if ($device_details->available_device_id == 1 || $device_details->available_device_id == 2) {
            $device_ods = \App\DeviceOds::where('device_id', $device_id)->get();
            if ($device_ods) {
                foreach ($device_ods as $device_od) {
                    $device = \App\LocationDevices::find($device_od->od_id);
                    if ($device) {
                        if ($this->is_device_synched($device->id)) {
                            $ods[] = $device;
                        }
                    }
                }
            }
        }
        return $ods;
    }

    public function get_device_ticket_readers($device_id) {
        $ticket_readers = array();
        $device_details = \App\LocationDevices::find($device_id);
        if (!$device_details) {
            return $ticket_readers;
        }
        if ($device_details->available_device_id != 3) {
            return $ticket_readers;
        }
        $device_ticket_readers = \App\DeviceTicketReaders::where('device_id', $device_id)->first();
        if ($device_ticket_readers) {
            $ticket_readers[] = $this->get_device_ods($device_ticket_readers->ticket_reader_id);
        }
        return $ticket_readers;
    }

    function od_message_codes() {
        return array(
            'rejected' => 8,
            'normal' => 16,
            'vip' => 17,
            'unknown' => 3,
            'number_plate_access' => 15,
            'blocked' => 18,
            'goodbye_exit' => 28,
        );
    }

    function get_od_message_codes($key) {
        $od_message_codes = $this->od_message_codes();
        if (is_array($od_message_codes) && array_key_exists($key, $od_message_codes)) {
            return $od_message_codes[$key];
        }
        return FALSE;
    }

    public function reset_device_settings($device_id) {
        $device_settings = \App\DeviceSettings::where('device_id', $device_id)->first();
        if ($device_settings) {
            $device_settings->location_settings = 0;
            $device_settings->location_timings_settings = 0;
            $device_settings->location_whitelist_timings_settings = 0;
            $device_settings->whitelist_settings = 0;
            $device_settings->userlist_settings = 0;
            $device_settings->device_settings = 0;
            $device_settings->other_settings = 0;
            $device_settings->location_settings_details = NULL;
            $device_settings->location_timings_settings_details = NULL;
            $device_settings->location_whitelist_timings_settings_details = NULL;
            $device_settings->whitelist_settings_details = NULL;
            $device_settings->userlist_settings_details = NULL;
            $device_settings->device_settings_details = NULL;
            $device_settings->other_settings_details = NULL;
            $device_settings->save();
        }
    }

    public function door_close(Request $request, $key, $id) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return 0;
            }
            $device_details = \App\LocationDevices::find($id);
            if ($device_details) {
                $device_details->is_opened = 0;
                $device_details->save();
                return 1;
            }
            return 0;
        } catch (\Exception $ex) {
            return 0;
        }
    }

    public function remove_prending_transaction(Request $request, $key, $id, $vehicle) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return 0;
            }
            $device_plate_reader = \App\DeviceTicketReaders::where('ticket_reader_id', $id)->orderBy('created_at', 'DESC')->first();
            if (!$device_plate_reader) {
                return 0;
            }
            if ($vehicle == 0) {
//                \App\DeviceBookings::where([
//                            ['device_id', $device_plate_reader->device_id],
//                        ])
//                        ->delete();
                Artisan::call('command:ReadyForRecognition', [
                    'device' => $device_plate_reader->device_id
                ]);
            } else {
//                \App\DeviceBookings::where([
//                            ['vehicle_num', $vehicle],
//                            ['device_id', $device_plate_reader->device_id],
//                        ])
//                        ->delete();
                Artisan::call('command:ReadyForRecognition', [
                    'device' => $device_plate_reader->device_id
                ]);
            }
            return 1;
        } catch (\Exception $ex) {
            return 0;
        }
    }

    public function release_plate_reader(Request $request, $key, $id) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return 0;
            }
            $device_plate_reader = \App\DeviceTicketReaders::where('ticket_reader_id', $id)->orderBy('created_at', 'DESC')->first();
            Artisan::call('command:ReadyForRecognition', [
                'device' => $device_plate_reader->device_id
            ]);
            \App\DeviceBookings::where('device_id', $device_plate_reader->device_id)->update(array('is_operator' => 0));
            return 1;
        } catch (\Exception $ex) {
            return 0;
        }
    }

    public function get_related_plate_reader_state(Request $request, $key, $id) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return 2;
            }
            $device_plate_reader = \App\DeviceTicketReaders::where('ticket_reader_id', $id)->orderBy('created_at', 'DESC')->first();
            if (!$device_plate_reader) {
                return 2;
            }
            $plate_reader_id = $device_plate_reader->device_id;
            $device_settings = \App\LocationDevices::find($plate_reader_id);
            if (!$device_settings) {
                return 2;
            }
            if ($device_settings->is_synched) {
                return 1;
            }
            return 0;
        } catch (\Exception $ex) {
            return 2;
        }
    }

    public function handle_missed_bookings() {
        try {
            $devices = \App\LocationDevices::where([
                        ['device_direction', 'in'],
                        ['is_synched', 1]
                    ])->get();
            if ($devices->count() > 0) {
                $valid_devices = array();
                foreach ($devices as $device) {
                    $valid_devices[] = $device->id;
                }
                if (count($valid_devices) > 0) {
                    $device_bookings = \App\DeviceBookings::whereDate('created_at', \Carbon\Carbon::today())
                            ->whereIn('device_id', $valid_devices)
                            ->where('confidence', '>=', 80)
                            ->get();
                    if ($device_bookings->count() > 0) {
                        foreach ($device_bookings as $device_booking) {
                            if (empty($device_booking->vehicle_num)) {
                                continue;
                            }
                            $booking = NULL;
                            $user_list_statuses = array(2, 3);
                            $userlist_booking_details = \App\Bookings::where([
                                        ['vehicle_num', $device_booking->vehicle_num]
                                    ])
                                    ->whereIn('type', $user_list_statuses)
                                    ->first();
                            if ($userlist_booking_details) {
                                $booking = $userlist_booking_details;
                            } else {
                                $booking_details = \App\Bookings::where([
                                            ['vehicle_num', $device_booking->vehicle_num]
                                        ])
                                        ->whereIn('type', array(1, 4, 7))
                                        ->where('checkout_time', '>', date('Y-m-d H:i'))
                                        ->orderBy('created_at', 'DESC')
                                        ->first();
                                if ($booking_details) {
                                    $booking = $booking_details;
                                }
                            }
                            if (!$booking) {
                                $vehicle_num = $device_booking->vehicle_num;
                                $dataArray = array(
                                    'first_name' => 'Paid Vehicle',
                                    'vehicle_num' => $vehicle_num,
                                    'type' => 4,
                                    'is_paid' => 0,
                                    'checkin_time' => date('Y-m-d H:i:s', strtotime($device_booking->created_at)),
                                    'amount' => 0,
                                    'payment_id' => 'Paid Vehicle'
                                );
                                $booking = new \App\Bookings();
                                $booking->type = $dataArray['type'];
                                $booking->first_name = $dataArray['first_name'];
                                $booking->vehicle_num = $dataArray['vehicle_num'];
                                $booking->checkin_time = $dataArray['checkin_time'];
                                $booking->confidence = $device_booking->confidence;
                                $booking->country_code = $device_booking->country_code;
                                $booking->image_path = $device_booking->file_path;
                                $booking->save();
                                $booking_payment = new \App\BookingPayments();
                                $booking_payment->booking_id = $booking->id;
                                $booking_payment->amount = $dataArray['amount'];
                                $booking_payment->payment_id = $dataArray['payment_id'];
                                $booking_payment->checkin_time = $dataArray['checkin_time'];
                                $booking_payment->save();
                                try {
                                    if (!$this->key) {
                                        $error_log = new \App\Http\Controllers\LogController();
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
                                    ]);
                                    $responseData = json_decode((string) $response->getBody(), true);
                                    if (is_array($responseData) && array_key_exists('booking_info_live_id', $responseData['data'])) {
                                        $booking->live_id = $responseData['data']['booking_info_live_id'];
                                        $booking->save();
                                    }
                                    if (is_array($responseData) && array_key_exists('booking_payment_live_id', $responseData['data'])) {
                                        $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                                        $booking_payment->save();
                                    }
                                } catch (\Exception $ex) {
                                    $error_log = new \App\Http\Controllers\LogController();
                                    $error_log->log_create('get_vehicle_booking', $ex->getMessage(), $ex->getTraceAsString());
                                }
                            }
                            $attendant_existing = \App\Attendants::where('booking_id', $booking->id)->first();
                            if ($attendant_existing) {
                                $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendant_existing->id)
                                        ->whereNull('check_out')
                                        ->whereDate('check_in', \Carbon\Carbon::today())
                                        ->first();
                                if ($attendant_transaction) {
                                    $device_booking->delete();
                                    continue;
                                }
                            }
                            $bookingId = $booking->id;
                            $attendant = \App\Attendants::where('booking_id', $bookingId)->first();
                            if (!$attendant) {
                                $attendant = new \App\Attendants();
                            }
                            $attendant->booking_id = $bookingId;
                            $attendant->save();
                            $attendant_id = $attendant->id;
                            $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendant_id)
                                    ->whereDate('created_at', \Carbon\Carbon::today())
                                    ->first();
                            if (!$attendant_transaction) {
                                $attendant_transaction = new \App\AttendantTransactions();
                                $attendant_transaction->attendant_id = $attendant_id;
                                $attendant_transaction->check_in = date('Y-m-d H:i:s', strtotime($device_booking->created_at));
                                $attendant_transaction->save();
                            }

                            $transaction_images = new \App\TransactionImages();
                            $transaction_images->image_path = $device_booking->file_path;
                            $transaction_images->device_id = $device_booking->device_id;
                            $transaction_images->transaction_id = $attendant_id;
                            $transaction_images->type = 'in';
                            $transaction_images->save();
                            $device_booking->delete();
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            
        }
    }

}
