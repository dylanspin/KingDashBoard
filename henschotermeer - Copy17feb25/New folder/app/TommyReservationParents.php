<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class TommyReservationParents extends Model
{
    //
    use SoftDeletes;
    use Sortable;
    public $sortable = ['email', 'date_of_arrival', 'date_of_departure', 'license_plate'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function tommy_reservation_childrens(){
        return $this->hasMany(TommyReservationChildrens::class, 'tommy_reservation_parent_id', 'id');
    }
}
