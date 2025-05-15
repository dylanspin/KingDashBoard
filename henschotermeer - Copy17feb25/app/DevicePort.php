<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DevicePort extends Model
{
    //

    public function related_switch_device()
    {
        return $this->belongsTo(LocationDevices::class, 'device_id');
    }
}