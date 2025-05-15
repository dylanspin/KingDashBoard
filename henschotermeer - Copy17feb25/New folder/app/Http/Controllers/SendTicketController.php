<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\LocationOptions;
use Illuminate\Support\Facades\Session;

class SendTicketController extends Controller {

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
        return view('send-ticket.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone_num' => 'required',
            'checkin_date' => 'required',
            'checkin_time' => 'required',
            'checkout_date' => 'required',
            'checkout_time' => 'required',
            'vehicle_num' => 'required',
        ]);
        try {
            $data = $request->all();

            $locationOption = LocationOptions::find(1);
            $locationId = $locationOption->live_id;
            $user_id = auth()->user()->live_id;
            $Key = base64_encode($locationId . '_' . $user_id);
            $responseData['success'] = 0;
            try {
                $http = new Client();
                $response = $http->post(env('API_BASE_URL').'/api/send-ticket', [
                    'form_params' => [
                        'token' => $Key,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
            } catch (\Exception $ex) {
                
            }

            if ($responseData['success']) {
                Session::flash('heading', 'Success!');
                Session::flash('message', __('send-ticket.ticket_send'));
                Session::flash('icon', 'success');
                return redirect('send-ticket');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'System is down Ticket can\'t sent.');
                Session::flash('icon', 'error');
                return redirect('send-ticket');
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
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
    }

}
