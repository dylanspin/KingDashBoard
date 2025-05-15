<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    //
    public function ruleLog()
    {
        return $this->belongsTo(ParkingRulesName::class, 'rule_id');
    }
}
