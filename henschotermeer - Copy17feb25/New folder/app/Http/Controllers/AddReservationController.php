<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use paginate;
use Illuminate\Support\Facades\Validator;
class AddReservationController extends Controller
{
 public function index(Request $request)
 {
	$search_type = '';
	$booking_ref = '';
	$search_filter = '';
    $search_val = '';
	$bookingDetails = \App\Bookings::sortable();
	if(!isset($request->reset_btn)){
		if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))){
			$search_type = $request->search_type;
			$search_val = $request->search_val;
			if($request->search_type == 'license_plate'){
				$bookingDetails = $bookingDetails->where('vehicle_num', 'LIKE', "%{$request->search_val}%");
				 
			}
			if($request->search_type == 'name'){
				$bookingDetails = $bookingDetails->where('first_name', 'LIKE', "%{$request->search_val}%");
			}
		}
	}
    $bookingDetails = $bookingDetails->where('type', '=', 10)->where('checkout_time', '>', date('Y-m-d H:i'))->orderBy('id' ,'desc')->paginate(25);
     return view('reservations.addreservation', compact('bookingDetails', 'search_type', 'search_val','search_filter','booking_ref'));
    
 }

public function store(Request $request)
 {
    try{
        $validation = Validator::make($request->all(), 
            [
                'name' => 'required|max:25',
                'plate' => 'required',
                'check_in' =>'required',
                'check_out' =>'required',
            ],

            [
                'name.required' => @trans('reservations.individual'),
                'plate.required' => @trans('reservations.license'),
                'check_in.required' => @trans('reservations.check_in'),
                'check_out.required' => @trans('reservations.check_out'),
            ]

        );
            if ($validation->fails()) {
                $errors = array();
                if(count($errors) > 0){
                    $errors[] = $errors->first('name');
                    $errors[] = $errors->first('plate');
                }
                 return response()->json(['errors'=>$validation->errors()]);
            }
   
        $name = $request->name;
       
        $plate = $request->plate;
         $trimed_plate_number = str_replace(array(' ', '-', '\'', '"', ',', ';', '<', '>'), '', $plate);
        $check_in = date('Y-m-d 00:00:00', strtotime($request->check_in));
        $check_out = date('Y-m-d 23:59:59', strtotime($request->check_out));
        //Creating Object of Booking Class
        if($request->id > 0){
            $Booking = \App\Bookings::find($request->id);
        } else {
        $Booking = new \App\Bookings();
        }
        //Inserting Data into Booking Class
        $Booking->first_name = $name;
        $Booking->vehicle_num= $trimed_plate_number;
        $Booking->type= 10;
		$Booking->is_paid= 1;
        $Booking->checkin_time = $check_in;
        $Booking->checkout_time = $check_out;
        //Saving data into Booking 
        if($request->id > 0){
        $Booking->update();
        }else{
            $Booking->save();
        }
        //Check weather data is stored or not
        if($Booking){
            return response()->json(['success'=>__('reservations.win')]);
        }else{
            return response()->json(['error'=>'Internal Server Error']);

        }
    } catch(\Exception $ex){
        return response()->json(['error'=>$ex->getMessage()]);
    }
    
        
 }

 public function edit($booking_id)
 {
   $work= \App\Bookings::find($booking_id);
    return response()->json($work);
 
 }

 public function destroy($id){
    \App\Bookings::find($id)->forceDelete();
    Session::flash('heading', __('reservations.sucessfull'));
    Session::flash('message', __('reservations.sucess'));
    Session::flash('icon', __('reservations.sucessfull'));
    return redirect()->back()->with('success', 'Deleted Successfully');  
}
}
