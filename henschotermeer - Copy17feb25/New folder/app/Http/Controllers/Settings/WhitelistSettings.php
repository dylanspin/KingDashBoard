<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class WhiteListSettings extends Controller {

    public function __construct() {
        
    }

    public function generate_whitelist_settings(Request $request, $key, $id = null) {
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
            $whitelist_users = array();
            $location_whitelist = \App\WhitelistUsers::get();
            if ($location_whitelist->count() > 0) {
                foreach ($location_whitelist as $whitelist) {
                    $whitelist_users[] = $whitelist->email;
                }
            }
            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $whitelist_users,
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

    public function whitelist_settings_status(Request $request, $key, $id = null, $status) {
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
                $device_settings->whitelist_settings = 1;
                $device_settings->whitelist_settings_details = NULL;
            } else {
                $device_settings->whitelist_settings = 0;
                $device_settings->whitelist_settings_details = $error_message;
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
