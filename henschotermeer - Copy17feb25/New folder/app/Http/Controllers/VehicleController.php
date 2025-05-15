<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VehicleController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function details($id) {
        $bookingInfo = \App\Bookings::with(
                            'customer.profile', 
                            'booking_payments', 
                            'attendants.attendant_transactions.transaction_images', 
                            'customer_vehicle_info.customer.profile'
                        )
                        ->where('id', '=', $id)
                        ->first();
        $bookingDetails = array();
        if($bookingInfo){
            if($bookingInfo->type == 6){
                $bookingDetails[] = (object) $bookingInfo;
            }
            else{
                if (!empty($bookingInfo->customer_vehicle_info_id)) {
                    $bookings = \App\Bookings::with(
                                'customer.profile', 
                                'booking_payments', 
                                'attendants.attendant_transactions.transaction_images', 
                                'customer_vehicle_info.customer.profile'
                            )
                            ->orderBy('checkin_time', 'desc')
                            ->where('customer_vehicle_info_id', '=', $bookingInfo->customer_vehicle_info_id)
                            ->limit(5)
                            ->get();
                }
                else{
                    $bookings = \App\Bookings::with(
                                'customer.profile', 
                                'booking_payments', 
                                'attendants.attendant_transactions.transaction_images', 
                                'customer_vehicle_info.customer.profile'
                            )
                            ->orderBy('checkin_time', 'desc')
                            ->where('vehicle_num', '=', $bookingInfo->vehicle_num)
                            ->limit(5)
                            ->get();
                }
                foreach($bookings as $booking){
                    $bookingDetails[] = (object) $booking;
                }
            }
            if (!empty($bookingInfo->customer_vehicle_info_id)) {
                $userTotalBookings = \App\Bookings::where('customer_vehicle_info_id', '=', $bookingInfo->customer_vehicle_info_id)
                        ->where('type', '<>', 6)
                        ->count();
            } else {
                $userTotalBookings = \App\Bookings::where('vehicle_num', '=', $bookingInfo->vehicle_num)
                        ->where('type', '<>', 6)
                        ->count();
            }
        }
//        $totalAmount = \App\BookingPayments::where('customer_id', '=', $bookingDetails->customer_id)->sum('amount');
        return view('vehicle.index', compact('bookingInfo', 'bookingDetails', 'userTotalBookings'));
    }

    public function details_p($id) {
        $bookingInfo = \App\Bookings::with(
                            'customer.profile', 
                            'booking_payments', 
                            'attendants.attendant_transactions.transaction_images', 
                            'customer_vehicle_info.customer.profile'
                        )
                        ->where('id', '=', $id)
                        ->first();
        $bookingDetails = array();
        if($bookingInfo){
            if($bookingInfo->type == 6){
                $bookingDetails[] = (object) $bookingInfo;
            }
            $userTotalBookings = \App\Bookings::where('first_name', '=', $bookingInfo->first_name)
                    ->where('type', 6)
                    ->count();
        }
//        $totalAmount = \App\BookingPayments::where('customer_id', '=', $bookingDetails->customer_id)->sum('amount');
        return view('person.index', compact('bookingInfo', 'bookingDetails', 'userTotalBookings'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

}
