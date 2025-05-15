<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ServerTime extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ServerTime {device}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Access Server Time';

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
        if ($device_id == 'all') {
            $devices = \App\LocationDevices::get();
            foreach ($devices as $device) {

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
                    return;
                }
                $command = 'server_time';
                $data = '29|' . date('m/d/Y H:i:s');
                $client->send($command, $data);
            }
        } else {
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
                    return;
                }
                $command = 'server_time';
                $data = '29|' . date('d/m/Y H:i:s');
                $client->send($command, $data);
            }
        }
    }

}
