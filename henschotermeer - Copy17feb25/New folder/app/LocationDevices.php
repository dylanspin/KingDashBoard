<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationDevices extends Model {

    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function available_devices() {
        return $this->belongsTo(AvailableDevices::class, 'available_device_id');
    }

    public function device_settings() {
        return $this->belongsTo(DeviceSettings::class, 'device_id');
    }
	

    public function transaction_payment_persons() {
        return $this->hasMany(TransactionPaymentPersons::class, 'device_id', 'id');
    }

    public function transaction_payment_vehicles() {
        return $this->hasMany(TransactionPaymentVehicles::class, 'device_id', 'id');
    }

    public function device_alerts() {
        return $this->hasMany(DeviceAlerts::class, 'location_device_id', 'id');
    }

    public function device_bookings() {
        return $this->hasMany(DeviceBookings::class, 'device_id', 'id');
    }

}
