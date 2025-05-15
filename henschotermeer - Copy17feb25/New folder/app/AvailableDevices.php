<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AvailableDevices extends Model
{
    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function location_devices(){
        return $this->hasMany(LocationDevices::class, 'available_device_id', 'id');
    }
    
    public function group_devices(){
        return $this->hasMany(GroupDevices::class, 'device_id', 'id');
    }
}
