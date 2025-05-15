<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\UserlistUsers;
use App\WhitelistUsers;
use App\Language;
use App\LocationOptions;
use App\Customer;
use App\Profile;
use App\CustomerVehicleInfo;
use App\Group;
use Illuminate\Support\Facades\Session;

class WhiteListController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
        $whiteListUsers = WhitelistUsers::with('customer.profile', 'group')->get();
        return view('white-list.index', compact('whiteListUsers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
        $groups = Group::all();
        return view('white-list.create', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
        try {
            $this->validate($request, [
                'email' => 'required|email',
            ]);
            $data = $request->all();
            $whiteListUser = WhitelistUsers::where([
                        ['email', $data['email']]
                    ])->first();
            if ($whiteListUser == null) {
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/import-single-whitelist-data', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    
                }


                $customer_exist = Customer::where('email', $data['email'])->first();
                if ($customer_exist == null) {
                    $email_array = explode('@', $data['email']);
                    $customer = new Customer();
                    if ($responseData['success'] && isset($responseData['data']['user_live_id'])) {
                        $customer->live_id = $responseData['data']['user_live_id'];
                    }
                    $customer->name = $email_array[0];
                    $customer->email = $data['email'];
                    $customer->save();

                    $customerId = $customer->id;

                    $customerProfile = new Profile();
                    $customerProfile->customer_id = $customerId;
                    $customerProfile->first_name = $email_array[0];
                    $customerProfile->is_customer = 1;
                    $customerProfile->save();
                } else {
                    $customerId = $customer_exist->id;
                }

                $white_list = new WhitelistUsers();
                if ($responseData['success'] && isset($responseData['data']['whitelist_live_id'])) {
                    $white_list->live_id = $responseData['data']['whitelist_live_id'];
                }
                $white_list->customer_id = $customerId;
                if (array_key_exists('group', $data)) {
                    $white_list->group_id = $data['group'];
                }
                if (!empty($data['email'])) {
                    $white_list->email = $data['email'];
                }
                $white_list->is_ticket_generated = 0;
                $white_list->save();

                if ($responseData['success']) {
                    $settings = new Settings\Settings();
                    $settings->settings_updated('whitelist_users');
                    Session::flash('heading', 'Success!');
                    Session::flash('message', __('white-list.whitelist_add'));
                    Session::flash('icon', 'success');
                    return redirect('white-list');
                } else {
                    $settings = new Settings\Settings();
                    $settings->settings_updated('whitelist_users');
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', __('white-list.whitelist_add_localy'));
                    Session::flash('icon', 'warning');
                    return redirect('white-list');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('white-list.whitelist_add_already'));
                Session::flash('icon', 'error');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
        $whiteListUser = WhitelistUsers::findOrfail($id);

        $groups = Group::all();
        return view('white-list.edit', compact('whiteListUser', 'groups'));
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
        try {
            $this->validate($request, [
                'email' => 'required',
            ]);
            $data = $request->all();
            $white_list = WhitelistUsers::find($id);
            if ($white_list != null) {
//                $locationOption = LocationOptions::find(1);
//                $locationId = $locationOption->live_id;
//                $http = new Client();
//                $response = $http->post(env('API_BASE_URL').'/api/update-single-whitelist-data', [
//                    'form_params' => [
//                        'location_id' => $locationId,
//                        'data' => $data
//                    ],
//                ]);
//                $responseData = json_decode((string) $response->getBody(), true);
//                if ($responseData['success'] && isset($responseData['data']['whitelist_live_id'])) {
//                    $white_list->live_id = $responseData['data']['whitelist_live_id'];
//                }


                if (array_key_exists('group', $data)) {
                    $white_list->group_id = $data['group'];
                }
                $white_list->save();

                $settings = new Settings\Settings();
                $settings->settings_updated('whitelist_users');
                Session::flash('heading', 'Success!');
                Session::flash('message', __('white-list.whitelist_update'));
                Session::flash('icon', 'success');
                return redirect('white-list');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('white-list.whitelist_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
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
    public function destroy($id) {
        //
        try {
            $whiteListUser = WhitelistUsers::find($id);
            if ($whiteListUser != null) {
                $data['whitelist_live_id'] = $whiteListUser->live_id;
                $locationOption = LocationOptions::first();
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/destroy-single-whitelist-data', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    
                }

                $delete_controller = new DeleteController();
                $delete_controller->delete_whitelist_bookings($whiteListUser->email);
                $whiteListUser->forceDelete();

                if ($responseData['success']) {
                    $settings = new Settings\Settings();
                    $settings->settings_updated('whitelist_users');
                    Session::flash('heading', 'Success!');
                    Session::flash('message', __('white-list.whitelist_delete'));
                    Session::flash('icon', 'success');
                    return redirect('white-list');
                } else {
                    $settings = new Settings\Settings();
                    $settings->settings_updated('whitelist_users');
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', __('white-list.whitelist_delete_localy'));
                    Session::flash('icon', 'warning');
                    return redirect('white-list');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('white-list.whitelist_not_found'));
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
     * Send Instructions the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendInstrucions($id) {
        //
        try {
            $whiteListUser = WhitelistUsers::find($id);
            if ($whiteListUser != null) {
                $data['whitelist_live_id'] = $whiteListUser->live_id;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL').'/api/send-instructions-whitelist', [
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
                    Session::flash('message', __('white-list.whitelist_instructions_sent'));
                    Session::flash('icon', 'success');
                    return redirect('white-list');
                } else {
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', __('white-list.went_wrong'));
                    Session::flash('icon', 'warning');
                    return redirect('white-list');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('white-list.whitelist_not_found'));
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
