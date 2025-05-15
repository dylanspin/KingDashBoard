<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendantTransactions extends Model
{
    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function attendants()
    {
        return $this->belongsTo(Attendants::class, 'attendant_id');
    }

    public function transaction_images(){
        return $this->hasMany(TransactionImages::class, 'transaction_id', 'id');
    }
}
