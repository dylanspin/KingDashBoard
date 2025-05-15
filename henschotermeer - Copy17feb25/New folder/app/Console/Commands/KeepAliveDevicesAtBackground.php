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
            $devices = \App\LocationDevices::where('available_device_id','<>',2)->get();
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
                    } else {
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
                            $data = '19|' . $key . '|' . $localIP . '|' . json_encode($device_details) . '|' . json_encode($endpoints);
                        } else {
                            $settings = new \App\Http\Controllers\Settings\Settings();
                            $endpoints = $settings->get_endpoints();
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
                }
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('command-KeepAliveDevicesAtBackground', $ex->getMessage(), $ex->getTraceAsString());
        }
    }

}
