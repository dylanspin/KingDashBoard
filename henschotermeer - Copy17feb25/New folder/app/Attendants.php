<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendants extends Model
{
    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function attendant_transactions(){
        return $this->hasMany(AttendantTransactions::class, 'attendant_id', 'id');
    }

    public function bookings()
    {
        return $this->belongsTo(Bookings::class, 'booking_id');
    }
}
