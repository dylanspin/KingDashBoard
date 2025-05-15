<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\LocationOptions;
use App\TommyReservationParents;
use App\TommyReservationChildrens;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use File;
use Excel;

class TommyReservationController extends Controller {

    public $controller = 'App\Http\Controllers\TommyReservationController';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        //
        $search_type = '';
        $search_val = '';
        $tommyReservations = TommyReservationParents::sortable();
        $tommyReservations = $tommyReservations->with('tommy_reservation_childrens');
        if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn)) {
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_type)) {

                    if ($request->search_type == 'first_name') {
                        $tommyReservations = $tommyReservations->where('license_plate', 'LIKE', "%{$request->search_val}%");
                    } elseif ($request->search_type == 'email') {
                        $tommyReservations = $tommyReservations->where('email', 'LIKE', "%{$request->search_val}%");
                    } else {
                        $tommyReservations = $tommyReservations->where('license_plate', 'LIKE', "%{$request->search_val}%");
                        $tommyReservations = $tommyReservations->orWhere('email', 'LIKE', "%{$request->search_val}%");
                    }
                } else {
                    $tommyReservations = $tommyReservations->where('license_plate', 'LIKE', "%{$request->search_val}%");
                    $tommyReservations = $tommyReservations->orWhere('email', 'LIKE', "%{$request->search_val}%");
                }
            }
        }
        $tommyReservations = $tommyReservations->orderBy('date_of_arrival', 'desc')
                ->paginate(25);
        return view('tommy-reservation.index', compact('tommyReservations', 'search_type', 'search_val'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
        return view('tommy-reservation.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
        $this->validate($request, array(
            'file' => 'required'
        ));
        $response = array();
        if ($request->hasFile('file')) {
            $extension = File::extension($request->file->getClientOriginalName());
            if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {
                $tomyReservationFileName = time() . '.' . $request->file->getClientOriginalExtension();
                $request->file->move(public_path('uploads/tommy-reservation'), $tomyReservationFileName);
                $path = public_path('uploads/tommy-reservation/' . $tomyReservationFileName);
                $data = Excel::load($path, function($reader) {
                            
                        })->get();
                $tommyReservationParentData = array();

                if (!empty($data) && $data->count()) {
                    $count = 0;
                    $index = 0;
//                    print '<pre>';
//                    print_r($data);
//                    print '</pre>';
//                    die();
                    while ($count < $data->count()) {
                        $tommyReservationParentData[$index] = array(
                            'live_id' => 0,
                            'total_members' => 0,
                            'email' => $data[$count]->email,
                            'date_of_arrival' => date('Y-m-d H:i:s', strtotime($data[$count]->aankomstdatum)),
                            'date_of_departure' => date('Y-m-d H:i:s', strtotime($data[$count]->vertrekdatum)),
                            'license_plate' => $data[$count]->kenteken,
                            'other_license_plate' => $data[$count]->kenteken_eventuele_2e_auto
                        );
                        $current_line = $count;
                        $tommyReservationChildData = array();
                        for ($i = $count; $i < $data->count(); $i++) {
                            if ($current_line != $i && !empty($data[$i]->aankomstdatum)) {
                                break;
                            }
                            if (!empty($data[$i]->geboortedatum)) {
                                $tommyReservationParentData[$index]['total_members'] += 1;
                                $tommyReservationChildData[] = array(
                                    'live_id' => 0,
                                    'name' => $data[$i]->gastmedegasten,
                                    'family_status' => $data[$i]->aanhef,
                                    'dob' => date('Y-m-d', strtotime($data[$i]->geboortedatum)),
                                    'first_name' => $data[$i]->voornaam,
                                    'middle_name' => $data[$i]->tussenvoegsel,
                                    'last_name' => $data[$i]->achternaam
                                );
                            }
							else{
								//print_r($data[$i]);
							}
                        }
                        $tommyReservationParentData[$index]['tommyReservationChild'] = $tommyReservationChildData;
                        $count = $i;
                        if ($count == $current_line) {
                            $count++;
                        }
                        $index++;
                    }
                }
         //      print '<pre>';
         //      print_r($tommyReservationParentData);
         //       print '</pre>';
         //       die();
                foreach ($tommyReservationParentData as $parentData) {
                    try {
                        $tommy_reservation_parent = new TommyReservationParents();
                        $tommy_reservation_parent->live_id = 0;
                        $tommy_reservation_parent->total_members = $parentData['total_members'];
                        $tommy_reservation_parent->email = $parentData['email'];
                        $tommy_reservation_parent->date_of_arrival = $parentData['date_of_arrival'];
                        $tommy_reservation_parent->date_of_departure = $parentData['date_of_departure'];
                        $tommy_reservation_parent->license_plate = !empty($parentData['license_plate']) ? $parentData['license_plate'] : '';
                        $tommy_reservation_parent->other_license_plate = !empty($parentData['other_license_plate']) ? $parentData['other_license_plate'] : '';
                        $tommy_reservation_parent->save();
                        foreach ($parentData['tommyReservationChild'] as $i => $importChildData) {
                            $tommy_reservation_child = new TommyReservationChildrens();
                            $tommy_reservation_child->tommy_reservation_parent_id = $tommy_reservation_parent->id;
                            $tommy_reservation_child->name = !empty($importChildData['name']) ? $importChildData['name'] : '';
                            $tommy_reservation_child->family_status = !empty($importChildData['family_status']) ? $importChildData['family_status'] : '';
                            $tommy_reservation_child->dob = date('Y-m-d', strtotime(!empty($importChildData['dob']) ? $importChildData['dob'] : ''));
                            $tommy_reservation_child->first_name = !empty($importChildData['first_name']) ? $importChildData['first_name'] : '';
                            $tommy_reservation_child->middle_name = !empty($importChildData['middle_name']) ? $importChildData['middle_name'] : '';
                            $tommy_reservation_child->last_name = !empty($importChildData['last_name']) ? $importChildData['last_name'] : '';
                            $tommy_reservation_child->save();
                            $tommyReservationChildId = $tommy_reservation_child->id;
                            $this->add_booking($tommyReservationChildId, 0, 0);
                        }
                    } catch (\Exception $ex) {
//                        print_r($ex->getLine());
//                        print_r($ex->getMessage());
                        continue;
                    }
                }
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $user_id = auth()->user()->live_id;
                $Key = base64_encode($locationId . '_' . $user_id);
                try {
                    $http = new Client([
                        'headers' => [
                            'Accept' => 'application/json',
                    ]]);
                    $response = $http->post(env('API_BASE_URL').'/api/store-tommy-reservation-data', [
                        'form_params' => [
                            'token' => $Key,
                            'data' => json_encode($tommyReservationParentData)
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    File::delete($path);
                    Session::flash('heading', 'Error!');
                    Session::flash('message', 'System is down that\'s why data can\'t be imported');
                    Session::flash('icon', 'error');
                    return redirect('tommy-reservations');
                }
                File::delete($path);
                Session::flash('heading', 'Success!');
                Session::flash('message', 'Your Data has successfully imported.');
                Session::flash('icon', 'success');
                return redirect('tommy-reservations');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'File is a ' . $extension . ' file.!! Please upload a valid xls/csv file..!!"');
                Session::flash('icon', 'error');
                return redirect()->back()->withInput();
            }
        }
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
        try {
            $tommyReservation = TommyReservationParents::find($id);
            if ($tommyReservation != null) {
                $data['tommy_reservation_live_id'] = $tommyReservation->live_id;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/destroy-single-tommy-reservation-data', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    
                }

                $tommyReservation->delete();
                $tommyReservationChilds = TommyReservationChildrens::where(
                                'tommy_reservation_parent_id', '=', $id
                        )->get();
                foreach ($tommyReservationChilds as $tommyReservationChild) {
                    $booking = \App\Bookings::where('tommy_childeren_id', $tommyReservationChild->id)->first();
                    if ($booking) {
                        \App\BookingPayments::where('booking_id', $booking->id)->forceDelete();
                        $booking->forceDelete();
                    }
                    $tommyReservationChild->delete();
                }
                if ($responseData['success']) {
                    Session::flash('heading', 'Success!');
                    Session::flash('message', 'Tommy Reservation User has been deleted.');
                    Session::flash('icon', 'success');
                    return redirect('tommy-reservations');
                } else {
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', 'Tommy Reservation User has been deleted localy, but there is a problem of connectivity with live server.');
                    Session::flash('icon', 'warning');
                    return redirect('tommy-reservations');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'User not found in list.');
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }

    /**
     * Send Ticket the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendTicket($id) {
        //
        try {
            $tommyReservation = TommyReservationParents::find($id);
            if ($tommyReservation != null) {
                $data['tommy_reservation_live_id'] = $tommyReservation->live_id;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/send-ticket-tommy-reservation', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    
                }

                if ($responseData['success']) {
                    Session::flash('heading', 'Success!');
                    Session::flash('message', 'Ticket has been sent.');
                    Session::flash('icon', 'success');
                    return redirect('tommy-reservations');
                } else {
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', 'Something went wrong! Ticket not send.');
                    Session::flash('icon', 'warning');
                    return redirect('tommy-reservations');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'User not found in list.');
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }

    /**
     * Print Ticket the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printTicket12($id) {
        try {
            $tommyReservation = TommyReservationParents::find($id);
            if ($tommyReservation != null) {
                $data['tommy_reservation_live_id'] = $tommyReservation->live_id;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/print-ticket-tommy-reservation', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                    foreach ($responseData['data'] as $indexKey => $importData) {
                        $qr_code = base64_encode(QrCode::format('png')->size(100)->generate($importData));
                    }

                    exit;
                } catch (\Exception $ex) {
                    $error_log = new \App\Http\Controllers\LogController();
                    $error_log->log_create('print-ticket-tommy-reservation', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

                    Session::flash('heading', 'Error!');
                    Session::flash('message', $ex->getMessage());
                    Session::flash('icon', 'error');
                    return redirect()->back();
                }

                if ($responseData['success']) {
                    Session::flash('heading', 'Success!');
                    Session::flash('message', 'Ticket has been sent.');
                    Session::flash('icon', 'success');
                    return redirect('tommy-reservations');
                } else {
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', 'Something went wrong! Ticket not send.');
                    Session::flash('icon', 'warning');
                    return redirect('tommy-reservations');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'User not found in list.');
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('printTicket', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');

            return redirect()->back();
        }
    }

    public function printTicket($id) {
        try {
            $tommyReservation = TommyReservationChildrens::find($id);
            if (!$tommyReservation) {
                Session::flash('heading', 'Warning!');
                Session::flash('message', 'Something went wrong! Ticket not send.');
                Session::flash('icon', 'warning');
                return redirect()->back();
            }


            $booking = \App\Bookings::where('tommy_childeren_id', $id)->orderBy('created_at', 'DESC')->first();
            if (!$booking) {
                Session::flash('heading', 'Warning!');
                Session::flash('message', 'Something went wrong! Ticket not send.');
                Session::flash('icon', 'warning');
                return redirect()->back();
            }
            $booking_id = $booking->id;
            $checkin_time = $booking->checkin_time;
            $checkout_time = $booking->checkout_time;
            $dob = $tommyReservation->dob || $tommyReservation->dob != date('Y-m-d', strtotime('1970-01-01')) ? $tommyReservation->dob : '';
            $name = $tommyReservation->name;
            $pdf = \PDF::loadView('tommy-reservation.pdf', [
                        'booking_id' => $booking_id,
                        'checkin_time' => $checkin_time,
                        'checkout_time' => $checkout_time,
                        'name' => $name,
                        'dob' => $dob,
                    ])->setPaper([0, 0, 263.78, 200.91], 'potrait');
            return $pdf->stream('Ticket.pdf');
        } catch (\Exception $ex) {

            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');

            return redirect()->back();
        }
    }

    public function add_booking($tommy_children_id, $live_booking_id, $live_booking_payment_id) {
        $tommy_reservation_child = TommyReservationChildrens::find($tommy_children_id);
        if (!$tommy_reservation_child) {
            return;
        }
        if (empty($tommy_reservation_child->tommy_reservation_parent_id)) {
            return;
        }
        $parent_details = TommyReservationParents::find($tommy_reservation_child->tommy_reservation_parent_id);
        $checkin = !empty($parent_details->date_of_arrival) ? date('Y-m-d 00:00:00', strtotime($parent_details->date_of_arrival)) : date('Y-m-d 00:00:00');
        $checkout = !empty($parent_details->date_of_departure) ? date('Y-m-d 23:59:59', strtotime($parent_details->date_of_departure)) : date('Y-m-d 23:59:59');
        $name = $tommy_reservation_child->name;
        $dob = $tommy_reservation_child->dob;
        $email = $parent_details->email;
        $booking = new \App\Bookings();
        $booking->checkin_time = $checkin;
        $booking->checkout_time = $checkout;
        $booking->first_name = $name;
        $booking->email = $email;
        $booking->live_id = 0;
        $booking->tommy_childeren_id = $tommy_reservation_child->id;
        $booking->tommy_children_dob = $dob;
        $booking->is_paid = 1;
        $booking->is_local_updated = 1;
        $booking->is_live_updated = 1;
        $booking->type = 6;
        $booking->save();
        if ($live_booking_payment_id > 0) {
            $booking_payment = new \App\BookingPayments();
            $booking_payment->booking_id = $booking->id;
            $booking_payment->live_id = $live_booking_payment_id;
            $booking_payment->checkin_time = $checkin;
            $booking_payment->checkout_time = $checkout;
            $booking_payment->amount = 0;
            $booking_payment->payment_id = 'Paid Person';
            $booking_payment->save();
        }
        return;
    }

    /**
     * View Members.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewMembers($id) {
        //
        $tommyReservations = TommyReservationParents::with('tommy_reservation_childrens')
                ->where('id', '=', $id)
                ->first();
        $familyHead = "Family Head";
        foreach ($tommyReservations->tommy_reservation_childrens as $tommy_reservation_childrens) {
            if ($tommy_reservation_childrens->family_status == 'Familie') {
                $familyHead = $tommy_reservation_childrens->name;
            }
        }
        return view('tommy-reservation.members', compact('tommyReservations', 'familyHead'));
    }

    /**
     * Set Check Out Time of All Person Type Users
     * @return type
     */
    public function setCheckOut() {
        try {
            $type = 6;
            $attendantTransactions = \App\AttendantTransactions::whereHas('attendants.bookings',
                            function ($query) use ($type) {
                        $query->where('type', $type);
                    }
                    )->with(['attendants.bookings' =>
                        function ($query) use ($type) {
                            $query->where('type', $type);
                        }
                    ])->whereNotNull('check_in')
                    ->whereNull('check_out')
                    ->get();
            foreach ($attendantTransactions as $attendantTransaction) {
                $attendantTransaction->check_out = date('Y-m-d H:i:s');
                $attendantTransaction->save();

                if (isset($attendantTransaction->attendants->bookings) &&
                        $attendantTransaction->attendants->bookings->checkout_time == NULL) {
                    $attendantTransaction->attendants->bookings->checkout_time = date('Y-m-d H:i:s');
                    $attendantTransaction->attendants->bookings->save();
                }
            }
            if (count($attendantTransactions) <= 0) {
                Session::flash('heading', 'Warning!');
                Session::flash('message', 'Booking not Found.');
                Session::flash('icon', 'warning');
                return redirect()->back();
            } else {
                Session::flash('heading', 'Success!');
                Session::flash('message', 'Person CheckOut time updated successfully');
                Session::flash('icon', 'success');
                return redirect()->back();
            }
        } catch (Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('setCheckOut', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');

            return redirect()->back();
        }
    }

    /**
     * Set Check Out Time of All Person Type Users
     * @return type
     */
    public function setVehicleCheckOut() {
        try {
            $type = [1, 2, 3, 4, 5, 7, 8];
            $attendantTransactions = \App\AttendantTransactions::whereHas('attendants.bookings',
                            function ($query) use ($type) {
                        $query->whereIn('type', $type);
                    }
                    )->with(['attendants.bookings' =>
                        function ($query) use ($type) {
                            $query->whereIn('type', $type);
                        }
                    ])->whereNotNull('check_in')
                    ->whereNull('check_out')
                    ->get();
            foreach ($attendantTransactions as $attendantTransaction) {
                $attendantTransaction->check_out = date('Y-m-d H:i:s');
                $attendantTransaction->save();

                if (isset($attendantTransaction->attendants->bookings) &&
                        $attendantTransaction->attendants->bookings->checkout_time == NULL) {
                    $attendantTransaction->attendants->bookings->checkout_time = date('Y-m-d H:i:s');
                    $attendantTransaction->attendants->bookings->save();
                }
            }
            if (count($attendantTransactions) <= 0) {
                Session::flash('heading', 'Warning!');
                Session::flash('message', 'Booking not Found.');
                Session::flash('icon', 'warning');
                return redirect()->back();
            } else {
                Session::flash('heading', 'Success!');
                Session::flash('message', 'Vehicle CheckOut time updated successfully');
                Session::flash('icon', 'success');
                return redirect()->back();
            }
        } catch (Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('setVehicleCheckOut', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');

            return redirect()->back();
        }
    }

    /**
     * Store Value Of CheckOutTime Of Person Bookings to DB
     * @param type $bookings
     * @return boolean
     */
    public function checkOutPerson($bookings = NULL) {
        try {
            if (!$bookings) {
                $bookings = \App\Bookings::where('type', 6)
                        ->whereNull('checkout_time')
                        ->get();
            }
            foreach ($bookings as $booking) {
                $checkOutDate = date('Y-m-d 23:59:59');
                $data = array(
                    'live_id' => $booking->live_id,
                    'checkOutDate' => $checkOutDate
                );
                $responseData['success'] = 0;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $user_id = auth()->user()->live_id;
                $Key = base64_encode($locationId . '_' . $user_id);
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/set-checkout', [
                        'form_params' => [
                            'token' => $Key,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    $error_log = new \App\Http\Controllers\LogController();
                    $error_log->log_create('set-checkout-person', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
                }
                if ($responseData['success']) {
                    $bookingInfo = \App\Bookings::find($booking->id);
                    if ($bookingInfo->checkout_time == NULL) {
                        $bookingInfo->checkout_time = $data['checkOutDate'];
                        $bookingInfo->save();
                    }
                    $attendant = \App\Attendants::where('booking_id', $booking->id)
                            ->first();
                    if ($attendant) {
                        $attendantTransactions = \App\AttendantTransactions::where('attendant_id', $attendant->id)
                                ->whereNotNull('check_in')
                                ->whereNull('check_out')
                                ->get();
                        foreach ($attendantTransactions as $attendantTransaction) {
                            $attendat_transction = \App\AttendantTransactions::find($attendantTransaction->id);
                            if ($attendat_transction) {
                                if ($attendat_transction->check_out == NULL) {
                                    $attendat_transction->check_out = $data['checkOutDate'];
                                    $attendat_transction->save();
                                }
                            }
                        }
                    }
                }
            }
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('checkOutPerson', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

            return FALSE;
        }
    }

    /**
     * Store Value Of CheckOutTime Of Vehicle Bookings to DB
     * @param type $bookings
     * @return boolean
     */
    public function checkOutVehicle($bookings = NULL) {
        try {
            if (!$bookings) {
                $bookings = \App\Bookings::where('type', '<>', 6)
                        ->whereNull('checkout_time')
                        ->get();
            }
            foreach ($bookings as $booking) {
                $checkOutDate = date('Y-m-d 23:59:59');
                $data = array(
                    'live_id' => $booking->live_id,
                    'checkOutDate' => $checkOutDate
                );
                $responseData['success'] = 0;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $user_id = auth()->user()->live_id;
                $Key = base64_encode($locationId . '_' . $user_id);
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/set-checkout', [
                        'form_params' => [
                            'token' => $Key,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    $error_log = new \App\Http\Controllers\LogController();
                    $error_log->log_create('set-checkout-person', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
                }
                if ($responseData['success']) {
                    $bookingInfo = \App\Bookings::find($booking->id);
                    if ($bookingInfo->checkout_time == NULL) {
                        $bookingInfo->checkout_time = $data['checkOutDate'];
                        $bookingInfo->save();
                    }
                    $attendant = \App\Attendants::where('booking_id', $booking->id)
                            ->first();
                    if ($attendant) {
                        $attendantTransactions = \App\AttendantTransactions::where('attendant_id', $attendant->id)
                                ->whereNotNull('check_in')
                                ->whereNull('check_out')
                                ->get();
                        foreach ($attendantTransactions as $attendantTransaction) {
                            $attendat_transction = \App\AttendantTransactions::find($attendantTransaction->id);
                            if ($attendat_transction) {
                                if ($attendat_transction->check_out == NULL) {
                                    $attendat_transction->check_out = $data['checkOutDate'];
                                    $attendat_transction->save();
                                }
                            }
                        }
                    }
                }
            }
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('checkOutVehicle', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

            return FALSE;
        }
    }

    public function bookings(Request $request) {
        $search_type = '';
        $search_val = '';
        $onLineBookings = array();
        $bookings = \App\Bookings::sortable();
        $bookings = $bookings->where('type', 6)
                ->where('is_tommy_online', 1)
                ->whereNotNull('email');
        if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn)) {
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_type)) {

                    if ($request->search_type == 'first_name') {
                        $bookings = $bookings->where(function($q) use ($request) {
                            $q->where('first_name', 'LIKE', "%{$request->search_val}%")->orWhere('last_name', 'LIKE', "%{$request->search_val}%");
                        });
                    } elseif ($request->search_type == 'email') {
                        $bookings = $bookings->where('email', 'LIKE', "%{$request->search_val}%");
                    } else {
                        $bookings = $bookings->where(function($q) use ($request) {
                            $q->where('first_name', 'LIKE', "%{$request->search_val}%")->orWhere('last_name', 'LIKE', "%{$request->search_val}%")
                                    ->orWhere('email', 'LIKE', "%{$request->search_val}%");
                        });
                    }
                } else {
                    $bookings = $bookings->where(function($q) use ($request) {
                        $q->where('first_name', 'LIKE', "%{$request->search_val}%")->orWhere('last_name', 'LIKE', "%{$request->search_val}%")
                                ->orWhere('email', 'LIKE', "%{$request->search_val}%");
                    });
                }
            }
        }
        $bookings = $bookings->where('checkout_time', '>', date('Y-m-d H:i:s'))
                ->orderBy('created_at', 'desc')
                ->paginate(25);
        foreach ($bookings as $booking) {
            $bookingCount = \App\Bookings::where('type', 6)
                    ->where('is_tommy_online', 1)
                    ->where('tommy_parent_id', $booking->tommy_parent_id)
                    ->where('checkout_time', '>', date('Y-m-d H:i:s'))
                    ->count();

            $data = (object) array(
                        'id' => $booking->id,
                        'first_name' => $booking->first_name,
                        'last_name' => $booking->last_name,
                        'email' => $booking->email,
                        'members' => $bookingCount,
                        'checkin_time' => $booking->checkin_time,
                        'checkout_time' => $booking->checkout_time
            );

            $onLineBookings[] = $data;
        }
        return view('tommy-reservation.bookings', compact('onLineBookings', 'bookings', 'search_type', 'search_val'));
    }

    /**
     * View Members.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewOnLineMembers($id) {
        //
        $onLineMembers = array();
        $findBooking = \App\Bookings::find($id);
        $familyHead = "Family Head";
        if ($findBooking) {
            $familyHead = $findBooking->first_name . ' ' . $findBooking->last_name;
            $bookings = \App\Bookings::where('type', 6)
                    ->where('is_tommy_online', 1)
                    ->where('tommy_parent_id', $findBooking->tommy_parent_id)
                    ->where('checkout_time', '>', date('Y-m-d H:i:s'))
                    ->orderBy('created_at', 'desc')
                    ->get();
            foreach ($bookings as $booking) {
                $familyStatus = "Familie";
                if (!$booking->email) {
                    $familyStatus = "Guest";
                }
                $data = (object) array(
                            'id' => $booking->id,
                            'first_name' => $booking->first_name,
                            'last_name' => $booking->last_name,
                            'family_status' => $familyStatus,
                            'dob' => $booking->tommy_children_dob
                );

                $onLineMembers[] = $data;
            }
        }
        return view('tommy-reservation.bookings_members', compact('onLineMembers', 'familyHead'));
    }

    public function printOnLineTicket($id) {
        try {
            $booking = \App\Bookings::find($id);
            if (!$booking) {
                Session::flash('heading', 'Warning!');
                Session::flash('message', 'Something went wrong! Ticket not send.');
                Session::flash('icon', 'warning');
                return redirect()->back();
            }
            $booking_id = $booking->id;
            $checkin_time = $booking->checkin_time;
            $checkout_time = $booking->checkout_time;
            $dob = $booking->tommy_children_dob || $booking->tommy_children_dob != date('Y-m-d', strtotime('1970-01-01')) ? $booking->tommy_children_dob : '';
            $name = $booking->first_name . ' ' . $booking->last_name;
            $pdf = \PDF::loadView('tommy-reservation.pdf', [
                        'booking_id' => $booking_id,
                        'checkin_time' => $checkin_time,
                        'checkout_time' => $checkout_time,
                        'name' => $name,
                        'dob' => $dob,
                    ])->setPaper([0, 0, 263.78, 200.91], 'potrait');
            return $pdf->stream('Ticket.pdf');
        } catch (\Exception $ex) {

            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');

            return redirect()->back();
        }
    }
    public function printTickekInOneGo($id){
        try{
            $findBooking = \App\Bookings::find($id);
        if (isset($findBooking) && !empty($findBooking)) {
            $bookings = \App\Bookings::where('type', 6)
                    ->where('is_tommy_online', 1)
                    ->where('tommy_parent_id', $findBooking->tommy_parent_id)
                    ->where('checkout_time', '>', date('Y-m-d H:i:s'))
                    ->orderBy('created_at', 'asc')
                    ->get();
            $pdf = \PDF::loadView('tommy-reservation.multiplepdf',compact('bookings'))->setPaper([0, 0, 263.78, 200.91], 'potrait');
            return $pdf->stream('ticket.pdf');
        }
        }
        catch (\Exception $ex) {

            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');

            return redirect()->back();
        }
    }

    public function sendOnLineTicket($id) {
        try {
            $findBooking = \App\Bookings::find($id);
            if ($findBooking != null) {
                $data['tommy_reservation_live_id'] = $findBooking->tommy_parent_id;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/send-online-ticket-tommy-reservation', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    
                }

                if ($responseData['success']) {
                    Session::flash('heading', 'Success!');
                    Session::flash('message', 'Ticket has been sent.');
                    Session::flash('icon', 'success');
                    return redirect('tommy-reservations/bookings');
                } else {
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', 'Something went wrong! Ticket not send.');
                    Session::flash('icon', 'warning');
                    return redirect('tommy-reservations/bookings');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'User not found in list.');
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }

    public function destroyOnLineBooking($id) {
        //
        try {
            $findBooking = \App\Bookings::find($id);
            if ($findBooking != null) {
                $data['tommy_reservation_live_id'] = $findBooking->tommy_parent_id;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/destroy-single-tommy-reservation-data', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    
                }
                $bookings = \App\Bookings::where(
                                'tommy_parent_id', '=', $findBooking->tommy_parent_id
                        )->get();
                foreach ($bookings as $booking) {
                    \App\BookingPayments::where('booking_id', $booking->id)->forceDelete();
                    $booking->forceDelete();
                }
                if ($responseData['success']) {
                    Session::flash('heading', 'Success!');
                    Session::flash('message', 'Bookings has been deleted.');
                    Session::flash('icon', 'success');
                    return redirect('tommy-reservations/bookings');
                } else {
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', 'Bookings has been deleted localy, but there is a problem of connectivity with live server.');
                    Session::flash('icon', 'warning');
                    return redirect('tommy-reservations/bookings');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'Booking found in list.');
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }

}
