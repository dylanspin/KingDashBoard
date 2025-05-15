<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HandleMissedBookings extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:HandleMissedBookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle Bookings that missed somhow through SDL';

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
        $settings = new \App\Http\Controllers\Settings\Settings();
        $settings->handle_missed_bookings();
    }

}
