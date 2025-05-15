<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KeepAliveDevice extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:KeepAliveDevice {device}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will test device synched status and keep device alive';

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
        $device_id = $this->argument('device');
        $device = \App\LocationDevices::find($device_id);
        if ($device && $device->is_synched) {
            $ip = $device->device_ip;
            $port = $device->device_port;

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
            $command = 'keep_alive';
            $data = '30|' . $key . '|keep_alive';
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
