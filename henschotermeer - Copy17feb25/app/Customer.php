<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Customer extends Model
{
    //
    use SoftDeletes;
    use Sortable;
    public $sortable = ['name', 'email', 'max_cars'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function user_list_users(){
        return $this->hasOne(UserlistUsers::class, 'customer_id', 'id');
    }
    
    public function profile(){
        return $this->hasOne(Profile::class, 'customer_id', 'id');
    }
    
    public function white_list_users(){
        return $this->hasOne(WhitelistUsers::class, 'customer_id', 'id');
    }

    public function bookings(){
        return $this->hasMany(Bookings::class, 'customer_id', 'id');
    }

    public function customer_vehicle_info(){
        return $this->hasOne(CustomerVehicleInfo::class, 'customer_id', 'id');
    }

    public function customer_vehicles(){
        return $this->hasMany(CustomerVehicleInfo::class, 'customer_id', 'id');
}
}
