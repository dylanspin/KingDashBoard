<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BarcodeFailSafeForTicketReader extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:BarcodeFailSafeForTicketReader {type} {barcode}';

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
        $type = $this->argument('type');
        $barcode = $this->argument('barcode');
        
        if($type == 'person'){
            $availableDevice = \App\AvailableDevices::where('name', '=', 'Person Ticket Readers')->first();
            if($availableDevice){
                $devices = \App\LocationDevices::where('available_device_id', $availableDevice->id)->get();
                foreach($devices as $device){
                    $ip = $device->device_ip;
                    $port = $device->device_port;
                    $client = new \App\Http\Controllers\Connection\Client($ip, $port);
                    $key = strtotime(date('Y-m-d H:i:s')) . '-' . $device->id;
                    $command = 'person_fail_safe';
                    $data = '37|' . $key . '|' . $barcode;
                    $connection = $client->send($command, $data);
                }
            }
        }
        else if($type == 'parking'){
            $availableDevice = \App\AvailableDevices::where('name', '=', 'Ticket Readers')->first();
            if($availableDevice){
                $devices = \App\LocationDevices::where('available_device_id', $availableDevice->id)->get();
                foreach($devices as $device){
                    $ip = $device->device_ip;
                    $port = $device->device_port;
                    $client = new \App\Http\Controllers\Connection\Client($ip, $port);
                    $key = strtotime(date('Y-m-d H:i:s')) . '-' . $device->id;
                    $command = 'parking_fail_safe';
                    $data = '38|' . $key . '|' . $barcode;
                    $connection = $client->send($command, $data);
                }
            }
        }
        return;
    }
}
