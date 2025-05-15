<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Bookings extends Model {

    //
    use SoftDeletes;
    use Sortable;
    public $sortable = ['first_name', 'last_name', 'email', 'checkin_time', 'checkout_time'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function booking_payments() {
        return $this->hasOne(BookingPayments::class, 'booking_id', 'id');
    }

    public function attendants() {
        return $this->hasOne(Attendants::class, 'booking_id', 'id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function customer_vehicle_info() {
        return $this->belongsTo(CustomerVehicleInfo::class, 'customer_vehicle_info_id');
    }

    public function transaction_payment_vehicles() {
        return $this->hasMany(TransactionPaymentVehicles::class, 'booking_id', 'id');
    }

    public function promo() {
        return $this->belongsTo(Promo::class, 'promo_code', 'code');
    }

    public function barcode() {
        return $this->belongsTo(Barcode::class, 'barcode', 'barcode');
    }

    public function attendant_transactions() {
        return $this->hasManyThrough('App\AttendantTransactions', 'App\Attendants', 'booking_id', 'attendant_id');
    }

    public function email_bounce_bookings() {
        return $this->hasMany(EmailBounceBooking::class, 'booking_id', 'id');
    }

}
