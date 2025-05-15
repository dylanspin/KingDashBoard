<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LocationOptions;
use App\LocationTodayTime;
use Exception;
use GuzzleHttp\Client;
use Auth;
use App\User;

class SetParkingCloseEnable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SetParkingCloseEnable';

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
        $res = $this->check_today_hours();
    }
    public function check_today_hours()
    {
        try {
            $url = env('API_BASE_URL');
            $http = new Client();
            $location = LocationOptions::first();
            $user = User::first();
            $today_hours = LocationTodayTime::first();
            $user_id = $user->live_id;
            $data = [];
            $Key = base64_encode($location->live_id . '_' . $user_id);
            $current_time = date('H:i:s');
            if ($today_hours) {
                if (($current_time >= $today_hours->opening_time) && ($current_time <= $today_hours->closing_time)) {
					print 'open';
                    $location->online_parking_closed_enable = 0;
                    $location->update();
                    $response = $http->post($url . '/api/parking-enable-close', [
                        'form_params' => [
                            'token' => $Key,
                            'online_parking_closed_enable' => 0
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                    $message = 'success';
                    
                } else {
					print 'close';
                    $location->online_parking_closed_enable = 1;
                    $location->update();
                    $response = $http->post($url . '/api/parking-enable-close', [
                        'form_params' => [
                            'token' => $Key,
                            'online_parking_closed_enable' => 1
                        ],
                    ]);
                    $responseData = json_decode($response->getBody(), true);
					print_r($responseData);
                    $message = 'success';
                    
                }
            } else {
                $location->online_parking_closed_enable = 0;
                $location->update();
                $response = $http->post($url . '/api/parking-enable-close', [
                    'form_params' => [
                        'token' => $Key,
                        'online_parking_closed_enable' => 0
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
                $message = 'success';
              
            }
			
			$verifyVehicle = new \App\Http\Controllers\PlateReaderController\VerifyVehicle();
			$bookings = \App\Bookings::where('type',4)->where('live_id',0)->where('checkin_time','>=',date('Y-m-d 00:00:00'))->get();
			
			foreach($bookings as $booking){
				$verifyVehicle->push_booking_cloud($booking);
			}
            
        } catch (Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }
}
