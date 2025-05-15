<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeniedAccess extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DeniedAccess {device} {message} {vehicle_num=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Error Message to related ticket reader';

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
        $message = $this->argument('message');
        $vehicle_num = $this->argument("vehicle_num");
        $device = \App\LocationDevices::find($device_id);
        if ($device) {
            if ($device->available_device_id == 1) {
                $related_ticket_reader_id = $device_id;
            } elseif ($device->available_device_id == 3) {
                $related_ticket_reader = \App\DeviceTicketReaders::where([
                            ['device_id', $device_id]
                        ])->first();

                if (!$related_ticket_reader) {
                    return FALSE;
                }
                $related_ticket_reader_id = $related_ticket_reader->ticket_reader_id;
            } else {
                return FALSE;
            }
            $device = \App\LocationDevices::find($related_ticket_reader_id);
            if (!$device) {
                return FALSE;
            }
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

            $client = new \App\Http\Controllers\Connection\Client($ip, $port);
            $location = new \App\Http\Controllers\Settings\LocationSettings();
            $location_details = $location->get_location();
            if (!$location_details) {
                $device->is_synched = 0;
                $device->save();
                return;
            }
            $key = strtotime($location_details->created_at) . '-' . $device->id;
            $command = 'Plate Reader Message';
            $data = '33|' . $key . '|' . $message;
            if($vehicle_num){
                $data.="|tentave_vehicle_".$vehicle_num;
            }
            $client->send($command, $data);
            return;
        }
    }

}
