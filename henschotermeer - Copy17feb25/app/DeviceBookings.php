<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class DeviceBookings extends Model {

    //
    use SoftDeletes,Sortable;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
	
	public function location_devices() {
        return $this->belongsTo(LocationDevices::class, 'device_id');
    }

}
