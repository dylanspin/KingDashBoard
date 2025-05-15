<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;
use paginate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Bookings;
use App\Barcode;

class PersonManualReservationController extends Controller
{
    //
    public function index(Request $request)
 {
    // 
	$search_type = '';
	$booking_ref = '';
	$search_filter = '';
    $search_val = '';
	$bookingDetails = \App\Bookings::sortable();
	if(!isset($request->reset_btn)){
		if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))){
			$search_type = $request->search_type;
			$search_val = $request->search_val;
			if($request->search_type == 'name'){
				$bookingDetails = $bookingDetails->where('first_name', 'LIKE', "%{$request->search_val}%");
			}
		}
	}
    $bookingDetails = $bookingDetails->where('type', '=', 11)->where('checkout_time', '>', date('Y-m-d H:i'))->orderBy('id' ,'desc')->paginate(25);
    return view('reservations.personreservation', compact('bookingDetails', 'search_type', 'search_val','search_filter','booking_ref'));
    
 }

public function store(Request $request)
 {
    try{
        $validation = Validator::make($request->all(), 
            [
                'name' => 'required|max:60',
                'check_in' =>'required',
                'check_out' =>'required',
            ],

            [
                'name.required' => @trans('reservations.individual'),
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
        $check_in = date('Y-m-d 00:00:00', strtotime($request->check_in));
        $check_out = date('Y-m-d 23:59:59', strtotime($request->check_out));
        //Creating Object of Booking Class
        if($request->booking_number > 0){
            $booking = Bookings::find($request->booking_number);
            $check_in = date('Y-m-d 00:00:00', strtotime($request->check_in));
            $check_out = date('Y-m-d 23:59:59', strtotime($request->check_out));
            $booking->first_name=$request->name;
            $booking->checkin_time=$check_in;
            $booking->checkout_time=$check_out;
            $booking->save();
            Session::flash('heading', 'Success!');
            Session::flash('message', __('booking.updated'));
            Session::flash('icon', 'success');
            return redirect()->back();
        } else {
        $Booking = new Bookings();
        }
        //Inserting Data into Booking Class
        $Booking->first_name = $name;
        $Booking->type= 11;
        $Booking->checkin_time = $check_in;
        $Booking->checkout_time = $check_out;
        //Saving data into Booking 
        $Booking->save();
        //Check weather data is stored or not
        if($Booking){
            $barcode = new Barcode();
            $barcode->type = 'person';
            $barcode->barcode=$Booking->id;
            $barcode->save();
            return response()->json(['success'=>__('reservations.win')]);
        }else{
            return response()->json(['error'=>'Internal Server Error']);

        }
    } catch(\Exception $ex){
        return response()->json(['error'=>$ex->getMessage()]);
    }
    
        
 }
 public function update(Request $request){
    $booking=Bookings::find($request->booking_id);
    $check_in = date('Y-m-d H:i:s', strtotime($request->arrival_time));
    $check_out = date('Y-m-d H:i:s', strtotime($request->departure_time));
    $booking->first_name=$request->name;
    $booking->checkin_time=$check_in;
    $booking->checkout_time=$check_out;
    $booking->save();
    Session::flash('heading', 'Success!');
    Session::flash('message', __('booking.updated'));
    Session::flash('icon', 'success');
    return redirect()->back();
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
public function download($id, $locale) {
    //
    try {
        $current_locale = \App::getLocale();
        \App::setLocale($locale);
        $barcode = Barcode::where('barcode',$id)->first();
        $booking=Bookings::find($id);
        if (!$barcode) {
            Session::flash('heading', 'Error!');
            Session::flash('message', __('barcode.barcode_not_found'));
            Session::flash('icon', 'error');
            return redirect()->back();
        }
        $barcode_number = $barcode->barcode;
        $pdf = \PDF::loadView('barcode.person.personreservationbarcode', [
            'barcode' => $barcode_number,
            'booking'=>$booking,
        ])->setPaper([0, 0, 263.78, 200.91], 'potrait');
        \App::setLocale($current_locale);
        return $pdf->stream('BarCode.pdf');
    } catch (\Exception $e) {
         Session::flash('heading', 'Error!');
        Session::flash('message', $e->getMessage());
        Session::flash('icon', 'error');
        return redirect()->back();
    }
}
}
