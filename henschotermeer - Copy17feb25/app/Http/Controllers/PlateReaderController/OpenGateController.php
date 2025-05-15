<?php

namespace App\Http\Controllers\PlateReaderController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Settings\Settings;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Process as Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redirect;
use App\DeviceBookings;
use App\Http\Controllers\PlateReaderController\VerifyVehicle;
use App\Http\Controllers\AccessCheckController;

class OpenGateController extends Controller {

    public $controller = 'App\Http\Controllers\VerifyVehicle';
    public $confidence_val = 80;
    public $lang_id = FALSE;
    public $ticket_reader;
    public $lag_time = 10;
    public $settings;
    public $key = FALSE;
    public $url = "";
    public $location_created_at = '1552661741';
    public $accessCall = false;

    public function __construct($key = NULL) {
        $this->url = env('API_BASE_URL');
        $this->ticket_reader = new \App\Http\Controllers\Settings\VerifyBookings();
        $this->settings = new \App\Http\Controllers\Settings\Settings();
        if ($key !== NULL) {
            $this->key = $key;
        } else {
            $user = \App\User::first();
            if ($user) {
                $location_setting = \App\LocationOptions::first();
                if ($location_setting) {
                    $key = $location_setting->live_id . '_' . $user->live_id;
                    $this->key = base64_encode($key);
                }
            }
        }
        $location_setting = \App\LocationOptions::first();
        if (!empty($location_setting->time_lag)) {
            $this->lag_time = $location_setting->time_lag;
        }
        $this->location_created_at = strtotime($location_setting->created_at);
        $this->accessCall = new AccessCheckController();
    }

    /**
     * Is call valid
     * @param type $key
     * @param type $id
     * @return boolean
     */
    public function is_valid_call($key, $id) {
        try {
            $key_array = explode('-', $key);
            if (count($key_array) != 2) {
                return FALSE;
            }
            $location_settings = new \App\Http\Controllers\Settings\LocationSettings();
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
            $device_ticket_readers = \App\DeviceTicketReaders::where('device_id', $id)->first();
            if ($device_ticket_readers) {
                $ticket_reader = \App\LocationDevices::find($device_ticket_readers->ticket_reader_id);
                if ($ticket_reader) {
                    $location_device->device_ticket_reader = $ticket_reader;
                }
            }
            return $location_device;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create($this->controller . '-is_valid_call', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    /**
     * Get booking details
     * @param type $vehicle_num
     * @param type $status
     * @return boolean
     */
    public function get_vehicle_booking($vehicle_num, $status) {
        try {
            $valid_bookings_types = array(1, 4, 7, 10);
            if ($status == 'in') {
                $user_list_statuses = array(2, 3);
                $userlist_booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereNotNull('customer_vehicle_info_id')
                        ->whereIn('type', $user_list_statuses)
                        ->first();
                if ($userlist_booking_details) {
                    if ($userlist_booking_details->customer_id > 0) {
                        $userlist_user = \App\UserlistUsers::where('customer_id', $userlist_booking_details->customer_id)->first();
                        if ($userlist_user) {
                            $this->lang_id = $userlist_user->language_id;
                        }
                    }
                    return $userlist_booking_details;
                }
                $booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereIn('type', $valid_bookings_types)
                        // ->whereNotNull('customer_vehicle_info_id')
                        ->where('checkout_time', '>', date('Y-m-d H:i'))
                        ->where('checkin_time', '<=', date('Y-m-d H:i'))
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($booking_details) {
                    return $booking_details;
                }
                return FALSE;
            } elseif ($status == 'out') {
                $user_list_statuses = array(2, 3);
                $userlist_booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereIn('type', $user_list_statuses)
                        ->whereNotNull('customer_vehicle_info_id')
                        ->first();
                if ($userlist_booking_details) {
                    if ($userlist_booking_details->customer_id > 0) {
                        $userlist_user = \App\UserlistUsers::where('customer_id', $userlist_booking_details->customer_id)->first();
                        if ($userlist_user) {
                            $this->lang_id = $userlist_user->language_id;
                        }
                    }
                    return $userlist_booking_details;
                }
                $booking_details = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                    $query->whereNull('check_out');
                                })->whereIn('type', $valid_bookings_types)
                                ->where('vehicle_num', $vehicle_num)->first();
                if ($booking_details) {
                    return $booking_details;
                }
                $booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereIn('type', $valid_bookings_types)
                        //->whereNotNull('customer_vehicle_info_id')
                        ->where('checkout_time', '>', date('Y-m-d H:i'))
                        ->where('checkin_time', '<=', date('Y-m-d H:i'))
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($booking_details) {
                    return $booking_details;
                }
                return FALSE;
            } else {
                return FALSE;
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create($this->controller . '-get_vehicle_booking_open_gate', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    /**
     * Check is vehicle blocked
     * @param type $vehicle_num
     * @return boolean
     */
    public function is_vehicle_blocked($vehicle_num) {
        try {
            $vehicle_blocked = FALSE;
            $userlist_users = \App\UserlistUsers::where('is_blocked', 1)->get();
            if ($userlist_users) {
                foreach ($userlist_users as $userlist_user) {
                    $customer_id = $userlist_user->customer_id;
                    if (empty($customer_id)) {
                        continue;
                    }
                    $customer_vehicle_infos = \App\CustomerVehicleInfo::where('customer_id', $customer_id)->get();
                    if ($customer_vehicle_infos->count() > 0) {
                        foreach ($customer_vehicle_infos as $vehicle_info) {
                            if ($vehicle_info->num_plate == $vehicle_num) {
                                $vehicle_blocked = TRUE;
                            }
                        }
                    }
                }
            }
            return $vehicle_blocked;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create($this->controller . '-is_vehicle_blocked', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    /**
     * Set booking entry
     * @param type $booking_details
     * @param type $device_id
     * @return boolean
     */
    public function set_booking_entry($booking_details, $device_id) {
        $attendant = \App\Attendants::where('booking_id', $booking_details->id)->first();
        if (!$attendant) {
            $attendant = new \App\Attendants();
        }
        $attendant->booking_id = $booking_details->id;
        $attendant->save();
        $attendant_id = $attendant->id;
        $attendants = array();
        $existing_checked_in_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
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
        \App\AttendantTransactions::whereIn('attendant_id', $attendants)
                ->whereNull('check_out')
                ->update(['check_out' => date('Y-m-d H:i:s')]);
        $attendant_transaction = new \App\AttendantTransactions();
        $attendant_transaction->attendant_id = $attendant_id;
        $attendant_transaction->check_in = date('Y-m-d H:i:s');
        $attendant_transaction->save();
        $this->update_transaction_table($device_id, $attendant_transaction->id, 'in', FALSE, $booking_details);
        $this->update_booking_from_temporary_booking($device_id, $booking_details->id);
        return TRUE;
    }

    public function set_booking_entry_no_entry($booking_details, $device_id) {
        try {
            $attendant = \App\Attendants::where('booking_id', $booking_details->id)->first();
            if (!$attendant) {
                $attendant = new \App\Attendants();
            }
            $attendant->booking_id = $booking_details->id;
            $attendant->save();
            $attendant_id = $attendant->id;
            $attendants = array();
            $existing_checked_in_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
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
            \App\AttendantTransactions::whereIn('attendant_id', $attendants)
                    ->whereNull('check_out')
                    ->update(['check_out' => date('Y-m-d H:i:s')]);
            $attendant_transaction = new \App\AttendantTransactions();
            $attendant_transaction->attendant_id = $attendant_id;
            $attendant_transaction->check_in = date('Y-m-d H:i:s');
            $attendant_transaction->check_out = date('Y-m-d H:i:s');
            $attendant_transaction->save();
            $this->update_transaction_table($device_id, $attendant_transaction->id, 'out', TRUE);
            $this->update_booking_from_temporary_booking($device_id, $booking_details->id);
        } catch (\Exception $ex) {
//            $error_log = new \App\Http\Controllers\LogController();
//            $error_log->log_create('set_booking_entry', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
        }
        return TRUE;
    }

    /**
     * Set booking exit
     * @param type $booking_details
     * @param type $device_id
     * @return boolean
     */
    public function set_booking_exit($booking_details, $device_id) {
        try {
            try {
                if (!$this->key) {
                    $error_log = new \App\Http\Controllers\LogController();
                    $error_log->log_create('import-key', 'custom: Import key not found');
                    return FALSE;
                }
                $Key = $this->key;
                $dataArray = array(
                    'booking_info_live_id' => $booking_details->live_id,
                    'checkout_time' => date('Y-m-d H:i:s'),
                    'attendant' => 1
                );
                $http = new Client();
                $response = $http->post($this->url . '/api/store-booking-info', [
                    'form_params' => [
                        'token' => $Key,
                        'data' => $dataArray
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
            } catch (\Exception $ex) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('set_booking_exit', $ex->getMessage(), $ex->getTraceAsString());
            }
            $attendant = \App\Attendants::where('booking_id', $booking_details->id)->first();
            if (!$attendant) {
                $attendant = new \App\Attendants();
            }
            $attendant->booking_id = $booking_details->id;
            $attendant->save();
            $attendant_id = $attendant->id;
            $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendant_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
            if (!$attendant_transaction) {
                $attendant_transaction = new \App\AttendantTransactions();
                $attendant_transaction->attendant_id = $attendant_id;
                $attendant_transaction->check_in = date('Y-m-d H:i:s');
                $attendant_transaction->check_out = date('Y-m-d H:i:s');
                $attendant_transaction->save();
            } else {
                $attendant_transaction->attendant_id = $attendant_id;
                $attendant_transaction->check_out = date('Y-m-d H:i:s');
                $attendant_transaction->save();
            }
            $this->update_transaction_table($device_id, $attendant_transaction->id, 'out', FALSE, $booking_details);
            $this->update_booking_from_temporary_booking($device_id, $booking_details->id);
            $attendants = array();
            $existing_checked_in_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
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
            \App\AttendantTransactions::whereIn('attendant_id', $attendants)
                    ->whereNull('check_out')
                    ->update(['check_out' => date('Y-m-d H:i:s')]);
        } catch (\Exception $ex) {
//            $error_log = new \App\Http\Controllers\LogController();
//            $error_log->log_create('set_booking_entry', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
        }
        return TRUE;
    }

    /**
     * Create new booking
     * @param type $vehicle
     * @param type $device_id
     * @return boolean
     */
    public function create_booking($vehicle, $device_id, $type = FALSE, $customer_name = FALSE, $range = FALSE) {
        try {
            $dataArray = array(
                'first_name' => 'Paid Vehicle',
                'vehicle_num' => $vehicle,
                'type' => 4,
                'is_paid' => 0,
                'checkin_time' => date('Y-m-d H:i:s'),
                'amount' => 0,
                'payment_id' => 'Paid Vehicle',
                'attendant' => 1
            );
            if ($type) {
                $booking_type = $type;
                if ($booking_type == 1) {
                    //$newtimestamp = strtotime(date('Y-m-d H:i:s') . ' + 1 minute');
                    // $dataArray['checkout_time'] = date('Y-m-d H:i:s', $newtimestamp);
                    $customer_vehicle = \App\CustomerVehicleInfo::where('num_plate', $dataArray['vehicle_num'])
                            ->orderBy('created_at', 'desc')
                            ->first();
                    if (!$customer_vehicle) {
                        $customer_vehicle = new \App\CustomerVehicleInfo();
                        $customer_vehicle->num_plate = $dataArray['vehicle_num'];
                        $customer_vehicle->save();
                    }
                    $dataArray['customer_vehicle_info_id'] = $customer_vehicle->id;
                } else if ($booking_type == 2) {
                    $dataArray['is_paid'] = 1;
                    $dataArray['checkout_time'] = date('Y-m-d 23:59:59');
                    $customer_vehicle = \App\CustomerVehicleInfo::where('num_plate', $dataArray['vehicle_num'])
                            ->orderBy('created_at', 'desc')
                            ->first();
                    if (!$customer_vehicle) {
                        $customer_vehicle = new \App\CustomerVehicleInfo();
                        $customer_vehicle->num_plate = $dataArray['vehicle_num'];
                        $customer_vehicle->save();
                    }
                    $dataArray['customer_vehicle_info_id'] = $customer_vehicle->id;
                } else {
                    if ($range) {
                        $dataArray['checkout_time'] = date('Y-m-d 23:59:59', strtotime($range));
                        $dataArray['is_paid'] = 1;
                    }
                    if ($customer_name) {
                        $booking_customer = $customer_name;
                        if (!empty($booking_customer)) {
                            $customer = new \App\Customer();
                            $customer->api_person_id = rand(10001, 99999);
                            $customer->name = $booking_customer;
                            $customer->language_id = 2;
                            $customer->is_active = 1;
                            $customer->save();
                            $dataArray['customer_id'] = $customer->id;
                            $profile = new \App\Profile();
                            $profile->customer_id = $dataArray['customer_id'];
                            $profile->first_name = $booking_customer;
                            $profile->country = 'nl';
                            $profile->save();
                            $dataArray['first_name'] = $customer->name;
                            $customer_vehicle = \App\CustomerVehicleInfo::where('customer_id', $dataArray['customer_id'])
                                    ->where('num_plate', $dataArray['vehicle_num'])
                                    ->orderBy('created_at', 'desc')
                                    ->first();
                            if (!$customer_vehicle) {
                                $customer_vehicle = new \App\CustomerVehicleInfo();
                                $customer_vehicle->customer_id = $dataArray['customer_id'];
                                $customer_vehicle->num_plate = $dataArray['vehicle_num'];
                                $customer_vehicle->save();
                            }
                            $dataArray['customer_vehicle_info_id'] = $customer_vehicle->id;
                        }
                    }
                }
            }
            $booking = new \App\Bookings();
            if (array_key_exists('customer_id', $dataArray)) {
                $booking->customer_id = $dataArray['customer_id'];
            }
            if (array_key_exists('customer_vehicle_info_id', $dataArray)) {
                $booking->customer_vehicle_info_id = $dataArray['customer_vehicle_info_id'];
            }
            $booking->type = $dataArray['type'];
            $booking->first_name = $dataArray['first_name'];
            $booking->vehicle_num = $dataArray['vehicle_num'];
            $booking->checkin_time = $dataArray['checkin_time'];
            if (array_key_exists('checkout_time', $dataArray)) {
                $booking->checkout_time = $dataArray['checkout_time'];
            }
            $booking->is_paid = $dataArray['is_paid'];
            $booking->save();
            $bookingId = $booking->id;
            $booking_payment = new \App\BookingPayments();
            $booking_payment->booking_id = $bookingId;
            $booking_payment->amount = $dataArray['amount'];
            $booking_payment->payment_id = $dataArray['payment_id'];
            $booking_payment->checkin_time = $dataArray['checkin_time'];
            if (array_key_exists('checkout_time', $dataArray)) {
                $booking_payment->checkout_time = $dataArray['checkout_time'];
            }
            $booking_payment->save();


            $bookingPaymentId = $booking_payment->id;

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
                    $booking = \App\Bookings::find($bookingId);
                    if ($booking) {
                        $booking->live_id = $responseData['data']['booking_info_live_id'];
                        $booking->save();
                    }
                }
                if (is_array($responseData) && array_key_exists('booking_payment_live_id', $responseData['data'])) {
                    $booking_payment = \App\BookingPayments::find($bookingPaymentId);
                    if ($booking_payment) {
                        $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                        $booking_payment->save();
                    }
                }
            } catch (\Exception $ex) {
//                $error_log = new \App\Http\Controllers\LogController();
//                $error_log->log_create('move_booking_live', $ex->getMessage(), $ex->getTraceAsString());
            }
            $this->set_booking_entry($booking, $device_id);
            return $booking;
        } catch (\Exception $ex) {
//            $error_log = new \App\Http\Controllers\LogController();
//            $error_log->log_create('create_booking', $ex->getMessage(), $ex->getTraceAsString());
        }
    }

    /**
     * Create new booking
     * @param type $vehicle
     * @param type $device_id
     * @return boolean
     */
    public function create_no_enytry_booking($vehicle, $device_id) {
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
            $booking = new \App\Bookings();
            $booking->type = $dataArray['type'];
            $booking->first_name = $dataArray['first_name'];
            $booking->vehicle_num = $dataArray['vehicle_num'];
            $booking->checkin_time = $dataArray['checkin_time'];
            $booking->is_paid = $dataArray['is_paid'];
            $booking->save();
            $bookingId = $booking->id;
            $booking_payment = new \App\BookingPayments();
            $booking_payment->booking_id = $bookingId;
            $booking_payment->amount = $dataArray['amount'];
            $booking_payment->payment_id = $dataArray['payment_id'];
            $booking_payment->checkin_time = $dataArray['checkin_time'];
            $booking_payment->save();


            $bookingPaymentId = $booking_payment->id;

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
                    $booking = \App\Bookings::find($bookingId);
                    if ($booking) {
                        $booking->live_id = $responseData['data']['booking_info_live_id'];
                        $booking->save();
                    }
                }
                if (is_array($responseData) && array_key_exists('booking_payment_live_id', $responseData['data'])) {
                    $booking_payment = \App\BookingPayments::find($bookingPaymentId);
                    if ($booking_payment) {
                        $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                        $booking_payment->save();
                    }
                }
            } catch (\Exception $ex) {
//                $error_log = new \App\Http\Controllers\LogController();
//                $error_log->log_create('move_booking_live', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            }
            $this->set_booking_entry_no_entry($booking, $device_id);
        } catch (\Exception $ex) {
//            $error_log = new \App\Http\Controllers\LogController();
//            $error_log->log_create('create_booking', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
        }
    }

    /**
     * check is booking paid
     * @param type $booking
     * @return type
     */
    public function is_booking_paid($booking) {
        try {
            if ($booking->is_paid) {
                if ($booking->type == 2 || $booking->type == 3) {
                    return array(
                        'status' => 1,
                        'message' => 'Thanks',
                    );
                }
                if (empty($booking->checkout_time)) {
                    return array(
                        'status' => 0,
                        'message' => 'Please go to nearby payment terminal and complete payment.',
                    );
                }
                if (date('Y-m-d H:i') > date('Y-m-d H:i', strtotime($booking->checkout_time))) {
                    return array(
                        'status' => 0,
                        'message' => 'Please go to nearby payment terminal and complete payment.',
                    );
                }
                return array(
                    'status' => 1,
                    'message' => 'Thanks',
                );
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create($this->controller . '-is_booking_paid', $ex->getMessage(), $ex->getTraceAsString());
        }

        return array(
            'status' => 0,
            'message' => $this->ticket_reader->get_error_message('goto_nearby_payment_terminal', '', $this->lang_id)
        );
    }

    /**
     * Set device confidence to public parameter
     * @param type $device_id
     */
    public function set_device_confidence($device_id) {
        $device_details = \App\LocationDevices::find($device_id);
        if ($device_details) {
            if (!empty($device_details->confidence) && is_numeric($device_details->confidence)) {
                $this->confidence_val = $device_details->confidence;
            }
        }
    }

    /**
     * Set language id for message
     * @param type $country_code
     */
    public function set_lang_id($country_code) {
        if ($country_code == NULL) {
            
        }
        $lang_details = \App\Language::where('code', $country_code)->first();
        if ($lang_details) {
            $this->lang_id = $lang_details->id;
        }
    }

    /**
     * Set temporary entry in device booking
     * @param type $device_id
     * @param type $vehicle_num
     * @param type $confidence
     * @param type $file_path
     * @param type $country_code
     */
    public function set_temporary_booking_entry($device_id, $vehicle_num, $confidence, $file_path, $country_code) {
        try {
            // available device id check only for plate reader
            $booking = new \App\DeviceBookings();
            $booking->device_id = $device_id;
            $booking->vehicle_num = $vehicle_num;
            $booking->confidence = $confidence;
            $booking->file_path = $file_path;
            $booking->country_code = $country_code;
            $booking->save();
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create($this->controller . '-set_temporary_booking_entry', $ex->getMessage(), $ex->getTraceAsString());
        }
    }

    /**
     * Update temporary booking in device booking
     * @param type $device_id
     * @param type $booking_id
     * @return boolean
     */
    public function update_booking_from_temporary_booking($device_id, $booking_id) {
        try {
            $related_plate_reader = \App\DeviceTicketReaders::where([
                        ['ticket_reader_id', $device_id]
                    ])->first();

            if (!$related_plate_reader) {
                return FALSE;
            }
            $low_confidence = 0;
            $related_plate_reader_id = $related_plate_reader->device_id;
            $device_details = \App\LocationDevices::find($related_plate_reader_id);
            if (!$device_details) {
                return FALSE;
            }

            $temporary_booking = \App\DeviceBookings::where('device_id', $related_plate_reader_id)
                    ->orderBy('created_at', 'DESC')
                    ->first();

            if (!$temporary_booking) {
                return FALSE;
            }
            if (!empty($device_details->confidence)) {
                if ($temporary_booking->confidence < $device_details->confidence) {
                    $low_confidence = 1;
                }
            }
            $booking = \App\Bookings::find($booking_id);
            if (!$booking) {
                return FALSE;
            }
            $booking->confidence = $temporary_booking->confidence;
            $booking->low_confidence = $low_confidence;
            $booking->country_code = $temporary_booking->country_code;
            $booking->image_path = $temporary_booking->file_path;
            $booking->checkout_time = date('Y-m-d H:i:s');
            $booking->save();
            $temporary_booking->delete();
            \App\DeviceBookings::where('device_id', $related_plate_reader_id)
                    ->where('is_operator', '1')
                    ->delete();
            return TRUE;
        } catch (\Exception $ex) {
//            $error_log = new \App\Http\Controllers\LogController();
//            $error_log->log_create('update_booking_from_temporary_booking', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            return FALSE;
        }
    }

    /**
     * Update TransactionImages entry
     * @param type $device_id
     * @param type $attendant_id
     * @param type $status
     * @return boolean
     */
    public function update_transaction_table($device_id, $attendant_id, $status, $no_entry_transaction = FALSE, $booking_details = FALSE) {
        try {
            $device_details = \App\LocationDevices::find($device_id);
            if ($device_details->available_device_id != 3) {
                $related_plate_reader = \App\DeviceTicketReaders::where([
                            ['ticket_reader_id', $device_id]
                        ])->first();

                if (!$related_plate_reader) {
                    return FALSE;
                }
                $related_plate_reader_id = $related_plate_reader->device_id;
                $device_details = \App\LocationDevices::find($related_plate_reader_id);
            }
            if (!$device_details) {
                return FALSE;
            }
            if ($booking_details) {
                $temporary_booking = \App\DeviceBookings::where('device_id', $device_details->id)
                        ->where('vehicle_num', $booking_details->vehicle_num)
                        ->orderBy('created_at', 'DESC')
                        ->first();
            } else {
                $temporary_booking = \App\DeviceBookings::where('device_id', $device_details->id)
                        ->orderBy('created_at', 'DESC')
                        ->first();
            }
            if (!$temporary_booking) {
                return FALSE;
            }
            $transaction_images = new \App\TransactionImages();
            $transaction_images->image_path = $temporary_booking->file_path;
            $transaction_images->device_id = $device_details->id;
            $transaction_images->transaction_id = $attendant_id;
            $transaction_images->type = $status;
            $transaction_images->save();


            $manual_open_gate = new \App\OpenGateManualTransaction();
            $manual_open_gate->transaction_images_id = $transaction_images->id;
            $manual_open_gate->attendant_transaction_id = $attendant_id;
            if (\Illuminate\Support\Facades\Session::has('open_gate_by')) {
                $open_gate_by = \Illuminate\Support\Facades\Session::get('open_gate_by');
                $manual_open_gate->user_id = $open_gate_by;
            } else {
                if (\Illuminate\Support\Facades\Auth::check()) {
                    $manual_open_gate->user_id = \Illuminate\Support\Facades\Auth::id();
                } else {
                    $manual_open_gate->user_id = 1;
                }
            }
            if ($no_entry_transaction) {
                $manual_open_gate->reason = 'No entry transaction';
            } else {
                if ($status == 'in') {
                    $manual_open_gate->reason = 'Manual enterance transaction';
                } else {
                    $manual_open_gate->reason = 'Manual exit transaction';
                }
            }
            $manual_open_gate->location_device_id = $device_details->id;
            $manual_open_gate->save();
        } catch (\Exception $ex) {
//            $error_log = new \App\Http\Controllers\LogController();
//            $error_log->log_create('update_booking_from_temporary_booking', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            return FALSE;
        }
    }

    /**
     * Set vehicle booking checkout
     * @param type $vehicle_booking
     * @param type $booking
     * @param type $status
     * @return boolean
     */
    public function set_vehicle_booking_checkout($vehicle_booking, $booking, $status) {
        try {
            if ($status == 'out') {
                $valid_bookings_types = array(1, 2, 3, 4, 7);
                $booking_details = \App\Bookings::where([
                            ['live_id', $booking]
                        ])
                        ->whereIn('type', $valid_bookings_types)
                        ->first();
                $promoCode = NULL;
                if ($booking_details) {
                    if ($booking_details->type == 4) {
                        $promoCode = $booking_details->promo_code;
                        if ($booking_details->promo_code != NULL) {
                            $promo = \App\Promo::where('code', $booking_details->promo_code)->first();
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
                    $vehicle_booking_details = \App\Bookings::find($vehicle_booking->id);
                    if ($vehicle_booking_details) {
                        $data = array(
                            'booking_info_live_id' => $vehicle_booking_details->live_id,
                            'is_paid' => 1,
                            'checkout_time' => date('Y-m-d H:i:s'),
                            'promo_code' => $promoCode,
                            'attendant' => 1
                        );
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

                        $booking_payment = \App\BookingPayments::where('booking_id', $vehicle_booking_details->id)->first();
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
        } catch (\Exception $ex) {
//            $error_log = new \App\Http\Controllers\LogController();
//            $error_log->log_create('set_vehicle_booking_checkout', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            return $vehicle_booking;
        }
    }

    public function open_gate_status(Request $request, $key, $id, $vehicle) {
        try {
            $valid_settings = $this->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            if ($valid_settings->device_direction == 'in') {
                $vehicle_booking = $this->get_vehicle_booking($vehicle, 'in');
                if ($vehicle_booking) {
                    $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
//                    $at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
//                    if ($at_location) {
//                        $message = $this->ticket_reader->get_error_message('already_at_location', $user_name, $this->lang_id);
//                        return array(
//                            'status' => 1,
//                            'access_status' => 'denied',
//                            'message' => $message,
//                            'data' => FALSE,
//                        );
//                    }
                    $this->set_booking_entry($vehicle_booking, $valid_settings->id);
                    $message = $this->ticket_reader->get_error_message('welcome_entrance', $user_name, $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $message = $this->ticket_reader->get_error_message('welcome_entrance', '', $this->lang_id);
                $this->create_booking($vehicle, $valid_settings->id);
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => $message,
                    'data' => FALSE,
                );
            } elseif ($valid_settings->device_direction == 'out') {
                $vehicle_booking = $this->get_vehicle_booking($vehicle, 'out');
                if (!$vehicle_booking) {
                    $this->create_no_enytry_booking($vehicle, $valid_settings->id);
                    $message = $this->ticket_reader->get_error_message('goodbye_exit', '', $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'data' => FALSE,
                    );
                }

                $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
                $at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
                if (!$at_location) {
                    $this->create_no_enytry_booking($vehicle, $valid_settings->id);
                    $message = $this->ticket_reader->get_error_message('goodbye_exit', '', $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $user_name = $this->ticket_reader->get_user_name($vehicle_booking);

                $this->set_booking_exit($vehicle_booking, $valid_settings->id);
                $message = $this->ticket_reader->get_error_message('goodbye_exit', $user_name, $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => $message,
                    'data' => FALSE,
                );
            } else {
                $message = 'Bidirectional Devices is not supported';
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            $message = $this->ticket_reader->get_error_message('unknown', '', $this->lang_id);
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'message' => $message,
                'data' => FALSE,
            );
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            exit;
//            $error_log = new \App\Http\Controllers\LogController();
//            $error_log->log_create('verify_plate_num_status', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            $message = $this->ticket_reader->get_error_message('unknown');
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    public function verify_low_confidence_vehicle(Request $request, $key, $id, $vehicle = FALSE, $confidence, $country_code = '') {
        try {
            $valid_settings = $this->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $data = $request->all();
            $file_path = 'plugins/images/assets/no_image.png';
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->extension() ? : 'png';
                $destinationPath = public_path('/uploads/vehicles');
                $safeName = str_random(10) . '.' . $extension;
                $file->move($destinationPath, $safeName);
//                $request['pic'] = $safeName;
                $file_path = '/uploads/vehicles' . '/' . $safeName;
                $request->session()->put('vehicle_image', '/uploads/vehicles' . '/' . $safeName);
            }
            $device_bookings = DeviceBookings::where('device_id', $id)
                    //->where('confidence', $confidence)
                    //->where('vehicle_num', $vehicle)
                    ->where('is_operator', 1)
                    ->orderBy('created_at', 'desc')
                    ->get();
            foreach ($device_bookings as $booking_device) {
                $booking_device->is_operator = 0;
                $booking_device->save();
            }
            $device_booking = DeviceBookings::where('device_id', $id)->orderBy('created_at', 'desc')->first();
            if (!$device_booking) {
                $device_booking = new DeviceBookings();
                $device_booking->device_id = $id;
                $device_booking->confidence = $confidence;
                $device_booking->file_path = $file_path;
            }
            $device_booking->vehicle_num = $vehicle;
            $device_booking->is_operator = 1;
            if (array_key_exists('reason', $data)) {
                $device_booking->reason = $data['reason'];
            }
            $device_booking->save();
            return array(
                'status' => 1,
                'access_status' => 'success',
                'message' => 'success',
                'data' => "$device_booking->id"
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $ex->getMessage(),
                'data' => FALSE
            );
        }
    }

    public function send_cancel_manual_access_control(Request $request, $key, $id, $device_booking) {
        try {
            $valid_settings = $this->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $find_device_booking = DeviceBookings::find($device_booking);
            if ($find_device_booking) {
                $find_device_booking->delete();
            }
            return array(
                'status' => 1,
                'access_status' => 'success',
                'message' => 'success',
                'data' => FALSE
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $ex->getMessage(),
                'data' => FALSE
            );
        }
    }

    function get_operator_bookings(Request $request) {
        try {
            $response = 0;
            $data = array();
            if ($request->current_device_booking_popup > 0) {
                $device_booking = DeviceBookings::find($request->current_device_booking_popup);
                if (!$device_booking) {
                    return json_encode(array('status' => $response, 'data' => $data));
                }
            }
            $device_booking = DeviceBookings::where('is_operator', 1)->first();
            if ($device_booking) {
                $location_device = \App\LocationDevices::find($device_booking->device_id);
                if ($location_device) {
                    $response = 1;
                    $data['location_device'] = $location_device;
                    $data['device_booking'] = $device_booking;
                    $vehicle_booking = $this->get_vehicle_booking($device_booking->vehicle_num, $location_device->device_direction);
                    if ($vehicle_booking) {
                        $data['vehicle_booking'] = $vehicle_booking;
                        $data['vehicle_booking']->checkin_time = date('d/m/Y H:i', strtotime($vehicle_booking->checkin_time));
                        if (!empty($vehicle_booking->checkout_time)) {
                            $data['vehicle_booking']->checkout_time = date('d/m/Y H:i', strtotime($vehicle_booking->checkout_time));
                        } else {
                            $data['vehicle_booking']->checkout_time = 'N/A';
                        }
                        $data['vehicle_booking']->amount = 'N/A';
                        $vehicle_booking_payment = $vehicle_booking->booking_payments;
                        if ($vehicle_booking_payment && !empty($vehicle_booking_payment->amount)) {
                            $data['vehicle_booking']->amount = $vehicle_booking_payment->amount . '&euro;';
                        }
                    }
                    $data['device_booking']->file_path = url($device_booking->file_path);
                }
            }
            return json_encode(array('status' => $response, 'data' => $data));
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create($this->controller . '-get_operator_bookings', $ex->getMessage(), $ex->getTraceAsString());
        }
    }

    public function update_device_vehicle(Request $request) {
        try {
            $response = 0;
            $data = array();
            $device_booking = DeviceBookings::find($request->device_booking_id);
            $message = '';
            $plate = str_replace(array(' ', '-', '\'', '"', ',', ';', '<', '>'), '', $request->open_gate_vehcile_num);
            $related_ticket_reader = false;
            if ($device_booking) {
                $location_device = \App\LocationDevices::find($device_booking->device_id);
                if ($location_device) {
                    $location_setting = \App\LocationOptions::first();
                    $device_ticket_reader = \App\DeviceTicketReaders::where('device_id', $location_device->id)->first();
                    if ($device_ticket_reader) {
                        $related_ticket_reader = \App\LocationDevices::find($device_ticket_reader->ticket_reader_id);
                        $location_device['device_ticket_reader'] = $related_ticket_reader;
                    }
                    if ($location_setting) {
                        $user = \App\User::first();
                        if ($user) {
                            $key = strtotime($location_setting->created_at) . '-' . $device_booking->device_id;
                        }
                    }
                    $response = 1;
                    $data['location_device'] = $location_device;
                    $data['device_booking'] = $device_booking;
                    $verify_vehicle = new \App\Http\Controllers\PlateReaderController\VerifyVehicle();
                    $verify_vehicle->set_temporary_booking = FALSE;
                    if (isset($location_device->device_ticket_reader->tr_version) && $location_device->device_ticket_reader->tr_version == "2.0") {
                        $verify_plate_num = $this->accessCall->verifyAccessRequest(
                            $request,
                            $key,
                            $device_booking->device_id,
                            $plate,
                            100,
                            NULL
                        );
                        if ($verify_plate_num['status'] && isset($verify_plate_num['data'])) {
                            $vehicle_booking = \App\Bookings::find($verify_plate_num['data']);
                            $data['vehicle_booking'] = $vehicle_booking;
                            $data['vehicle_booking']->checkin_time = date('d/m/Y H:i', strtotime($vehicle_booking->checkin_time));
                            if (!empty($vehicle_booking->checkout_time)) {
                                $data['vehicle_booking']->checkout_time = date('d/m/Y H:i', strtotime($vehicle_booking->checkout_time));
                            } else {
                                $data['vehicle_booking']->checkout_time = 'N/A';
                            }
                            $data['vehicle_booking']->amount = 'N/A';
                            $vehicle_booking_payment = $vehicle_booking->booking_payments;
                            if ($vehicle_booking_payment && !empty($vehicle_booking_payment->amount)) {
                                $data['vehicle_booking']->amount = $vehicle_booking_payment->amount . '&euro;';
                            }
                            if ($verify_plate_num['access_status'] == 'success') {
                                $response = 2;
                                if ($location_device && $location_device->has_gate) {
                                    Artisan::call('command:OpenTicketReader', [
                                        'device' => $request->device_id,
                                        'vehicle' => $plate
                                    ]);
                                }
                            }
                            $device_booking->vehicle_num = $plate;
                            if ($verify_plate_num['access_status'] == 'success') {
                                $device_booking->is_operator = 0;
                            }
                            $device_booking->save();
                        } else {
                            $data['device_booking']->reason = $verify_plate_num['message'];
                        }
                        $message = $verify_plate_num['message'];
                    } else {
                        $verify_plate_num = $verify_vehicle->verify_plate_num(
                            $request,
                            $key,
                            $device_booking->device_id,
                            $plate,
                            100,
                            NULL
                        );
                        if ($verify_plate_num['status'] && isset($verify_plate_num['vehicle_booking_data'])) {
                            $vehicle_booking = $verify_plate_num['vehicle_booking_data'];
                            $data['vehicle_booking'] = $vehicle_booking;
                            $data['vehicle_booking']->checkin_time = date('d/m/Y H:i', strtotime($vehicle_booking->checkin_time));
                            if (!empty($vehicle_booking->checkout_time)) {
                                $data['vehicle_booking']->checkout_time = date('d/m/Y H:i', strtotime($vehicle_booking->checkout_time));
                            } else {
                                $data['vehicle_booking']->checkout_time = 'N/A';
                            }
                            $data['vehicle_booking']->amount = 'N/A';
                            $vehicle_booking_payment = $vehicle_booking->booking_payments;
                            if ($vehicle_booking_payment && !empty($vehicle_booking_payment->amount)) {
                                $data['vehicle_booking']->amount = $vehicle_booking_payment->amount . '&euro;';
                            }
                            if ($verify_plate_num['access_status'] == 'success') {
                                $response = 2;
                                if ($location_device && $location_device->has_gate) {
                                    Artisan::call('command:OpenTicketReader', [
                                        'device' => $request->device_id,
                                        'vehicle' => $plate
                                    ]);
                                }
                            }
                            $device_booking->vehicle_num = $plate;
                            if ($verify_plate_num['access_status'] == 'success') {
                                $device_booking->is_operator = 0;
                            }
                            $device_booking->save();
                        } else {
                            $data['device_booking']->reason = $verify_plate_num['message'];
                        }
                        $message = $verify_plate_num['message'];
                    }
                    
                }
            }
            return json_encode(array('status' => $response, 'data' => $data, 'message' => $message));
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create($this->controller . '-update_device_vehicle', $ex->getMessage(), $ex->getTraceAsString());
            return json_encode(array('status' => 0, 'data' => FALSE, 'message' => $ex->getMessage()));
        }
    }

    function vehicle_access_allow(Request $request) {
        try {
            $response = 0;
            $location_device_id = 0;

            $plate = str_replace(array(' ', '-', '\'', '"', ',', ';', '<', '>'), '', $request->open_gate_vehcile_num);
            if (isset($request->device_id)) {
                $location_device_id = $request->device_id;
                $location_device = \App\LocationDevices::find($request->device_id);
                if ($location_device && !$location_device->has_gate) {
                    $related_ticket_reader = \App\DeviceTicketReaders::where([
                                ['device_id', $request->device_id]
                            ])->first();
                    if ($related_ticket_reader) {
                        $location_device = \App\LocationDevices::find($related_ticket_reader->ticket_reader_id);
                        if ($location_device && !$location_device->has_gate) {
                            $message = __('dashboard.verify_device_configuration');
                            return json_encode(array('status' => $response, 'message' => $message));
                        }
                        $location_device_id = $related_ticket_reader->ticket_reader_id;
                    }
                }
                $device_booking = DeviceBookings::find($request->device_booking_id);
                if ($device_booking) {
                    $device_booking->is_operator = 0;
                    $device_booking->save();
                }
                $booking = NULL;
                if ($request->open_gate_booking_type == 2) {
                    $booking = $this->create_booking(
                            $plate, $request->device_id, $request->open_gate_booking_type);
                } else if ($request->open_gate_booking_type == 3) {
                    $booking = $this->create_booking(
                            $plate, $request->device_id, $request->open_gate_booking_type, $request->open_gate_customer, $request->open_gate_booking_range);
                } else if ($request->open_gate_booking_type == 1 && $location_device->available_device_id == 1) {
                    $booking = $this->create_booking(
                            $plate, $request->device_id, $request->open_gate_booking_type);
                }
                if ($location_device && $location_device->has_gate) {
                    if ($location_device->available_device_id == 1) {
                        $this->open_gate_plate_reader($location_device, $plate, '', $location_device->device_direction == 'in' ? 'entry' : 'exit', $booking);
                    } else {
                        Artisan::call('command:OpenTicketReader', [
                            'device' => $location_device_id,
                            'vehicle' => $plate
                        ]);
                    }
                }
                $response = 1;
                if (\Illuminate\Support\Facades\Auth::check()) {
                    \Illuminate\Support\Facades\Session::forget('open_gate_by', \Illuminate\Support\Facades\Auth::id());
                }
                $message = 'Please wait process is in progress';
                if (\Illuminate\Support\Facades\Session::has('open_gate_message')) {
                    $message = \Illuminate\Support\Facades\Session::get('open_gate_message');
                    \Illuminate\Support\Facades\Session::forget('open_gate_message');
                }
                if (\Illuminate\Support\Facades\Session::has('open_gate_status')) {
                    $response = \Illuminate\Support\Facades\Session::get('open_gate_status');
                    \Illuminate\Support\Facades\Session::forget('open_gate_status');
                }
                return json_encode(array('status' => $response, 'message' => $message));
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create($this->controller . '-vehicle_access_denied', $ex->getMessage(), $ex->getTraceAsString());
        }
    }

    function vehicle_access_denied(Request $request) {
        try {
            $response = 0;
            $device_booking = DeviceBookings::find($request->device_booking_id);
            if ($device_booking) {
                $response = 1;
                $device_booking->is_operator = 0;
                $device_booking->save();
                $device_booking->delete();
            }
            $message = 'Not Access';
            return json_encode(array('status' => $response, 'message' => $message));
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create($this->controller . '-vehicle_access_denied', $ex->getMessage(), $ex->getTraceAsString());
        }
    }

    public function open_gate_plate_reader($ticket_reader_details, $vehicle_num, $message, $type, $booking = FALSE)
    {
        try {
            $open_gate_call_start = microtime(true);
            if ($ticket_reader_details->available_device_id == 3) {
                $related_ticket_reader = \App\DeviceTicketReaders::where([
                            ['device_id', $ticket_reader_details->id]
                        ])->first();

                if (!$related_ticket_reader) {
                    if ($ticket_reader_details->has_sdl || $ticket_reader_details->gate_close_transaction_enabled) {
                        return True;
                    }
                    return FALSE;
                }
                $related_ticket_reader_id = $related_ticket_reader->ticket_reader_id;
                $ticket_reader_details = \App\LocationDevices::find($related_ticket_reader_id);
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
            if ($type == 'entry') {
                if (empty($message)) {
                    $message = $this->ticket_reader->get_error_message('welcome_entrance', '', $this->lang_id);
                }
                $client = new \App\Http\Controllers\Connection\Client($ip, $port);

                $key = $this->location_created_at . '-' . $ticket_reader_details->id;
                $command = 'open_gate';
				
                $data = '31|' . $key . '|' . $vehicle_num . '|' . $message;
                if ($ticket_reader_details->tr_version != "1.0") {
                    $data = '31|' . $key . '|' . $vehicle_num . '|' . $booking->id . '|' . $message;
                }					
                $open_gate_call_total_time_start = (round(microtime(true) - $open_gate_call_start, 3) * 1000);

                $connection = $client->send($command, $data);
                $open_gate_call_total_time_after = (round(microtime(true) - $open_gate_call_start, 3) * 1000);

                \Illuminate\Support\Facades\Session::put('open_gate_call_total_time_start', $open_gate_call_total_time_start);
                \Illuminate\Support\Facades\Session::put('open_gate_call_total_time_after', $open_gate_call_total_time_after);

                if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                    if ($connection['status'] >= 3) {
                        $ticket_reader_details->is_synched = 1;
                        $ticket_reader_details->is_opened = 1;
                        $ticket_reader_details->save();
                    }
                }
            } elseif ($type == 'exit') {
                if (empty($message)) {
                    $message = $this->ticket_reader->get_error_message('goodbye_exit', '', $this->lang_id);
                }
                $client = new \App\Http\Controllers\Connection\Client($ip, $port);
                $key = $this->location_created_at . '-' . $ticket_reader_details->id;
                $command = 'open_gate_Exit';
                $data = '35|' . $key . '|' . $vehicle_num . '|' . $message;
                $connection = $client->send($command, $data);
                if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                    if ($connection['status'] >= 3) {
                        $ticket_reader_details->is_synched = 1;
                        $ticket_reader_details->is_opened = 1;
                        $ticket_reader_details->save();
                    }
                }
            }
            return TRUE;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function get_vehicle_group() {
        
    }

    public function handle_emergency_entry_exit($valid_settings, $vehicle) {
        try {
            if ($valid_settings->device_direction == 'in') {
                $vehicle_booking = $this->get_vehicle_booking($vehicle, 'in');
                if ($vehicle_booking) {
                    //$at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
                    //if (!$at_location) {
                    if ($vehicle_booking->type != 3) {
                        return FALSE;
                    }
                    $device_group = $this->ticket_reader->device_has_group($valid_settings->id);
                    if ($device_group) {
                        $vehicle_group = $this->ticket_reader->get_vehicle_group($vehicle_booking);
                        if (!$vehicle_group) {
                            return FALSE;
                        }
                        $has_group_access = $this->ticket_reader->is_valid_group_device($vehicle_group, $valid_settings->id);
                        if ($has_group_access) {
                            $this->set_booking_entry($vehicle_booking, $valid_settings->id);
                            return TRUE;
                        } else {
                            return FALSE;
                        }
                    }
                    $this->set_booking_entry($vehicle_booking, $valid_settings->id);
                    return TRUE;
                }
                return FALSE;
            } elseif ($valid_settings->device_direction == 'out') {
                $vehicle_booking = $this->get_vehicle_booking($vehicle, 'out');
                if (!$vehicle_booking) {
                    $this->create_no_enytry_booking($vehicle, $valid_settings->id);
                    return TRUE;
                }
                $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
                $at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
                if (!$at_location) {
                    $this->create_no_enytry_booking($vehicle, $valid_settings->id);
                    return TRUE;
                }
                $this->set_booking_exit($vehicle_booking, $valid_settings->id);
                return TRUE;
            } else {
                return FALSE;
            }
            return FALSE;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

}
