<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PushLiveChanges extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PushLiveChanges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push local changes to live';

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
        $push_changes = new \App\Http\Controllers\Settings\PushChangesLive();
        $push_changes->push_whitelist();
        $push_changes->push_userlist();
        $push_changes->push_devices();
        $push_changes->push_location_data();
    }

}
