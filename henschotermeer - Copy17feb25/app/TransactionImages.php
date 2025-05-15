<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionImages extends Model
{
    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function attendant_transactions()
    {
        return $this->belongsTo(AttendantTransactions::class, 'transaction_id');
    }
    public function location_device()
    {
        return $this->belongsTo(LocationDevices::class, 'device_id');
    }
}