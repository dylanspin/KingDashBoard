<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckLiveEmailBounceChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CheckLiveEmailBounceChanges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update data of email bounces';

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
        $locationOption = \App\LocationOptions::find(1);
        $locationId = $locationOption->live_id;        
        $http = new \GuzzleHttp\Client();
        $response = $http->post(env('API_BASE_URL').'/api/get-bounce-emails', [
            'form_params' => [
                'location_id' => $locationId
            ],
        ]);
        $responseData = json_decode((string) $response->getBody(), true);
        if ($responseData['success']) {
            foreach($responseData['data'] as $bounce_data){
                $bounce_email = new \App\EmailBounce();
                $bounce_email->live_id = $bounce_data['id'];
                $bounce_email->email = $bounce_data['email'];
                $bounce_email->reason = $bounce_data['reason'];
                $bounce_email->description = $bounce_data['description'];
                $bounce_email->save();
                
                $bookings = explode(',', $bounce_data['booking_id']);
                foreach($bookings as $booking){
                    $find_booking = \App\Bookings::where('live_id', $booking)->first();
                    if($find_booking){
                        $email_bounce_booking = new \App\EmailBounceBooking();
                        $email_bounce_booking->booking_id = $find_booking->id;
                        $email_bounce_booking->email_bounce_id = $bounce_email->id;
                        $email_bounce_booking->save();
                    }
                }
            }
        }
    }
}
