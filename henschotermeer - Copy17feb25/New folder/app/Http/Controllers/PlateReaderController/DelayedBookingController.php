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

class DelayedBookingController extends Controller {

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
        $this->ticket_reader = new \App\Http\Controllers\Settings\VerifyBookings();
        $this->settings = new \App\Http\Controllers\Settings\Settings();
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

    public function is_valid_call($key, $id) {
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
        return $location_device;
    }

    public function delayed_bookings(Request $request, $key, $id, $vehicle, $url_encoded_date, $confidence = NULL) {
        try {
            $booking_date = urldecode($url_encoded_date);
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
            $device_id = $id;
            $related_plate_reader = \App\DeviceTicketReaders::where([
                        ['ticket_reader_id', $id]
                    ])->first();
            if ($related_plate_reader) {
                $device_id = $related_plate_reader->device_id;
            }

            if ($valid_settings->device_direction == 'in') {
                $vehicle_booking = $this->get_vehicle_booking($vehicle, 'in');
                if ($vehicle_booking) {
                    $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
                    $this->set_booking_entry($vehicle_booking, $booking_date);
                    $message = $this->ticket_reader->get_error_message('welcome_entrance', $user_name, $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $message = $this->ticket_reader->get_error_message('welcome_entrance', '', $this->lang_id);
                $this->create_booking($vehicle, $booking_date);
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => $message,
                    'data' => FALSE,
                );
            } elseif ($valid_settings->device_direction == 'out') {
                $vehicle_booking = $this->get_vehicle_booking($vehicle, 'out');
                if ($vehicle_booking) {
                    $this->set_booking_exit($vehicle_booking, $booking_date);
                    $message = $this->ticket_reader->get_error_message('goodbye_exit', $user_name, $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
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

            $message = $this->ticket_reader->get_error_message('unknown');
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

    public function set_booking_entry($booking_details, $booking_date) {
        $attendant = \App\Attendants::where('booking_id', $booking_details->id)->first();
        if (!$attendant) {
            $attendant = new \App\Attendants();
        }
        $attendant->booking_id = $booking_details->id;
        $attendant->save();
        $attendant_id = $attendant->id;
        \App\AttendantTransactions::where('attendant_id', $attendant_id)
                ->whereNull('check_out')
                ->update(['check_out' => date('Y-m-d H:i:s')]);
        $attendant_transaction = new \App\AttendantTransactions();
        $attendant_transaction->attendant_id = $attendant_id;
        $attendant_transaction->check_in = date('Y-m-d H:i:s', strtotime($booking_date));
        $attendant_transaction->save();
        return TRUE;
    }

    public function set_booking_exit($booking_details, $booking_date) {
        if (empty($booking_details->checkout_time)) {
            $booking_details->checkout_time = date('Y-m-d H:i:s');
            $booking_details->save();
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
            $attendant_transaction->check_in = date('Y-m-d H:i:s', strtotime($booking_date));
            $attendant_transaction->check_out = date('Y-m-d H:i:s', strtotime($booking_date));
            $attendant_transaction->save();
        } else {
            $attendant_transaction->attendant_id = $attendant_id;
            $attendant_transaction->check_out = date('Y-m-d H:i:s', strtotime($booking_date));
            $attendant_transaction->save();
        }

        return TRUE;
    }

    public function create_booking($vehicle, $booking_date) {

        $dataArray = array(
            'first_name' => 'Paid Vehicle',
            'vehicle_num' => $vehicle,
            'type' => 4,
            'is_paid' => 0,
            'checkin_time' => date('Y-m-d H:i:s', strtotime($booking_date)),
            'amount' => 0,
            'payment_id' => 'Paid Vehicle'
        );
        $booking = new \App\Bookings();
        $booking->type = $dataArray['type'];
        $booking->first_name = $dataArray['first_name'];
        $booking->vehicle_num = $dataArray['vehicle_num'];
        $booking->checkin_time = $dataArray['checkin_time'];
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
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('get_vehicle_booking', $ex->getMessage(), $ex->getTraceAsString());
        }
        $this->set_booking_entry($booking, $booking_date);
    }

    public function get_vehicle_booking($vehicle_num, $status) {
        try {
            $valid_bookings_types = array(1, 2, 3, 4, 7);
            if ($status == 'in') {
                $booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereIn('type', $valid_bookings_types)
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($booking_details) {
                    if ($booking_details->type == 2 || $booking_details->type == 3) {
                        //if (date('H:i', strtotime($booking_details->checkin_time)) <= date('H:i') && date('H:i', strtotime($booking_details->checkout_time)) >= date('H:i')) 
                        {
                            if ($booking_details->type == 3 && $booking_details->customer_id > 0) {
                                $userlist_user = \App\UserlistUsers::where('customer_id', $booking_details->customer_id)->first();
                                if ($userlist_user) {
                                    $this->lang_id = $userlist_user->language_id;
                                }
                            }
                            return $booking_details;
                        }
                        return FALSE;
                    } elseif ($booking_details->checkout_time > date('Y-m-d H:i', strtotime('-' . $this->lag_time . ' minutes', strtotime(date("Y-m-d H:i"))))) {
                        return $booking_details;
                    }
                }
                return FALSE;
            } elseif ($status == 'out') {
                $booking_details = \App\Bookings::where([
                            ['vehicle_num', $vehicle_num]
                        ])
                        ->whereIn('type', $valid_bookings_types)
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($booking_details) {

                    $attendants = \App\Attendants::where('booking_id', $booking_details->id)->first();
                    if ($attendants) {
                        $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendants->id)
                                ->orderBy('created_at', 'DESC')
                                ->first();
                        if ($attendant_transaction) {
                            if ($attendant_transaction->check_out == NULL) {
                                if ($booking_details->type == 3 && $booking_details->customer_id > 0) {
                                    $userlist_user = \App\UserlistUsers::where('customer_id', $booking_details->customer_id)->first();
                                    if ($userlist_user) {
                                        $this->lang_id = $userlist_user->language_id;
                                    }
                                }
                                return $booking_details;
                            }
                        }
                    }
                }
               
                return FALSE;
            } else {
                return FALSE;
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('get_vehicle_booking', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

}
