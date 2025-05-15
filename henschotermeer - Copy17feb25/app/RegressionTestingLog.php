<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegressionTestingLog extends Model
{
    //
    public function ruleLog()
    {
        return $this->belongsTo(ParkingRulesName::class, 'rule_id');
    }
}
