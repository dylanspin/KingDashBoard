<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use GuzzleHttp\Client;

class BarcodeSettings extends Controller
{

    public $confidence_val = 80;
    public $lang_id = FALSE;
    public $location_created_at = '1552661741';
    public $ticket_reader;
    public $lag_time = 30;
    public $settings;
    public $key = 'MTk3Nl8yODI=';
    public $url = "";

    public function __construct($key = NULL)
    {
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

    public function is_valid_barcode(Request $request, $key, $id, $barcode)
    {
        $settings = new Settings();
        $verify_booking = new VerifyBookings();
        $valid_settings = $settings->is_valid_call($key, $id);
        if (!$valid_settings) {
            \Illuminate\Support\Facades\Session::put('error_message', 'Invalid Access');
            $message = $verify_booking->get_error_message('unknown');
            return array(
                'status' => 1,
                'access_status' => 'error',
                'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                'message' => $message,
                'data' => FALSE,
            );
        }

        if ($valid_settings->available_device_id == 2) {
            $person_timings = $verify_booking->valid_person_timings(NULL);
            if (!$person_timings['status']) {
                $message = $verify_booking->get_error_message($person_timings['message_code'], '');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            return $this->is_valid_person_booking($barcode, $valid_settings);
        }
        $vip_barcode = \App\Barcode::where('barcode', $barcode)
            ->where('type', 'parking')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($vip_barcode) {
            //            $is_barcode_at_location = $this->is_barcode_at_location($barcode);
            //            if ($is_barcode_at_location) {
            //                \Illuminate\Support\Facades\Session::put('error_message', 'You are already on location!');
            //                $message = $verify_booking->get_error_message('anti_passback_message_barcode');
            //                return array(
            //                    'status' => 1,
            //                    'access_status' => 'error',
            //                    'od_sent' => $settings->send_message_od($id, $message, 'vip'),
            //                    'message' => $message,
            //                    'data' => FALSE,
            //                );
            //            }
            $booking = $this->add_barcode_booking($barcode, $vip_barcode);
            if (!$booking) {

                \Illuminate\Support\Facades\Session::put('error_message', 'Sorry You do not have access');
                $message = $verify_booking->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'od_sent' => $settings->send_message_od($id, $message, 'vip'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $valid_passback = $verify_booking->is_valid_antipassback($booking, $id);
            if (!$valid_passback['status']) {

                \Illuminate\Support\Facades\Session::put('error_message', 'You are already on location!');
                $message = $verify_booking->get_error_message('anti_passback_message_barcode', '');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'vip'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $message = $verify_booking->get_error_message('welcome_entrance', '');
            if ($vip_barcode->message != NULL) {
                $message = $vip_barcode->message;
            }
            return array(
                'status' => 1,
                'access_status' => 'allow',
                'od_sent' => $settings->send_message_od($id, $message, 'vip'),
                'message' => $message,
                'data' => $booking->id,
                'type' => 'vip',
            );
        }
        $location = new LocationSettings();
        $location_details = $location->get_location();
        if (!$location_details) {
            \Illuminate\Support\Facades\Session::put('error_message', 'Invalid');
            $message = $verify_booking->get_error_message('unauthorized');
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                'message' => $message,
                'data' => FALSE,
            );
        }
        if ($location_details->barcode_series == NULL) {
            \Illuminate\Support\Facades\Session::put('error_message', 'Sorry, You do not have access!');
            $message = $verify_booking->get_error_message('unauthorized');
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                'message' => $message,
                'data' => FALSE,
            );
        }
        $barcode_range = explode('-', $location_details->barcode_series);

        if (!is_array($barcode_range) || count($barcode_range) != 2) {

            \Illuminate\Support\Facades\Session::put('error_message', 'Sorry, You do not have access!');
            $message = $verify_booking->get_error_message('unauthorized');
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                'message' => $message,
                'data' => FALSE,
            );
        }


        if ($barcode >= $barcode_range[0] && $barcode <= $barcode_range[1]) {

            $is_barcode_at_location = $this->is_barcode_at_location($barcode);
            if ($is_barcode_at_location) {

                \Illuminate\Support\Facades\Session::put('error_message', 'You are already on location!');
                $message = $verify_booking->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }


            $booking_barcode = $this->add_barcode_booking($barcode);
            if (!$booking_barcode) {
                \Illuminate\Support\Facades\Session::put('error_message', 'Sorry You do not have access!');
                $message = $verify_booking->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $booking_id = $booking_barcode->id;

            $valid_passback = $verify_booking->is_valid_antipassback($booking_barcode, $id);
            if (!$valid_passback['status']) {

                \Illuminate\Support\Facades\Session::put('error_message', 'You are already on location!');
                $message = $verify_booking->get_error_message('anti_passback_message_barcode', '');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'normal'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            \Illuminate\Support\Facades\Session::put('error_message', 'Welcome');
            $message = $verify_booking->get_error_message('welcome_entrance', '');
            return array(
                'status' => 1,
                'access_status' => 'allow',
                'od_sent' => $settings->send_message_od($id, $message, 'normal'),
                'message' => $message,
                'data' => $booking_id,
            );
        }
        \Illuminate\Support\Facades\Session::put('error_message', 'Sorry You do not have access!');
        $message = $verify_booking->get_error_message('unauthorized_whitelist');
        return array(
            'status' => 1,
            'access_status' => 'denied',
            'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
            'message' => $message,
            'data' => FALSE,
        );
    }

    function is_valid_person_booking($barcode, $device_info)
    {
        $settings = new Settings();
        $verify_booking = new VerifyBookings();
        if ($device_info->available_device_id != 2) {
            $message = $verify_booking->get_error_message('anti_passback_message');
            return array(
                'status' => 1,
                'access_status' => 'error',
                'od_sent' => FALSE,
                'message' => $message,
                'data' => FALSE,
            );
        }

        try {
            $booking_details = \App\Bookings::where([
                ['live_id', intval($barcode)]
            ])
                ->whereIn('type', array(6, 11))
                ->whereDate('checkin_time', '<=', \Carbon\Carbon::today())
                ->whereDate('checkout_time', '>=', \Carbon\Carbon::today())
                ->orderBy('created_at', 'DESC')
                ->first();
            $blocked_person = $this->is_person_blocked($barcode);
            if ($blocked_person) {
                $message = $this->ticket_reader->get_error_message('user_blocked', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($device_info->id, $message, 'user_blocked'),
                    'data' => FALSE,
                );
            }
            if ($booking_details && isset($booking_details->id)) {
                $user_name = $verify_booking->get_user_name($booking_details);
                //$is_booking_at_location = $settings->is_booking_at_location($booking_details->id);
                if ($device_info->device_direction == 'in') {
                    if (isset($booking_details->product_id) && $booking_details->type != 3) {
                        $product = $this->ticket_reader->getProduct($booking_details->product_id);
                        if ($product && $product->no_of_time == null) {
                            $bookings_onlocation = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                $query->whereNull('check_out');
                            })->where('live_id', $booking_details->live_id)->count();
                            if ($bookings_onlocation > 0) {
                                $message = $verify_booking->get_error_message('already_at_location', '');
                                return array(
                                    'status' => 1,
                                    'access_status' => 'denied',
                                    'message' => $message,
                                    'od_sent' => FALSE,
                                    'data' => $booking_details,
                                );
                            }
                        }
                    }
                    if ($device_info->anti_passback) {
                        if (empty($device_info->time_passback) || $device_info->time_passback == 0) {
                            $is_booking_at_location = $settings->is_booking_at_location($booking_details->id);
                            if ($is_booking_at_location) {
                                \Illuminate\Support\Facades\Session::put('error_message', 'Booking is already at location');
                                $message = $verify_booking->get_error_message('already_at_location', '');
                                return array(
                                    'status' => 1,
                                    'access_status' => 'denied',
                                    'message' => $message,
                                    'od_sent' => FALSE,
                                    'data' => $booking_details,
                                );
                            }
                        } else {
                            $valid_timepassback = $verify_booking->is_valid_timepassback($booking_details, $device_info);
                            if (!$valid_timepassback['status']) {
                                \Illuminate\Support\Facades\Session::put('error_message', 'Booking is already at location');
                                $message = $verify_booking->get_error_message('already_at_location', '');
                                return array(
                                    'status' => 1,
                                    'access_status' => 'denied',
                                    'message' => $message,
                                    'od_sent' => FALSE,
                                    'data' => $booking_details,
                                );
                            }
                        }
                    }
                } elseif ($device_info->device_direction == 'out') {
                    $message = $verify_booking->get_error_message('goodbye_exit', $user_name);
                    return array(
                        'status' => 1,
                        'access_status' => 'allow',
                        'od_sent' => false,
                        'message' => $message,
                        'data' => $booking_details->id,
                    );
                }
                \Illuminate\Support\Facades\Session::put('error_message', 'Welcome');
                $message = $verify_booking->get_error_message('welcome_entrance_person', $user_name);
                return array(
                    'status' => 1,
                    'access_status' => 'allow',
                    'od_sent' => false,
                    'message' => $message,
                    'data' => $booking_details->id,
                );
            }

            $booking_details = \App\Bookings::where([
                ['id', intval($barcode)]
            ])
                ->whereIn('type', array(6, 11))
                ->whereDate('checkin_time', '<=', \Carbon\Carbon::today())
                ->whereDate('checkout_time', '>=', \Carbon\Carbon::today())
                ->orderBy('created_at', 'DESC')
                ->first();
            $blocked_person = $this->is_person_blocked($barcode);
            if ($blocked_person) {
                $message = $this->ticket_reader->get_error_message('user_blocked', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($device_info->id, $message, 'user_blocked'),
                    'data' => FALSE,
                );
            }
            if ($booking_details && isset($booking_details->id)) {
                $user_name = $verify_booking->get_user_name($booking_details);
                //$is_booking_at_location = $settings->is_booking_at_location($booking_details->id);
                if ($device_info->device_direction == 'in') {
                    if (isset($booking_details->product_id) && $booking_details->type != 3) {
                        $product = $this->ticket_reader->getProduct($booking_details->product_id);
                        if ($product && $product->no_of_time == null) {
                            $bookings_onlocation = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                $query->whereNull('check_out');
                            })->where('live_id', $booking_details->live_id)->count();
                            if ($bookings_onlocation > 0) {
                                $message = $verify_booking->get_error_message('already_at_location', '');
                                return array(
                                    'status' => 1,
                                    'access_status' => 'denied',
                                    'message' => $message,
                                    'od_sent' => FALSE,
                                    'data' => $booking_details,
                                );
                            }
                        }
                    }
                    if ($device_info->anti_passback) {
                        if (empty($device_info->time_passback) || $device_info->time_passback == 0) {
                            $is_booking_at_location = $settings->is_booking_at_location($booking_details->id);
                            if ($is_booking_at_location) {
                                \Illuminate\Support\Facades\Session::put('error_message', 'Booking is already at location');
                                $message = $verify_booking->get_error_message('already_at_location', '');
                                return array(
                                    'status' => 1,
                                    'access_status' => 'denied',
                                    'message' => $message,
                                    'od_sent' => FALSE,
                                    'data' => $booking_details,
                                );
                            }
                        } else {
                            $valid_timepassback = $verify_booking->is_valid_timepassback($booking_details, $device_info);
                            if (!$valid_timepassback['status']) {
                                \Illuminate\Support\Facades\Session::put('error_message', 'Booking is already at location');
                                $message = $verify_booking->get_error_message('already_at_location', '');
                                return array(
                                    'status' => 1,
                                    'access_status' => 'denied',
                                    'message' => $message,
                                    'od_sent' => FALSE,
                                    'data' => $booking_details,
                                );
                            }
                        }
                    }
                } elseif ($device_info->device_direction == 'out') {
                    $message = $verify_booking->get_error_message('goodbye_exit', $user_name);
                    return array(
                        'status' => 1,
                        'access_status' => 'allow',
                        'od_sent' => false,
                        'message' => $message,
                        'data' => $booking_details->id,
                    );
                }
                \Illuminate\Support\Facades\Session::put('error_message', 'Welcome');
                $message = $verify_booking->get_error_message('welcome_entrance_person', $user_name);
                return array(
                    'status' => 1,
                    'access_status' => 'allow',
                    'od_sent' => false,
                    'message' => $message,
                    'data' => $booking_details->id,
                );
            }
            $vip_barcode = \App\Barcode::where('barcode', $barcode)
            ->where('type', 'person')
            ->orderBy('created_at', 'desc')
            ->first();
            if ($vip_barcode) {
                if ($device_info->device_direction == 'out') {
                    $booking_details = \App\Bookings::where('barcode', $barcode)
                        ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
                        ->whereNull('checkout_time')
                        ->where('type', 5)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($booking_details) {
                        $message = $verify_booking->get_error_message('goodbye_exit', $booking_details->first_name ? $booking_details->first_name : '');
                        return array(
                            'status' => 1,
                            'access_status' => 'allow',
                            'od_sent' => false,
                            'message' => $message,
                            'data' => $booking_details->id,
                        );
                    }
                }
                //                $is_barcode_at_location = $this->is_barcode_at_location($barcode);
                //                if ($is_barcode_at_location) {
                //                    \Illuminate\Support\Facades\Session::put('error_message', 'You are already on location!');
                //                    $message = $verify_booking->get_error_message('anti_passback_message_barcode');
                //                    return array(
                //                        'status' => 1,
                //                        'access_status' => 'error',
                //                        'od_sent' => $settings->send_message_od($device_info->id, $message, 'vip'),
                //                        'message' => $message,
                //                        'data' => FALSE,
                //                    );
                //                }

                $booking = $this->add_barcode_booking($barcode, $vip_barcode);
                if (!$booking) {
                    \Illuminate\Support\Facades\Session::put('error_message', 'Sorry You do not have access');
                    $message = $verify_booking->get_error_message('unauthorized');
                    return array(
                        'status' => 1,
                        'access_status' => 'error',
                        'od_sent' => $settings->send_message_od($device_info->id, $message, 'vip'),
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $valid_passback = $verify_booking->is_valid_antipassback($booking, $device_info->id);
                if (!$valid_passback['status']) {
                    \Illuminate\Support\Facades\Session::put('error_message', 'You are already on location!');
                    $message = $verify_booking->get_error_message('anti_passback_message_barcode', '');
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'od_sent' => $settings->send_message_od($device_info->id, $message, 'vip'),
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $message = $verify_booking->get_error_message('welcome_entrance_person', '');
                if ($vip_barcode->message != NULL) {
                    $message = $vip_barcode->message;
                }
                return array(
                    'status' => 1,
                    'access_status' => 'allow',
                    'od_sent' => $settings->send_message_od($device_info->id, $message, 'vip'),
                    'message' => $message,
                    'data' => $booking->id,
                    'type' => 'vip',
                );
            }
            if ($device_info->barrier_status == 3) {
                $message = $verify_booking->get_error_message('welcome_entrance_person', '');
                return array(
                    'status' => 1,
                    'access_status' => 'allow',
                    'od_sent' => false,
                    'message' => $message,
                    'data' => 1,
                );
            }
            $message = $verify_booking->get_error_message('unauthorized_whitelist');
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => false,
                'message' => $message,
                'data' => FALSE,
            );
        } catch (Exception $ex) {
            $message = $verify_booking->get_error_message('unauthorized_whitelist');
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => false,
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    function add_barcode_booking($barcode, $vip_barcode = FALSE)
    {
        try {
            if ($barcode == NULL) {
                return FALSE;
            }
            $payment_id = 'Parking Ticket';
            $booking = new \App\Bookings();
            if ($vip_barcode) {
                $booking->first_name = $vip_barcode->name;
                if ($vip_barcode->type == 'parking') {
                    $booking->vehicle_num = $vip_barcode->vehicle_no;
                } else {
                    $payment_id = 'Person Ticket';
                }
            }
            $booking->type = 5;
            $booking->checkin_time = date('Y-m-d H:i:s');
            $booking->barcode = $barcode;
            $booking->save();
            $booking_id = $booking->id;
            $location = \App\LocationOptions::first();
            $locationId = $location->live_id;
            $user_id = \App\User::first()->live_id;
            $Key = base64_encode($locationId . '_' . $user_id);
            $data = array(
                'barcode' => $barcode,
                'ticket_type' => 'barcode',
                'type' => 5,
                'amount' => 0,
                'payment_id' => $payment_id
            );
            $http = new Client();
            $response = $http->post(env('API_BASE_URL') . '/api/store-booking-info', [
                'form_params' => [
                    'token' => $Key,
                    'data' => $data
                ],
            ]);
            $responseData = json_decode((string) $response->getBody(), true);

            if ($responseData['success'] && count($responseData['data']) > 0) {
                if (array_key_exists('booking_info_live_id', $responseData['data'])) {
                    $booking_details = \App\Bookings::find($booking_id);

                    $booking_details->live_id = $responseData['data']['booking_info_live_id'];
                    $booking_details->save();
                }
                if (array_key_exists('booking_payment_live_id', $responseData['data'])) {
                    $booking_payments = new \App\BookingPayments();
                    $booking_payments->live_id = $responseData['data']['booking_payment_live_id'];
                    $booking_payments->booking_id = $booking->id;
                    $booking_payments->amount = 0;
                    $booking_payments->save();
                } else {
                    $booking_payments = new \App\BookingPayments();
                    $booking_payments->live_id = 0;
                    $booking_payments->booking_id = $booking->id;
                    $booking_payments->amount = 0;
                    $booking_payments->save();
                }
            }
            return $booking;
        } catch (\Exception $ex) {
            return FALSE;
            $ex->getTrace();
        }
    }

    function is_barcode_at_location($barcode)
    {
        $booking = \App\Bookings::where('type', 5)
            ->where('barcode', $barcode)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$booking) {
            return FALSE;
        }
        $settings = new Settings();
        return $settings->is_booking_at_location($booking->id);
    }

    public function validate_barcode_exit(Request $request, $key, $id, $barcode, $vehicle_number)
    {
        $settings = new Settings();
        $verify_booking = new VerifyBookings();
        try {
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $verify_booking->get_error_message('unknown');
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'od_sent' => FALSE,
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $vehicle_blocked = $this->is_vehicle_blocked($vehicle_number);
            if ($vehicle_blocked) {
                $message = $this->ticket_reader->get_error_message('user_blocked', '', $this->lang_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($id, $message, 'user_blocked'),
                    'data' => FALSE,
                );
            }
            if ($valid_settings->available_device_id != 1) {
                $message = $verify_booking->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'od_sent' => FALSE,
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            $vip_barcode = \App\Barcode::where('barcode', $barcode)
            ->orderBy('created_at', 'desc')
            ->first();
            if ($vip_barcode) {
                if ($vip_barcode->type == 'person') {
                    $message = $verify_booking->get_error_message('unauthorized');
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'od_sent' => FALSE,
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $booking_details = $this->get_vehicle_booking($vehicle_number, TRUE);

                if (!$booking_details) {
                    $message = $verify_booking->get_error_message('goodbye_exit');
                    $verify_vehicle = new \App\Http\Controllers\PlateReaderController\VerifyVehicle();
                    $open_gate = $verify_vehicle->open_gate_plate_reader($valid_settings, $vehicle_number, $message, 'exit');
                    return array(
                        'status' => 1,
                        'access_status' => 'allow',
                        'od_sent' => FALSE,
                        'message' => $message,
                        'data' => 0
                    );
                }
                //$booking = \App\Bookings::find($booking_details->id);
                $booking_details->barcode = $vip_barcode->id;
                $booking_details->is_paid = 1;
                //$booking_details->checkout_time = date('Y-m-d H:i:s');
                $booking_details->save();
                $message = $verify_booking->get_error_message('goodbye_exit');
                $verify_vehicle = new \App\Http\Controllers\PlateReaderController\VerifyVehicle();
                $open_gate = $verify_vehicle->open_gate_plate_reader($valid_settings, $vehicle_number, $message, 'exit');
                return array(
                    'status' => 1,
                    'access_status' => 'allow',
                    'od_sent' => FALSE,
                    'message' => $message,
                    'data' => $booking_details->id
                );
            }
            $booking_details = \App\Bookings::where(
                'live_id',
                $barcode
            )
                ->where('checkin_time', '<=', date('Y-m-d H:i'))
                ->where('checkout_time', '>', date('Y-m-d H:i'))
                ->first();
            if ($booking_details) {
                if ($booking_details->type == 6) {
                    $message = $verify_booking->get_error_message('unauthorized');
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'od_sent' => FALSE,
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $vehicle_blocked = $this->is_vehicle_blocked(null, $booking_details);
                if ($vehicle_blocked) {
                    $message = $this->ticket_reader->get_error_message('user_blocked', '', $this->lang_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'user_blocked'),
                        'data' => FALSE,
                    );
                }
                $message = $verify_booking->get_error_message('goodbye_exit');
                $verify_vehicle = new \App\Http\Controllers\PlateReaderController\VerifyVehicle();
                $open_gate = $verify_vehicle->open_gate_plate_reader($valid_settings, $vehicle_number, $message, 'exit');
                return array(
                    'status' => 1,
                    'access_status' => 'allow',
                    'od_sent' => FALSE,
                    'message' => $message,
                    'data' => $booking_details->id
                );
            }
            $location = new LocationSettings();
            $location_details = $location->get_location();
            if (!$location_details) {
                \Illuminate\Support\Facades\Session::put('error_message', 'Invalid');
                $message = $verify_booking->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            if ($location_details->barcode_series == NULL) {
                \Illuminate\Support\Facades\Session::put('error_message', 'Sorry, You do not have access!');
                $message = $verify_booking->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $barcode_range = explode('-', $location_details->barcode_series);

            if (!is_array($barcode_range) || count($barcode_range) != 2) {

                \Illuminate\Support\Facades\Session::put('error_message', 'Sorry, You do not have access!');
                $message = $verify_booking->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }


            if ($barcode >= $barcode_range[0] && $barcode <= $barcode_range[1]) {

                $is_barcode_at_location = $this->is_barcode_at_location($barcode);
                if ($is_barcode_at_location) {

                    \Illuminate\Support\Facades\Session::put('error_message', 'You are already on location!');
                    $message = $verify_booking->get_error_message('unauthorized');
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                        'message' => $message,
                        'data' => FALSE,
                    );
                }


                $booking_barcode = $this->add_barcode_booking($barcode);
                if (!$booking_barcode) {
                    \Illuminate\Support\Facades\Session::put('error_message', 'Sorry You do not have access!');
                    $message = $verify_booking->get_error_message('unauthorized');
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $booking_id = $booking_barcode->id;

                $valid_passback = $verify_booking->is_valid_antipassback($booking_barcode, $id);
                if (!$valid_passback['status']) {

                    \Illuminate\Support\Facades\Session::put('error_message', 'You are already on location!');
                    $message = $verify_booking->get_error_message('anti_passback_message_barcode', '');
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'od_sent' => $settings->send_message_od($id, $message, 'normal'),
                        'message' => $message,
                        'data' => FALSE,
                    );
                }

                \Illuminate\Support\Facades\Session::put('error_message', 'Welcome');
                $message = $verify_booking->get_error_message('welcome_entrance', '');
                return array(
                    'status' => 1,
                    'access_status' => 'allow',
                    'od_sent' => $settings->send_message_od($id, $message, 'normal'),
                    'message' => $message,
                    'data' => $booking_id,
                );
            }
            \Illuminate\Support\Facades\Session::put('error_message', 'Sorry You do not have access!');
            $message = $verify_booking->get_error_message('unauthorized_whitelist');
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                'message' => $message,
                'data' => FALSE,
            );
        } catch (\Exception $ex) {
            $message = $verify_booking->get_error_message('unauthorized');
            return array(
                'status' => 1,
                'access_status' => 'denied',
                'od_sent' => FALSE,
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    public function get_vehicle_booking($vehicle_num, $barcode_booking = FALSE)
    {
        try {
            $booking_details = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                $query->whereNull('check_out');
            })->where('vehicle_num', $vehicle_num)->first();
            if ($booking_details) {
                return $booking_details;
            } else {
                if ($barcode_booking) {
                    $new_booking = $this->add_vehicle_booking($vehicle_num);
                    if ($new_booking) {
                        return $new_booking;
                    }
                }
            }
            return FALSE;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function add_vehicle_booking($vehicle_num)
    {
        try {
            $checkin_time = date('Y-m-d H:i:s');
            $device_booking = \App\DeviceBookings::where([
                ['vehicle_num', $vehicle_num]
            ])
                ->whereIn('device_id', [5, 25])
                ->whereDate('created_at', \Carbon\Carbon::today())
                ->orderBy('created_at', 'desc')
                ->first();
            if ($device_bookings) {
                $checkin_time = date('Y-m-d H:i:s', strtotime($device_booking->created_at));
            }
            $dataArray = array(
                'first_name' => 'Paid Vehicle',
                'vehicle_num' => $vehicle_num,
                'type' => 8,
                'is_paid' => 0,
                'checkin_time' => $checkin_time,
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
                return FALSE;
            }
            $attendant = \App\Attendants::where('booking_id', $bookingId)->first();
            if (!$attendant) {
                $attendant = new \App\Attendants();
            }
            $attendant->booking_id = $bookingId;
            $attendant->save();
            $attendant_id = $attendant->id;
            $attendant_transaction = new \App\AttendantTransactions();
            $attendant_transaction->attendant_id = $attendant_id;
            $attendant_transaction->check_in = $checkin_time;
            $attendant_transaction->save();
            return $booking;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }
    public function is_vehicle_blocked($vehicle_num, $booking = null)
    {
        $vehicle_blocked = FALSE;
        if ($vehicle_num) {
            $userlist_users = \App\UserlistUsers::whereHas('customer_vehicle_info', function ($query) use ($vehicle_num) {
                $query->where('num_plate', $vehicle_num);
            })->where('is_blocked', 1)->first();

            if ($userlist_users) {
                $vehicle_blocked = TRUE;
            }
            $day_ticket_vehicle = \App\Bookings::where('vehicle_num', $vehicle_num)->where('is_blocked', 1)->first();
            if ($day_ticket_vehicle) {
                $vehicle_blocked = TRUE;
            }
        } else {
            $blocked = \App\Bookings::where('id', $booking->id)->where('is_blocked', 1)->first();
            if ($blocked) {
                $vehicle_blocked = TRUE;
            }
        }

        return $vehicle_blocked;
    }
    public function is_person_blocked($barcode)
    {
        $person_blocked = FALSE;
        $person_day_ticket = \App\Bookings::whereIn('type', [6, 11])->where(function ($query) use ($barcode) {
            $query->where('id', $barcode)
                ->orWhere('live_id', $barcode);
        })->where('is_blocked', 1)->first();
        if ($person_day_ticket) {
            $person_blocked = TRUE;
        }
        return $person_blocked;
    }
}
