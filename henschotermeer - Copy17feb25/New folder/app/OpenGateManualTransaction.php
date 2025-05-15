<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpenGateManualTransaction extends Model {

    //
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function users() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction_view() {
        return $this->belongsTo(TransactionView::class, 'attendant_transaction_id', 'attendant_transaction_id');
    }

    public function attendant_transactions() {
        return $this->belongsTo(AttendantTransactions::class, 'attendant_transaction_id');
    }

    public function location_devices() {
        return $this->belongsTo(LocationDevices::class, 'location_device_id');
    }

}
