<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Controllers\Settings\Settings;
use App\Http\Controllers\Settings\VerifyBookings;
use App\Http\Controllers\Settings\BarcodeSettings;

class BookingController extends BaseController {

    /**
     * Store Single Userlist Record.
     *
     * @return \Illuminate\Http\Response
     */
    public function store_single_data(Request $request) {
        //
        try {
            \Illuminate\Support\Facades\Config::set('database.connections.mysql.database', 'parkingshopv4');
            \Illuminate\Support\Facades\DB::purge('mysql');
            $headers = $request->headers->all();
            $token = explode('-', $headers['authorization'][0]);
            if ($token == '' || empty($token)) {
                $token = explode('-', $request->token);
            }
            $error_log = new \App\Http\Controllers\LogController();
            if (count($token) != 4) {
                $error_log->log_create('API_BookingController_get_data', 'Invalid API Key', 'Invalid API Key');
                return $this->sendError('Invalid API Key', 'Invalid Key!');
            } else {
                $error_log->log_create('API_BookingController_get_data', 'Valid API Key', 'Valid API Key');
            }
            $location_id = base64_decode($token[2]);
            $location = \App\LocationOptions::where([
                        ['live_id', $location_id]
                    ])->first();
            if (!$location) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('API_BookingController_get_data', 'Location Not Found', 'Location Not Found');
                return $this->sendError('Invalid API Key', 'Invalid Key!');
            } else {
                $error_log->log_create('API_BookingController_get_data', 'Location found', 'Location found');
            }
            $data_store = $this->store_data($request->data);
            if ($data_store) {
                return $this->sendResponse($request->data, 'Booking Info stored successfully.');
            }
            return $this->sendError('Exception', 'Something Went Wrong!');
        } catch (\Exception $e) {
            return $this->sendError('Exception', $e->getMessage());
        }
    }

    public function store_data($data) {
        try {
            $booking = '';
            $payment = '';
            if (array_key_exists('BookingInfo', $data)) {
                $booking = $data['BookingInfo'];
            }
            if (array_key_exists('PaymentDetails', $data)) {
                $payment = $data['PaymentDetails'];
            }
            if ($booking == '' && $payment == '') {
                $error_log->log_create('API_BookingController_store_data', 'Invalid Data', 'Invalid Data');
                return FALSE;
            }

            $bookings = \App\Bookings::where('live_id', $booking['id'])->first();
            if (!$bookings) {
                $bookings = new \App\Bookings();
            }

            $bookings->live_id = $booking['id'];
            if (array_key_exists('checkin_time', $booking)) {
                $bookings->checkin_time = $booking['checkin_time'];
            }
            if (array_key_exists('checkout_time', $booking)) {
                $bookings->checkout_time = $booking['checkout_time'];
            }
            if (array_key_exists('type', $booking)) {
                $bookings->type = $booking['type'];
            }
            if (array_key_exists('vehicle_num', $booking)) {
                $bookings->vehicle_num = $booking['vehicle_num'];
            }
            if (array_key_exists('first_name', $booking)) {
                $bookings->first_name = $booking['first_name'];
            }
            if (array_key_exists('last_name', $booking)) {
                $bookings->last_name = $booking['last_name'];
            }
            if (array_key_exists('email', $booking)) {
                $bookings->email = $booking['email'];
            }
            if (array_key_exists('tommy_children_dob', $booking)) {
                $bookings->tommy_children_dob = $booking['tommy_children_dob'];
            }
            $bookings->save();

            $bookingsId = $bookings->id;

            $bookingPayments = \App\BookingPayments::where('booking_id', $bookingsId)->first();
            if (!$bookingPayments) {
                $bookingPayments = new \App\BookingPayments();
            }
            $bookingPayments->live_id = $payment['id'];
            if (array_key_exists('amount', $payment)) {
                $bookingPayments->amount = $payment['amount'];
            }
            $bookingPayments->booking_id = $bookingsId;
            if (array_key_exists('checkin_time', $payment)) {
                $bookingPayments->checkin_time = $payment['checkin_time'];
            }
            if (array_key_exists('checkout_time', $payment)) {
                $bookingPayments->checkout_time = $payment['checkout_time'];
            }
            if (array_key_exists('is_online', $payment)) {
                $bookingPayments->is_online = $payment['is_online'];
            }
            $bookingPayments->save();
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-bookings', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function verify_booking(Request $request) {
        try {
            $current_locale = \App::getLocale();
            $verify_booking = new VerifyBookings();
            $settings = new Settings();
            $barcode_settings = new BarcodeSettings();
            \App::setLocale($request->language);
            $language_id = 2;
            $language = \App\Language::where('code', $request->language)->first();
            if ($language) {
                $language_id = $language->id;
            }
            $response_data = array();
            $val = (int) $request->booking;
            $booking_details = $this->validate_by_booking_id($val);
            if (!$booking_details) {
                return $this->sendError('Error', __('booking.ticketInvalidMessage'));
            }
            $user_name = $this->get_user_name($booking_details);
//            $is_parking_open = $this->is_parking_open();
//            if (!$is_parking_open) {
//                return $this->sendError('Error', 'Parking is closed');
//            }
            $timings = $this->valid_timings($booking_details);
            if (!$timings['status']) {
                return $this->sendError('Error', $timings['message_code']);
            }
            if ($booking_details->type == 4) {
                if ($booking_details->checkout_time == date('Y-12-31 23:59:59')) {
                    $successMessage = __('booking.ticketValidMessage') . '(' . __('booking.parking') . ' ' . __('booking.subscription') . ')';
                } else {
                    $successMessage = __('booking.ticketValidMessage') . '(' . __('booking.parking') . ' ' . __('booking.day_ticket') . ')';
                }
            } else {
                $device_info = \App\LocationDevices::where('available_device_id', 2)
                        ->where('device_direction', 'in')
                        ->first();
                $is_valid_person_booking = $barcode_settings->is_valid_person_booking($booking_details->id, $device_info);
                if ($is_valid_person_booking['access_status'] != 'allow') {
                    $message = $is_valid_person_booking['message'];
                    return $this->sendError('Error', $message);
                }
//                $is_booking_at_location = $settings->is_booking_at_location($booking_details->id);
//                if ($is_booking_at_location) {
//                    $message = $verify_booking->get_error_message('already_at_location', $user_name, $language_id);
//                    return $this->sendError('Error', $message);
//                }
                $attendant = \App\Attendants::where('booking_id', $booking_details->id)->first();
                if (!$attendant) {
                    $attendant = new \App\Attendants();
                    $attendant->booking_id = $booking_details->id;
                    $attendant->save();
                }
                $attendant_id = $attendant->id;
//                $attendant_transaction = \App\AttendantTransactions::where([
//                            ['attendant_id', $attendant_id],
//                        ])
//                        ->whereNotNull('check_in')
//                        ->whereNull('check_out')
//                        ->orderBy('created_at', 'desc')
//                        ->first();
//                if ($attendant_transaction) {
//                    $attendant_transaction->check_out = date('Y-m-d H:i:s');
//                    $attendant_transaction->save();
//                }
                $attendant_transaction = new \App\AttendantTransactions();
                $attendant_transaction->attendant_id = $attendant_id;
                $attendant_transaction->check_in = date('Y-m-d H:i:s');
                $attendant_transaction->save();

                if ($booking_details->checkout_time == date('Y-12-31 23:59:59')) {
                    $successMessage = __('booking.ticketValidMessage') . '(' . __('booking.person') . ' ' . __('booking.subscription') . ')';
                } else {
                    $successMessage = __('booking.ticketValidMessage') . '(' . __('booking.person') . ' ' . __('booking.day_ticket') . ')';
                }
            }

            \App::setLocale($current_locale);
            return $this->sendResponse($response_data, $successMessage);
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('verify_booking', $ex->getMessage(), $ex->getTraceAsString());
            return $this->sendError('Error', __('booking.ticketInvalidMessage'));
        }
    }

    public function validate_by_booking_id($booking_id) {
        try {
            $booking_details = \App\Bookings::where('live_id', $booking_id)->first();
            if (!$booking_details) {
                return FALSE;
            }
            return $booking_details;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function get_user_name($booking_details) {
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
        return $user;
    }

    public function is_parking_open() {
        $day_num = date('w');
        $location_timings = \App\LocationTimings::where([
                    ['is_whitelist', 0],
                    ['week_day_num', $day_num],
                ])->first();
        if ($location_timings) {
            $now = date('H:i');
            $start = date('H:i', strtotime($location_timings->opening_time));
            $end = date('H:i', strtotime($location_timings->closing_time));
            if (($now >= $start) && ($now <= $end )) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function valid_timings($booking) {
        $now = date('Y-m-d H:i');
        $start = date('Y-m-d H:i', strtotime($booking->checkin_time));
        $end = date('Y-m-d H:i', strtotime($booking->checkout_time));

        if (($now >= $start) && ($now <= $end )) {
            return array(
                'status' => TRUE,
                'message_code' => '',
            );
        }
        if ($now < $start) {
            return array(
                'status' => FALSE,
                'message_code' => __('booking.ticketEarlyInvalidMessage'),
            );
        }
        if ($now > $end) {
            return array(
                'status' => FALSE,
                'message_code' => __('booking.ticketLateInvalidMessage'),
            );
        }
        return array(
            'status' => FALSE,
            'message_code' => __('booking.ticketInvalidMessage'),
        );
    }

}
