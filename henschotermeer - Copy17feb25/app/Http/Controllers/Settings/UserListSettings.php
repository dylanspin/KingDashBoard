<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class UserListSettings extends Controller {

    public function __construct() {
        
    }

    public function generate_userlist_settings(Request $request, $key, $id = null) {
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
            $record = \App\LocationOptions::first();
            if (!$record) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Access',
                    'data' => FALSE,
                );
            }
            $data = array();
            $data['userlist_users'] = $this->userlist_users();
            $data['userlist_blocked_users'] = $this->userlist_blocked_users();
            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $data,
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

    public function userlist_users() {
        $userlist_users = array();
        $location_userlist = \App\UserlistUsers::where([
                    ['is_blocked', 0]
                ])
                ->get();
        if ($location_userlist->count() > 0) {
            $ticket_serial = time();
            foreach ($location_userlist as $indexKey => $userlist) {
                $list = array();
                $user_name = trim($userlist->user_name);
                $user_email = trim($userlist->email);
                $user_vehicle = trim($userlist->user_vehicle);
                $energy_limit = $userlist->energy_limit != NULL ? $userlist->energy_limit : 0;
                $energy_limit = trim($energy_limit);
                $language_details = \App\Language::find($userlist->language_id);
                $language_code = $language_details->code;
                $language_code = trim($language_code);
                $list['name'] = $user_name;
                $list['email'] = $user_email;
                $list['vehicle'] = $user_vehicle;
                $list['energy'] = $energy_limit;
                $list['lang'] = $language_code;
                $list['ticket_serial'] = $ticket_serial;
                $userlist_users[] = $list;
            }
        }
        return $userlist_users;
    }

    public function userlist_blocked_users() {
        $userlist_users = array();
        $location_userlist = \App\UserlistUsers::where([
                    ['is_blocked', 1]
                ])
                ->get();
        if ($location_userlist->count() > 0) {
            $ticket_serial = time();
            foreach ($location_userlist as $indexKey => $userlist) {
                $list = array();
                $user_vehicle = array();
                $user_name = trim($userlist->user_name);
                $user_email = trim($userlist->email);
                if (!empty($userlist->user_vehicle)) {
                    $user_vehicle[] = trim($userlist->user_vehicle);
                }
                if (!empty($userlist->customer_id)) {
                    $customer_vehicle_info = \App\CustomerVehicleInfo::where('customer_id', $userlist->customer_id)->get();
                    if ($customer_vehicle_info->count() > 0) {
                        foreach ($customer_vehicle_info as $vehicle) {
                            if (!empty($vehicle->num_plate)) {
                                $user_vehicle[] = trim($vehicle->num_plate);
                            }
                        }
                    }
                }
                $energy_limit = $userlist->energy_limit != NULL ? $userlist->energy_limit : 0;
                $energy_limit = trim($energy_limit);
                $language_details = \App\Language::find($userlist->language_id);
                $language_code = $language_details->code;
                $language_code = trim($language_code);
                $list['name'] = $user_name;
                $list['email'] = $user_email;
                $list['vehicle'] = $user_vehicle;
                $list['energy'] = $energy_limit;
                $list['lang'] = $language_code;
                $list['ticket_serial'] = $ticket_serial;
                $userlist_users[] = $list;
            }
        }
        return $userlist_users;
    }

    public function userlist_settings_status(Request $request, $key, $id = null, $status) {
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
            $error_message = 'Unable to connect';
            if (!$status) {
                if (!empty($request->error_message)) {
                    $error_message = $request->error_message;
                }
            }
            $device_id = $valid_settings->id;
            $device_settings = \App\DeviceSettings::where('device_id', $device_id)->first();
            if (!$device_settings) {
                $device_settings = new \App\DeviceSettings();
            }
            if ($status) {
                $device_settings->userlist_settings = 1;
                $device_settings->userlist_settings_details = NULL;
            } else {
                $device_settings->userlist_settings = 0;
                $device_settings->userlist_settings_details = $error_message;
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

}
