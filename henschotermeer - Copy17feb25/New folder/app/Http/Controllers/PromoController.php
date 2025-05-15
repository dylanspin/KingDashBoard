<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Promo;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $promos = Promo::with('promo_type', 'bookings')->orderBy('created_at', 'desc')->get();
        return view('promo.index', compact('promos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('promo.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        try {
            if($request->discount_type == 'price'){
                $this->validate($request, [
                    'code' => 'required|unique:promos',
                    'price' => 'required',
                    'valid_number_of_times' => 'required',
                    'valid_dates' => 'required',
                ]);
            }
            else if($request->discount_type == 'percent'){
                $this->validate($request, [
                    'code' => 'required|unique:promos',
                    'percentage' => 'required',
                    'valid_number_of_times' => 'required',
                    'valid_dates' => 'required',
                ]);
            }
            else{
                $this->validate($request, [
                    'code' => 'required|unique:promos',
                    'valid_dates' => 'required',
                ]);
            }
            $data = array(
                'type' => 2,
                'code' => $request->code,
                'price' => $request->price,
                'percentage' => $request->percentage,
                'promo_number_limit' => $request->valid_number_of_times,
                'valid_dates' => $request->valid_dates,
            );
            $promo = new Promo();
            if(array_key_exists('type', $data) && $data['type'] != ''){
                $promo->promo_type_id = $data['type'];
            }
            if(array_key_exists('code', $data) && $data['code'] != ''){
                $promo->code = $data['code'];
            }
            if(array_key_exists('price', $data) && $data['price'] != ''){
                $promo->price = $data['price'];
            }
            if(array_key_exists('percentage', $data) && $data['percentage'] != ''){
                $promo->percentage = $data['percentage'];
            }
            if(array_key_exists('valid_number_of_times', $data) && $data['valid_number_of_times'] != ''){
                $promo->promo_number_limit = $data['valid_number_of_times'];
            }
            if(array_key_exists('valid_dates', $data) && $data['valid_dates'] != ''){
                $valid_dates_array = explode(' - ', $data['valid_dates']);
                if(isset($valid_dates_array[0])){
                    $promo->start_date = date('Y-m-d 00:00:00', strtotime($valid_dates_array[0]));
                }
                if(isset($valid_dates_array[1])){
                    $promo->end_date = date('Y-m-d 23:59:59', strtotime($valid_dates_array[1]));
                }
            }
            $promo->save();
            $locationOption = \App\LocationOptions::find(1);
            $locationId = $locationOption->live_id;
            $responseData['success'] = 0;
            try {
                $http = new Client();
                $response = $http->post(env('API_BASE_URL').'/api/import-single-promo-data', [
                    'form_params' => [
                        'location_id' => $locationId,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
                if ($responseData['success'] && isset($responseData['data']['promo_live_id'])) {
                    $promo = Promo::find($promo->id);
                    $promo->live_id = $responseData['data']['promo_live_id'];
                    $promo->save();
                }
            } catch (\Exception $ex) {

            }
            Session::flash('heading', 'Success!');
            Session::flash('message', __('promo.promo_added'));
            Session::flash('icon', 'success');
            return redirect('promo');
        } 
        catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendCode(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->passes()) {
            try {
                $promoOption = Promo::find($id);
                $promoId = $promoOption->live_id;
                $data = array(
                    'email' => $request->email,
                    'promo_id' => $promoId,
                    'promo_option' => array(
                        'type' => $promoOption->promo_type_id,
                        'code' => $promoOption->code,
                        'price' => $promoOption->price,
                        'percentage' => $promoOption->percentage,
                        'start_date' => $promoOption->start_date,
                        'end_date' => $promoOption->end_date,
                        'promo_number_limit' => $promoOption->valid_number_of_times,
                        'promo_used' => $promoOption->promo_used
                    )
                );
                try {
                    $locationOption = \App\LocationOptions::find(1);
                    $locationId = $locationOption->live_id;
                    $responseData['success'] = 0;
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/send-promo-invitation', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                    if ($responseData['success']) {
                        $promoOption->live_id = $responseData['data']['promo_id'];
                        $promoOption->save();
                        return response()->json([
                            'success' => array(
                                'updated' => __('promo.promo_send')
                            )
                        ]);
                    }
                    else{
                        return response()->json([
                            'error' => array(
                                'notSend' => __('promo.promo_not_send')
                            )
                        ]);
                    }
                } catch (\Exception $ex) {

                }
            } 
            catch (\Exception $e) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('promo-send', $e->getMessage(), $e->getTraceAsString());
                return response()->json([
                    'error' => array(
                        'wentWrong' => __('promo.promo_not_send')
                    )
                ]);
            }
        }
        else{
            return response()->json(['error' => $validator->errors()->all()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        try {
            $promo = Promo::find($id);
            if ($promo != null) {
                $data['promo_live_id'] = $promo->live_id;
                $locationOption = \App\LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/destroy-single-promo-data', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    
                }
                
                $promo->delete();
                
                Session::flash('heading', 'Success!');
                Session::flash('message', __('promo.promo_delete'));
                Session::flash('icon', 'success');
                return redirect('promo');
                
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('promo.promo_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } 
        catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }
}
