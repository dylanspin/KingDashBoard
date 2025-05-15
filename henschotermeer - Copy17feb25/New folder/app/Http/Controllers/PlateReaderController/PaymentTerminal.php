<?php

namespace App\Http\Controllers\PlateReaderController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Settings\Settings;
use App\Http\Controllers\PlateReaderController\VerifyVehicle;
use GuzzleHttp\Client;

class PaymentTerminal extends Controller {

    public $price_per_minute = 1;
    public $price_per_hour = 1;
    public $price_per_day = 1;
    public $price_per_day_product = 6;
    public $lag_time = 10;
    public $time_limit = 180;
    public $ticket_reader;
    public $settings;
    public $verfify_vehicle;
    public $key = FALSE;
    public $url = "";

    public function __construct($key = NULL) {
        $this->url = env('API_BASE_URL');
        $this->ticket_reader = new \App\Http\Controllers\Settings\VerifyBookings();
        $this->settings = new \App\Http\Controllers\Settings\Settings();
        $this->verfify_vehicle = new VerifyVehicle();
        $location_setting = \App\LocationOptions::first();
        $this->price_per_minute = $location_setting->price_per_hour / 60;
        $this->price_per_hour = $location_setting->price_per_hour;
        $this->price_per_day = $location_setting->price_per_day;
        if (!empty($location_setting->time_lag)) {
            $this->lag_time = $location_setting->time_lag;
        }
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
    }

    public function get_vehicle_ticket_price(Request $request, $key, $id, $vehicle, $type_id, $language_id = 2) {
        try {
            $valid_settings = $this->settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $vehicle_booking = $this->verfify_vehicle->get_vehicle_booking($vehicle, 'out');

            if (!$vehicle_booking) {
                $message = $this->ticket_reader->get_error_message('Unauthorized', '', $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            if ($vehicle_booking->type == 2 || $vehicle_booking->type == 3) {
                $message = $this->ticket_reader->get_error_message('payment_not_eligible', '', $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
            $at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
            if (!$at_location) {
                $message = $this->ticket_reader->get_error_message('Unauthorized', $user_name, $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            $day_ticket_id = $this->get_day_ticket_product()->id;
            if ($type_id == 0 || $type_id == $day_ticket_id) {
                $at_location_minutes = $this->get_at_location_time_minutes($vehicle_booking);
                if (!$at_location_minutes) {
                    $message = $this->ticket_reader->get_error_message('payment_not_eligible', '', $language_id);
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                if ($type_id == $day_ticket_id) {
                    if ($at_location_minutes > $this->time_limit) {
                        $message = $this->ticket_reader->get_error_message('not_authorized_day_ticket', '', $language_id);
                        return array(
                            'status' => 1,
                            'access_status' => 'denied',
                            'message' => $message,
                            'data' => FALSE,
                        );
                    }
                }
                $booking_price = $this->get_booking_price($vehicle_booking);
                if (!$booking_price['status']) {
                    $message = $this->ticket_reader->get_error_message('payment_not_eligible', '', $language_id);

                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $data = array();
                $data['booking'] = $vehicle_booking->id;
                $data['day_price'] = $booking_price['day'];
                $data['hourly_price'] = $booking_price['hourly'];
                $data['eligible_type'] = 'hourly';
                if ($at_location_minutes < $this->time_limit) {
                    if (!$vehicle_booking->is_paid) {
                        if ($data['day_price'] > $data['hourly_price']) {
                            $data['eligible_type'] = 'hourly';
                        } else {
                            $data['eligible_type'] = 'day';
                        }
                    }
                }
                $status = 'hourly';
                if ($type_id == $day_ticket_id) {
                    $status = 'day';
                    $data['hourly_price'] = $data['day_price'];
                }
                $message = 'Success';
                return array(
                    'status' => 1,
                    'access_status' => $status,
                    'message' => $message,
                    'data' => $data
                );
            } else {
                $message = $this->ticket_reader->get_error_message('Unauthorized', '', $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('vehicle-verify', $ex->getMessage(), $ex->getTraceAsString());
            $message = $this->ticket_reader->get_error_message('unknown', '', $language_id);
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    public function get_day_ticket_product() {
        $products = \App\Products::where('type', 'day_ticket')->first();
        if ($products) {
            return $products;
        }
        return FALSE;
    }

    public function get_person_ticket_product() {
        $products = \App\Products::where('type', 'person_ticket')->first();
        if ($products) {
            return $products;
        }
        return FALSE;
    }

    public function get_lag_time() {
        $startTime = date("Y-m-d H:i:s");
        return date('Y-m-d H:i:s', strtotime('+' . $this->lag_time . ' minutes', strtotime($startTime)));
    }

    public function delete_lag_time($checkout_time) {
        return date('Y-m-d H:i', strtotime('-' . $this->lag_time . ' minutes', strtotime($checkout_time)));
    }

    public function get_at_location_time_minutes($vehicle_booking) {
        if (empty($vehicle_booking->checkin_time)) {
            return FALSE;
        }
        $attendant = \App\Attendants::where('booking_id', $vehicle_booking->id)->first();
        if (!$attendant) {
            return false;
        }
        $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendant->id)->whereNull('check_out')
                ->orderBy('created_at', 'desc')
                ->first();
        if (!$attendant_transaction) {
            return FALSE;
        }
        $booking_time = $attendant_transaction->check_in;
        $datetime1 = new \DateTime($booking_time);
        $datetime2 = new \DateTime();
        $interval = $datetime2->diff($datetime1);
        $minutes = $interval->days * 24 * 60;
        $minutes += $interval->h * 60;
        $minutes += $interval->i;
        return $minutes;
    }

    public function get_difference_time($start = NULL, $end = NULL, $type = 1) {
        if ($start == NULL) {
            $start = date('Y-m-d H:i');
        }
        if ($end == NULL) {
            $end = date('Y-m-d H:i');
        }
        $datetime1 = new \DateTime($start);
        $datetime2 = new \DateTime($end);
        $interval = $datetime2->diff($datetime1);
        if ($type == 2) {
            return $interval->h;
        } elseif ($type == 3) {
            $minutes = $interval->days * 24 * 60;
            $minutes += $interval->h * 60;
            $minutes += $interval->i;
            return $minutes;
        }
        return $interval->days;
    }

    public function get_booking_time_payment($vehicle_booking) {
        try {
            if ($vehicle_booking->checkout_time != NULL) {
                if ($vehicle_booking->booking_payments != null && $vehicle_booking->booking_payments->is_online) {
                    return $vehicle_booking->checkout_time;
                }
                return $this->delete_lag_time($vehicle_booking->checkout_time);
            }
            $attendants = \App\Attendants::where('booking_id', $vehicle_booking->id)->first();
            if ($attendants) {
                $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendants->id)
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($attendant_transaction) {
                    if ($attendant_transaction->check_out == NULL) {
                        return date('Y-m-d H:i', strtotime($attendant_transaction->check_in));
                    }
                }
            }
            return FALSE;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function get_booking_price($vehicle_booking) {
        $day_ticket_price = 0;
        $hourly_price = 0;
        if ($vehicle_booking->checkout_time != NULL) {
            if ($vehicle_booking->checkout_time > date('Y-m-d H:i')) {
                return array(
                    'status' => 0,
                    'day' => number_format($day_ticket_price, 2, ",", ","),
                    'hourly' => number_format($hourly_price, 2, ",", ","),
                );
            }
        }
        $booking_time = $this->get_booking_time_payment($vehicle_booking);
        if (!$booking_time) {
            return array(
                'status' => 0,
                'day' => number_format($day_ticket_price, 2, ",", ","),
                'hourly' => number_format($hourly_price, 2, ",", ","),
            );
        }
        $products = $this->get_day_ticket_product();
        if (!empty($products->price)) {
            $day_ticket_price = $products->price;
        }
        if (date('Y-m-d', strtotime($booking_time)) == date('Y-m-d')) {
            $at_location_minutes = $this->get_difference_time(date('Y-m-d H:i', strtotime($booking_time)), date('Y-m-d H:i'), 3);
            if (!$at_location_minutes) {
                return array(
                    'status' => 0,
                    'day' => number_format($day_ticket_price, 2, ",", ","),
                    'hourly' => number_format($hourly_price, 2, ",", ","),
                );
            }
            $hourly_price = $at_location_minutes * $this->price_per_minute;
            if ($hourly_price > $this->price_per_day) {
                $hourly_price = $this->price_per_day;
            }
        } elseif (date('Y-m-d', strtotime($booking_time)) < date('Y-m-d')) {

//            calculate checkin date price
            $total_price = 0;
            $checkin_time_difference = $this->get_difference_time(date('Y-m-d H:i', strtotime($booking_time)), date('Y-m-d 23:59', strtotime($booking_time)), 3);
            $checkin_time_difference_price = $checkin_time_difference * $this->price_per_minute;
            if ($checkin_time_difference_price > $this->price_per_day) {
                $checkin_time_difference_price = $this->price_per_day;
            }
            //Calculate other days price
            $other_days_time_difference = $this->get_difference_time(date('Y-m-d 00:00', strtotime($booking_time)), date('Y-m-d H:i'), 1);
            $other_days_time_difference = $other_days_time_difference - 1;
            if ($other_days_time_difference > 0) {
                $other_days_time_difference_price = $other_days_time_difference * $this->price_per_day;
            } else {
                $other_days_time_difference_price = 0;
            }
            //calculate checkout date price
            $checkout_time_difference = $this->get_difference_time(date('Y-m-d 00:00'), date('Y-m-d H:i'), 3);

            $checkout_time_difference_price = $checkout_time_difference * $this->price_per_minute;
            if ($checkout_time_difference_price > $this->price_per_day) {
                $checkout_time_difference_price = $this->price_per_day;
            }
            $hourly_price = $checkin_time_difference_price + $other_days_time_difference_price + $checkout_time_difference_price;
        }
        return array(
            'status' => 1,
            'day' => number_format($day_ticket_price, 2, ",", ","),
            'hourly' => number_format($hourly_price, 2, ",", ","),
        );
    }

    public function get_vehicle_ticket_price_status(Request $request, $key, $id, $booking, $price, $status, $transaction_reference, $transaction_id = NULL, $language_id = 2) {
        try {
            $price = str_replace(',', '.', $price);
            $valid_settings = $this->settings->is_valid_call($key, $id);
            $xml_string = NULL;
            if (isset($request->xml_string) && !empty($request->xml_string)) {
                $xml_string = $request->xml_string;
            }
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown', '', $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            if ($status != 1) {
                if ($status == 0 || $status == 2) {
                    $transaction_details = $this->make_transaction_entry($booking, $id, $price, $status, $transaction_reference, $transaction_id, $xml_string);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => 'not updated',
                        'data' => array(
                            'transaction_id' => $transaction_details
                        ),
                    );
                }
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => 'not updated',
                    'data' => FALSE
                );
            }
            $vehicle_booking = $this->get_booking_details($booking);
            if (!$vehicle_booking) {
                $message = $this->ticket_reader->get_error_message('Unauthorized', '', $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
            $at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
            if (!$at_location) {
                $message = $this->ticket_reader->get_error_message('Unauthorized', $user_name, $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            if ($transaction_reference > 0) {
                $checkout_time = date('Y-m-d 23:59:59');
            } else {
                $checkout_time = $this->get_lag_time();
            }
            $dataArray = array(
                'checkout_time' => $checkout_time,
                'is_paid' => 1,
                'amount' => $price,
                'booking_info_live_id' => 0,
                'booking_payment_live_id' => 0
            );
            $booking_details = $booking_details = \App\Bookings::find($booking);
            if ($booking_details) {
                $dataArray['booking_info_live_id'] = $booking_details->live_id;
                $dataArray['checkin_time'] = $booking_details->checkin_time;
                if ($dataArray['booking_info_live_id'] == 0) {
                    $dataArray['first_name'] = $booking_details->first_name;
                    $dataArray['vehicle_num'] = $booking_details->vehicle_num;
                }
                $booking_details->checkout_time = $dataArray['checkout_time'];
                $booking_details->is_paid = $dataArray['is_paid'];
                $booking_details->save();
                $bookingId = $booking_details->id;
                $booking_payment = \App\BookingPayments::where('booking_id', $booking_details->id)->first();
                if ($booking_payment) {
                    $dataArray['booking_payment_live_id'] = $booking_payment->live_id;
                    if ($dataArray['booking_payment_live_id'] == 0) {
                        $dataArray['payment_id'] = $booking_payment->payment_id;
                    }
                    $new_price = $booking_payment->amount + $dataArray['amount'];
                    $booking_payment->booking_id = $booking_details->id;
                    $booking_payment->amount = $new_price;
                    $booking_payment->checkout_time = $checkout_time;
					$booking_payment->is_online = 0;
                    $booking_payment->save();
                }
				else{
					$booking_payment = new \App\BookingPayments();
					$new_price = $dataArray['amount'];
                    $booking_payment->booking_id = $booking_details->id;
                    $booking_payment->amount = $new_price;
					$booking_payment->checkin_time =  $booking_details->checkin_time;
                    $booking_payment->checkout_time = $checkout_time;
					$booking_payment->is_online = 0;
                    $booking_payment->save();
				}
                $bookingPaymentId = $booking_payment->id;
//                try {
//                    $Key = $this->key;
//                    $http = new Client();
//                    $response = $http->post($this->url . '/api/store-booking-info', [
//                        'form_params' => [
//                            'token' => $Key,
//                            'data' => $dataArray
//                        ],
//                    ]);
//                    $responseData = json_decode((string) $response->getBody(), true);
//
//                    if (array_key_exists('booking_info_live_id', $responseData['data'])) {
//                        $booking = \App\Bookings::find($bookingId);
//                        if ($booking) {
//                            $booking->live_id = $responseData['data']['booking_info_live_id'];
//                            $booking->save();
//                        }
//                    }
//                    if (array_key_exists('booking_payment_live_id', $responseData['data'])) {
//                        $booking_payment = \App\BookingPayments::find($bookingPaymentId);
//                        if ($booking_payment) {
//                            $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
//                            $booking_payment->save();
//                        }
//                    }
//                } catch (\Exception $ex) {
//                    
//                }
            }
            $message = $this->ticket_reader->get_error_message('successfull_vehicle_payment', '', $language_id);
            $message = str_replace('{{time}}', date('m/d/Y H:i', strtotime($checkout_time)), $message);
            $transaction_details = $this->make_transaction_entry($booking, $id, $price, $status, $transaction_reference, $transaction_id, $xml_string);
            return array(
                'status' => 1,
                'access_status' => 'success',
                'message' => $message,
                'data' => array(
                    'transaction_id' => $transaction_details
                ),
            );
        } catch (\Exception $ex) {
			print_r($ex->getLine());
			print_r($ex->getTraceAsString());
			die();
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('ticket-price-vehicle-verify-status', $ex->getMessage(), $ex->getTraceAsString());
            $message = $this->ticket_reader->get_error_message('unknown', '', $language_id);
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    /**
     * Get vehicle ticket price response from payment terminal
     * @param Request $request
     * @param type $key
     * @param type $id
     * @param type $booking
     * @param type $price
     * @param type $status
     * @param type $transaction_reference
     * @param type $transaction_id
     * @return type
     */
    public function get_vehicle_ticket_price_payment_status(Request $request, $key, $id, $booking, $price, $status, $payment_type, $transaction_reference, $transaction_id = NULL, $language_id = 2, $promo_card = NULL, $discount = NULL, $amount = NULL) {
        try {			
            $price = str_replace(',', '.', $price);
            $valid_settings = $this->settings->is_valid_call($key, $id);
            $xml_string = NULL;
            if (isset($request->xml_string) && !empty($request->xml_string)) {
                $xml_string = $request->xml_string;
            }
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown', '', $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            if ($status != 1) {
                if ($status == 0 || $status == 2) {
                    $transaction_details = $this->make_transaction_entry($booking, $id, $price, $status, $transaction_reference, $transaction_id, $xml_string, $discount, $amount);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => 'not updated',
                        'data' => array(
                            'transaction_id' => $transaction_details
                        ),
                    );
                }
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => 'not updated',
                    'data' => FALSE
                );
            }
            $vehicle_booking = $this->get_booking_details($booking);
            if (!$vehicle_booking) {
                $message = $this->ticket_reader->get_error_message('Unauthorized', '', $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $user_name = $this->ticket_reader->get_user_name($vehicle_booking);
            $at_location = $this->settings->is_booking_at_location($vehicle_booking->id);
            if (!$at_location) {
                $message = $this->ticket_reader->get_error_message('Unauthorized', $user_name, $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $day_ticket_id = $this->get_day_ticket_product();
            if ($day_ticket_id) {
                if ($day_ticket_id->id == $payment_type) {
                    $checkout_time = date('Y-m-d 23:59:59');
                } else {
                    $checkout_time = $this->get_lag_time();
                }
            } else {
                $checkout_time = $this->get_lag_time();
            }
            $dataArray = array(
                'checkout_time' => $checkout_time,
                'is_paid' => 1,
                'amount' => $price,
                'booking_info_live_id' => 0,
                'booking_payment_live_id' => 0
            );
            $booking_details = \App\Bookings::find($booking);
            if ($booking_details) {
                $dataArray['booking_info_live_id'] = $booking_details->live_id;
                $dataArray['checkin_time'] = $booking_details->checkin_time;
                if ($dataArray['booking_info_live_id'] == 0) {
                    $dataArray['first_name'] = $booking_details->first_name;
                    $dataArray['vehicle_num'] = $booking_details->vehicle_num;
                }
                $booking_details->checkout_time = $dataArray['checkout_time'];
                $booking_details->is_paid = $dataArray['is_paid'];
                $booking_details->save();
                $bookingId = $booking_details->id;
                $booking_payment = \App\BookingPayments::where('booking_id', $booking_details->id)->first();
                if ($booking_payment) {
                    $dataArray['booking_payment_live_id'] = $booking_payment->live_id;
                    if ($dataArray['booking_payment_live_id'] == 0) {
                        $dataArray['payment_id'] = $booking_payment->payment_id;
                    }
                    $new_price = $booking_payment->amount + $dataArray['amount'];
                    $booking_payment->booking_id = $booking_details->id;
                    $booking_payment->amount = $new_price;
                    $booking_payment->checkout_time = $checkout_time;
                    $booking_payment->save();
                }
                $bookingPaymentId = $booking_payment->id;
                $vehicle_num = $booking_details->vehicle_num;
                if (empty($vehicle_num) && !empty($booking_details->customer_vehicle_info_id)) {
                    $customer_vehicle_info = \App\CustomerVehicleInfo::find($booking_details->customer_vehicle_info_id);
                    if ($customer_vehicle_info) {
                        $vehicle_num = $booking_details->vehicle_num;
                    }
                }
                if (!empty($promo_card) && !empty($vehicle_num)) {
                    $value_card = \App\ValueCard::find($promo_card);
                    if ($value_card) {
                        $value_card_availed = new \App\ValueCardAvailed();
                        $value_card_availed->value_card_id = $value_card->id;
                        $value_card_availed->vehicle_num = $customer_vehicle_info->num_plate;
                        $value_card_availed->save();
                    }
                }
                try {
                    $Key = $this->key;
                    $http = new Client();
                    $response = $http->post($this->url . '/api/store-booking-info', [
                        'form_params' => [
                            'token' => $Key,
                            'data' => $dataArray
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);

                    if (array_key_exists('booking_info_live_id', $responseData['data'])) {
                        $bookings = \App\Bookings::find($bookingId);
                        if ($bookings) {
                            $bookings->live_id = $responseData['data']['booking_info_live_id'];
                            $bookings->save();
                        }
                    }
                    if (array_key_exists('booking_payment_live_id', $responseData['data'])) {
                        $booking_payment = \App\BookingPayments::find($bookingPaymentId);
                        if ($booking_payment) {
                            $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                            $booking_payment->save();
                        }
                    }
                } catch (\Exception $ex) {
                    
                }
            }

            $message = $this->ticket_reader->get_error_message('successfull_vehicle_payment', '', $language_id);
            $message = str_replace('{{time}}', date('d/m/Y H:i:59', strtotime($checkout_time)), $message);

            $transaction_details = $this->make_transaction_entry($booking, $id, $price, $status, $transaction_reference, $transaction_id, $xml_string, $discount, $amount);
            return array(
                'status' => 1,
                'access_status' => 'success',
                'message' => $message,
                'data' => array(
                    'transaction_id' => $transaction_details
                ),
            );
        } catch (\Exception $ex) {

            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('get_vehicle_ticket_price_status', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            $message = $this->ticket_reader->get_error_message('unknown', '', $language_id);
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    public function get_booking_details($booking_id) {
        try {
            $booking_details = \App\Bookings::find($booking_id);
            if (!$booking_details) {
                return FALSE;
            }
            return $booking_details;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function get_person_ticket_price(Request $request, $key, $id) {
        try {
            $valid_settings = $this->settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $product = $this->get_person_ticket_product();
            if (!$product) {
                $message = 'Person Ticket info not found';
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => $product,
                );
            }
            return array(
                'status' => 1,
                'access_status' => 'success',
                'message' => 'success',
                'data' => $product,
            );
        } catch (\Exception $ex) {

            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('person-ticket-price', $ex->getMessage(), $ex->getTraceAsString());
            $message = $this->ticket_reader->get_error_message('unknown');
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    public function get_person_ticket_price_status(Request $request, $key, $id, $quantity, $status, $transaction_reference, $transaction_id = NULL, $language_id = 2) {
        try {
            $valid_settings = $this->settings->is_valid_call($key, $id);
            $xml_string = NULL;
            if (isset($request->xml_string) && !empty($request->xml_string)) {
                $xml_string = $request->xml_string;
            }
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown', '', $language_id);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            if (!is_numeric($quantity)) {
                $message = 'Invalid Quantity';
//                $message = $this->ticket_reader->get_error_message('unknown');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            if ($status != 1) {
                if ($status == 0 || $status == 2) {
                    $transaction_details = $this->make_person_transaction_entry($id, $status, $quantity, $transaction_reference, $transaction_id, $xml_string);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => 'not updated',
                        'data' => array(
                            'transaction_id' => $transaction_id
                        ),
                    );
                }


                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => 'not updated',
                    'data' => FALSE
                );
            }
            $booking_ids = "";
            for ($i = 0; $i < $quantity; $i++) {
                $booking = $this->add_person_booking($id);
                if (isset($booking->id)) {
                    $currentId = sprintf("%13d", $booking->id);
                    $booking_ids .= $booking_ids == "" ? $currentId : "," . $currentId;
                }
            }
            $transaction_details = $this->make_person_transaction_entry($id, $status, $quantity, $transaction_reference, $transaction_id, $xml_string);
            $message = $this->ticket_reader->get_error_message('successfull_person_payment', '', 2);
            return array(
                'status' => 1,
                'access_status' => 'success',
                'message' => $message,
                'booking_ids' => $booking_ids,
                'data' => array(
                    'transaction_id' => $transaction_details
                ),
            );
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('person-ticket-price-status', $ex->getMessage(), $ex->getTraceAsString());
            $message = $this->ticket_reader->get_error_message('unknown', '', $language_id);
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    public function get_person_ticket_price_transaction(Request $request, $key, $id, $quantity) {
        try {
            $valid_settings = $this->settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            if (!is_numeric($quantity)) {
                $message = 'Invalid Quantity';
//                $message = $this->ticket_reader->get_error_message('unknown');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            $transaction_details = $this->make_person_transaction_entry($id, NULL, $quantity, NULL, NULL);
            return array(
                'status' => 1,
                'access_status' => 'success',
                'message' => 'success',
                'data' => array(
                    'transaction_id' => $transaction_details
                ),
            );
        } catch (\Exception $ex) {

            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('person-ticket-price-status', $ex->getMessage(), $ex->getTraceAsString());
            $message = $this->ticket_reader->get_error_message('unknown');
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    public function add_person_booking($device_id) {
        try {
            $amount = 0;
            $product = $this->get_person_ticket_product();
            if ($product) {
                $amount = $product->price;
            }
            $dataArray = array(
                'first_name' => 'Paid Person',
                'type' => 6,
                'is_paid' => 1,
                'checkin_time' => date('Y-m-d H:i:s'),
                'checkout_time' => date('Y-m-d 23:59:59'),
                'amount' => $amount,
                'payment_id' => 'Paid Person'
            );
            $booking = new \App\Bookings();
            $booking->type = $dataArray['type'];
            $booking->first_name = $dataArray['first_name'];
            $booking->checkin_time = $dataArray['checkin_time'];
            $booking->checkout_time = $dataArray['checkout_time'];
            $booking->is_local_updated = 1;
            $booking->is_live_updated = 0;
            $booking->save();
            $bookingId = $booking->id;
            $booking_payment = new \App\BookingPayments();
            $booking_payment->booking_id = $booking->id;
            $booking_payment->amount = $dataArray['amount'];
            $booking_payment->payment_id = $dataArray['payment_id'];
            $booking_payment->checkin_time = $dataArray['checkin_time'];
            $booking_payment->checkout_time = $dataArray['checkout_time'];
            $booking_payment->save();
            $bookingPaymentId = $booking_payment->id;
//            if (!$this->key) {
//                $error_log = new \App\Http\Controllers\LogController();
//                $error_log->log_create('import-key', 'custom: Import key not found');
//                return FALSE;
//            }
//            $Key = $this->key;
//            $http = new Client();
//            $response = $http->post($this->url . '/api/store-booking-info', [
//                'form_params' => [
//                    'token' => $Key,
//                    'data' => $dataArray
//                ],
//            ]);
//            $responseData = json_decode((string) $response->getBody(), true);
//            if (array_key_exists('booking_info_live_id', $responseData['data'])) {
//                $booking = \App\Bookings::find($bookingId);
//                if ($booking) {
//                    $booking->live_id = $responseData['data']['booking_info_live_id'];
//                    $booking->save();
//                }
//            }
//            if (array_key_exists('booking_payment_live_id', $responseData['data'])) {
//                $booking_payment = \App\BookingPayments::find($bookingPaymentId);
//                if ($booking_payment) {
//                    $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
//                    $booking_payment->save();
//                }
//            }
//            $this->verfify_vehicle->set_booking_entry($booking, $device_id);
            return $booking;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('person-ticket-price-status-booking-entry', $ex->getMessage(), $ex->getTraceAsString());
            return false;
        }
    }

    public function make_transaction_entry($booking, $device_id, $price, $status, $transaction_reference, $transaction_id, $xml_string, $discount = NULL, $amount = NULL) {
        $transaction_details = \App\TransactionPaymentVehicles::find($transaction_id);
        if (!$transaction_details) {
            $transaction_details = new \App\TransactionPaymentVehicles();
        }
        $transaction_details->device_id = $device_id;
        $transaction_details->booking_id = $booking;
        $transaction_details->status = $status;
        $transaction_details->price = $price;
        if (!empty($discount)) {
            $transaction_details->discount = $discount;
        }
        if (!empty($amount)) {
            $transaction_details->amount = $amount;
        } else {
            if (!empty($discount)) {
                $transaction_details->amount = $price - $discount;
            } else {
                $transaction_details->amount = $price;
            }
        }
        $transaction_details->transaction = $transaction_reference;
        $get_attendant_transaction = $this->get_attendant_transaction($booking);
        if ($get_attendant_transaction) {
            $transaction_details->attendant_transaction_id = $get_attendant_transaction;
        }
        if ($xml_string != NULL) {
            $transaction_details->e_general = $xml_string;
        }
        $transaction_details->save();
        return $transaction_details->id;
    }

    public function get_attendant_transaction($booking) {
        $attendant = \App\Attendants::where('booking_id', $booking)->first();
        if ($attendant) {
            $attendant_transaction = \App\AttendantTransactions::where('attendant_id', $attendant->id)->orderBy('created_at', 'DESC')->first();
            if ($attendant_transaction) {
                return $attendant_transaction->id;
            }
        }
        return FALSE;
    }

    public function make_person_transaction_entry($device_id, $status, $quantity, $transaction_reference, $transaction_id, $xml_string = NULL) {
        try {
            $transaction_details = new \App\TransactionPaymentPersons;
            if ($transaction_id != NULL) {
                $transaction_details = \App\TransactionPaymentPersons::find($transaction_id);
            }
            $transaction_details->device_id = $device_id;
            $transaction_details->quantity = $quantity;
            $transaction_details->status = $status;
//            $transaction_details->amount = $price;
            $transaction_details->transaction = $transaction_reference;
            if ($xml_string != NULL) {
                $transaction_details->e_general = $xml_string;
            }
            $transaction_details->save();
            return $transaction_details->id;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('person-ticket-price-status-booking-entry', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function search_on_plate(Request $request, $key, $id, $vehicle, $language_id = 2) {
        try {
            $valid_settings = $this->settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->ticket_reader->get_error_message('unknown');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $response_data = array();
            if (empty($vehicle) || strlen($vehicle) > 7) {
                return array(
                    'status' => 1,
                    'message' => $message,
                    'data' => $response_data
                );
            }
            $language = \App\Language::find($language_id);
            if ($language) {
                \App::setLocale($language->code);
            } else {
                \App::setLocale('nl');
            }
            $product = \App\Products::where('type', 'day_ticket')
                    ->first();
            if ($product) {
                $this->price_per_day_product = $product->price;
            }
            $checked_in_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                        $query->whereNull('check_out');
                    })
                    ->where('vehicle_num', 'like', '%' . $vehicle . '%')
                    ->where('type', 4)
                    ->where(function($query) {
                        $query->where('checkout_time', '<', date('Y-m-d H:i:s'));
                        $query->orWhereNull('checkout_time');
                    })
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
            foreach ($checked_in_bookings as $index => $booking) {
                $response_data[$index]['vehicle_num'] = $booking->vehicle_num;
                $response_data[$index]['image_path'] = $booking->image_path;
                $attendant_transactions = $booking->attendant_transactions()
                        ->whereNull('check_out')
                        ->orderBy('created_at', 'desc')
                        ->first();
                if ($attendant_transactions) {
                    $response_data[$index]['check_in'] = date('d/m/Y H:i', strtotime($attendant_transactions->check_in));
                }
                $device_booking = \App\DeviceBookings::withTrashed()
                        ->where('file_path', $booking->image_path)
                        ->where('confidence', $booking->confidence)
                        ->where('country_code', $booking->country_code)
                        ->orderBy('created_at', 'desc')
                        ->first();
                if ($device_booking) {
                    $response_data[$index]['device_id'] = $device_booking->device_id;
                    $response_data[$index]['device_name'] = $device_booking->location_devices->device_name;
                    $transaction_image = \App\TransactionImages::where('transaction_id', $attendant_transactions->id)
                            ->where('device_id', $device_booking->device_id)
                            ->where('type', 'in')
                            ->orderBy('created_at', 'desc')
                            ->first();
                    if ($transaction_image) {
                        $response_data[$index]['image_path'] = $transaction_image->image_path;
                    }
                }
                $at_location_minutes = $this->get_at_location_time_minutes($booking);
                $response_data[$index]['booking'] = $booking->id;
                $response_data[$index]['at_location_minutes'] = 0;
                $response_data[$index]['per_day_price'] = $this->price_per_day_product;
                $response_data[$index]['total_day_price'] = 0;
                $response_data[$index]['per_hour_price'] = $this->price_per_hour;
                $response_data[$index]['total_hourly_price'] = 0;
                $response_data[$index]['eligible_type'] = 'hourly';
				$response_data[$index]['checkout'] = '';
                if ($at_location_minutes) {
                    $booking_price = $this->get_booking_price($booking);
                    if ($booking_price['status']) {
                        $get_minutes = intdiv($at_location_minutes, 60);
                            $at_location_time = ' : ' .  intdiv($get_minutes, 24) . ' ' . __('booking.days') . ' ' . number_format(fmod($get_minutes, 24)) . ' ' . __('booking.hours') . ' ' . fmod($at_location_minutes, 60) . ' ' . __('booking.minutes');
                        if ($get_minutes >= 24) {
                        } else {
                            $at_location_time = ' : '. number_format(intdiv($at_location_minutes, 60)) . ' '. __('booking.hours') .' '. number_format(fmod($at_location_minutes, 60)) . ' '.__('booking.minutes');
                        }
                        $response_data[$index]['at_location_minutes'] = $at_location_time;
                        $response_data[$index]['total_day_price'] = $booking_price['day'];
                        $response_data[$index]['total_hourly_price'] = $booking_price['hourly'];
                        $response_data[$index]['eligible_type'] = 'hourly';
                        if ($at_location_minutes < $this->time_limit) {
                            $response_data[$index]['eligible_type'] = 'day';
                        }
                    }
                }
            }
            $message = 'Success';
            return array(
                'status' => 1,
                'message' => $message,
                'data' => $response_data
            );
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('search_on_plate', $ex->getMessage(), $ex->getTraceAsString());
            $message = $this->ticket_reader->get_error_message('unknown', '', $language_id);
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

}
