<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OpenGateForExitVehcile extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OpenGateForExitVehcile {device} {vehicle} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $vehicle = $this->argument('vehicle');
        $message = $this->argument('message');
        $device = \App\LocationDevices::find($device_id);
        if ($device) {
           // $details = array();
            $ip = $device->device_ip;
            $port = $device->device_port;
//            $name = $device->device_name;
//            if ($name == NULL) {
//                $device->is_synched = 0;
//                $device->save();
//                return;
//            }
//            $details['name'] = $name;

            $client = new \App\Http\Controllers\Connection\Client($ip, $port);
//            $location = new \App\Http\Controllers\Settings\LocationSettings();
//            $location_details = $location->get_location();
//            if (!$location_details) {
//                $device->is_synched = 0;
//                $device->save();
//                return;
//            }
            $key = strtotime(date('Y-m-d H:i:s')) . '-' . $device->id;
            $command = 'open_gate_Exit';
            $data = '35|' . $key . '|' . $vehicle . '|' . $message;
            $connection = $client->send($command, $data);
            if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                if ($connection['status'] >= 3) {
                    $device->is_synched = 1;
                    $device->is_opened = 1;
                    $device->save();
                } else {
                    $device->is_synched = 0;
                    $device->save();
                }
            } else {
                $device->is_synched = 0;
                $device->save();
            }
            return;
        }
    }

}
