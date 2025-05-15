<?php

namespace App\Console;

use App\Console\Commands\RefreshSite;
use App\Console\Commands\InitializeDevice;
use App\Console\Commands\CheckLiveChanges;
use App\Console\Commands\KeepAliveDevicesAtBackground;
use App\Console\Commands\ImportOtherData;
use App\Console\Commands\CheckLiveBookingChanges;
use App\Console\Commands\PushLiveChanges;
use App\Console\Commands\CheckOutAllVehicles;
use App\Console\Commands\CheckOutAllPerson;
use App\Console\Commands\CheckLiveEmailBounceChanges;
use App\Console\Commands\UpdateBookingsStatistics;
use App\Console\Commands\HandleMissedBookings;
use App\Console\Commands\SetParkingCloseEnable;
use App\Console\Commands\HistoryTable;
use Appzcoder\CrudGenerator\Commands\CrudCommand;
use Appzcoder\CrudGenerator\Commands\CrudControllerCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CrudCommand::class,
        CrudControllerCommand::class,
        RefreshSite::class,
        InitializeDevice::class,
        CheckLiveChanges::class,
        PushLiveChanges::class,
        KeepAliveDevicesAtBackground::class,
        ImportOtherData::class,
        CheckLiveBookingChanges::class,
        CheckOutAllVehicles::class,
        CheckOutAllPerson::class,
        HandleMissedBookings::class,
        CheckLiveEmailBounceChanges::class,
        UpdateBookingsStatistics::class,
		SetParkingCloseEnable::class,
		HistoryTable::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
              $schedule->command(CheckLiveBookingChanges::class)
                ->everyMinute();
        $schedule->command(KeepAliveDevicesAtBackground::class)
                ->everyFiveMinutes();
        //$schedule->command(ImportOtherData::class)
          //      ->everyThirtyMinutes();
        $schedule->command(CheckOutAllVehicles::class)
                ->daily();
        $schedule->command(CheckOutAllPerson::class)
                ->daily();
        $schedule->command(CheckLiveEmailBounceChanges::class)
                ->everyMinute();
        $schedule->command(UpdateBookingsStatistics::class)
                ->everyMinute();
		$schedule->command(SetParkingCloseEnable::class)
				->everyMinute();
		$schedule->command(HistoryTable::class)
                ->dailyAt("02:00");
//        $schedule->command(HandleMissedBookings::class)
//                ->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}
