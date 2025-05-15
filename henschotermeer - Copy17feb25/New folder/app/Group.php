<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    //
    public function group_devices(){
        return $this->hasMany(GroupDevices::class, 'group_id', 'id');
    }
    
    public function userlist_users(){
        return $this->hasMany(UserlistUsers::class, 'group_id', 'id');
    }
    
    public function whitelist_users(){
        return $this->hasMany(WhitelistUsers::class, 'group_id', 'id');
    }
}
