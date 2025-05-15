<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionPaymentVehicles extends Model {

    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function location_devices() {
        return $this->belongsTo(LocationDevices::class, 'device_id');
    }

    public function bookings() {
        return $this->belongsTo(Bookings::class, 'booking_id');
    }
}
