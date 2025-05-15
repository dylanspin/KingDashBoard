<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailBounceBooking extends Model
{
    public function booking() {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
    
    public function email_bounce() {
        return $this->belongsTo(Booking::class, 'email_bounce_id');
    }
}
