<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CloseTicketReader extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CloseTicketReader {device}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close Gate';

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
            if (!$device->is_synched) {
                return;
            }
            if ($device->available_device_id == 3 && $device->has_gate) {
                $port = 8090;
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
            $command = 'open_gate';
            $data = '27|' . $key;
            $connection = $client->send($command, $data);
            if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                if ($connection['status'] >= 3) {
                    $device->is_synched = 1;
                    $device->is_opened = 0;
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
