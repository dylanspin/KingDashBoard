<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Bookings;

class BarcodeJsonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public $booking = false;
    public $status = false;
    public $bookingText = [];
    public $data = [];
    public $res = null;
    public $barcode = false;
    public $history = null;
    public $device_name = "";

    public function __construct($status, $booking = null, $barcode = null)
    {

        $this->booking = $booking;
        $this->status = $status;
        $this->history = $this->gethistory();
        $this->device_name = collect($this->history->toArray())->sortByDesc('created_at')->first() ?? null;
        if ($this->status == "expired") {
            $this->bookingText['status'] = trans('device-authorize.ticket_expired');
            $this->bookingText['message'] = trans('device-authorize.can_not_checked_in');
            $this->res = 1;
        } elseif ($this->status == "valid") {
            $this->bookingText['status'] = trans('device-authorize.valid_ticket');
            $this->bookingText['message'] = trans('device-authorize.checked_in_first');
            $this->res = 6;
        } elseif ($this->status == "not_valid") {
            $this->bookingText['status'] = trans('device-authorize.not_valid');
            $this->bookingText['message'] = trans('device-authorize.not_valid_for_today');
            $this->res = 5;
        } else if ($this->status == "checked_in") {
            $this->bookingText['status'] = trans('device-authorize.checkin');
            $this->bookingText['message'] = trans('device-authorize.ticket_successfully_checked_in');
            $this->res = 2;
        } elseif ($this->status == "checked_out") {
            $this->bookingText['status'] = trans('device-authorize.checkout');
            $this->bookingText['message'] = trans('device-authorize.ticket_successfully_checked_out');
            $this->res = 3;
        } elseif ($this->status == "on_location") {
            $this->bookingText['status'] = trans('device-authorize.already_checked_in');
            $this->bookingText['message'] = trans('device-authorize.ticket_has_already_been_check_in');
            $this->res = 0;
            $this->data['device_name'] = $this->device_name['inGoingDevice_name'] ?? 'N/A';
        } elseif ($this->status == "already_checkout") {
            $this->bookingText['status'] = trans('device-authorize.already_checkout');
            $this->bookingText['message'] = trans('device-authorize.ticket_already_checkout');
            $this->res = 4;
            $this->data['device_name'] = $this->device_name['outGoingDevice_name'] ?? 'N/A';
        } elseif ($this->status == "in_valid") {
            $this->bookingText['status'] = trans('device-authorize.not_valid');
            $this->bookingText['message'] = trans('device-authorize.no_personal_access');
            $this->res = 7;
        } elseif ($this->status == "blocked") {
            $this->bookingText['status'] = trans('device-authorize.blocked');
            $this->bookingText['message'] = trans('device-authorize.block_person');
            $this->res = 8;
        }
        if (!empty($booking)) {
            if ($booking->first_name || $booking->last_name) {
                $this->data['name'] = $booking->first_name . ' ' . $booking->last_name;
            } else {
                $this->data['name'] = 'N/A';
            }
            $this->data['dob'] = $booking->tommy_children_dob ? $booking->tommy_children_dob : 'N/A';
            $this->data['checkin_time'] = $booking->checkin_time ? $booking->checkin_time : "";
            $this->data['checkout_time'] = $booking->checkout_time ?  $booking->checkout_time : "";
            if ($booking->checkout_time == date('Y-12-31 23:59:59')) {
                $this->data['subscription'] = true;
            }
        } else if (!empty($barcode)) {
            $this->data['name'] = $barcode->name ? $barcode->name : 'n/a';
            $this->data['dob'] = null;
            $this->data['checkin_time'] = "";
            $this->data['checkout_time'] = "";
        }
    }
    public function toArray($request)
    {
        return [
            'data' => $this->data,
            'history' => $this->history,
            'response' => $this->res,
            'status' => $this->bookingText['status'],
            'message' => $this->bookingText['message']
        ];
    }
    public function gethistory()
    {
        if (!empty($this->booking) && $this->booking->attendant_transactions->isNotEmpty()) {
            $attendantTransactions = $this->booking->attendant_transactions;
        } else {
            $booking = Bookings::with([
                'attendant_transactions.transaction_images.location_device'
            ])->where('id', $this->booking->id)->orderBy('created_at', 'Desc')->first();

            $attendantTransactions = $booking->attendant_transactions ?? collect();
        }
        $data = $attendantTransactions->map(function ($transaction) {
            $transactionImages = $transaction->transaction_images ?? collect();
            $inGoingImage = $transactionImages->firstWhere('type', 'in');
            $outGoingImage = $transactionImages->firstWhere('type', 'out');
            $transaction->inGoingDevice_name = $inGoingImage->location_device->device_name ?? null;
            $transaction->outGoingDevice_name = $outGoingImage->location_device->device_name ?? null;
            unset($transaction->transaction_images);
            return $transaction;
        });
        return $data;
    }
}
