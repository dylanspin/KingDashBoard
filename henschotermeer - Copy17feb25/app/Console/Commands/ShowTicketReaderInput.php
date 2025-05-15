<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ShowTicketReaderInput extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShowTicketReaderInput {device}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Commanmd will show input keyboard for vehicle at ticket reader';

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
            $command = 'ShowTicketReaderInput';
            $data = '32|' . $key . '|' . '1';
            $client->send($command, $data);
        }
    }

}
