<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateBookingsStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateBookingsStatistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Method for reporting current booking statistics';

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
        $statisticsBookings = new \App\Http\Controllers\Settings\ImportLivetSetting();
        $statisticsBookings->get_booking_statistics();
    }
}
