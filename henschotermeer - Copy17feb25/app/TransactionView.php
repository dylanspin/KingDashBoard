<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class TransactionView extends Model {

    use Sortable;

    protected $table = 'TransactionView';
    public $sortable = ['first_name', 'vehicle_num', 'type', 'check_in','check_out'];

	public function open_gate_manual_transaction() {
        return $this->hasMany(OpenGateManualTransaction::class, 'attendant_transaction_id', 'attendant_transaction_id');
    }
}
