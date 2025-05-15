<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ImportBookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Bookings From API';

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
        $importBookings = new \App\Http\Controllers\BookingController();
        $importBookings->import_bookings();
    }
}
