<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessType extends Model {

    public function group_access() {
        return $this->hasMany(GroupAccess::class);
    }

}
