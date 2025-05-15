<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InitializeDevice extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InitializeDevice {device} {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Device';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $device_id = $this->argument('device');
        $status = $this->argument('status');
        $device = \App\LocationDevices::find($device_id);
        if ($device) {
            $details = array();
            $ip = $device->device_ip;
            $port = $device->device_port;
            $name = $device->device_name;
            if ($name == NULL) {
                $device->is_synched = 0;
                $device->save();
                return;
            }
            $details['name'] = $name;
            if ($ip == '' || $port == NULL) {
                $device->is_synched = 0;
                $device->save();
                return;
            }
            $client = new \App\Http\Controllers\Connection\Client($ip, $port);

            $location = new \App\Http\Controllers\Settings\LocationSettings();
            $location_details = $location->get_location();
            if (!$location_details) {
                $device->is_synched = 0;
                $device->save();
                return;
            }
            $key = strtotime($location_details->created_at) . '-' . $device->id;
            $settings = new \App\Http\Controllers\Settings\Settings();
            $endpoints = $settings->get_endpoints();
            if ($status == 'all') {
                $device_od = \App\LocationDevices::find($device_id);
                if ($device_od->available_device_id == 4) {
                    $endpoints = $settings->get_od_endpoints();
                    $command = 'initialize_device';
                    $od_settings = array(
                        'device' => $device_od,
                        'message_text_size' => $device_od->message_text_size,
                        'time_text_size' => $device_od->time_text_size,
                        'date_text_size' => $device_od->date_text_size,
                        'bottom_tray_text_size' => $device_od->bottom_tray_text_size,
                        'enable_idle_screen' => $device_od->enable_idle_screen ? 'yes' : 'no',
                    );
                    $data = '25|' . $key . '|' . json_encode($od_settings);
                } elseif ($device_od->available_device_id == 6) {
                    $endpoints = $settings->get_payment_terminal_endpoints();
                    $command = 'initialize_device';
                    $payment_terminal_settings = array(
                        'device' => $device_od,
                        'location' => array(
                            'id' => $location_details->id,
                            'title' => $location_details->title,
                        ),
                        'enable_log' => $device_od->enable_log ? 'yes' : 'no',
                    );
                    $data = '25|' . $key . '|' . json_encode($payment_terminal_settings) . '|' . json_encode($endpoints);
                } elseif ($device_od->available_device_id == 3) {
                    $endpoints = $settings->get_plate_reader_endpoints();
                    $command = 'initialize_device';
                    $localIP = getHostByName(getHostName());
                    $level = '';
                    $light_condition = \App\LightCondition::where('device_id', $device->id)->first();
                    $light=array();
                    if($light_condition){
                        if ($light_condition->level == 1) {
                            $level = "light";
                        } elseif ($light_condition->level == 2) {
                            $level = "medium";
                        } elseif ($light_condition->level == 3) {
                            $level = "strong";
                        }
                        $light['level']=$level;
                        $light['gain'] = $light_condition->gain;
                        $light['exposure_time']= $light_condition->exposure_time;
                    }
                    
                    $array_settings = array(
                        'id' => $device_od->id, 'device_name' => $device_od->device_name, 'device_direction' => $device->device_direction, 'device_ip' => $device->device_ip, 'device_port' => $device->device_port, 'enable_log' => $device->enable_log,
                        'character_match_limit' => $device->character_match_limit, 'has_sdl' => $device_od->has_sdl, 'gate_close_transaction_enabled' => $device_od->gate_close_transaction_enabled, 'has_gate' => $device_od->has_gate, 'confidence' => $device_od->confidence, 'retries' => $device_od->retries, 'popup_time' => $device_od->popup_time,
                        'emergency_access' => $device->has_emergency, 'plate_length' => $device->plate_length, 'character_height' => $device->character_height, 'exposure_mode' => $device->exposure_mode, 'disable_night_mode' => $device->disable_night_mode, 'light_conditions' => $light
                    );
                    $data = '19|' . $key . '|' . $localIP . '|' . json_encode($array_settings) . '|' . json_encode($endpoints);
                } else {
                    $command = 'initialize_device';
					$endpoints = $device->tr_version != "2.0" ? $settings->get_endpoints_v1() : $settings->get_endpoints();
                    $data = '19|' . $key . '|' . json_encode($endpoints);
                }
            } elseif ($status == 'location_setting') {
                $command = 'location_setting';
                $data = '20|' . $key;
            } elseif ($status == 'timings') {
                $command = 'timings';
                $data = '21|' . $key;
            } elseif ($status == 'whitelist_tiings') {
                $command = 'whitelist_tiings';
                $data = '22|' . $key;
            } elseif ($status == 'whitelist_users') {
                $command = 'whitelist_users';
                $data = '23|' . $key;
            } elseif ($status == 'userlist_users') {
                $command = 'userlist_users';
                $data = '24|' . $key;
            } elseif ($status == 'device_settings') {
                $device_od = \App\LocationDevices::find($device_id);
                if ($device_od->available_device_id == 4) {
                    $endpoints = $settings->get_od_endpoints();
                    $command = 'od';
                    $od_settings = array(
                        'device' => $device_od,
                        'message_text_size' => $device_od->message_text_size,
                        'time_text_size' => $device_od->time_text_size,
                        'date_text_size' => $device_od->date_text_size,
                        'bottom_tray_text_size' => $device_od->bottom_tray_text_size,
                        'enable_idle_screen' => $device_od->enable_idle_screen ? 'yes' : 'no',
                    );

                    $data = '25|' . $key . '|' . json_encode($od_settings);
                } else {
                    $command = 'device_settings';
                    $data = '25|' . $key;
                }
            } elseif ($status == 'od') {
                $endpoints = $settings->get_od_endpoints();
                $command = 'od';
                $device_od = \App\LocationDevices::find($device_id);
                if ($device_od->available_device_id != 4) {
                    return;
                }
                $od_settings = array(
                    'device' => $device_od,
                    'message_text_size' => $device_od->message_text_size,
                    'time_text_size' => $device_od->time_text_size,
                    'date_text_size' => $device_od->date_text_size,
                    'bottom_tray_text_size' => $device_od->bottom_tray_text_size,
                    'enable_idle_screen' => $device_od->enable_idle_screen ? 'yes' : 'no',
                );

                $data = '25|' . $key . '|' . json_encode($od_settings);
            } else {
                $device->is_synched = 0;
                $device->save();
                return;
            }
            $connection = $client->send($command, $data);
            if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                if ($connection['status'] >= 3) {
                    $device->is_synched = 1;
                    $device->save();
                } else {
                    $device->is_synched = 0;
                    $device->save();
                }
            } else {
                $device->is_synched = 0;
                $device->save();
            }
        }
    }
}
