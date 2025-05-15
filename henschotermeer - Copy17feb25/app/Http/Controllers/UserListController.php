<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\UserlistUsers;
use App\Language;
use App\LocationOptions;
use App\Customer;
use App\Profile;
use App\CustomerVehicleInfo;
use App\Group;
use App\Bookings;
use App\BookingPayments;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use File;

class UserListController extends Controller {

    public $controller = 'App\Http\Controllers\UserListController';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        //
//        $userListUsers = UserlistUsers::with('group')->get();
        $search_type = '';
        $search_val = '';
        $userListUsers = null;
        if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn)) {
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_type)) {
                    if ($request->search_type == 'name') {
                        $userListUsers = UserlistUsers::with(
                                        'customer.customer_vehicle_info', 'customer_vehicle_info', 'group'
                                )->where('user_name', 'LIKE', "%{$request->search_val}%")->get();
                    } elseif ($request->search_type == 'email') {
                        $userListUsers = UserlistUsers::with(
                                        'customer.customer_vehicle_info', 'customer_vehicle_info', 'group'
                                )->where('email', 'LIKE', "%{$request->search_val}%")->get();
                    } elseif ($request->search_type == 'plate') {
                        $search_value = $request->search_val;
                        $userListUsers = UserlistUsers::with(
                                        'customer.customer_vehicle_info', 'customer_vehicle_info', 'group'
                                )->whereHas('customer_vehicle_info', function ($query) use($search_value) {
                                    $query->where('num_plate', 'LIKE', "%{$search_value}%");
                                })->get();
                    } else {
                        $userListUsers = UserlistUsers::with(
                                        'customer.customer_vehicle_info', 'customer_vehicle_info', 'group'
                                )->get();
                    }
                } else {
                    $userListUsers = UserlistUsers::with(
                                    'customer.customer_vehicle_info', 'customer_vehicle_info', 'group'
                            )->get();
                }
            } else {
                $userListUsers = UserlistUsers::with(
                                'customer.customer_vehicle_info', 'customer_vehicle_info', 'group'
                        )->get();
            }
        } else {
            $userListUsers = UserlistUsers::with(
                            'customer.customer_vehicle_info', 'customer_vehicle_info', 'group'
                    )->get();
        }
        $languages = Language::all();
        $groups = Group::all();
        $rights = \App\GroupAccess::all();
        return view('user-list.index', compact('userListUsers', 'languages', 'groups', 'rights', 'search_type', 'search_val'));
    }

    public function viewPlates(Request $request) {
        $customer_vehicles = \App\CustomerVehicleInfo::where('userlist_user_id', $request->id)
                ->get();
        ob_start();
        ?>
        <div>
            <ul>
                <?php
                foreach ($customer_vehicles as $customer_vehicle) {
                    ?>
                    <li><?= $customer_vehicle->num_plate ?></li>
                    <hr>
                    <?php
                }
                ?>
            </ul>
        </div>
        <?php
        return $data = ob_get_clean();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
        $languages = Language::all();
        $groups = Group::all();
        $rights = \App\GroupAccess::all();
        return view('user-list.create', compact('languages', 'groups', 'rights'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        try {
            $created_vehicle_id = FALSE;
            $request->validate([
                'name' => 'required|min:3',
                'language' => 'required',
                "plates.*" => "required|string|distinct|min:3",
            ]);
            $user_arrival_notification = 0;
            $notify_emails = NULL;
            if (isset($request->user_arrival_notification) && $request->user_arrival_notification) {
                $user_arrival_notification = 1;
                $validator = Validator::make($request->all(), [
                            'notify_email' => 'required'
                ]);
                if ($validator->fails()) {
                    Session::flash('heading', 'Error!');
                    Session::flash('message', __('Emails for recieve notifications is required'));
                    Session::flash('icon', 'error');
                    return redirect()->back()->withInput();
                }
                $notify_emails = $request->notify_email;
                $explode_notify_emails = explode(',', str_replace(' ', '', $request->notify_email));
                foreach ($explode_notify_emails as $explode_notify_email) {
                    $validator = Validator::make(
                                    array('email' => $explode_notify_email), array('email' => array('required', 'email'))
                    );
                    if ($validator->fails()) {
                        Session::flash('heading', 'Error!');
                        Session::flash('message', __($explode_notify_email . ' is not a valid email address in field (Emails for recieve notifications)'));
                        Session::flash('icon', 'error');
                        return redirect()->back()->withInput();
                    }
                }
            }
            $data = $request->all();
            $data['is_paid'] = 1;
            $data['user_checkin_time'] = NULL;
            $data['user_checkout_time'] = NULL;
            $responsewarn = 0;
            $duplicate = array();
            $number_plates = array();
            if (isset($data['plates'])) {
                foreach ($data['plates'] as $plate) {
                    $number_plates[] = str_replace(array(' ', '-', '\'', '"', ',', ';', '<', '>'), '', $plate);
                }
            }
            if ($data['email'] == '' || !isset($data['email'])) {
                //no email case
                $locationTiming = \App\LocationTimings::where('week_day_num', date('w'))
                        ->where('is_whitelist', 1)
                        ->first();
                if ($locationTiming) {
                    $data['user_checkin_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->opening_time));
                    $data['user_checkout_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->closing_time));
                } else {
                    $data['user_checkin_time'] = date('Y-m-d 00:00');
                    $data['user_checkout_time'] = date('Y-m-d 23:59');
                }

                $user_list = new UserlistUsers();
                if (!empty($data['group'])) {
                    $user_list->group_id = $data['group'];
                }
                if (!empty($data['name'])) {
                    $user_list->user_name = $data['name'];
                }
//               
                if (!empty($data['access_rights'])) {
                    $user_list->group_access_id = $data['access_rights'];
                }
                $user_list->user_arrival_notification = $user_arrival_notification;
                $user_list->notify_emails = $notify_emails;
                if (!empty($data['language'])) {
                    $user_list->language_id = $data['language'];
                }
                $user_list->save();
                $userListId = $user_list->id;

                foreach ($number_plates as $key => $number_plate) {
                    $exists_plate = CustomerVehicleInfo::where('num_plate', $number_plate)->first();
                    if ($exists_plate != null) {
                        $responsewarn = 1;
                        $duplicate[] = $number_plate;
                    }
                    $new_vehicle = new CustomerVehicleInfo();
                    $new_vehicle->num_plate = $number_plate;
                    $new_vehicle->userlist_user_id = $userListId;
                    $new_vehicle->save();
                    $created_vehicle_id = $new_vehicle->id;

                    $bookings = new Bookings();
                    $bookings->customer_vehicle_info_id = $created_vehicle_id;
                    $bookings->checkin_time = $data['user_checkin_time'];
                    $bookings->checkout_time = $data['user_checkout_time'];
                    $bookings->type = 3;
                    $bookings->vehicle_num = $number_plate;
                    $bookings->first_name = $data['name'];
                    $bookings->is_paid = $data['is_paid'];
                    $bookings->sender_name = auth()->user()->name;
                    $bookings->user_arrival_notification = $user_arrival_notification;
                    $bookings->save();

                    $bookingPayments = BookingPayments::where('booking_id', $bookings->id)->first();
                    if (!$bookingPayments) {
                        $bookingPayments = new BookingPayments();
                    }
                    $bookingPayments->booking_id = $bookings->id;
                    $bookingPayments->checkin_time = $data['user_checkin_time'];
                    $bookingPayments->checkout_time = $data['user_checkout_time'];
                    $bookingPayments->payment_id = 'Userlist Ticket';
                    $bookingPayments->save();

                    // $bookingPaymentsId = $bookingPayments->id;
                }
                $settings = new Settings\Settings();
                $settings->settings_updated('userlist_users');
                Session::flash('heading', 'Success!');
                Session::flash('message', __('user-list.userlist_add'));
                Session::flash('icon', 'success');
                if ($responsewarn == 1) {
                    return redirect('user-list')->with('warning', __('user-list.userlist_add_duplicate_plate') . "{" . implode(',', $duplicate) . "}");
                }
                return redirect('user-list');
            } else {
                $imagePath = "";
                if ($file = $request->file('pic_file')) {
                    $extension = $file->extension() ?: 'png';
                    $destinationPath = public_path('/uploads/users');
                    $imagePath = str_random(10) . '.' . $extension;
                    $file->move($destinationPath, $imagePath);
                    $data['image_path'] = $imagePath;
                    $data['image_url'] = url('/uploads/users/' . $imagePath);
                }

                $language = Language::find($data['language']);
                $data['language_live_id'] = $language->live_id;
                $data['vehicle_live_id'] = 0;
                $locationTiming = \App\LocationTimings::where('week_day_num', date('w'))
                        ->where('is_whitelist', 1)
                        ->first();
                if ($locationTiming) {
                    $data['user_checkin_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->opening_time));
                    $data['user_checkout_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->closing_time));
                }
                $userListUser = UserlistUsers::where([
                            ['email', $data['email']]
                        ])->first();
                if ($userListUser) {
                    Session::flash('heading', 'Error!');
                    Session::flash('message', __('user-list.userlist_add_already'));
                    Session::flash('icon', 'error');
                    return redirect()->back()->withInput();
                }
                $user_list = new UserlistUsers();
                if (array_key_exists('group', $data)) {
                    $user_list->group_id = $data['group'];
                }
                if ($created_vehicle_id) {
                    $user_list->user_vehicle = $created_vehicle_id;
                }
                $user_list->has_email = 1;
                if (!empty($data['name'])) {
                    $user_list->user_name = $data['name'];
                }
                if (!empty($data['email'])) {
                    $user_list->email = $data['email'];
                }
                if (!empty($data['note'])) {
                    $user_list->notation = $data['note'];
                }
                if (!empty($data['access_rights'])) {
                    $user_list->group_access_id = $data['access_rights'];
                }
                $user_list->bike_range_start = $data['bike_selector_from'];
                $user_list->bike_range_end = $data['bike_selector_to'];
                $user_list->door_range_start = $data['door_selector_from'];
                $user_list->door_range_end = $data['door_selector_to'];
                $user_list->ev_charger_range_start = $data['ev_charger_selector_from'];
                $user_list->ev_charger_range_end = $data['ev_charger_selector_to'];
                if (!empty($data['language'])) {
                    $user_list->language_id = $data['language'];
                }
                if ($imagePath != NULL || $imagePath != "") {
                    $user_list->profile_image = $imagePath;
                }
                if (!empty($data['energy_limit'])) {
                    $user_list->energy_limit = $data['energy_limit'];
                }
                if (!empty($data['phone'])) {
                    $user_list->user_phone = $data['phone'];
                }
                $customer = new Customer();
                $customer->name = $data['name'];
                $customer->email = $data['email'];
                $customer->save();
                $customer_id = $customer->id;

                $user_list->customer_id = $customer->id;

                $name_array = explode(' ', $data['name']);

                $customerProfile = new Profile();
                $customerProfile->customer_id = $customer->id;
                if (count($name_array) > 0) {
                    if (count($name_array) == 1) {
                        $customerProfile->first_name = $name_array[0];
                    } else {
                        $customerProfile->first_name = $name_array[0];
                        $customerProfile->last_name = $name_array[1];
                    }
                }
                $customerProfile->phone_num = $data['phone'];
                if ($imagePath != NULL || $imagePath != "") {
                    $customerProfile->pic = $imagePath;
                }
                $customerProfile->is_customer = 1;
                $customerProfile->save();
                $user_list->user_arrival_notification = $user_arrival_notification;
                $user_list->notify_emails = $notify_emails;
                $user_list->save();
                $created_user_list = $user_list->id;

                if (!empty($number_plates)) {
                    foreach ($number_plates as $key => $number_plate) {
                        $exists_plate = CustomerVehicleInfo::where('num_plate', $number_plate)->first();
                        if ($exists_plate != null) {
                            $responsewarn = 1;
                            $duplicate[] = $number_plate;
                        }
                        $new_vehicle = new CustomerVehicleInfo();
                        $new_vehicle->num_plate = $number_plate;
                        $new_vehicle->userlist_user_id = $created_user_list;
                        $new_vehicle->save();
                        $bookings = new Bookings();

                        $bookings->customer_id = $customer_id;
                        $bookings->customer_vehicle_info_id = $new_vehicle->id;
                        $bookings->checkin_time = $data['user_checkin_time'];
                        $bookings->checkout_time = $data['user_checkout_time'];
                        $bookings->type = 3;
                        $bookings->vehicle_num = $number_plate;
                        $bookings->is_paid = $data['is_paid'];
                        $bookings->phone_number = $data['phone'];
                        $bookings->first_name = $data['name'];
                        $bookings->email = $data['email'];
                        $bookings->is_paid = $data['is_paid'];
                        $bookings->sender_name = auth()->user()->name;
                        $bookings->user_arrival_notification = $user_arrival_notification;
                        $bookings->save();

                        $bookingsId = $bookings->id;

                        $bookingPayments = BookingPayments::where('booking_id', $bookingsId)->first();
                        if (!$bookingPayments) {
                            $bookingPayments = new BookingPayments();
                        }
                        $bookingPayments->customer_id = $customer_id;
                        $bookingPayments->booking_id = $bookings->id;
                        $bookingPayments->checkin_time = $data['user_checkin_time'];
                        $bookingPayments->checkout_time = $data['user_checkout_time'];
                        $bookingPayments->payment_id = 'Userlist Ticket';
                        $bookingPayments->save();
                    }
                }
                $time = date("Y-m-d H:i:s", strtotime('+24 hours'));
                $email_notification = new \App\EmailNotification();
                $email_notification->customer_id = $customer_id;
                $email_notification->ticket_token = rand();
                $email_notification->type = "user_list";
                $email_notification->type_id = $created_user_list;
                $email_notification->has_sent = 0;
                $email_notification->checkin_time = $data['user_checkin_time'];
                $email_notification->checkout_time = $data['user_checkout_time'];
                $email_notification->save();
                $settings = new Settings\Settings();
                $settings->settings_updated('userlist_users');

                Session::flash('heading', 'Success!');
                Session::flash('message', __('user-list.userlist_add'));
                Session::flash('icon', 'success');
                if ($responsewarn == 1) {
                    return redirect('user-list')->with('warning', __('user-list.userlist_add_duplicate_plate') . "{" . implode(',', $duplicate) . "}");
                }
                return redirect('user-list');
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_without_email(Request $request) {
        //
        try {
            $created_vehicle_id = FALSE;
            $this->validate($request, [
                'name' => 'required|min:3',
                'vehicle_no' => 'required',
            ]);

            $data = $request->all();
            $data['vehicle_no'] = str_replace(array(' ', '-', '\'', '"', ',', ';', '<', '>'), '', $data['vehicle_no']);
            $data['is_paid'] = 1;
            $data['user_checkin_time'] = NULL;
            $data['user_checkout_time'] = NULL;
            $data['uservehicle_live_id'] = 0;
            $locationTiming = \App\LocationTimings::where('week_day_num', date('w'))
                    ->where('is_whitelist', 1)
                    ->first();
            if ($locationTiming) {
                $data['user_checkin_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->opening_time));
                $data['user_checkout_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->closing_time));
            } else {
                $data['user_checkin_time'] = date('Y-m-d 00:00');
                $data['user_checkout_time'] = date('Y-m-d 23:59');
            }
            $customer_vehicle_info = CustomerVehicleInfo::where('num_plate', $data['vehicle_no'])
                    ->orderBy('created_at', 'DESC')
                    ->first();
            if ($customer_vehicle_info) {
                $created_vehicle_id = $customer_vehicle_info->id;
                $data['uservehicle_live_id'] = $customer_vehicle_info->live_id;
                $userListUser = UserlistUsers::where([
                            ['user_vehicle', $customer_vehicle_info->id]
                        ])->first();
                if ($userListUser) {
                    Session::flash('heading', 'Error!');
                    Session::flash('message', __('user-list.userlist_use_vehicle_already'));
                    Session::flash('icon', 'error');
                    return redirect()->back()->withInput();
                }
            } else {
                $new_vehicle = new CustomerVehicleInfo();
                $new_vehicle->num_plate = $data['vehicle_no'];
                $new_vehicle->save();
                $created_vehicle_id = $new_vehicle->id;
            }
            $user_list = new UserlistUsers();
            if (!empty($data['group'])) {
                $user_list->group_id = $data['group'];
            }
            if (!empty($data['name'])) {
                $user_list->user_name = $data['name'];
            }
            if ($created_vehicle_id) {
                $user_list->user_vehicle = $created_vehicle_id;
            }
            $user_list->save();

            $userListId = $user_list->id;

            $bookings = new Bookings();
            $bookings->customer_vehicle_info_id = $created_vehicle_id;
            $bookings->checkin_time = $data['user_checkin_time'];
            $bookings->checkout_time = $data['user_checkout_time'];
            $bookings->type = 3;
            $bookings->vehicle_num = $data['vehicle_no'];
            $bookings->first_name = $data['name'];
            $bookings->is_paid = $data['is_paid'];
            $bookings->sender_name = auth()->user()->name;
            $bookings->save();

            $bookingsId = $bookings->id;

            $bookingPayments = BookingPayments::where('booking_id', $bookingsId)->first();
            if (!$bookingPayments) {
                $bookingPayments = new BookingPayments();
            }
            $bookingPayments->booking_id = $bookingsId;
            $bookingPayments->checkin_time = $data['user_checkin_time'];
            $bookingPayments->checkout_time = $data['user_checkout_time'];
            $bookingPayments->payment_id = 'Userlist Ticket';
            $bookingPayments->save();

            $bookingPaymentsId = $bookingPayments->id;

            $locationOption = LocationOptions::find(1);
            $locationId = $locationOption->live_id;
            $responseData['success'] = 0;
            try {
                $http = new Client();

                $response = $http->post(env('API_BASE_URL') . '/api/import-single-userlist-without-email-data', [
                    'form_params' => [
                        'location_id' => $locationId,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
                if ($responseData['success'] && isset($responseData['data']['uservehicle_live_id'])) {
                    $customer_vehicle_info = CustomerVehicleInfo::find($created_vehicle_id);
                    $customer_vehicle_info->live_id = $responseData['data']['uservehicle_live_id'];
                    $customer_vehicle_info->save();
                }
                if ($responseData['success'] && isset($responseData['data']['userlist_live_id'])) {
                    $user_list = UserlistUsers::find($userListId);
                    $user_list->live_id = $responseData['data']['userlist_live_id'];
                    $user_list->save();
                }
                if ($responseData['success'] && isset($responseData['data']['booking_info_live_id'])) {
                    $booking = Bookings::find($bookingsId);
                    $booking->live_id = $responseData['data']['booking_info_live_id'];
                    $booking->save();
                }
                if ($responseData['success'] && isset($responseData['data']['booking_payment_live_id'])) {
                    $booking_payment = BookingPayments::find($bookingPaymentsId);
                    $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                    $booking_payment->save();
                }
            } catch (\Exception $ex) {
                
            }
            if ($responseData['success'] && isset($responseData['data'])) {
                $settings = new Settings\Settings();
                $settings->settings_updated('userlist_users');
                Session::flash('heading', 'Success!');
                Session::flash('message', __('user-list.userlist_add'));
                Session::flash('icon', 'success');
                return redirect('user-list');
            } else {
                $settings = new Settings\Settings();
                $settings->settings_updated('userlist_users');
                Session::flash('heading', 'Warning!');
                Session::flash('message', __('user-list.userlist_add_localy'));
                Session::flash('icon', 'warning');
                return redirect('user-list');
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
        $userListUser = UserlistUsers::with(
                        'customer', 'customer_vehicle_info'
                )->findOrfail($id);
        $user_vehicles_info = CustomerVehicleInfo::where('userlist_user_id', $id)->get();
        $languages = Language::all();
        $groups = Group::all();
        $rights = \App\GroupAccess::all();
        return view('user-list.edit', compact('user_vehicles_info', 'userListUser', 'languages', 'groups', 'rights'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        try {
            $this->validate($request, [
                'name' => 'required|min:3',
                'energy_limit' => 'required',
                'language' => 'required',
            ]);
            $total_plates = $request->total_plates;
            if ($total_plates <= 0 && !isset($request->plates)) {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('user-list.atleast_one_plate'));
                Session::flash('icon', 'error');
                return redirect()->back()->withInput();
            }
            $responseData['success'] = 0;
            $responsewarn = 0;
            $data['email'] = NULL;
            $customer_id = NULL;
            $data['user_checkin_time'] = NULL;
            $data['user_checkout_time'] = NULL;
            $user_arrival_notification = 0;
            $notify_emails = NULL;
            if (isset($request->user_arrival_notification) && $request->user_arrival_notification) {
                $user_arrival_notification = 1;
                $validator = Validator::make($request->all(), [
                            'notify_email' => 'required'
                ]);
                if ($validator->fails()) {
                    Session::flash('heading', 'Error!');
                    Session::flash('message', __('Emails for recieve notifications is required'));
                    Session::flash('icon', 'error');
                    return redirect()->back()->withInput();
                }
                $notify_emails = $request->notify_email;
                $explode_notify_emails = explode(',', str_replace(' ', '', $request->notify_email));
                foreach ($explode_notify_emails as $explode_notify_email) {
                    $validator = Validator::make(
                                    array('email' => $explode_notify_email), array('email' => array('required', 'email'))
                    );
                    if ($validator->fails()) {
                        Session::flash('heading', 'Error!');
                        Session::flash('message', __($explode_notify_email . ' is not a valid email address in field (Emails for recieve notifications)'));
                        Session::flash('icon', 'error');
                        return redirect()->back()->withInput();
                    }
                }
            }
            $data = $request->all();
            $userListUser = UserlistUsers::find($id);
            $number_plates = array();
            $duplicate = array();
            if (isset($data['plates'])) {
                foreach ($data['plates'] as $plate) {
                    $number_plates[] = str_replace(array(' ', '-', '\'', '"', ',', ';', '<', '>'), '', $plate);
                }
            }
            $removed = $data['removed'];

            $imagePath = "";
            if ($file = $request->file('pic_file')) {
                $extension = $file->extension() ?: 'png';
                $destinationPath = public_path('/uploads/users');
                $imagePath = str_random(10) . '.' . $extension;
                $file->move($destinationPath, $imagePath);
                $data['image_path'] = $imagePath;
                $data['image_url'] = url('/uploads/users/' . $imagePath);
            }
            if ($userListUser != null) {
                if ($userListUser->customer_id) {
                    $customer = Customer::where('id', $userListUser->customer_id)
                            ->first();
                    if ($customer) {
                        $customer->name = $data['name'];
                        $customer->user_arrival_notification = $user_arrival_notification;
                        $customer->notify_emails = $notify_emails;
                        if (isset($data['email']) && !empty($data['email'])) {
                            $customer->email = $data['email'];
                        }
                        $customer->save();
                        $customer_id = $customer->id;

                        $name_array = explode(' ', $data['name']);
                        $customerProfile = Profile::where('customer_id', $customer_id)->first();
                        if ($customerProfile) {
                            if (count($name_array) > 0) {
                                if (count($name_array) == 1) {
                                    $customerProfile->first_name = $name_array[0];
                                } else {
                                    $customerProfile->first_name = $name_array[0];
                                    $customerProfile->last_name = $name_array[1];
                                }
                            }
                            $customerProfile->phone_num = $data['phone'];
                            if ($imagePath != NULL || $imagePath != "") {
                                $customerProfile->pic = $imagePath;
                            }
                            $customerProfile->save();
                        }
                    }
                } else {
                    if (isset($data['email']) && !empty($data['email'])) {
                        $userListUser->has_email = 1;
                        $locationTiming = \App\LocationTimings::where('week_day_num', date('w'))
                                ->where('is_whitelist', 1)
                                ->first();
                        if ($locationTiming) {
                            $data['user_checkin_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->opening_time));
                            $data['user_checkout_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->closing_time));
                        }
                        $customer = new Customer();
                        $customer->name = $data['name'];
                        $customer->email = $data['email'];
                        $customer->save();
                        $customer->user_arrival_notification = $user_arrival_notification;
                        $customer->notify_emails = $notify_emails;
                        $customer_id = $customer->id;


                        $name_array = explode(' ', $data['name']);

                        $customerProfile = new Profile();
                        $customerProfile->customer_id = $customer_id;
                        if (count($name_array) > 0) {
                            if (count($name_array) == 1) {
                                $customerProfile->first_name = $name_array[0];
                            } else {
                                $customerProfile->first_name = $name_array[0];
                                $customerProfile->last_name = $name_array[1];
                            }
                        }
                        $customerProfile->phone_num = $data['phone'];
                        if ($imagePath != NULL || $imagePath != "") {
                            $customerProfile->pic = $imagePath;
                        }
                        $customerProfile->is_customer = 1;
                        $customerProfile->save();
                        $customer_vehicle_info = \App\CustomerVehicleInfo::where('userlist_user_id', $id)
                                ->first();
                        $booking=\App\Bookings::where('customer_vehicle_info_id', $customer_vehicle_info->id)
                                ->update(['customer_id' => $customer_id]);
                        $find_email_notification = \App\EmailNotification::where('type_id', $id)
                                ->first();
                        if (!$find_email_notification) {
                            $email_notification = new \App\EmailNotification();
                            $email_notification->customer_id = $customer_id;
                            $email_notification->ticket_token = rand();
                            $email_notification->type = "user_list";
                            $email_notification->type_id = $id;
                            $email_notification->has_sent = 0;
                            $email_notification->checkin_time = $data['user_checkin_time'];
                            $email_notification->checkout_time = $data['user_checkout_time'];
                            $email_notification->save();
                        }
                    }
                }

                $data['is_paid'] = 1;
                $data['userlist_live_id'] = $userListUser->live_id;
                $data['customer_live_id'] = 0;
                $userListUser->customer_id = $customer_id;
                $userListUser->has_email = 0;
                if (array_key_exists('group', $data)) {
                    $userListUser->group_id = $data['group'];
                }
                if (!empty($data['name'])) {
                    $userListUser->user_name = $data['name'];
                    $customer_vehicle_info = \App\CustomerVehicleInfo::where('userlist_user_id', $id)
                            ->first();
                    \App\Bookings::where('customer_vehicle_info_id', $customer_vehicle_info->id)
                            ->update(['first_name' => $data['name']]);
                }
                if (!empty($data['email'])) {
                    $userListUser->has_email = 1;
                    $userListUser->email = $data['email'];
                }
                if (!empty($data['note'])) {
                    $userListUser->notation = $data['note'];
                }
                if (!empty($data['access_rights'])) {
                    $userListUser->group_access_id = $data['access_rights'];
                } else {
                    $userListUser->group_access_id = NULL;
                }
                if (!empty($data['language'])) {
                    $userListUser->language_id = $data['language'];
                }
                $userListUser->bike_range_start = $data['bike_selector_from'];
                $userListUser->bike_range_end = $data['bike_selector_to'];
                $userListUser->door_range_start = $data['door_selector_from'];
                $userListUser->door_range_end = $data['door_selector_to'];
                $userListUser->ev_charger_range_start = $data['ev_charger_selector_from'];
                $userListUser->ev_charger_range_end = $data['ev_charger_selector_to'];

                if ($imagePath != NULL || $imagePath != "") {
                    $userListUser->profile_image = $imagePath;
                }
                if (!empty($data['energy_limit'])) {
                    $userListUser->energy_limit = $data['energy_limit'];
                }
                if (!empty($data['phone'])) {
                    $userListUser->user_phone = $data['phone'];
                }
                $userListUser->user_arrival_notification = $user_arrival_notification;
                $userListUser->notify_emails = $notify_emails;
                $userListUser->save();
                //delete vehicles, bookings,payments
                if (isset($removed)) {
                    $removed = explode(',', $removed);
                    foreach ($removed as $remove) {
                        $customer_vehicle_info = \App\CustomerVehicleInfo::where('num_plate', $remove)
                                ->where('userlist_user_id', $id)
                                ->first();
                        $booking = \App\Bookings::where('vehicle_num', $remove)
                                ->where('customer_vehicle_info_id', $customer_vehicle_info->id)
                                ->first();
                        $payments = \App\BookingPayments::where('booking_id', $booking->id)->first();
                        $customer_vehicle_info->forceDelete();
                        $booking->forceDelete();
                        $payments->forceDelete();
                    }
                }
                //create new vehicles
                if (!empty($number_plates)) {
                    foreach ($number_plates as $key => $number_plate) {
                        $exists_plate = CustomerVehicleInfo::where('num_plate', $number_plate)->where('userlist_user_id', '<> ', $userListUser->id)->first();
                        if ($exists_plate != null) {
                            $responsewarn = 1;
                            $duplicate[] = $number_plate;
                        }
                        $new_vehicle = new CustomerVehicleInfo();
                        $new_vehicle->num_plate = $number_plate;
                        $new_vehicle->userlist_user_id = $userListUser->id;
                        $new_vehicle->customer_id = $customer_id;
                        $new_vehicle->save();
                        $created_vehicle_id = $new_vehicle->id;

                        $bookings = new Bookings();
                        $bookings->customer_vehicle_info_id = $created_vehicle_id;
                        $bookings->type = 3;
                        $bookings->vehicle_num = $number_plate;
                        $bookings->is_paid = $data['is_paid'];
                        $bookings->phone_number = $data['phone'];
                        $bookings->first_name = $data['name'];
                        $bookings->email = $userListUser->email;
                        $bookings->customer_id = $customer_id;
                        $bookings->sender_name = auth()->user()->name;
                        $bookings->user_arrival_notification = $user_arrival_notification;
                        $bookings->save();

                        $bookingsId = $bookings->id;


                        $bookingPayments = new BookingPayments();
                        $bookingPayments->booking_id = $bookingsId;
                        $bookingPayments->payment_id = 'Userlist Ticket';
                        $bookingPayments->save();
                    }
                }
                $vehicle_ids = $userListUser->customer_vehicle_info()->pluck('id')->toArray();
                Bookings::whereIn('customer_vehicle_info_id', $vehicle_ids)->update(array('first_name' => $data['name'], 'user_arrival_notification' => $user_arrival_notification));
                Session::flash('heading', 'Success!');
                Session::flash('message', __('user-list.userlist_update'));
                Session::flash('icon', 'success');
                if ($responsewarn == 1) {
                    return redirect('user-list')->with('warning', __('user-list.userlist_update_duplicate_plate') . "{" . implode(',', $duplicate) . "}");
                }
                return redirect('user-list');
//                if ($responseData['success'] && isset($responseData['data'])) {
//                    $settings = new Settings\Settings();
//                    $settings->settings_updated('userlist_users');
//                    Session::flash('heading', 'Success!');
//                    Session::flash('message', __('user-list.userlist_update'));
//                    Session::flash('icon', 'success');
//                    return redirect('user-list');
//                } else {
//                    Session::flash('heading', 'Warning!');
//                    Session::flash('message', __('user-list.userlist_update_localy'));
//                    Session::flash('icon', 'warning');
//                    return redirect('user-list');
//                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('user-list.userlist_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('userlist-edit', $e->getMessage(), $e->getTraceAsString());
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_without_email(Request $request, $id) {
        //
        try {
            $created_vehicle_id = FALSE;
            $this->validate($request, [
                'name' => 'required|min:3',
                'vehicle_no' => 'required',
            ]);

            $data = $request->all();
            $data['vehicle_no'] = str_replace(array(' ', '-', '\'', '"', ',', ';', '<', '>'), '', $data['vehicle_no']);
            $data['is_paid'] = 1;
            $data['user_checkin_time'] = NULL;
            $data['user_checkout_time'] = NULL;
            $data['userlist_live_id'] = 0;
            $data['uservehicle_live_id'] = 0;
            $data['booking_info_live_id'] = 0;
            $data['booking_payment_live_id'] = 0;
            $locationTiming = \App\LocationTimings::where('week_day_num', date('w'))
                    ->where('is_whitelist', 1)
                    ->first();
            if ($locationTiming) {
                $data['user_checkin_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->opening_time));
                $data['user_checkout_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->closing_time));
            }
            $user_list = UserlistUsers::find($id);
            if ($user_list) {
                $userListId = $user_list->id;
                $data['userlist_live_id'] = $user_list->live_id;
                $customer_vehicle_info = CustomerVehicleInfo::where('num_plate', $data['vehicle_no'])
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($customer_vehicle_info) {
                    $created_vehicle_id = $customer_vehicle_info->id;
                    $data['uservehicle_live_id'] = $customer_vehicle_info->live_id;
                } else {
                    $new_vehicle = new CustomerVehicleInfo();
                    $new_vehicle->num_plate = $data['vehicle_no'];
                    $new_vehicle->save();
                    $created_vehicle_id = $new_vehicle->id;
                }
                $user_list->has_email = 0;
                if (array_key_exists('group', $data) && !empty($data['group'])) {
                    $user_list->group_id = $data['group'];
                }
                if (!empty($data['name'])) {
                    $user_list->user_name = $data['name'];
                }
                if ($created_vehicle_id) {
                    $user_list->user_vehicle = $created_vehicle_id;
                }
                $user_list->save();
                $bookings = Bookings::where('customer_vehicle_info_id', $created_vehicle_id)
                        ->where('vehicle_num', $data['vehicle_no'])
                        ->where('type', 3)
                        ->first();
                if ($bookings) {
                    $bookingsId = $bookings->id;
                    $data['booking_info_live_id'] = $bookings->live_id;
                } else {
                    $bookings = new Bookings();
                }
                $bookings->customer_vehicle_info_id = $created_vehicle_id;
                $bookings->checkin_time = $data['user_checkin_time'];
                $bookings->checkout_time = $data['user_checkout_time'];
                $bookings->type = 3;
                $bookings->vehicle_num = $data['vehicle_no'];
                $bookings->first_name = $data['name'];
                $bookings->is_paid = $data['is_paid'];
                $bookings->sender_name = auth()->user()->name;
                $bookings->save();

                $bookingsId = $bookings->id;

                $bookingPayments = BookingPayments::where('booking_id', $bookingsId)->first();
                if (!$bookingPayments) {
                    $bookingPayments = new BookingPayments();
                }
                $bookingPayments->booking_id = $bookingsId;
                $bookingPayments->checkin_time = $data['user_checkin_time'];
                $bookingPayments->checkout_time = $data['user_checkout_time'];
                $bookingPayments->payment_id = 'Userlist Ticket';
                $bookingPayments->save();

                $bookingPaymentsId = $bookingPayments->id;

                $data['booking_payment_live_id'] = $bookingPayments->live_id;

                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();

                    $response = $http->post(env('API_BASE_URL') . '/api/update-single-userlist-without-email-data', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                    if ($responseData['success'] && isset($responseData['data']['uservehicle_live_id'])) {
                        $customer_vehicle_info = CustomerVehicleInfo::find($created_vehicle_id);
                        $customer_vehicle_info->live_id = $responseData['data']['uservehicle_live_id'];
                        $customer_vehicle_info->save();
                    }
                    if ($responseData['success'] && isset($responseData['data']['userlist_live_id'])) {
                        $user_list = UserlistUsers::find($userListId);
                        $user_list->live_id = $responseData['data']['userlist_live_id'];
                        $user_list->save();
                    }
                    if ($responseData['success'] && isset($responseData['data']['booking_info_live_id'])) {
                        $booking = Bookings::find($bookingsId);
                        $booking->live_id = $responseData['data']['booking_info_live_id'];
                        $booking->save();
                    }
                    if ($responseData['success'] && isset($responseData['data']['booking_payment_live_id'])) {
                        $booking_payment = BookingPayments::find($bookingPaymentsId);
                        $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                        $booking_payment->save();
                    }
                } catch (\Exception $ex) {
                    
                }
                if ($responseData['success'] && isset($responseData['data'])) {
                    $settings = new Settings\Settings();
                    $settings->settings_updated('userlist_users');
                    Session::flash('heading', 'Success!');
                    Session::flash('message', __('user-list.userlist_update'));
                    Session::flash('icon', 'success');
                    return redirect('user-list');
                } else {
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', __('user-list.userlist_update_localy'));
                    Session::flash('icon', 'warning');
                    return redirect('user-list');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('user-list.userlist_not_found'));
                Session::flash('icon', 'error');
            }
        } catch (\Exception $e) {
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
    public function update_quick(Request $request, $id) {
        $validator = Validator::make($request->all(), [
                    'language' => 'required',
        ]);
        if ($validator->passes()) {
            try {
                $data = $request->all();
                $userListUser = UserlistUsers::find($id);
                if (!$userListUser) {
                    return response()->json([
                                'error' => array(
                                    'notFound' => 'Record not Found.'
                                )
                    ]);
                }
                if (array_key_exists('group', $data) && !empty($data['group'])) {
                    $userListUser->group_id = $data['group'];
                }
                if (array_key_exists('language', $data) && !empty($data['language'])) {
                    $userListUser->language_id = $data['language'];
                }
                if (array_key_exists('access_rights', $data) && !empty($data['access_rights'])) {
                    $userListUser->group_access_id = $data['access_rights'];
                }
                $userListUser->save();
                return response()->json([
                            'success' => array(
                                'updated' => 'Your data has been Updated Successfully.'
                            )
                ]);
            } catch (\Exception $e) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('userlist-edit', $e->getMessage(), $e->getTraceAsString());
                return response()->json(['error' => $validator->errors()->all()]);
            }
        } else {
            return response()->json(['error' => $validator->errors()->all()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_quick_without_email(Request $request, $id) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required|min:3',
                    'vehicle_no' => 'required',
        ]);
        if ($validator->passes()) {
            try {
                $data = $request->all();
                $data['vehicle_no'] = str_replace(array(' ', '-', '\'', '"', ',', ';', '<', '>'), '', $data['vehicle_no']);
                $data['is_paid'] = 1;
                $data['user_checkin_time'] = NULL;
                $data['user_checkout_time'] = NULL;
                $data['userlist_live_id'] = 0;
                $data['uservehicle_live_id'] = 0;
                $data['booking_info_live_id'] = 0;
                $data['booking_payment_live_id'] = 0;
                $user_list = UserlistUsers::find($id);
                if (!$user_list) {
                    return response()->json([
                                'error' => array(
                                    'notFound' => __('user-list.userlist_not_found')
                                )
                    ]);
                }
                $data['userlist_live_id'] = $user_list->live_id;
                $locationTiming = \App\LocationTimings::where('week_day_num', date('w'))
                        ->where('is_whitelist', 1)
                        ->first();
                if ($locationTiming) {
                    $data['user_checkin_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->opening_time));
                    $data['user_checkout_time'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->closing_time));
                }
                $customer_vehicle_info = CustomerVehicleInfo::where('num_plate', $data['vehicle_no'])
                        ->orderBy('created_at', 'DESC')
                        ->first();
                if ($customer_vehicle_info) {
                    $created_vehicle_id = $customer_vehicle_info->id;
                    $data['uservehicle_live_id'] = $customer_vehicle_info->live_id;
                } else {
                    $new_vehicle = new CustomerVehicleInfo();
                    $new_vehicle->num_plate = $data['vehicle_no'];
                    $new_vehicle->save();
                    $created_vehicle_id = $new_vehicle->id;
                }
                $user_list->has_email = 0;
                if (array_key_exists('group', $data) && !empty($data['group'])) {
                    $user_list->group_id = $data['group'];
                }
                if (!empty($data['name'])) {
                    $user_list->user_name = $data['name'];
                }
                if ($created_vehicle_id) {
                    $user_list->user_vehicle = $created_vehicle_id;
                }
                $user_list->save();
                $bookings = Bookings::where('customer_vehicle_info_id', $created_vehicle_id)
                        ->where('vehicle_num', $data['vehicle_no'])
                        ->where('type', 3)
                        ->first();
                if ($bookings) {
                    $bookingsId = $bookings->id;
                    $data['booking_info_live_id'] = $bookings->live_id;
                } else {
                    $bookings = new Bookings();
                }
                $bookings->customer_vehicle_info_id = $created_vehicle_id;
                $bookings->checkin_time = $data['user_checkin_time'];
                $bookings->checkout_time = $data['user_checkout_time'];
                $bookings->type = 3;
                $bookings->vehicle_num = $data['vehicle_no'];
                $bookings->first_name = $data['name'];
                $bookings->is_paid = $data['is_paid'];
                $bookings->sender_name = auth()->user()->name;
                $bookings->save();

                $bookingsId = $bookings->id;

                $bookingPayments = BookingPayments::where('booking_id', $bookingsId)->first();
                if (!$bookingPayments) {
                    $bookingPayments = new BookingPayments();
                }
                $bookingPayments->booking_id = $bookingsId;
                $bookingPayments->checkin_time = $data['user_checkin_time'];
                $bookingPayments->checkout_time = $data['user_checkout_time'];
                $bookingPayments->payment_id = 'Userlist Ticket';
                $bookingPayments->save();

                $bookingPaymentsId = $bookingPayments->id;

                $data['booking_payment_live_id'] = $bookingPayments->live_id;

                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData['success'] = 0;
                try {
                    $http = new Client();

                    $response = $http->post(env('API_BASE_URL') . '/api/update-single-userlist-without-email-data', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                    if ($responseData['success'] && isset($responseData['data']['uservehicle_live_id'])) {
                        $customer_vehicle_info = CustomerVehicleInfo::find($created_vehicle_id);
                        $customer_vehicle_info->live_id = $responseData['data']['uservehicle_live_id'];
                        $customer_vehicle_info->save();
                    }
                    if ($responseData['success'] && isset($responseData['data']['userlist_live_id'])) {
                        $user_list = UserlistUsers::find($userListId);
                        $user_list->live_id = $responseData['data']['userlist_live_id'];
                        $user_list->save();
                    }
                    if ($responseData['success'] && isset($responseData['data']['booking_info_live_id'])) {
                        $booking = Bookings::find($bookingsId);
                        $booking->live_id = $responseData['data']['booking_info_live_id'];
                        $booking->save();
                    }
                    if ($responseData['success'] && isset($responseData['data']['booking_payment_live_id'])) {
                        $booking_payment = BookingPayments::find($bookingPaymentsId);
                        $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
                        $booking_payment->save();
                    }
                } catch (\Exception $ex) {
                    
                }
                if ($responseData['success'] && isset($responseData['data'])) {
                    return response()->json([
                                'success' => array(
                                    'updated' => __('user-list.userlist_update')
                                )
                    ]);
                } else {
                    return response()->json([
                                'error' => array(
                                    'notFound' => __('user-list.userlist_update_localy')
                                )
                    ]);
                }
            } catch (\Exception $e) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('userlist-edit', $e->getMessage(), $e->getTraceAsString());
                return response()->json(['error' => $validator->errors()->all()]);
            }
        } else {
            return response()->json(['error' => $validator->errors()->all()]);
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
            $userListUser = UserlistUsers::find($id);
            if ($userListUser != null) {
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;

                $delete_controller = new DeleteController();
                $delete_controller->delete_userlist_user($userListUser);
                $userListUser->forceDelete();
                $settings = new Settings\Settings();
                $settings->settings_updated('userlist_users');
                Session::flash('heading', 'Success!');
                Session::flash('message', __('user-list.userlist_delete'));
                Session::flash('icon', 'success');
                return redirect('user-list');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('user-list.userlist_not_found'));
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
     * Bolck Or Unblock the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bockOrUnblockUser($id) {
        //
        try {
            $userListUser = UserlistUsers::find($id);
            if ($userListUser != null) {

                $userListUser->is_blocked == 0 ? $userListUser->is_blocked = 1 : $userListUser->is_blocked = 0;
                $userListUser->save();
                $settings = new Settings\Settings();
                $settings->settings_updated('userlist_users');
                Session::flash('heading', 'Success!');
                if ($userListUser->is_blocked == 1) {
                    Session::flash('message', __('user-list.userlist_block'));
                } else {
                    Session::flash('message', __('user-list.userlist_unblock'));
                }
                Session::flash('icon', 'success');
                return redirect('user-list');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('user-list.userlist_not_found'));
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
        $userListUser = UserlistUsers::find($id);
        if ($userListUser != null) {
            try {
                $customer = $userListUser->customer;
                $profile = \App\Profile::where('customer_id', $customer->id)->first();
                $booking = \App\Bookings::where('customer_id', $customer->id)->first();
                $locationOption = \App\LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $email_notification = \App\EmailNotification::where('type_id', $userListUser->id)->where('type', 'user_list')->first();
                $emailType = $email_notification->type ?: "user_list";
                if ($email_notification) {
                    $data = array(
                        "Name" => $customer->name,
                        "Phone" => $profile->phone_num,
                        "Email" => $customer->email,
                        "BeginDate" => $booking->checkin_time,
                        "BeginTime" => $booking->checkin_time,
                        "Type" => $emailType,
                        "EndDate" => $booking->checkout_time,
                        "EndTime" => $booking->checkout_time
                    );
                    $locationTiming = \App\LocationTimings::where('week_day_num', date('w'))
                            ->where('is_whitelist', 1)
                            ->first();
                    if ($locationTiming) {
                        $data['BeginTime'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->opening_time));
                        $data['EndTime'] = date('Y-m-d') . ' ' . date('H:i', strtotime($locationTiming->closing_time));
                    }
                    $responseData = array();
                    $responseData['success'] = FALSE;

                    $http = new Client();
                    $data['ticket_token'] = $email_notification->ticket_token;
                    $response = $http->post(env('API_BASE_URL') . '/api/send-ticket-instant', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                    if ($responseData['success']) {
                        $email_notification->has_sent = 1;
                    }
                    Session::flash('heading', 'Success!');
                    Session::flash('message', __('send-ticket.ticket_send'));
                    Session::flash('icon', 'success');
                } else {
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', __('user-list.went_wrong'));
                    Session::flash('icon', 'warning');
                    return redirect('user-list');
                }
                return redirect('user-list');
            } catch (\Exception $ex) {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('user-list.went_wrong'));
                Session::flash('icon', 'error');
                //return redirect('user-list');
                //return $this->sendError('Exception', $e->getMessage());
            }
        }
    }

    /**
     * Check User Status the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function checkUserStatus(Request $request) {
        $response = array();
        $response['vehicles'] = array();
        $response['vehicles_found'] = 0;
        $customer = Customer::with('profile')->where('email', '=', $request->email)->first();
        if ($customer) {
            $first_name = '';
            $last_name = '';
            if (!empty($customer->profile->first_name)) {
                $first_name = $customer->profile->first_name;
            }
            if (!empty($customer->profile->last_name)) {
                $last_name = $customer->profile->last_name;
            }
            $response['status'] = 1;
            $response['data'] = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
            );
            if ($first_name == '' && $last_name == '') {
                $response['status'] = 0;
            }
            $customer_vehicles = CustomerVehicleInfo::where('customer_id', $customer->id)->get();
            if ($customer_vehicles->count() > 0) {
                $response['vehicles_found'] = 1;
                foreach ($customer_vehicles as $customer_vehicle) {
                    $response['vehicles'][] = array(
                        'title' => $customer_vehicle->name . ' ' . $customer_vehicle->num_plate,
                        'id' => $customer_vehicle->id
                    );
                }
            }
        } else {
            $response['status'] = 0;
        }
        return response()->json(['response' => $response]);
    }

    /**
     * Check User Status the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function checkVehicleStatus(Request $request) {
        $customer_vehicle_info = CustomerVehicleInfo::where('num_plate', $request->vehicle_no)
                ->orderBy('created_at', 'DESC')
                ->first();
        if ($customer_vehicle_info) {
            $userListUser = UserlistUsers::where([
                        ['user_vehicle', $customer_vehicle_info->id]
                    ])->first();
            if ($userListUser) {
                return response()->json(['valid' => FALSE]);
            }
        }
        return response()->json(['valid' => TRUE]);
    }

}
