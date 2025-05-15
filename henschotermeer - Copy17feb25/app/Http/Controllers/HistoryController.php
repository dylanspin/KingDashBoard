<?php

namespace App\Http\Controllers;

use App\Barcode;
use App\Bookings;
use App\BookingHistory;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\PseudoTypes\True_;

class HistoryController extends Controller
{
    public function index() {
		$search_type = '';
        $search_val = '';
        $history = BookingHistory::where('booking_type', 'tommy_booking')->paginate(10);
        return view('history.index', compact('history','search_type','search_val'));
    }

    public function search(Request $request) {
        $query = BookingHistory::where('booking_type', 'tommy_booking');
		$search_type = '';
        $search_val = '';
		if(!$request->reset_btn){
			if ($request->search_type == 'vehicle_number' && $request->search_val) {
			$search_type = $request->search_type;
			$search_val = $request->search_val;
            $vehicle_number = strtoupper($request->search_val);
            $query->where('vehicle_num', 'like', '%' . $vehicle_number . '%');

        } 
        elseif ($request->search_type == 'name' && $request->search_val) {
			$search_type = $request->search_type;
			$search_val = $request->search_val;
            /*$query->where(function ($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->search_val . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search_val . '%');
            });*/
			$query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->search_val}%"]);
        }
		}
        
        $history = $query->paginate(25);
    
        return view('history.index', compact('history','search_type', 'search_val'));
    }
    
       
    }
