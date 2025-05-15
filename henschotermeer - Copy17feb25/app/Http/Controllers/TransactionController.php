<?php

namespace App\Http\Controllers;

use App\Blog;
use App\BlogCategory;
use App\BlogComment;
use App\Tag;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;

class TransactionController extends Controller {

    public $controller = 'App\Http\Controllers\TransactionController';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Get Attendants Transactions Details
     * @param Request $request
     * @param type $type
     * @param type $id
     * @return type
     */
    function details(Request $request, $type, $id) {
        $data = array();
        if ($type == 1) {
            $attendant_transaction = \App\TransactionImages::find($id);
            if ($attendant_transaction) {
                $transaction_details = \App\AttendantTransactions::find($attendant_transaction->transaction_id);
                if (!$transaction_details) {
                    return redirect('/');
                }
                $attendants = \App\Attendants::find($transaction_details->attendant_id);
                if (!$attendants) {
                    return redirect('/');
                }
                $booking_details = \App\Bookings::find($attendants->booking_id);
                if (!$booking_details) {
                    return redirect('/');
                }
                $content = '';
                if (!empty($booking_details->vehicle_num)) {
                    $content = $booking_details->vehicle_num . ' - ' . date('d/m/Y H:i', strtotime($attendant_transaction->updated_at));
                } elseif (!empty($booking_details->first_name)) {
                    $content = $booking_details->first_name . ' ' . $booking_details->last_name . ' - ' . date('d/m/Y H:i', strtotime($attendant_transaction->updated_at));
                }
                $manual_transaction_details = FALSE;
               $manual_transaction = \App\OpenGateManualTransaction::where(
                        'attendant_transaction_id', $transaction_details->id
                        )
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($manual_transaction) {
                    $user_name = 'N/A';
                    $user = User::find($manual_transaction->user_id);
                    if ($user) {
                        $user_name = $user->name;
                    }
                    $manual_transaction_details = (object) array(
                                'name' => $user_name,
                                'reason' => $manual_transaction->reason,
                    );
                }
                $data = array(
                    'vehicle' => $booking_details->vehicle_num,
                    'name' => $booking_details->first_name . ' ' . $booking_details->last_name,
                    'booking_id' => $booking_details->id,
                    'id' => $attendant_transaction->id,
                    'type' => $attendant_transaction->type,
                    'content' => $content,
                    'manual_transaction_details' => $manual_transaction_details,
                    'image_path' => $attendant_transaction->image_path,
                    'time' => date('d/m/Y H:i', strtotime($attendant_transaction->updated_at)),
                );
            }
        } elseif ($type == 2) {
            $vehcile_payment_transaction = \App\TransactionPaymentVehicles::find($id);
            if ($vehcile_payment_transaction) {
                  $transaction_details = \App\AttendantTransactions::find($vehcile_payment_transaction->attendant_transaction_id);
                if (!$transaction_details) {
                    return redirect('/');
                }
                $attendants = \App\Attendants::find($transaction_details->attendant_id);
                if (!$attendants) {
                   return redirect('/');
                }
                $booking_details = \App\Bookings::find($attendants->booking_id);
                if (!$booking_details) {
                    return redirect('/');
                }
                $status = 'unknown';
                if ($vehcile_payment_transaction->status == 0) {
                    $status = 'Failed';
                } elseif ($vehcile_payment_transaction->status == 1) {
                    $status = 'Success';
                } elseif ($vehcile_payment_transaction->status == 2) {
                    $status = 'Unknown';
                }
                $content = '';
                if (!empty($booking_details->vehicle_num)) {
                    $content = $booking_details->vehicle_num . ' - ' . date('d/m/Y H:i', strtotime($vehcile_payment_transaction->updated_at)) . ' - ' . $status;
                } elseif (!empty($booking_details->first_name)) {
                    $content = $booking_details->first_name . ' ' . $booking_details->last_name . ' - ' . date('d/m/Y H:i', strtotime($vehcile_payment_transaction->updated_at)) . ' - ' . $status;
                }
                $data = array(
                    'vehicle' => $booking_details->vehicle_num,
                    'name' => $booking_details->first_name . ' ' . $booking_details->last_name,
                    'booking_id' => $booking_details->id,
                    'type' => $status,
                    'id' => $vehcile_payment_transaction->id,
                    'e_journal' => !empty($vehcile_payment_transaction->e_general) ? $vehcile_payment_transaction->e_general : 'N/A',
                    'content' => $content,
                    'amount' => $vehcile_payment_transaction->amount,
                    'time' => date('d/m/Y H:i', strtotime($vehcile_payment_transaction->updated_at)),
                );
            }
        } elseif ($type == 3) {
            $person_payment_transaction = \App\TransactionPaymentPersons::find($id);
            if ($person_payment_transaction) {
                $status = 'unknown';
                if ($person_payment_transaction->status == 0) {
                    $status = 'Failed';
                } elseif ($person_payment_transaction->status == 1) {
                    $status = 'Success';
                } elseif ($person_payment_transaction->status == 2) {
                    $status = 'Unknown';
                }
                $amount = 'N/A';
                $quantity = $person_payment_transaction->quantity;
                if (is_numeric($quantity)) {
                    $person_ticket_price = \App\Products::where('type', 'person_ticket')->first();
                    if ($person_ticket_price) {
                        $amount = $quantity * $person_ticket_price->price;
                    }
                }
                $content = $quantity . ' Persons - ' . date('d/m/Y H:i', strtotime($person_payment_transaction->updated_at)) . ' - ' . $status;
                $data = array(
                    'type' => $status,
                    'amount' => $amount,
                    'id' => $person_payment_transaction->id,
                    'content' => $content,
                    'e_journal' => !empty($person_payment_transaction->e_general) ? $person_payment_transaction->e_general : 'N/A',
                    'quantity' => $quantity,
                    'time' => date('d/m/Y H:i', strtotime($person_payment_transaction->updated_at)),
                );
            }
        } else {
            return redirect('/');
        }
        return view('transactions.details', [
            'type' => $type,
            'data' => (object) $data,
        ]);
    }

}
