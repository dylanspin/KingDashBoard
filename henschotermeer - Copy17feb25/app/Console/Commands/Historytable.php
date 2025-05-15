<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Bookings;
use Carbon\Carbon;

class HistoryTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:createhistory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy specific bookings to the history table and delete them from the bookings table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
    DB::disableQueryLog();

    $bookings = Bookings::where(function ($query) {
        $query->whereNotNull('tommy_parent_id')
              ->orWhere('type', '4');
    })
    ->where('checkout_time', '<', Carbon::today())
    ->orderBy('id')
    ->cursor(); // Use cursor instead of chunkById

    $dataToInsert = [];
    $bookingIds = [];

    foreach ($bookings as $booking) {
        $dataToInsert[] = [
            'live_id'        => $booking->live_id,
            'customer_id'    => $booking->customer_id,
            'type'           => $booking->type,
            'checkin_time'   => $booking->checkin_time,
            'checkout_time'  => $booking->checkout_time,
            'vehicle_num'    => $booking->vehicle_num,
            'first_name'     => $booking->first_name,
            'last_name'      => $booking->last_name,
            'is_tommy_online' => $booking->is_tommy_online,
            'tommy_parent_id'=> $booking->tommy_parent_id,
            'tommy_children_id'=> $booking->tommy_childeren_id,
            'tommy_children_dob' => $booking->tommy_children_dob,
            'booking_type'   => $booking->tommy_parent_id ? 'tommy_booking' : 'normal_booking',
        ];

        $bookingIds[] = $booking->id;

        // Insert and delete in small batches (e.g., every 500 records)
        if (count($dataToInsert) >= 500) {
            DB::transaction(function () use (&$dataToInsert, &$bookingIds) {
                DB::table('booking_histories')->insert($dataToInsert);
                Bookings::whereIn('id', $bookingIds)->delete();
            });

            $this->info(count($bookingIds) . ' records moved to history and deleted from bookings.');

            // Reset arrays for next batch
            $dataToInsert = [];
            $bookingIds = [];
        }
    }

    // Final batch insert if there are remaining records
    if (!empty($dataToInsert)) {
        DB::transaction(function () use ($dataToInsert, $bookingIds) {
            DB::table('booking_histories')->insert($dataToInsert);
            Bookings::whereIn('id', $bookingIds)->delete();
        });

        $this->info(count($bookingIds) . ' records moved to history and deleted from bookings.');
    }

    $this->info('All applicable bookings have been transferred to the history table.');
} catch (\Exception $e) {
    $this->error('An error occurred: ' . $e->getMessage());
    \Log::error('HistoryTable Command Error: ' . $e->getMessage());
}


        return 0;
    }
}
