<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ParkingRulesName extends Model
{
    //
    public function access()
    {
        return $this->hasOne(ParkingAccessRule::class, 'rule_id')->orderBy('enable', 'asc');;
    }
}
