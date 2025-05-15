<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckOutAllPerson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CheckOutAllPerson';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set CheckOut time of all persons at midnight';

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
        $type = 6;
        $attendantTransactions = \App\AttendantTransactions::whereHas('attendants.bookings', 
                function ($query) use ($type) {
                    $query->where('type', $type);
                }
            )->with(['attendants.bookings' =>
                function ($query) use ($type) {
                    $query->where('type', $type);
                }
            ])->whereNotNull('check_in')
            ->whereNull('check_out')
            ->get();
        foreach ($attendantTransactions as $attendantTransaction) {
            $attendantTransaction->check_out = date('Y-m-d H:i:s');
			$attendantTransaction->auto_check_out = true;
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
