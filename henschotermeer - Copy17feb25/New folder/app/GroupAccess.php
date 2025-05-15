<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupAccess extends Model {

    public function access_type() {
        return $this->belongsTo(AccessType::class);
    }
    public function userlist_users() {
        return $this->hasMany(UserlistUsers::class, 'group_access_id', 'id');
    }

}
