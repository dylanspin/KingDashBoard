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

class BookingController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    function details($id) {
        return redirect('/vehicle/' . $id);
        $bookingDetails = \App\Bookings::with(
                        'booking_payments', 'attendants.attendant_transactions.transaction_images', 'customer.profile'
                )->where('id', '=', $id)->first();
        $userTotalBookings = \App\Bookings::where('customer_id', '=', $bookingDetails->customer_id)->count();
        $totalAmount = \App\BookingPayments::where('customer_id', '=', $bookingDetails->customer_id)->sum('amount');
        return view('bookings.index', compact('bookingDetails', 'userTotalBookings', 'totalAmount'));
    }

    public function change_booking_details($id, $data) {
        $bookingDetails = \App\Bookings::where([
                    ['customer_id', $id]
                ])->get();
        if ($bookingDetails) {
            $name_array = explode(' ', $data['name']);
            foreach ($bookingDetails as $bookingDetail) {
                if ($bookingDetail->type == 3) {
                    continue;
                }
                $bookings = \App\Bookings::find($bookingDetail->id);
                if ($bookings) {
                    if (count($name_array) > 0) {
                        if (count($name_array) == 1) {
                            $bookings->first_name = $name_array[0];
                        } else {
                            $bookings->first_name = $name_array[0];
                            $bookings->last_name = $name_array[1];
                        }
                    }
                    $bookings->save();
                }
            }
        }
        return TRUE;
    }

    function widget1_details() {
        $group_bookings = array();
        $promos = \App\Promo::with('promo_type')->get();
        foreach ($promos as $promo) {
            $bookings = \App\Bookings::where('promo_code', $promo->code)
                    ->orderBy('checkin_time', 'desc')
                    ->whereDate('checkin_time', '<=', Carbon::today())
                    ->whereDate('checkout_time', '>=', Carbon::today())
                    ->whereNotIn('type', [6, 7])
                    ->get();
            if (count($bookings) > 0) {
                $validity = 'Unlimited';
                if ($promo->end_date != Null && $promo->coupon_number_limit == Null) {
                    $validity = date('d M Y', strtotime($promo->start_date)) . ' - ' . date('d M Y', strtotime($promo->end_date));
                } else if ($promo->end_date == Null && $promo->coupon_number_limit != Null) {
                    $validity = $promo->coupon_number_limit - $promo->coupon_used . " Coupons Remaining";
                }
                $group_bookings[] = (object) array(
                            'code' => $promo->code ? $promo->code : 'N/A',
                            'type' => $promo->promo_type->title ? $promo->promo_type->title : 'N/A',
                            'validity' => $validity,
                            'bookings' => $bookings
                );
            }
        }

        $expected_bookings = \App\Bookings::whereNull('promo_code')
                ->orderBy('checkin_time', 'desc')
                ->whereDate('checkin_time', '<=', Carbon::today())
                ->whereDate('checkout_time', '>=', Carbon::today())
                ->whereNotIn('type', [6, 7])
                ->get();

        $today_entered_booking_unexpected = \App\Bookings::whereNull('promo_code')
                ->orderBy('checkin_time', 'desc')
                ->whereDate('checkin_time', '<=', Carbon::today())
                ->whereDate('checkout_time', '>=', Carbon::today())
                ->whereNotIn('type', [6, 7])
                ->get();

        $today_on_location_bookings_unexpected = \App\Bookings::whereNull('promo_code')
                ->orderBy('checkin_time', 'desc')
                ->whereDate('checkin_time', '<=', Carbon::today())
                ->whereNull('checkout_time')
                ->whereNotIn('type', [6, 7])
                ->get();

        return view('widgets.widget1', compact(
                        'group_bookings',
                        'expected_bookings',
                        'today_entered_booking_unexpected',
                        'today_on_location_bookings_unexpected'
        ));
    }

    function widget2_details() {
        $bookings = \App\Bookings::whereDate('checkin_time', '<=', Carbon::today())
                ->whereDate('checkout_time', '>=', Carbon::today())
                ->whereNotIn('type', [6, 7, 2, 3])
                ->get();

        $bookings_unexpected = \App\Bookings::whereDate('checkin_time', '<=', Carbon::today())
                ->whereNULL('checkout_time')
                ->whereNotIn('type', [6, 7, 2, 3])
                ->get();

        $expected_bookings = array();
        $on_location = array();
        $arrivals = array();

        if ($bookings->count() > 0) {
            foreach ($bookings as $booking) {
                $attendant_details = \App\Attendants::where('booking_id', $booking->id)->first();
                if ($attendant_details) {
                    $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                    if ($transaction) {
                        if ($transaction->check_in != NULL && $transaction->check_out == NULL) {
                            $on_location[] = $booking;
                        } elseif ($transaction->check_in != NULL && $transaction->check_out != NULL) {
                            $arrivals[] = $booking;
                        }
                    }
                } else {
                    $expected_bookings[] = $booking;
                }
            }
        }

        if ($bookings_unexpected->count() > 0) {
            foreach ($bookings_unexpected as $booking) {
                $attendant_details = \App\Attendants::where('booking_id', $booking->id)->first();
                if ($attendant_details) {
                    $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                    if ($transaction) {
                        $booking->checkin_time = $transaction->check_in;
                        $booking->checkout_time = $transaction->check_out;
                        if ($transaction->check_in != NULL && $transaction->check_out == NULL) {
                            $on_location[] = $booking;
                        } elseif ($transaction->check_in != NULL && $transaction->check_out != NULL) {
                            $arrivals[] = $booking;
                        }
                    }
                }
            }
        }

        $bookings_userlist = \App\Bookings::whereIn('type', [2, 3])
                ->get();

        if ($bookings_userlist->count() > 0) {
            foreach ($bookings_userlist as $booking) {
                if ($booking->type == 3) {
                    $vehicle_num = $booking->vehicle_num;
                    if ($vehicle_num != NULL) {
                        $user_vehicle_details = \App\CustomerVehicleInfo::where('num_plate', $vehicle_num)->orderBy('created_at', 'DESC')->first();
                        if (!$user_vehicle_details) {
                            continue;
                        }
                        $userlist = \App\UserlistUsers::where('user_vehicle', $user_vehicle_details->id)->orderBy('created_at', 'DESC')->first();
                        if (!$userlist) {
                            continue;
                        }
                    }
                } elseif ($booking->type == 2) {

                    $whitelist_user = \App\WhitelistUsers::where('customer_id', $booking->customer_id)->orderBy('created_at', 'DESC')->first();
                    if (!$whitelist_user) {
                        continue;
                    }
                } else {
                    continue;
                }
                $attendant_details = \App\Attendants::where('booking_id', $booking->id)->first();
                if ($attendant_details) {
                    $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                    if ($transaction) {
                        if ($transaction->check_in != NULL && $transaction->check_out == NULL) {
//                            $total = $total + 1;
                            $on_location[] = $booking;
                        } elseif ($transaction->check_in != NULL && $transaction->check_out != NULL) {
                            if (date('Y-m-d', strtotime($transaction->check_out)) == date('Y-m-d')) {
//                                $total = $total + 1;
                                $arrivals[] = $booking;
                                $expected_bookings[] = $booking;
                            } else {
//                                $total = $total + 1;
                                $expected_bookings[] = $booking;
                            }
                        }
                    }
                } else {
//                    $total = $total + 1;
                    $expected_bookings[] = $booking;
                }
            }
        }
        if (isset($request->developer_test) && $request->developer_test == 'ABCXYZ') {

            exit;
        }

        return view('widgets.widget2', compact(
                        'expected_bookings', 'on_location', 'arrivals'
        ));
    }

    function widget2_details_1() {
        $bookings = \App\Bookings::whereDate('checkin_time', '<=', Carbon::today())
                ->whereDate('checkout_time', '>=', Carbon::today())
                ->whereNotIn('type', [6, 7])
                ->get();

//        $bookings_unexpected = \App\Bookings::whereDate('checkin_time', '<=', Carbon::today())
//                ->whereNULL('checkout_time')
//                ->whereNotIn('type', [6, 7])
//                ->get();

        $expected_bookings = array();
        $on_location = array();
        $arrivals = array();

        if ($bookings->count() > 0) {
            foreach ($bookings as $booking) {
                if ($booking->checkout_time == NULL) {
                    $attendant_details = \App\Attendants::where('booking_id', $booking->id)->first();
                    if ($attendant_details) {
                        $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                        if ($transaction) {
                            if ($transaction->check_in != NULL && $transaction->check_out == NULL) {
                                $on_location[$booking->id] = $booking;
                            } elseif ($transaction->check_in != NULL && $transaction->check_out != NULL) {
                                $arrivals[$booking->id] = $booking;
                            }
                        }
                    }
                } else {
                    $attendant_details = \App\Attendants::where('booking_id', $booking->id)->first();
                    if ($attendant_details) {
                        $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
                        if ($transaction) {
                            if ($transaction->check_in != NULL && $transaction->check_out == NULL) {
                                $on_location[$booking->id] = $booking;
                            } elseif ($transaction->check_in != NULL && $transaction->check_out != NULL) {
                                $arrivals[$booking->id] = $booking;
                            }
                        }
                    } else {
                        if (strtotime($booking->checkout_time) >= strtotime(Carbon::today())) {
                            $expected_bookings[$booking->id] = $booking;
                        }
                    }
                }
            }
        }

//        if ($bookings->count() > 0) {
//            foreach ($bookings as $booking) {
//                $attendant_details = \App\Attendants::where('booking_id', $booking->id)->first();
//                if ($attendant_details) {
//                    $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
//                    if ($transaction) {
//                        if ($transaction->check_in != NULL && $transaction->check_out == NULL) {
//                            $on_location[$booking->id] = $booking;
//                        } 
//                        elseif ($transaction->check_in != NULL && $transaction->check_out != NULL) {
//                            $arrivals[$booking->id] = $booking;
//                        }
//                    }
//                } else {
//                    if(strtotime($booking->checkout_time) >= strtotime(Carbon::today())){
//                        $expected_bookings[$booking->id] = $booking;
//                    }
//                }
//            }
//        }
//        
//        if ($bookings_unexpected->count() > 0) {
//            foreach ($bookings_unexpected as $booking) {
//                $attendant_details = \App\Attendants::where('booking_id', $booking->id)->first();
//                if ($attendant_details) {
//                    $transaction = \App\AttendantTransactions::where('attendant_id', $attendant_details->id)->orderBy('check_in', 'DESC')->first();
//                    if ($transaction) {
//                        $booking->checkin_time = $transaction->check_in;
//                        $booking->checkout_time = $transaction->check_out;
//                        if ($transaction->check_in != NULL && $transaction->check_out == NULL) {
//                            $on_location[$booking->id] = $booking;
//                        } 
//                        elseif ($transaction->check_in != NULL && $transaction->check_out != NULL) {
//                            $arrivals[$booking->id] = $booking;
//                        }
//                    }
//                }
//            }
//        }

        return view('widgets.widget2', compact(
                        'expected_bookings', 'on_location', 'arrivals'
        ));
    }

    function widget3_details() {
        $device_alerts = \App\DeviceAlerts::with('location_devices')
                ->orderBy('created_at', 'desc')
                ->orderBy('status', 'asc')
                ->whereNotIn('status', [1])
                ->get();

        return view('widgets.widget3', compact(
                        'device_alerts'
        ));
    }

}
