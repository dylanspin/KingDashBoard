<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model {

    public function customer() {
        return $this->belongsTo(Customer::class, 'id');
    }

}
