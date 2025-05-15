<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckOutAllVehicles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CheckOutAllVehicles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set CheckOut time of all vehicles at midnight';

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
        $type = [1, 2, 3, 4, 5, 7, 8];
        $attendantTransactions = \App\AttendantTransactions::whereHas('attendants.bookings', 
                function ($query) use ($type) {
                    $query->whereIn('type', $type);
                }
            )->with(['attendants.bookings' =>
                function ($query) use ($type) {
                    $query->whereIn('type', $type);
                }
            ])->whereNotNull('check_in')
            ->whereNull('check_out')
            ->get();
        foreach ($attendantTransactions as $attendantTransaction) {
            $attendantTransaction->check_out = date('Y-m-d H:i:s');
            $attendantTransaction->save();
                
            if(isset($attendantTransaction->attendants->bookings) && 
                $attendantTransaction->attendants->bookings->checkout_time == NULL){
                $attendantTransaction->attendants->bookings->checkout_time = date('Y-m-d H:i:s');
                $attendantTransaction->attendants->bookings->save();
            }
        }
        return;
    }
}
