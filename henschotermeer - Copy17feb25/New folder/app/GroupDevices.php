<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupDevices extends Model
{
    //
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    
    public function available_devices()
    {
        return $this->belongsTo(AvailableDevices::class, 'device_id');
    }
}
