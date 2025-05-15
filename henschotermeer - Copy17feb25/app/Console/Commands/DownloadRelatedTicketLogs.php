<?php

namespace App\Console\Commands;

use App\DeviceBookings;
use Illuminate\Console\Command;
use App\LocationDevices;
use Exception;
use App\Http\Controllers\Connection\Client;
use App\Http\Controllers\Settings\LocationSettings;
use App\Http\Controllers\Settings\Settings;
use App\DeviceDownloadLog;

class DownloadRelatedTicketLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:download_related_ticket_reader_logs {device_booking_id} {has_related_ticket_reader}';

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
        //
        try {
            $deviceBookingId = $this->argument('device_booking_id');
            $device = $this->argument('has_related_ticket_reader');
            $deviceBooking = DeviceBookings::where('id', $deviceBookingId)->first();
            if (!$deviceBooking) {
                return;
            }
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
                $client = new Client($ip, $port);
                $location = new LocationSettings();
                $location_details = $location->get_location();
                if (!$location_details) {
                    $device->is_synched = 0;
                    $device->save();
                    return;
                }
                $key = strtotime($location_details->created_at) . '-' . $device->id;
                $command = "send_logs_to_related_ticket_reader";
                $data = '44|' . $key . '|' . $deviceBookingId;
                $file = '';
                $connection = $client->send($command, $data);
                if (is_array($connection) && count($connection) > 0 && array_key_exists('status', $connection)) {
                    if ($connection['status'] >= 0) {
                        return;
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
        } catch (Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('send_device_booking_id_to_ticket_reader', $ex->getMessage(), $ex->getTraceAsString());
            return $ex->getMessage();
        }
    }
}
