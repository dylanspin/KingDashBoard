<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Promo;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\LocationOptions;

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
		$location = LocationOptions::first();
        $promos = Promo::with('promo_type', 'bookings')->orderBy('id', 'desc')->get();
        
        return view('promo.index', compact('promos','location'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
                    'valid_group_name' =>'required',
                    'valid_group_no'  => 'required',
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
                'group_name' =>$request->valid_group_name,
                'promo_number_limit' =>$request->valid_group_no,
				'show_header'=>0
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
             if(array_key_exists('group_name', $data) && $data['group_name'] != ''){
                $promo->group_name = $data['group_name'];

            }
            if(array_key_exists('promo_number_limit', $data) && $data['promo_number_limit'] != ''){
                $promo->promo_number_limit = $data['promo_number_limit'];

            }
            $promo->save();
            $locationOption = \App\LocationOptions::find(1);
            $locationId = $locationOption->live_id;
            $responseData['success'] = 0;
            try {
                $http = new Client(['verify'=>false]);
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
                        'promo_used' => $promoOption->promo_used,
                        
                    )
                );
                try {
                    $locationOption = \App\LocationOptions::find(1);
                    $locationId = $locationOption->live_id;
                    $responseData['success'] = 0;
                    $http = new Client(['verify'=>false]);
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
     * Edit Promo.
     * @return \Illuminate\Http\Response
     */
	public function editPromo($id){
		
        
           
	}
	public function updatePromo(Request $request, $id){

		
		 
		
        
           
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
        $promos = Promo::find($id);
        if($promos){
			
				return view('promo.promoEdit', compact('promos'));
		}else{
			
			abort(404);
		}
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
        try {
            $this->validate($request, [
                'code' => 'required',
                'valid_dates' => 'required',
            ]);
        
        $data = array(
            'type' => 2,
            'code' => $request->code,
            'price' => $request->price,
            'percentage' => $request->percentage,
            'promo_number_limit' => $request->valid_number_of_times,
            'valid_dates' => $request->valid_dates,
            'group_name' =>$request->valid_group_name,
            'promo_number_limit' =>$request->valid_group_no,
            'show_header'=>0
        );
        $promo = Promo::find($id);
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
         if(array_key_exists('group_name', $data) && $data['group_name'] != ''){
            $promo->group_name = $data['group_name'];

        }
        if(array_key_exists('promo_number_limit', $data) && $data['promo_number_limit'] != ''){
            $promo->promo_number_limit = $data['promo_number_limit'];

        }
        $promo->update();
        $locationOption = \App\LocationOptions::find(1);
        $locationId = $locationOption->live_id;
        $responseData['success'] = 0;
        try {
             $data = array(
            'type' => 2,
            'live_id' => $promo->live_id,
            'code' => $request->code,
            'price' => $request->price,
            'percentage' => $request->percentage,
            'promo_number_limit' => $request->valid_number_of_times,
            'valid_dates' => $request->valid_dates,
            'group_name' =>$request->valid_group_name,
            'promo_number_limit' =>$request->valid_group_no,
            'show_header'=>0
        );
            $http = new Client(['verify'=>false]);
            $response = $http->post(env('API_BASE_URL').'/api/import-single-promo-data-update', [
                'form_params' => [
                    'location_id' => $locationId,
                    'data' => $data
                ],
            ]);
            $responseData = json_decode((string) $response->getBody(), true);
            if ($responseData['success'] && isset($responseData['data']['promo_live_id'])) {
                $promo = Promo::find($promo->id);
                $promo->live_id = $responseData['data']['live_id'];
                $promo->update();
            }
        } catch (\Exception $ex) {

        }
		try {
                $bookings = \App\Bookings::where('group_invitation_id', $promo->id)->update(['checkin_time' => $promo->start_date, 'checkout_time' => $promo->end_date]);
            } catch (\Exception $ex) {
        }
        Session::flash('heading', 'Success!');
        Session::flash('message', __('Promo Updated SuccessFully'));
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
