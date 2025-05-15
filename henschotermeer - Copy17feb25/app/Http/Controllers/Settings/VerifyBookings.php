<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\PlateReaderController\VerifyVehicle;

class VerifyBookings extends Controller {

    public $device_id = FALSE;

    public function __construct() {
        
    }

    public function verify_booking(Request $request, $key, $id, $type, $val, $ticket_type = FALSE) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                $message = $this->get_error_message('unknown');
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
            $this->device_id = $id;

            $booking_details = FALSE;
            if ($type == 'booking') {
                $booking_details = $this->validate_by_booking_id($val);
            } elseif ($type == 'email') {
                $booking_details = $this->validate_by_email($val);
            } elseif ($type == 'vehicle') {
                $booking_details = $this->validate_by_vehicle($val);
            }

            if (!$booking_details) {

                $check_system_live_status = $settings->check_system_live_status();
                if (!$check_system_live_status) {
                    $message = $this->get_error_message('unauthorized');
                    return array(
                        'status' => 1,
                        'access_status' => 'unsure',
                        'od_sent' => FALSE,
                        'message' => $message,
                        'data' => FALSE,
                    );
                }

                $message = $this->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            if ($valid_settings->available_device_id == 1) {
                if ($booking_details->type == 6) {
                    $message = $this->get_error_message('unauthorized');
                    return array(
                        'status' => 1,
                        'access_status' => 'error',
                        'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
            } elseif ($valid_settings->available_device_id == 2) {
                if ($booking_details->type != 6) {
                    $message = $this->get_error_message('unauthorized');
                    return array(
                        'status' => 1,
                        'access_status' => 'error',
                        'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
            } else {
                $message = $this->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            $user_name = $this->get_user_name($booking_details);

            if ($valid_settings->device_direction == 'in') {
                if ($valid_settings->anti_passback) {
                    if (empty($valid_settings->time_passback) || $valid_settings->time_passback == 0) {
                        $is_booking_at_location = $settings->is_booking_at_location($booking_details->id);
                        if ($is_booking_at_location) {
                            \Illuminate\Support\Facades\Session::put('error_message', 'Booking is already at location');
                            $message = $this->get_error_message('already_at_location', $user_name);
                            return array(
                                'status' => 1,
                                'access_status' => 'denied',
                                'message' => $message,
                                'od_sent' => FALSE,
                                'data' => $booking_details,
                            );
                        }
                    } else {
                        $valid_timepassback = $this->is_valid_timepassback($booking_details, $valid_settings);
                        if (!$valid_timepassback['status']) {
                            $message = $this->get_error_message($valid_timepassback['message_code'], $user_name);
                            return array(
                                'status' => 1,
                                'access_status' => 'denied',
                                'od_sent' => FALSE,
                                'message' => $message,
                                'data' => FALSE,
                            );
                        }
                    }
                }
            }

            if (isset($request->vehicle_image) && $request->hasFile('vehicle_image')) {
                $file = $request->file('vehicle_image');
                $extension = $file->extension() ?: 'png';
                $destinationPath = public_path('/uploads/vehicles');
                $safeName = str_random(10) . '.' . $extension;
                $file->move($destinationPath, $safeName);
                $request['pic'] = $safeName;
                $booking = \App\Bookings::find($booking_details->id);
                if ($booking) {
                    $booking->image_path = $destinationPath . '/' . $safeName;
                    $booking->save();
                }
            }

            $is_parking_open = $this->is_parking_open();
            if (!$is_parking_open) {
                if ($booking_details->type == 1 || $booking_details->type == 2) {
                    $message = $this->get_error_message('parking_close_whitelist', $user_name);
                } else {
                    $message = $this->get_error_message('parking_close', $user_name);
                }

                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            $is_vehicle_blocked = $this->is_vehicle_blocked($booking_details);
            if ($is_vehicle_blocked) {
                $message = $this->get_error_message('user_blocked', $user_name);
                return array(
                    'status' => 1,
                    'access_status' => 'denied',
                    'od_sent' => $settings->send_message_od($id, $message, 'blocked'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }

            if (!$ticket_type || $ticket_type == 'person_ticket' || $booking_details->type == 6) {
                if ($valid_settings->available_device_id != 2) {

                    $message = $this->get_error_message('unauthorized');
                    return array(
                        'status' => 1,
                        'access_status' => 'error',
                        'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                        'message' => $message,
                        'data' => FALSE,
                    );
                }
                $timings = $this->valid_timings($booking_details);
                if (!$timings['status']) {
                    $message = $this->get_error_message($timings['message_code'], $user_name);
                    return array(
                        'status' => 1,
                        'access_status' => 'denied',
                        'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                        'message' => $message,
                        'data' => FALSE,
                    );
                }

                $message = $this->get_error_message('welcome_entrance', $user_name);
                if ($valid_settings->is_opened == 1) {
                    $valid_settings->is_opened = 0;
                    $valid_settings->save();
                }
                return array(
                    'status' => 1,
                    'access_status' => 'allow',
                    'od_sent' => $settings->send_message_od($id, $message, 'normal'),
                    'message' => $message,
                    'data' => $booking_details,
                );
            }

            if (!$ticket_type || $ticket_type == 'userlist') {
                if ($booking_details->type == 3) {
                    $userlist_user = $this->is_userlist_user($booking_details);
                    if ($userlist_user['status'] == 2) {
                        if ($userlist_user['data']->group_id != NULL) {
                            $is_valid_group_device = $this->is_valid_group_device($userlist_user['data']->group_id);
                            if (!$is_valid_group_device) {
                                \Illuminate\Support\Facades\Session::put('error_message', 'Devcie access denied for user group.');
                                $message = $this->get_error_message('group_device_access_denied', $user_name, $userlist_user['data']->language_id);
                                return array(
                                    'status' => 1,
                                    'access_status' => 'denied',
                                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                                    'message' => $message,
                                    'data' => FALSE,
                                );
                            }
                        }
                        \Illuminate\Support\Facades\Session::put('error_message', 'User is blocked');
                        $message = $this->get_error_message('user_blocked', $user_name, $userlist_user['data']->language_id);
                        return array(
                            'status' => 1,
                            'access_status' => 'denied',
                            'od_sent' => $settings->send_message_od($id, $message, 'blocked'),
                            'message' => $message,
                            'data' => FALSE,
                        );
                    } elseif ($userlist_user['status'] == 1) {
                        if ($userlist_user['data']->group_id != NULL) {
                            $is_valid_group_device = $this->is_valid_group_device($userlist_user['data']->group_id);
                            if (!$is_valid_group_device) {
                                \Illuminate\Support\Facades\Session::put('error_message', 'Devcie access denied for user group.');
                                $message = $this->get_error_message('group_device_access_denied', $user_name, $userlist_user['data']->language_id);
                                return array(
                                    'status' => 1,
                                    'access_status' => 'denied',
                                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                                    'message' => $message,
                                    'data' => FALSE,
                                );
                            }
                        }
                        $whitelist_timings = $this->valid_whitelist_timings($booking_details);
                        if ($valid_settings->available_device_id == 2) {
                            $whitelist_timings = $this->valid_person_timings($booking_details);
                        }
                        if (!$whitelist_timings['status']) {
                            $message = $this->get_error_message($whitelist_timings['message_code'], $user_name, $userlist_user['data']->language_id);
                            return array(
                                'status' => 1,
                                'access_status' => 'denied',
                                'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                                'message' => $message,
                                'data' => FALSE,
                            );
                        }
                        if ($valid_settings->is_opened == 1) {
                            $valid_settings->is_opened = 0;
                            $valid_settings->save();
                        }
                        $message = $this->get_error_message('welcome_entrance', $user_name, $userlist_user['data']->language_id);
                        return array(
                            'status' => 1,
                            'access_status' => 'allow',
                            'od_sent' => $settings->send_message_od($id, $message, 'normal'),
                            'message' => $message,
                            'data' => $booking_details,
                        );
                    }
                    \Illuminate\Support\Facades\Session::put('error_message', 'Userlist not found');
                    if ($ticket_type == 'userlist') {
                        $check_system_live_status = $settings->check_system_live_status();
                        if (!$check_system_live_status) {
                            $message = $this->get_error_message('unauthorized_whitelist');
                            return array(
                                'status' => 1,
                                'access_status' => 'unsure',
                                'od_sent' => FALSE,
                                'message' => $message,
                                'data' => $booking_details,
                            );
                        }

                        $message = $this->get_error_message('unauthorized_whitelist');
                        return array(
                            'status' => 1,
                            'access_status' => 'denied',
                            'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                            'message' => $message,
                            'data' => $booking_details,
                        );
                    }
                }
            }

            if (!$ticket_type || $ticket_type == 'whitelist') {
                if ($booking_details->type == 2) {
                    $whitelist_user = $this->is_whitelist_user($booking_details);
                    if ($whitelist_user) {
                        $whitelist_timings = $this->valid_whitelist_timings($booking_details);
                        if ($valid_settings->available_device_id == 2) {
                            $whitelist_timings = $this->valid_person_timings($booking_details);
                        }
                        if (!$whitelist_timings['status']) {
                            $message = $this->get_error_message($whitelist_timings['message_code'], $user_name);
                            return array(
                                'status' => 1,
                                'access_status' => 'denied',
                                'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                                'message' => $message,
                                'data' => FALSE,
                            );
                        }
                        if ($valid_settings->is_opened == 1) {
                            $valid_settings->is_opened = 0;
                            $valid_settings->save();
                        }
                        $message = $this->get_error_message('welcome_entrance', $user_name);
                        return array(
                            'status' => 1,
                            'access_status' => 'allow',
                            'od_sent' => $settings->send_message_od($id, $message, 'normal'),
                            'message' => $message,
                            'data' => $booking_details,
                        );
                    }
                    if (\Illuminate\Support\Facades\Session::has('error_group_message')) {
                        $message = $this->get_error_message('group_device_access_denied');
                        return array(
                            'status' => 1,
                            'access_status' => 'denied',
                            'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                            'message' => $message,
                            'data' => $booking_details,
                        );
                    }
                    if ($ticket_type == 'whitelist') {
                        $check_system_live_status = $settings->check_system_live_status();
                        if (!$check_system_live_status) {
                            $message = $this->get_error_message('unauthorized_whitelist');
                            return array(
                                'status' => 1,
                                'access_status' => 'unsure',
                                'od_sent' => FALSE,
                                'message' => $message,
                                'data' => $booking_details,
                            );
                        }
                        $message = $this->get_error_message('unauthorized_whitelist');
                        return array(
                            'status' => 1,
                            'access_status' => 'denied',
                            'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                            'message' => $message,
                            'data' => $booking_details,
                        );
                    }
                }
            }
            if (!$ticket_type || $ticket_type == 'parking_ticket_owner' || $ticket_type == 'reservation') {
                if ($booking_details->type == 1 || $booking_details->type == 4 || $booking_details->type == 5 || $booking_details->type == 7) {
                    $timings = $this->valid_timings($booking_details);
                    if (!$timings['status']) {
                        $message = $this->get_error_message($timings['message_code'], $user_name);
                        return array(
                            'status' => 1,
                            'access_status' => 'denied',
                            'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                            'message' => $message,
                            'data' => FALSE,
                        );
                    }
                    $message = $this->get_error_message('welcome_entrance', $user_name);
                    if ($valid_settings->is_opened == 1) {
                        $valid_settings->is_opened = 0;
                        $valid_settings->save();
                    }
                    return array(
                        'status' => 1,
                        'access_status' => 'allow',
                        'od_sent' => $settings->send_message_od($id, $message, 'normal'),
                        'message' => $message,
                        'data' => $booking_details,
                    );
                }
            }
        } catch (\Exception $ex) {

            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-admin', $ex->getMessage(), $ex->getTraceAsString());
            $settings = new Settings();
            $message = $this->get_error_message('unknown');
            return array(
                'status' => 0,
                'access_status' => 'denied',
                'od_sent' => FALSE,
                'message' => $message,
                'data' => FALSE,
            );
        }
    }

    public function validate_by_booking_id($booking_id) {
        try {
            $booking_details = \App\Bookings::where('live_id', $booking_id)->first();
            if (!$booking_details) {
                \Illuminate\Support\Facades\Session::put('error_message', 'Invalid booking id');
                return FALSE;
            }
            return $booking_details;
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Session::put('error_message', $ex->getMessage());
            return FALSE;
        }
    }

    public function validate_by_email($email) {
        try {
            $booking_details = \App\Bookings::where('email', $email)->orderBy('created_at', 'desc')->first();
            if (!$booking_details) {
                \Illuminate\Support\Facades\Session::put('error_message', 'Invalid booking email');
                return FALSE;
            }
            return $booking_details;
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Session::put('error_message', $ex->getMessage());
            return FALSE;
        }
    }

    public function validate_by_vehicle($vehicle_num) {
        try {
            $booking_details = \App\Bookings::where('vehicle_num', $vehicle_num)->orderBy('created_at', 'DESC')->first();
            if (!$booking_details) {
                \Illuminate\Support\Facades\Session::put('error_message', 'Invalid booking Vehicle');
                return FALSE;
            }
            return $booking_details;
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Session::put('error_message', $ex->getMessage());
            return FALSE;
        }
    }

    public function get_error_message($key = NULL, $name = '', $lang_id = FALSE)
    {
        $message = 'Something went wrong';
        if ($key != NULL) {
            $message_type = \App\MessageType::where('key', $key)->first();
            if ($message_type) {
                $type_id = $message_type->id;
                $location_settings = new LocationSettings();
                $details = $location_settings->get_location();
                if ($details) {
                    if (!$lang_id) {
                        $lang = $details->language_id;
                    } else {
                        $lang = $lang_id;
                    }
                    $message_string = \App\Messages::where([
                        ['message_type_id', $type_id],
                        ['language_id', $lang],
                    ])->first();
                    if ($name == "paid_string") {
                        $message_type_name = \App\MessageType::where('key', $name)->first();
                        $message_name = \App\Messages::where([
                            ['message_type_id', $message_type_name->id],
                            ['language_id', $lang],
                        ])->first();
                        $name = $message_name->message;
                    }
                    if ($message_string) {
                        if (!empty($message_string->message)) {

                            return str_replace('{0}', $name, $message_string->message);
                        }
                    }
                    $message_string_en = \App\Messages::where([
                        ['message_type_id', $type_id],
                        ['language_id', 1],
                    ])->first();
                    if ($message_string_en) {
                        if (!empty($message_string_en->message)) {
                            return str_replace('{0}', $name, $message_string_en->message);
                        }
                    }
                }
            }
        }
        if (\Illuminate\Support\Facades\Session::has('error_message')) {
            return \Illuminate\Support\Facades\Session::get('error_message');
        }
        return $message;
    }
    public function getMessage($key = NULL, $name = '', $lang_id = FALSE)
    {
        $message = 'Something went wrong';
        if ($key != NULL) {
            $message_type = \App\MessageType::where('key', $key)->first();
            if ($message_type) {
                $type_id = $message_type->id;
                $location_settings = new LocationSettings();
                $details = $location_settings->get_location();
                if ($details) {
                    if (!$lang_id) {
                        $lang = $details->language_id;
                    } else {
                        $lang = $lang_id;
                    }
                    $message_string = \App\Messages::where([
                                ['message_type_id', $type_id],
                                ['language_id', $lang],
                            ])->first();
                    if ($name == "paid_string") {
                        $message_type_name = \App\MessageType::where('key', $name)->first();
                        $message_name = \App\Messages::where([
                                    ['message_type_id', $message_type_name->id],
                                    ['language_id', $lang],
                                ])->first();
                        $name = $message_name->message;
                    }
                    if ($message_string) {
                        if (!empty($message_string->message)) {

                            return str_replace('{0}', $name, $message_string->message);
                        }
                    }
                    $message_string_en = \App\Messages::where([
                                ['message_type_id', $type_id],
                                ['language_id', 1],
                            ])->first();
                    if ($message_string_en) {
                        if (!empty($message_string_en->message)) {
                            return str_replace('{0}', $name, $message_string_en->message);
                        }
                    }
                }
            }
        }
        if (\Illuminate\Support\Facades\Session::has('error_message')) {
            return \Illuminate\Support\Facades\Session::get('error_message');
        }
        return $message;
    }

    public function is_whitelist_user($booking) {
        $email = $booking->email;
        if (empty($email)) {
            return FALSE;
        }
        $whitelist_users = \App\WhitelistUsers::where('email', $email)->first();
        if ($whitelist_users) {
            if ($whitelist_users->group_id == NULL) {
                return TRUE;
            }
            $is_valid_group_device = $this->is_valid_group_device($whitelist_users->group_id);
            if ($is_valid_group_device) {
                return TRUE;
            }
            \Illuminate\Support\Facades\Session::put('error_group_message', 'group_device_access_denied');
            return FALSE;
        }
        $customer_id = $booking->customer_id;
        if (empty($customer_id)) {
            return FALSE;
        }
        $whitelist_users = \App\WhitelistUsers::where('customer_id', $customer_id)->first();
        if ($whitelist_users) {
            if ($whitelist_users->group_id == NULL) {
                return TRUE;
            }
            $is_valid_group_device = $this->is_valid_group_device($whitelist_users->group_id);
            if ($is_valid_group_device) {
                return TRUE;
            }
            \Illuminate\Support\Facades\Session::put('error_group_message', 'group_device_access_denied');
            return FALSE;
        }
        return FALSE;
    }

    public function valid_whitelist_timings($booking = NULL) {
        $whitelist_timings = $this->get_today_whitelist_timings();
        \Illuminate\Support\Facades\Session::put('error_message', 'Non Valid whitelist timings');
        if (!$whitelist_timings) {
            return array(
                'status' => FALSE,
                'message_code' => 'unauthorized_whitelist',
            );
        }
        if (!empty($whitelist_timings->opening_time) && !empty($whitelist_timings->closing_time)) {
            $now = date('H:i');
            $start = date('H:i', strtotime($whitelist_timings->opening_time));
            $end = date('H:i', strtotime($whitelist_timings->closing_time));
            if (($now >= $start) && ($now <= $end )) {
                return array(
                    'status' => TRUE,
                    'message_code' => '',
                );
            }
        }
        if ($now < $start) {
            return array(
                'status' => FALSE,
                'message_code' => 'too_early_whitelist',
            );
        }
        return array(
            'status' => FALSE,
            'message_code' => 'unauthorized_whitelist',
        );
    }

    public function valid_person_timings($booking = NULL) {
        $person_timings = $this->get_today_person_timings();
        \Illuminate\Support\Facades\Session::put('error_message', 'Non Valid person timings');
        if (!$person_timings) {
            return array(
                'status' => FALSE,
                'message_code' => 'unauthorized_whitelist',
            );
        }
        if (!empty($person_timings->opening_time) && !empty($person_timings->closing_time)) {
            $now = date('H:i');
            $start = date('H:i', strtotime($person_timings->opening_time));
            $end = date('H:i', strtotime($person_timings->closing_time));
            if ($now >= $start && $now <= $end) {
                return array(
                    'status' => TRUE,
                    'message_code' => '',
                );
            }
            if ($now < $start) {
                return array(
                    'status' => FALSE,
                    'message_code' => 'too_early',
                );
            }
            if ($now > $end) {
                return array(
                    'status' => FALSE,
                    'message_code' => 'too_late',
                );
            }
        }
        return array(
            'status' => FALSE,
            'message_code' => 'unauthorized_whitelist',
        );
    }

    public function get_today_whitelist_timings() {
        $day_num = date('w');
        $location_timings = \App\LocationTimings::where([
                    ['is_whitelist', 1],
                    ['is_person', 0],
                    ['week_day_num', $day_num],
                ])->first();
        if ($location_timings) {
            return $location_timings;
        }
        return FALSE;
    }

    public function get_today_person_timings() {
        $day_num = date('w');
        $location_timings = \App\LocationTimings::where([
                    ['is_whitelist', 0],
                    ['is_person', 1],
                    ['week_day_num', $day_num],
                ])->first();
        if ($location_timings) {
            return $location_timings;
        }
        return FALSE;
    }

    public function is_userlist_user($booking) {
        $email = $booking->email;
        if (empty($email)) {
            return array(
                'data' => '',
                'status' => 0,
            );
        }
        $userlist_users = \App\UserlistUsers::where('email', $email)->first();

        if ($userlist_users) {
            if ($userlist_users->is_blocked) {
                \Illuminate\Support\Facades\Session::put('error_message', 'User is Blocked');
                return array(
                    'data' => $userlist_users,
                    'status' => 2,
                );
            }
            return array(
                'data' => $userlist_users,
                'status' => 1,
            );
        }
        $customer_id = $booking->customer_id;
        if (empty($customer_id)) {
            return array(
                'data' => '',
                'status' => 0,
            );
        }
        $userlist_users = \App\UserlistUsers::where('customer_id', $customer_id)->first();
        if ($userlist_users) {
            if ($userlist_users->is_blocked) {
                \Illuminate\Support\Facades\Session::put('error_message', 'User is Blocked');
                return array(
                    'data' => $userlist_users,
                    'status' => 2,
                );
            }
            return array(
                'data' => $userlist_users,
                'status' => 1,
            );
        }
        return array(
            'data' => '',
            'status' => 0,
        );
    }

    public function is_vehicle_blocked($booking) {

        \Illuminate\Support\Facades\Session::put('error_message', 'Vehicle is blocked');
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
                        if ($vehicle_info->num_plate == $booking->vehicle_num) {
                            $vehicle_blocked = TRUE;
                        }
                    }
                }
            }
        }
        if ($vehicle_blocked) {
            \Illuminate\Support\Facades\Session::put('error_message', 'Vehicle is Blocked');
        }
        return $vehicle_blocked;
    }

    public function valid_timings($booking, $is_whitelist = FALSE) {
        \Illuminate\Support\Facades\Session::put('error_message', 'Non Valid timings');

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
                'message_code' => 'too_early',
            );
        }
        if ($now > $end) {
            return array(
                'status' => FALSE,
                'message_code' => 'ticket_used',
            );
        }
        return array(
            'status' => FALSE,
            'message_code' => 'unauthorized',
        );
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
        return ($user == 'Paid Vehicle' || $user == 'Paid Person') ? 'paid_string' : $user;
    }

    public function is_parking_open() {

        \Illuminate\Support\Facades\Session::put('error_message', 'Parking is closed');
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
        \Illuminate\Support\Facades\Session::put('error_message', 'Parking is closed');
        return FALSE;
    }

    public function verify_booking_status(Request $request, $key, $id, $booking_id, $status) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'message' => 'Invalid Access',
                    'data' => FALSE,
                );
            }
            $booking_details = $this->get_booking_details($booking_id);
            if (!$booking_details) {
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'message' => 'Invalid',
                    'data' => FALSE,
                );
            }
            $user_name = $this->get_user_name($booking_details);
            if ($status == 1) {
                if ($valid_settings->device_direction == 'in') {
                    $attendant = \App\Attendants::where('booking_id', $booking_details->id)->first();
                    if (!$attendant) {
                        $attendant = new \App\Attendants();
                        $attendant->booking_id = $booking_details->id;
                        $attendant->save();
                    }
                    $attendant_id = $attendant->id;
                    $attendant_transaction = \App\AttendantTransactions::where([
                                ['attendant_id', $attendant_id],
                            ])
                            ->whereNotNull('check_in')
                            ->whereNull('check_out')
                            ->orderBy('created_at', 'desc')
                            ->first();
                    if ($attendant_transaction) {
                        $attendant_transaction->check_out = date('Y-m-d H:i:s');
                        $attendant_transaction->save();                        
                    }
                    $attendant_transaction = new \App\AttendantTransactions();
                    $attendant_transaction->attendant_id = $attendant_id;
                    $attendant_transaction->check_in = date('Y-m-d H:i:s');
                    $attendant_transaction->save();
                    $transaction_images = new \App\TransactionImages();
                    if (!empty($booking_details->image_path)) {
                        $transaction_images->image_path = $booking_details->image_path;
                        $booking = \App\Bookings::find($booking_details->id);
                        if ($booking) {
                            $booking->image_path = NULL;
                            $booking->save();
                        }
                    }
                    $transaction_images->transaction_id = $attendant_transaction->id;
                    $transaction_images->device_id = $id;
                    $transaction_images->type = 'in';
                    $transaction_images->save();
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => 'Success',
                        'data' => $attendant_transaction,
                    );
                } elseif ($valid_settings->device_direction == 'out') {
                    if (empty($booking_details->checkout_time)) {
                        $booking_details->checkout_time = date('Y-m-d H:i:s');
                        $booking_details->save();
                    }
                    $attendant = \App\Attendants::where('booking_id', $booking_details->id)->first();
                    if (!$attendant) {
                        return array(
                            'status' => 1,
                            'access_status' => 'error',
                            'message' => 'Entry Details Not Found',
                            'data' => $attendant,
                        );
                    }

                    $attendant_id = $attendant->id;
                    $attendant_transaction = \App\AttendantTransactions::where([
                                ['attendant_id', $attendant_id],
                            ])
                            ->whereNotNull('check_in')
                            ->whereNull('check_out')
                            ->orderBy('created_at', 'desc')
                            ->first();

                    if ($attendant_transaction) {
                        $attendant_transaction->check_out = date('Y-m-d H:i:s');
                        $attendant_transaction->save();
                    } else {
                        return array(
                            'status' => 1,
                            'access_status' => 'error',
                            'message' => 'Entry Details Not Found',
                            'data' => $attendant,
                        );
                    }
                    $transaction_images = new \App\TransactionImages();
                    if (!empty($booking_details->image_path)) {
                        $transaction_images->image_path = $booking_details->image_path;
                        $booking = \App\Bookings::find($booking_details->id);
                        if ($booking) {
                            $booking->image_path = NULL;
                            $booking->save();
                        }
                    }
                    $transaction_images->transaction_id = $attendant_transaction->id;
                    $transaction_images->device_id = $id;
                    $transaction_images->type = 'out';
                    $transaction_images->save();

                    \Illuminate\Support\Facades\Session::put('error_message', 'Goodbye');
                    $message = $this->get_error_message('goodbye_exit', $user_name);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'goodbye_exit'),
                        'data' => $attendant_transaction,
                    );
                } elseif ($valid_settings->device_direction == 'bi-directional') {
                    $attendant = \App\Attendants::where('booking_id', $booking_details->id)->orderBy('created_at', 'desc')->first();
                    if (!$attendant) {
                        $attendant = new \App\Attendants();
                    }
                    $attendant->booking_id = $booking_details->id;
                    $attendant->save();
                    $attendant_id = $attendant->id;
                    $attendant_transaction = \App\AttendantTransactions::where([
                                ['attendant_id', $attendant_id],
                            ])
                            ->whereNotNull('check_in')
                            ->whereNull('check_out')
                            ->orderBy('created_at', 'desc')
                            ->first();
                    if ($attendant_transaction) {
                        $attendant_transaction->check_out = date('Y-m-d H:i:s');
                        $attendant_transaction->save();
                        $transaction_images = new \App\TransactionImages();
                        if (!empty($booking_details->image_path)) {
                            $transaction_images->image_path = $booking_details->image_path;
                            $booking = \App\Bookings::find($booking_details->id);
                            if ($booking) {
                                $booking->image_path = NULL;
                                $booking->save();
                            }
                        }
                        $transaction_images->transaction_id = $attendant_transaction->id;
                        $transaction_images->device_id = $id;
                        $transaction_images->type = 'out';
                        $transaction_images->save();
                        \Illuminate\Support\Facades\Session::put('error_message', 'Goodbye');
                        $message = $this->get_error_message('goodbye_exit', $user_name);
                        return array(
                            'status' => 1,
                            'access_status' => 'success',
                            'message' => $message,
                            'od_sent' => $settings->send_message_od($id, $message, 'goodbye_exit'),
                            'data' => $attendant_transaction,
                        );
                    } else {
                        $attendant_transaction = new \App\AttendantTransactions();
                        $attendant_transaction->attendant_id = $attendant_id;
                        $attendant_transaction->check_in = date('Y-m-d H:i:s');
                        $attendant_transaction->save();
                        $transaction_images = new \App\TransactionImages();
                        if (!empty($booking_details->image_path)) {
                            $transaction_images->image_path = $booking_details->image_path;
                            $booking = \App\Bookings::find($booking_details->id);
                            if ($booking) {
                                $booking->image_path = NULL;
                                $booking->save();
                            }
                        }
                        $transaction_images->transaction_id = $attendant_transaction->id;
                        $transaction_images->device_id = $id;
                        $transaction_images->type = 'in';
                        $transaction_images->save();
                    }
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => 'Success',
                        'data' => $attendant_transaction,
                    );
                }
            }
            return array(
                'status' => 1,
                'access_status' => 'error',
                'message' => 'Invalid',
                'data' => FALSE,
            );
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-admin', $ex->getMessage(), $ex->getTraceAsString());
        }
    }

    public function check_status(Request $request, $key, $id, $booking_id, $is_barcode = FALSE, $vehicle_num = '') {
        $settings = new Settings();
        $valid_settings = $settings->is_valid_call($key, $id);
        if (!$valid_settings) {
            return array(
                'status' => 0,
                'access_status' => 'error',
                'message' => 'Invalid Access',
                'data' => FALSE,
            );
        }

        if ($is_barcode && $vehicle_num == '') {
            $booking_details = $this->get_barcode_booking($booking_id);
        } else {
            $booking_details = $this->validate_by_booking_id($booking_id);
        }
        if (!$booking_details) {
            if ($is_barcode && $vehicle_num == '') {
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => 'Booking is for incoming',
                    'data' => 'in',
                );
            }

            \Illuminate\Support\Facades\Session::put('error_message', 'Unauthorized');
            $message = $this->get_error_message('unauthorized');
            return array(
                'status' => 1,
                'access_status' => 'error',
                'message' => $message,
                'data' => FALSE,
            );
        }
        if ($valid_settings->available_device_id == 1) {
            if ($booking_details->type == 6) {
                $message = $this->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
        } elseif ($valid_settings->available_device_id == 2) {
            if ($booking_details->type != 6) {
                $message = $this->get_error_message('unauthorized');
                return array(
                    'status' => 1,
                    'access_status' => 'error',
                    'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                    'message' => $message,
                    'data' => FALSE,
                );
            }
        } else {
            $message = $this->get_error_message('unauthorized');
            return array(
                'status' => 1,
                'access_status' => 'error',
                'od_sent' => $settings->send_message_od($id, $message, 'rejected'),
                'message' => $message,
                'data' => FALSE,
            );
        }

        $user_name = $this->get_user_name($booking_details);
        if ($valid_settings->device_direction == 'out') {
            $is_booking_at_location = $settings->is_booking_at_location($booking_details->id);
            if (!$is_booking_at_location && $vehicle_num != '') {
                $verify_vehicle = new VerifyVehicle();
                $booking_details_external = $verify_vehicle->get_vehicle_booking($vehicle_num, 'out');
                if (is_object($booking_details_external)) {
                    $status = $this->valid_timings($booking_details);
                    if ($status['status']) {
                        $verify_vehicle->set_vehicle_booking_checkout($booking_details_external, $booking_details->id, "out");
                        \Illuminate\Support\Facades\Session::put('error_message', 'Booking is for outgoing');
                        $message = $this->get_error_message('goodbye_exit', $user_name);
                        return array(
                            'status' => 1,
                            'access_status' => 'success',
                            'message' => $message,
                            'data' => 'out',
                            'booking_id' => $booking_details_external->id,
                        );
                    }
                }
            }
            if (!$is_booking_at_location) {
                \Illuminate\Support\Facades\Session::put('error_message', 'Booking is not at location');
                $message = $this->get_error_message('unauthorized_reader_parking', $user_name);
                return array(
                    'status' => 1,
                    'access_status' => 'success',
                    'message' => $message,
                    'od_sent' => $settings->send_message_od($id, $message, 'unknown'),
                    'data' => 'error',
                    'booking_id' => $booking_details->id,
                );
            } else {
                if ($is_barcode && $vehicle_num == '') {
                    \Illuminate\Support\Facades\Session::put('error_message', 'Barcode Booking is at location');
                    $message = $this->get_error_message('goodbye_exit', $user_name);
                    return array(
                        'status' => 1,
                        'access_status' => 'success',
                        'message' => $message,
                        'od_sent' => $settings->send_message_od($id, $message, 'goodbye_exit'),
                        'data' => 'out',
                        'booking_id' => $booking_details->id,
                    );
                }
            }
        }
        $is_booking_at_location = $settings->is_booking_at_location($booking_details->id);
        if ($is_booking_at_location) {
            \Illuminate\Support\Facades\Session::put('error_message', 'Booking is for outgoing');
            $message = $this->get_error_message('goodbye_exit', $user_name);
            return array(
                'status' => 1,
                'access_status' => 'success',
                'message' => $message,
                'data' => 'out',
                'booking_id' => $booking_details->id,
            );
        }

        return array(
            'status' => 1,
            'access_status' => 'success',
            'message' => 'Booking is for incoming',
            'data' => 'in',
            'booking_id' => $booking_details->id,
        );
    }

    function get_booking_details($id) {
        $booking = \App\Bookings::find($id);
        if (!$booking) {
            return FALSE;
        }
        return $booking;
    }

    function is_valid_antipassback($booking, $device_id) {

        \Illuminate\Support\Facades\Session::put('error_message', 'Antipass back not allowed');
        $device_details = \App\LocationDevices::find($device_id);
        if (!$device_details) {
            return array(
                'status' => FALSE,
                'message_code' => 'unauthorized',
            );
        }

        $time_passback = 0;

        if ($device_details->anti_passback != 1) {
            return array(
                'status' => TRUE,
                'message_code' => 'unauthorized',
            );
        }

        if ($device_details->time_passback != '0' && $device_details->time_passback != NULL) {
            $time_passback = $device_details->time_passback;
        }
        if ($time_passback <= 0) {
            return array(
                'status' => TRUE,
                'message_code' => '',
            );
        }
        $attendant = \App\Attendants::where('booking_id', $booking->id)->first();
        if (!$attendant) {
            return array(
                'status' => TRUE,
                'message_code' => '',
            );
        }

        $transactions = \App\AttendantTransactions::where([
                    ['check_in', '<>', NULL],
                    ['check_out', NULL],
                    ['attendant_id', $attendant->id],
                ])
                ->orderBy('created_at', 'desc')
                ->first();

        if (!$transactions) {
            return array(
                'status' => TRUE,
                'message_code' => '',
            );
        }
        if (date('Y-m-d', strtotime($transactions->check_out)) < date('Y-m-d')) {
            return array(
                'status' => TRUE,
                'message_code' => '',
            );
        } elseif (date('Y-m-d', strtotime($transactions->check_out)) == date('Y-m-d')) {
            $checkout_time = date('H:i', strtotime($transactions->check_out));
            $current_time = date('H:i');
            $valid_time = date("H:i", strtotime('+ ' . $time_passback . ' minutes', strtotime($checkout_time)));
            if ($current_time < $valid_time) {
                if ($booking->type == 5) {
                    return array(
                        'status' => FALSE,
                        'message_code' => 'anti_passback_message_barcode',
                    );
                }
                return array(
                    'status' => FALSE,
                    'message_code' => 'anti_passback_message',
                );
            }
        }
        return array(
            'status' => TRUE,
            'message_code' => '',
        );
    }

    function is_valid_timepassback($booking, $device_details) {
        \Illuminate\Support\Facades\Session::put('error_message', 'Antipass back not allowed');
        $time_passback = 0;
        if ($device_details->time_passback != '0' && $device_details->time_passback != NULL) {
            $time_passback = $device_details->time_passback;
        }
        if ($time_passback <= 0) {
            return array(
                'status' => TRUE,
                'message_code' => '',
            );
        }
        $attendant = \App\Attendants::where('booking_id', $booking->id)->first();
        if (!$attendant) {
            return array(
                'status' => TRUE,
                'message_code' => '',
            );
        }

        $transactions = \App\AttendantTransactions::where([
                    ['check_in', '<>', NULL],
                    ['check_out', NULL],
                    ['attendant_id', $attendant->id],
                ])
                ->orderBy('created_at', 'desc')
                ->first();

        if (!$transactions) {
            return array(
                'status' => TRUE,
                'message_code' => '',
            );
        }
        if (date('Y-m-d', strtotime($transactions->check_in)) < date('Y-m-d')) {
            return array(
                'status' => TRUE,
                'message_code' => '',
            );
        } else if (date('Y-m-d', strtotime($transactions->check_in)) == date('Y-m-d')) {
            $checkin_time = date('H:i', strtotime($transactions->check_in));
            $current_time = date('H:i');
            $valid_time = date("H:i", strtotime('+ ' . $time_passback . ' minutes', strtotime($checkin_time)));
            if ($current_time < $valid_time) {
                if ($booking->type == 5) {
                    return array(
                        'status' => FALSE,
                        'message_code' => 'anti_passback_message_barcode',
                    );
                }
                return array(
                    'status' => FALSE,
                    'message_code' => 'anti_passback_message_barcode',
                );
            }
        }
        return array(
            'status' => TRUE,
            'message_code' => '',
        );
    }

    public function get_barcode_booking($barcode) {
        $booking_details = \App\Bookings::where('barcode', $barcode)
                ->orderBy('created_at', 'desc')
                ->first();
        if ($booking_details) {
            return $booking_details;
        }
        return FALSE;
    }

    public function get_vehicle_group($booking_details) {
        if (empty($booking_details->customer_vehicle_info_id)) {
            return FALSE;
        }
        $customer_vehicle_info = \App\CustomerVehicleInfo::find($booking_details->customer_vehicle_info_id);
        if ($customer_vehicle_info && $customer_vehicle_info->userlist_user_id > 0) {
            $user_list_user = \App\UserlistUsers::where('id', $customer_vehicle_info->userlist_user_id)->first();
            if ($user_list_user) {
                if ($user_list_user->group_id > 0) {
                    return $user_list_user->group_id;
                }
            }
        }
        return FALSE;
    }

    public function device_has_group($device_id) {
        $group = \App\GroupDevices::where([
                    ['device_id', $device_id]
                ])
                ->first();
        if ($group) {
            return TRUE;
        }
        return FALSE;
    }

    public function is_valid_group_device($group_id, $device_id = FALSE) {
        if (!$device_id) {
            $device_id = $this->device_id;
        }
        if ($group_id == NULL) {
            return TRUE;
        }
        $group = \App\GroupDevices::where([
                    ['group_id', $group_id],
                    ['device_id', $device_id]
                ])
                ->first();
        if ($group) {
            return TRUE;
        }
        return FALSE;
    }
    public function getProduct($id)
    {
        $product = \App\Products::find($id);
        if ($product) {
            return $product;
        }
        return false;
    }

}
