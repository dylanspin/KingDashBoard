<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LocationDevices;
use App\DevicePort;
use App\Http\Controllers\Settings\LocationSettings;
use App\Http\Controllers\Settings\Settings;
use App\Http\Controllers\Connection\Client;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;

class OpenClosePaymentTerminal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OpenClosePaymentTerminal {device} {switch} {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to open and close payment terminal connected with specified switch';

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
        $output = new ConsoleOutput();
        $device = $this->argument('device');
        $switch = $this->argument('switch');
        $status = $this->argument('status');
        $payment_terminal=$this->getStatus($device, $switch, $status);
    }
    public function getStatus($device, $switch, $status)
    {
        try {
            $ip_address=$switch->device_ip;
            $port=$switch->device_port;
            $command = 'open_close_payment_terminal';
            $open='11';
            $close='12';
            $all_ready_open='21';
            $all_ready_close='22';
            $location = new LocationSettings();
            $location_details = $location->get_location();
            if (!$location_details) {
                $device->is_synched = 0;
                $device->save();
            }
            $client=new Client($ip_address, $port);
            // for open payment terminal//
            if ($status==1) {
                $data='00';
                $responseData=$client->send($command, $data);
                $onStatus="on";
                $relay_number=1;
                if ($responseData['status']=="1") {
                    return array('status'=> $all_ready_open);
                } else {
                    $this->sendTcpPacket($relay_number, $onStatus, $ip_address, $port);
                    $responseData=$client->send($command, $data);
                    if ($responseData['status'] =="1") {
                        return array('status'=>$open);
                    }
                }
            }
            // for closed payment terminal//
            if ($status==0) {
                $data='00';
                $responseData=$client->send($command, $data);
                $onStatus="off";
                $relay_number=1;
                if ($responseData['status']=="1") {
                    $this->sendTcpPacket($relay_number, $onStatus, $ip_address, $port);
                    $responseData=$client->send($command, $data);
                    if ($responseData['status'] != "1") {
                        return array('status'=>$close);
                    }
                } else {
                    return array('status'=>$all_ready_close);
                }
            }
            return array('status'=>00);
        } catch (Exception $ex) {
            response()->json([
                'status'=>"error",
                'message'=> $ex->getMessage().' '.$ex->getLine()
            ]);
        }
    }
    public function sendTcpPacket($relay_number, $status, $ip_address, $port)
    {
        try {
            $text = "";
            $client=new Client($ip_address, $port);
            $command = 'open_close_payment_terminal';
            if ($relay_number == "1") {
                if ($status == "on") {
                    $text = "11";
                } else {
                    $text = "21";
                }
            }
            if ($relay_number == "2") {
                if ($status == "on") {
                    $text = "12";
                } else {
                    $text = "22";
                }
            }
            if ($relay_number == "3") {
                if ($status == "on") {
                    $text = "13";
                } else {
                    $text = "23";
                }
            }
            if ($relay_number == "4") {
                if ($status == "on") {
                    $text = "14";
                } else {
                    $text = "24";
                }
            }
            if ($relay_number == "5") {
                if ($status == "on") {
                    $text = "15";
                } else {
                    $text = "25";
                }
            }
            if ($relay_number == "6") {
                if ($status == "on") {
                    $text = "16";
                } else {
                    $text = "26";
                }
            }
            if ($relay_number == "7") {
                if ($status == "on") {
                    $text = "17";
                } else {
                    $text = "27";
                }
            }
            if ($relay_number == "8") {
                if ($status == "on") {
                    $text = "18";
                } else {
                    $text = "28";
                }
            }
            $stream=$client->send($command, $text);
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
}