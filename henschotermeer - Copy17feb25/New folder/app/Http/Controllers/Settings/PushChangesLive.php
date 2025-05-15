<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use GuzzleHttp\Client;

class PushChangesLive extends Controller {

    public $location_id;

    public function __construct() {
        $locationOption = \App\LocationOptions::first();
        $this->location_id = $locationOption->live_id;
    }

    public function push_whitelist() {
        try {
            $records = \App\WhitelistUsers::where('live_id', 0)->get();
            if ($records->count() > 0) {
                foreach ($records as $record) {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/export-single-whitelist-data', [
                        'form_params' => [
                            'location_id' => $this->location_id,
                            'data' => $record
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                    if ($responseData['success'] && !empty($responseData['data']['id'])) {
                        $record->live_id = $responseData['data']['id'];
                    }
                    $record->save();
                }
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('export-whitelist_command', $ex->getMessage(), $ex->getTrace());
        }
    }

    public function push_userlist() {
        try {
            $data = array();
            $records = \App\UserlistUsers::where('live_id', 0)->get();
            if ($records->count() > 0) {
                foreach ($records as $record) {
                    $data[] = array(
                        'id' => $record->id,
                        'live_id' => $record->live_id,
                        'customer_id' => $record->customer_id,
                        'group_id' => $record->group_id,
                        'email' => $record->email,
                        'notation' => $record->notation,
                        'bike_range_start' => $record->bike_range_start,
                        'bike_range_end' => $record->bike_range_end,
                        'door_range_start' => $record->door_range_start,
                        'door_range_end' => $record->door_range_end,
                        'ev_charger_range_start' => $record->ev_charger_range_start,
                        'ev_charger_range_end' => $record->ev_charger_range_end,
                        'language_id' => $record->language_id,
                        'user_name' => $record->user_name,
                        'user_phone' => $record->user_phone,
                        'user_vehicle' => $record->user_vehicle,
                        'is_blocked' => $record->is_blocked,
                        'profile_image' => $record->profile_image,
                        'energy_limit' => $record->energy_limit,
                        'use_profile_name' => $record->use_profile_name,
                    );
                }
                $http = new Client();
                $response = $http->post(env('API_BASE_URL').'/api/export-userlist-data', [
                    'form_params' => [
                        'location_id' => $this->location_id,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
                foreach ($records as $indexKey => $record) {
                    if ($responseData['success'] && !empty($responseData['data'][$indexKey]['live_id'])) {
                        $record->live_id = $responseData['data'][$indexKey]['live_id'];
                    }
                    $record->save();
                }
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('export-whitelist_command', $ex->getMessage(), $ex->getTrace());
        }
    }

    public function push_devices() {
        try {
            $data = array();
            $records = \App\LocationDevices::where('live_id', 0)->get();
            if ($records->count() > 0) {
                foreach ($records as $record) {
                    $data[] = array(
                        'id' => $record->id,
                        'live_id' => $record->live_id,
                        'device_name' => $record->device_name,
                        'available_device_id' => $record->available_device_id,
                        'device_direction' => $record->device_direction,
                        'device_ip' => $record->device_ip,
                        'device_port' => $record->device_port,
                        'anti_passback' => $record->anti_passback,
                        'time_passback' => $record->time_passback,
                        'is_synched' => $record->is_synched,
                        'enable_log' => $record->enable_log,
                        'enable_idle_screen' => $record->enable_idle_screen,
                        'focus_away' => $record->focus_away,
                        'opacity_input' => $record->opacity_input,
                        'camera_enabled' => $record->camera_enabled,
                        'has_gate' => $record->has_gate,
                        'has_barrier' => $record->has_barrier,
                        'message_text_size' => $record->message_text_size,
                        'time_text_size' => $record->time_text_size,
                        'date_text_size' => $record->date_text_size,
                        'bottom_tray_text_size' => $record->bottom_tray_text_size,
                        'od_enabled' => $record->od_enabled,
                        'barrier_close_time' => $record->barrier_close_time,
                        'qr_code_type' => $record->qr_code_type
                    );
                }
                $http = new Client();
                $response = $http->post(env('API_BASE_URL').'/api/export-device-data', [
                    'form_params' => [
                        'location_id' => $this->location_id,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
                foreach ($records as $indexKey => $record) {
                    if ($responseData['success'] && !empty($responseData['data'][$indexKey]['live_id'])) {
                        $record->live_id = $responseData['data'][$indexKey]['live_id'];
                    }
                    $record->save();
                }
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('export-whitelist_command', $ex->getMessage(), $ex->getTrace());
        }
    }

}
