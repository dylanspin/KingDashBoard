<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerVehicleInfo extends Model
{
    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function user_list_users()
    {
        return $this->belongsTo(UserlistUsers::class, 'userlist_user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function bookings(){
        return $this->hasMany(Bookings::class, 'customer_vehicle_info_id', 'id');
    }
}
