<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TommyReservationChildrens extends Model
{
    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function tommy_reservation_parents()
    {
        return $this->belongsTo(TommyReservationParents::class, 'tommy_reservation_parent_id');
    }
}
