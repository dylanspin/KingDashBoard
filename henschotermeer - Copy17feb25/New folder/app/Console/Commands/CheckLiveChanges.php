<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckLiveChanges extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CheckLiveChanges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for live changes';

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
        $import_settings = new \App\Http\Controllers\Settings\ImportLivetSetting();
        $import_settings->importDetails();
    }

}
