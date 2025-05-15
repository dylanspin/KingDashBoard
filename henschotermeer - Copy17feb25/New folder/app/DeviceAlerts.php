<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceAlerts extends Model
{
    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function location_devices() {
        return $this->belongsTo(LocationDevices::class, 'location_device_id');
    }

    public function error_logs() {
        return $this->belongsTo(ErrorLogs::class, 'error_log_id');
    }
}
