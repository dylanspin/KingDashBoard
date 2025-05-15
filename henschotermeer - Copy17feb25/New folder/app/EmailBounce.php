<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class EmailBounce extends Model
{
    use Sortable;
    public $sortable = ['email', 'reason', 'created_at'];

    public function email_bounce_bookings() {
        return $this->hasMany(EmailBounceBooking::class, 'email_bounce_id', 'id');
    }
}
