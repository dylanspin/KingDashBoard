<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function promo_type()
    {
        return $this->belongsTo('App\PromoType', 'promo_type_id', 'id');
    }
    
    public function bookings(){
        return $this->hasMany(Bookings::class, 'promo_code', 'code');
    }
}
