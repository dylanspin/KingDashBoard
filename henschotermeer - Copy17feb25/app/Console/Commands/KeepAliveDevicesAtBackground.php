<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KeepAliveDevicesAtBackground extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:KeepAliveDevicesAtBackground';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will check conection between devices and central server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        try {
            $devices = \App\LocationDevices::whereNotIn('available_device_id',array(2,12,6))->get();
            if ($devices->count() > 0) {
                foreach ($devices as $device) {
                    
                    $ip = $device->device_ip;
                    $port = $device->device_port;

                    if ($ip == '' || $port == NULL) {
                        $device->is_synched = 0;
                        $device->save();
                        continue;
                    }
                    $location = new \App\Http\Controllers\Settings\LocationSettings();
                    $location_details = $location->get_location();
                    if (!$location_details) {
                        $device->is_synched = 0;
                        $device->save();
                        continue;
                    }
                    $settings = new \App\Http\Controllers\Settings\Settings();
                    if ($device->is_synched) {
                        $client = new \App\Http\Controllers\Connection\Client($ip, $port);
                        $key = strtotime($location_details->created_at) . '-' . $device->id;
                        $command = 'keep_alive';
                        $data = '30|' . $key . '|keep_alive';
                        $connection = $client->send($command, $data);
                        if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                            if ($connection['status'] >= 3) {
                                $device->is_synched = 1;
                                $device->save();
                            } else {
                                $settings->reset_device_settings($device->id);
                                $device->is_synched = 0;
                                $device->save();
                            }
                        } else {
                            $settings->reset_device_settings($device->id);
                            $device->is_synched = 0;
                            $device->save();
                        }
					}
               //     } else {
                        $device_id = $device->id;
                        $device_settings = \App\DeviceSettings::where('device_id', $device_id)->first();
                        if (!$device_settings) {
                            continue;
                        }
                        $key = strtotime($location_details->created_at) . '-' . $device->id;
                        $device_details = \App\LocationDevices::find($device_id);
                        if ($device_details->available_device_id == 4) {
                            $endpoints = $settings->get_od_endpoints();
                            $command = 'initialize_device';
                            $od_settings = array(
                                'device' => $device_details,
                                'message_text_size' => $device_details->message_text_size,
                                'time_text_size' => $device_details->time_text_size,
                                'date_text_size' => $device_details->date_text_size,
                                'bottom_tray_text_size' => $device_details->bottom_tray_text_size,
                                'enable_idle_screen' => $device_details->enable_idle_screen ? 'yes' : 'no',
                            );
                            $data = '25|' . $key . '|' . json_encode($od_settings);
                        } elseif ($device_details->available_device_id == 6) {
                            $endpoints = $settings->get_payment_terminal_endpoints();
                            $command = 'initialize_device';
                            $payment_terminal_settings = array(
                                'device' => $device_details,
                                'location' => array(
                                    'id' => $location_details->id,
                                    'title' => $location_details->title,
                                ),
                                'enable_log' => $device_details->enable_log ? 'yes' : 'no',
                            );
                            $data = '25|' . $key . '|' . json_encode($payment_terminal_settings) . '|' . json_encode($endpoints);
                        } elseif ($device_details->available_device_id == 3) {
                            $endpoints = $settings->get_plate_reader_endpoints();
                            $command = 'initialize_device';
                            $localIP = getHostByName(getHostName());
                            $level = '';
                            $light_condition = \App\LightCondition::where('device_id', $device->id)->first();
                            $light = array();
                            if ($light_condition) {
                                if ($light_condition->level == 1) {
                                    $level = "light";
                                } elseif ($light_condition->level == 2) {
                                    $level = "medium";
                                } elseif ($light_condition->level == 3) {
                                    $level = "strong";
                                }
                                $light['level'] = $level;
                                $light['gain'] = $light_condition->gain;
                                $light['exposure_time'] = $light_condition->exposure_time;
                            }

                            $array_settings = array(
                                'id' => $device_details->id, 'device_name' => $device_details->device_name, 'device_direction' => $device->device_direction, 'device_ip' => $device->device_ip, 'device_port' => $device->device_port, 'enable_log' => $device->enable_log,
                                'character_match_limit' => $device->character_match_limit, 'has_sdl' => $device_details->has_sdl, 'gate_close_transaction_enabled' => $device_details->gate_close_transaction_enabled, 'has_gate' => $device_details->has_gate, 'confidence' => $device_details->confidence, 'retries' => $device_details->retries, 'popup_time' => $device_details->popup_time,
                                'emergency_access' => $device->has_emergency, 'plate_length' => $device->plate_length, 'character_height' => $device->character_height, 'exposure_mode' => $device->exposure_mode, 'disable_night_mode' => $device->disable_night_mode, 'light_conditions' => $light
                            );
                        } else {
                            $settings = new \App\Http\Controllers\Settings\Settings();
                            $endpoints = $settings->get_endpoints();
							if($device->available_device_id == 1 && $device->tr_version != "2.0"){
								$endpoints = $settings->get_endpoints_v1();
							}
                            $command = 'initialize_device';
                            $data = '19|' . $key . '|' . json_encode($endpoints);
                        }
                        $client = new \App\Http\Controllers\Connection\Client($ip, $port);
                        $connection = $client->send($command, $data);
                        $settings = new \App\Http\Controllers\Settings\Settings();
                        if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                            if ($connection['status'] >= 3) {
                                $device->is_synched = 1;
                                $device->save();
                            } else {
                                $settings->reset_device_settings($device->id);
                                $device->is_synched = 0;
                                $device->save();
                            }
                        } else {
                            $settings->reset_device_settings($device->id);
                            $device->is_synched = 0;
                            $device->save();
                        }
                    }
                //}
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('command-KeepAliveDevicesAtBackground', $ex->getMessage(), $ex->getTraceAsString());
        }
    }

}
