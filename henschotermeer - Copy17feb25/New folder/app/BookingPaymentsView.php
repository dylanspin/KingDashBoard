<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class BookingPaymentsView extends Model {

    use Sortable;

    protected $table = 'BookingPaymentsView';
    public $sortable = ['first_name', 'vehicle_num','amount', 'type', 'check_in', 'check_out', 'is_online'];

}
