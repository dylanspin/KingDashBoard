<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserlistUsers extends Model
{
    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function customer_vehicle_info(){
        return $this->hasMany(CustomerVehicleInfo::class, 'userlist_user_id', 'id');
    }

     public function group_access() {
        return $this->belongsTo(GroupAccess::class, 'group_access_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
