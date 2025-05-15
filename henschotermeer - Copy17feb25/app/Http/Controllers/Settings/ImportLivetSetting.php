<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use App\Language;
use App\LocationOptions;
use App\LocationImages;
use App\LocationTimings;
use App\UserlistUsers;
use App\WhitelistUsers;
use App\User;
use App\Customer;
use App\Profile;
use App\CustomerVehicleInfo;
use App\Bookings;
use App\BookingPayments;
use App\Attendants;
use App\AttendantTransactions;
use App\Products;
use App\TommyReservationParents;
use App\TommyReservationChildrens;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class ImportLivetSetting extends Controller
{

    public $key = FALSE;
    public $url = "";

    public function __construct($key = NULL)
    {
        $this->url = env('API_BASE_URL');
        if ($key !== NULL) {
            $this->key = $key;
        } else {
            $user = \App\User::first();
            if ($user) {
                $location_setting = \App\LocationOptions::first();
                if ($location_setting) {
                    $key = $location_setting->live_id . '_' . $user->live_id;
                    $this->key = base64_encode($key);
                }
            }
        }
    }

    public function import_admin_details($data)
    {

        try {
            $checkUserStatus = User::where(
                'live_id',
                '=',
                $data['id']
            )->first();
            if (!$checkUserStatus) {
                $adminUser = new User();
                $adminUser->live_id = $data['id'];
                $adminUser->name = $data['name'] != '' ? $data['name'] : $data['user_profile']['first_name'] . ' ' . $data['user_profile']['last_name'];
                $adminUser->email = $data['email'];
                $adminUser->password = $data['password'];
                $adminUser->status = 1;
                $adminUser->save();
                $adminUserId = $adminUser->id;

                $admin = User::first();
                $admin_role = \App\Role::firstOrcreate(['name' => 'admin']);
                $permission = \App\Permission::firstOrcreate(['name' => 'All Permission']);
                if ($admin) {
                    if (!$admin->hasRole('admin')) {
                        $admin->assignRole('admin');
                        $admin_role->givePermissionTo($permission);
                    }
                }

                $profile = new Profile();
                $profile->user_id = $adminUserId;
                $profile->first_name = $data['user_profile']['first_name'];
                $profile->last_name = $data['user_profile']['last_name'];
                $profile->phone_num = $data['user_profile']['phone_num'];
                //            $profile->pic = $data['user_profile']['profile_picture'];
                $profile->is_customer = 0;
                $profile->save();
            } else {
                $adminUser = \App\User::where('live_id', '<>', 0)->first();
                if (!$adminUser) {
                    $adminUser = new \App\User();
                }
                $adminUser->live_id = $data['id'];
                if (!empty($data['name'])) {
                    $adminUser->name = $data['name'];
                } else {
                    if (array_key_exists('user_profile', $data)) {
                        $adminUser->name = $data['user_profile']['first_name'] . ' ' . $data['user_profile']['last_name'];
                    }
                }
                $adminUser->email = $data['email'];
                $adminUser->password = $data['password'];
                $adminUser->status = 1;
                $adminUser->save();

                if (array_key_exists('user_profile', $data)) {
                    $profile = Profile::where('user_id', $adminUser->id)->first();
                    if (!$profile) {
                        $profile = new \App\Profile();
                    }
                    $profile->first_name = $data['user_profile']['first_name'];
                    $profile->last_name = $data['user_profile']['last_name'];
                    $profile->phone_num = $data['user_profile']['phone_num'];
                    $profile->is_customer = 0;
                    $profile->save();
                }
            }
            $service = User::where('email', '=', 'service@parkingshop.com')->first();
            if ($service == null) {
                $service = new User();
                $service->email = 'service@parkingshop.com';
                $service->name = 'Service';
                $service->password = bcrypt("oneunit");
                $service->save();
            }
            if ($service->profile == null) {
                $profile = new Profile();
                $profile->user_id = $service->id;
                $profile->save();
            }
            if (!$service->hasRole('service')) {
                $service->assignRole('service');
            }
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-admin', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function import_location_details($data)
    {
        try {
            foreach ($data['data']['languages'] as $language) {
                $is_lang_exist = Language::where('live_id', $language['id'])->first();
                if ($is_lang_exist) {
                    continue;
                }
                $languages = new Language();
                $languages->live_id = $language['id'];
                $languages->name = $language['name'];
                $languages->code = $language['code'];
                $languages->country = $language['country'];
                $languages->save();
            }
            $locationOptions = LocationOptions::first();
            if (!$locationOptions) {
                $locationOptions = new LocationOptions();
            }
            $locationOptions->live_id = $data['data']['location']['details']['id'];
            $locationOptions->address = $data['data']['location']['details']['address'];
            $locationOptions->avaialable_spots = $data['data']['location']['details']['avaialable_spots'];
            $locationOptions->is_covered = $data['data']['location']['details']['is_covered'];
            $locationOptions->is_gated = $data['data']['location']['details']['is_gated'];
            $locationOptions->other_specs = $data['data']['location']['details']['other_specs'];
            $locationOptions->description = $data['data']['location']['details']['description'];
            $locationOptions->total_spots = $data['data']['location']['details']['total_spots'];
            $locationOptions->title = $data['data']['location']['details']['title'];
            $locationOptions->city_country = $data['data']['location']['details']['city_country'];
            $locationOptions->is_external_link = $data['data']['location']['details']['is_external_link'];
            $locationOptions->external_link = $data['data']['location']['details']['external_link'];
            $locationOptions->postal_code = $data['data']['location']['details']['postal_code'];
            $locationOptions->extra_features = $data['data']['location']['details']['extra_features'];
            $locationOptions->is_approved = $data['data']['location']['details']['is_approved'];
            $locationOptions->is_completed = $data['data']['location']['details']['is_completed'];
            $locationOptions->is_active = $data['data']['location']['details']['is_active'];
            $locationOptions->disapproved_message = $data['data']['location']['details']['disapproved_message'];
            $locationOptions->height_restriction_value = empty($data['data']['location']['details']['height_restriction_value']) ? 0 : $data['data']['location']['details']['height_restriction_value'];
            $locationOptions->access_point = $data['data']['location']['details']['access_point'];
            $locationOptions->ev_charger = $data['data']['location']['details']['ev_charger'];
            $locationOptions->owner_phone_num = $data['data']['location']['details']['owner_phone_num'];
            $locationOptions->location_type = $data['data']['location']['details']['location_type'];
            $locationOptions->owner_operator_name = $data['data']['location']['details']['owner_operator_name'];
            $locationOptions->max_stay = $data['data']['location']['details']['max_stay'];
            $locationOptions->advance_booking_limit = $data['data']['location']['details']['advance_booking_limit'];
            $locationOptions->barcode_series = $data['data']['location']['details']['barcode_series'];
            $locationOptions->is_whitelist = $data['data']['location']['details']['is_whitelist'];
            $locationOptions->bike_range_start = $data['data']['location']['details']['bike_range_start'];
            $locationOptions->bike_range_end = $data['data']['location']['details']['bike_range_end'];
            $locationOptions->door_range_start = $data['data']['location']['details']['door_range_start'];
            $locationOptions->door_range_end = $data['data']['location']['details']['door_range_end'];
            $locationOptions->ev_charger_range_start = $data['data']['location']['details']['ev_charger_range_start'];
            $locationOptions->ev_charger_range_end = $data['data']['location']['details']['ev_charger_range_end'];
            $locationOptions->ev_charger_energy = $data['data']['location']['details']['ev_charger_energy'];
            $locationOptions->language_id = $data['data']['location']['details']['language_id'];
            $locationOptions->is_doors = $data['data']['location']['details']['is_doors'];
            $locationOptions->is_bikes = $data['data']['location']['details']['is_bikes'];
            $locationOptions->is_parkingshop_location = $data['data']['location']['details']['is_parkingshop_location'];
            $locationOptions->online_booking_spots = $data['data']['location']['details']['online_booking_spots'];
            $locationOptions->is_send_reservation_email = $data['data']['location']['details']['is_send_reservation_email'];
            $locationOptions->reservation_email = $data['data']['location']['details']['reservation_email'];
            $locationOptions->price_per_hour = $data['data']['location']['details']['price_per_hour'];
            $locationOptions->price_per_day = $data['data']['location']['details']['price_per_day'];
            $locationOptions->star_rank = $data['data']['location']['details']['star_rank'];
            $locationOptions->latitude = $data['data']['location']['details']['latitude'];
            $locationOptions->longitude = $data['data']['location']['details']['longitude'];
            $locationOptions->save();
            foreach ($data['data']['location']['images'] as $image) {
                //                        $locationImages = new LocationImages();
                //                        $locationImages->live_id = $image['id'];
                //                        $locationImages->image_encoded = $image['image_encoded'];
                //                        $locationImages->is_default = $image['is_default'];
                //                        $locationImages->domain = $image['domain'];
                //                        $locationImages->save();
            }

            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-location', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function import_location_timings($data)
    {
        try {
            Schema::disableForeignKeyConstraints();
            LocationTimings::truncate();
            Schema::enableForeignKeyConstraints();
            foreach ($data['data']['location']['timings']['weekDaysTimings'] as $weekDaysTimings) {
                $locationTimings = new LocationTimings();
                $locationTimings->live_id = $weekDaysTimings['id'];
                $locationTimings->week_day_num = $weekDaysTimings['week_day_num'];
                $locationTimings->opening_time = $weekDaysTimings['opening_time'];
                $locationTimings->closing_time = $weekDaysTimings['closing_time'];
                $locationTimings->save();
            }
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-timings', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function import_location_whitelist_timings($data)
    {
        try {
            foreach ($data['data']['location']['timings']['whitelistTimings'] as $whitelistTimings) {
                $locationTimings = new LocationTimings();
                $locationTimings->live_id = $whitelistTimings['id'];
                $locationTimings->week_day_num = $whitelistTimings['week_day_num'];
                $locationTimings->opening_time = $whitelistTimings['opening_time'];
                $locationTimings->closing_time = $whitelistTimings['closing_time'];
                $locationTimings->is_whitelist = 1;
                $locationTimings->save();
            }
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-whitelist_timings', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function import_location_userlist($data)
    {
        try {
            //USERLIST
            $userListUsersIdArray = array();
            $userListUsersLiveIdArray = array();
            $userListUsersLocal = UserlistUsers::all();
            foreach ($userListUsersLocal as $indexKey => $userListUserLocal) {
                $userListUsersIdArray[$indexKey] = $userListUserLocal->id;
                $userListUsersLiveIdArray[$indexKey] = $userListUserLocal->live_id;
            }
            foreach ($data['data']['userListUsers'] as $userListUsers) {
                $key = array_search($userListUsers['userlist_id'], $userListUsersLiveIdArray);
                if ($key !== false) {
                    unset($userListUsersIdArray[$key]);
                    unset($userListUsersLiveIdArray[$key]);
                }
                if ($userListUsers['location_userlist_user']['user_id'] != '' || $userListUsers['location_userlist_user']['user_id'] > 0) {
                    //                    $checkUserStatus = Customer::where('live_id', '=', $userListUsers['location_userlist_user']['user_id'])
                    //                            ->first();
                    //                    if (!$checkUserStatus) {
                    //                        $customer = new Customer();
                    //                        $customer->live_id = $userListUsers['location_userlist_user']['user']['id'];
                    //                        $customer->name = $userListUsers['location_userlist_user']['user']['name'];
                    //                        $customer->email = $userListUsers['location_userlist_user']['user']['email'];
                    //                        $customer->current_balance_amuount = $userListUsers['location_userlist_user']['user']['current_balance_amuount'];
                    //                        $customer->is_active = $userListUsers['location_userlist_user']['user']['is_active'];
                    //                        $customer->save();
                    //                        $customerId = $customer->id;
                    //                        $customerProfile = new Profile();
                    //                        $customerProfile->customer_id = $customerId;
                    //                        $customerProfile->first_name = $userListUsers['location_userlist_user']['user']['user_profile']['first_name'];
                    //                        $customerProfile->last_name = $userListUsers['location_userlist_user']['user']['user_profile']['last_name'];
                    //                        $customerProfile->phone_num = $userListUsers['location_userlist_user']['user']['user_profile']['phone_num'];
                    //                        $pic = $userListUsers['location_userlist_user']['user']['user_profile']['profile_picture'];
                    //                        if ($pic != Null || $pic != "") {
                    //                            try {
                    //                                $imageContent = file_get_contents($data['data']['site_url'] . '/images/' . $pic);
                    //                                file_put_contents(public_path('/uploads/users/' . $pic), $imageContent);
                    //                                $customerProfile->pic = $pic;
                    //                            } catch (\Exception $ex) {
                    //                                $customerProfile->pic = NULL;
                    //                            }
                    //                        }
                    //                        $customerProfile->is_customer = 1;
                    //                        $customerProfile->save();
                    //                        foreach ($userListUsers['location_userlist_user']['user']['user_vehicle_info'] as $userVehicleInfo) {
                    //                            $cusomerVehicleInfo = CustomerVehicleInfo::where('live_id', $userVehicleInfo['id'])->first();
                    //                            if (!$cusomerVehicleInfo) {
                    //                                $cusomerVehicleInfo = new CustomerVehicleInfo();
                    //                            }
                    //                            $cusomerVehicleInfo->live_id = $userVehicleInfo['id'];
                    //                            $cusomerVehicleInfo->customer_id = $customerId;
                    //                            $cusomerVehicleInfo->name = $userVehicleInfo['name'];
                    //                            $cusomerVehicleInfo->num_plate = $userVehicleInfo['num_plate'];
                    //                            $cusomerVehicleInfo->save();
                    //                        }
                    //                    } else {
                    //                        $customerId = $checkUserStatus->id;
                    //                        $customer = Customer::find($customerId);
                    //                        $customer->live_id = $userListUsers['location_userlist_user']['user']['id'];
                    //                        $customer->name = $userListUsers['location_userlist_user']['user']['name'];
                    //                        $customer->email = $userListUsers['location_userlist_user']['user']['email'];
                    //                        $customer->current_balance_amuount = $userListUsers['location_userlist_user']['user']['current_balance_amuount'];
                    //                        $customer->is_active = $userListUsers['location_userlist_user']['user']['is_active'];
                    //                        $customer->save();
                    //                        $customerId = $customer->id;
                    //                        $customerProfile = Profile::where('customer_id', $customerId)->first();
                    //                        if (!$customerProfile) {
                    //                            $customerProfile = new Profile();
                    //                        }
                    //                        $customerProfile->customer_id = $customerId;
                    //                        $customerProfile->first_name = $userListUsers['location_userlist_user']['user']['user_profile']['first_name'];
                    //                        $customerProfile->last_name = $userListUsers['location_userlist_user']['user']['user_profile']['last_name'];
                    //                        $customerProfile->phone_num = $userListUsers['location_userlist_user']['user']['user_profile']['phone_num'];
                    //                        $pic = $userListUsers['location_userlist_user']['user']['user_profile']['profile_picture'];
                    //                        if ($pic != Null || $pic != "") {
                    //                            try {
                    //                                $imageContent = file_get_contents($data['data']['site_url'] . '/images/' . $pic);
                    //                                file_put_contents(public_path('/uploads/users/' . $pic), $imageContent);
                    //                                $customerProfile->pic = $pic;
                    //                            } catch (\Exception $ex) {
                    //                                $customerProfile->pic = NULL;
                    //                            }
                    //                        }
                    //                        $customerProfile->is_customer = 1;
                    //                        $customerProfile->save();
                    //                        foreach ($userListUsers['location_userlist_user']['user']['user_vehicle_info'] as $userVehicleInfo) {
                    //                            $cusomerVehicleInfo = CustomerVehicleInfo::where('live_id', $userVehicleInfo['id'])->first();
                    //                            if (!$cusomerVehicleInfo) {
                    //                                $cusomerVehicleInfo = new CustomerVehicleInfo();
                    //                            }
                    //                            $cusomerVehicleInfo->live_id = $userVehicleInfo['id'];
                    //                            $cusomerVehicleInfo->customer_id = $customerId;
                    //                            $cusomerVehicleInfo->name = $userVehicleInfo['name'];
                    //                            $cusomerVehicleInfo->num_plate = $userVehicleInfo['num_plate'];
                    //                            $cusomerVehicleInfo->save();
                    //                        }
                    //                    }
                    $userList = UserlistUsers::where('live_id', $userListUsers['userlist_id'])
                    ->where('email', $userListUsers['location_userlist_user']['email'])->first();
                    if (!$userList) {
                        $userList = new UserlistUsers();
                    }
                    $userList->live_id = $userListUsers['userlist_id'];
                    $userList->customer_id = NULL;
                    $userList->email = $userListUsers['location_userlist_user']['email'];
                    $userList->notation = $userListUsers['location_userlist_user']['notation'];
                    $userList->bike_range_start = $userListUsers['location_userlist_user']['bike_range_start'];
                    $userList->bike_range_end = $userListUsers['location_userlist_user']['bike_range_end'];
                    $userList->door_range_start = $userListUsers['location_userlist_user']['door_range_start'];
                    $userList->door_range_end = $userListUsers['location_userlist_user']['door_range_end'];
                    $userList->ev_charger_range_start = $userListUsers['location_userlist_user']['ev_charger_range_start'];
                    $userList->ev_charger_range_end = $userListUsers['location_userlist_user']['ev_charger_range_end'];
                    $userList->language_id = $userListUsers['location_userlist_user']['language_id'];
                    $userList->user_name = $userListUsers['location_userlist_user']['user_name'];
                    $userList->user_phone = $userListUsers['location_userlist_user']['user_phone'];
                    $cusomerVehicle = CustomerVehicleInfo::where('live_id', $userListUsers['location_userlist_user']['user_vehicle'])->first();
                    if ($cusomerVehicle) {
                        $userList->user_vehicle = $cusomerVehicle->id;
                    }
                    $userList->is_blocked = $userListUsers['location_userlist_user']['is_blocked'];

                    $userList->energy_limit = $userListUsers['location_userlist_user']['energy_limit'];
                    $userList->save();
                }
            }
            foreach ($userListUsersIdArray as $userListId) {
                UserlistUsers::where('id', '=', $userListId)->forceDelete();
            }
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-userlist', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function import_location_whitelist($data)
    {
        try {

            //WHITELIST
            $whiteListUsersIdArray = array();
            $whiteListUsersLiveIdArray = array();
            $whiteListUsersLocal = WhitelistUsers::all();
            foreach ($whiteListUsersLocal as $indexKey => $whiteListUserLocal) {
                $whiteListUsersIdArray[$indexKey] = $whiteListUserLocal->id;
                $whiteListUsersLiveIdArray[$indexKey] = $whiteListUserLocal->live_id;
            }
            foreach ($data['data']['whiteListUsers'] as $whiteListUsers) {
                $key = array_search($whiteListUsers['id'], $whiteListUsersLiveIdArray);
                if ($key !== false) {

                    unset($whiteListUsersIdArray[$key]);
                    unset($whiteListUsersLiveIdArray[$key]);
                }
                if ($whiteListUsers['user']['id'] != '' || $whiteListUsers['user']['id'] > 0) {
                    $checkUserStatus = Customer::where('live_id', '=', $whiteListUsers['user']['id'])
                        ->first();
                    if (!$checkUserStatus) {
                        $customer = new Customer();
                        $customer->live_id = $whiteListUsers['user']['id'];
                        $customer->name = $whiteListUsers['user']['name'];
                        $customer->email = $whiteListUsers['user']['email'];
                        $customer->current_balance_amuount = $whiteListUsers['user']['current_balance_amuount'];
                        $customer->is_active = $whiteListUsers['user']['is_active'];
                        $customer->save();

                        $customerId = $customer->id;

                        $customerProfile = new Profile();
                        $customerProfile->customer_id = $customerId;
                        $customerProfile->first_name = $whiteListUsers['user']['user_profile']['first_name'];
                        $customerProfile->last_name = $whiteListUsers['user']['user_profile']['last_name'];
                        $customerProfile->phone_num = $whiteListUsers['user']['user_profile']['phone_num'];
                        $customerProfile->pic = $whiteListUsers['user']['user_profile']['profile_picture'];
                        $customerProfile->is_customer = 1;
                        $customerProfile->save();

                        foreach ($whiteListUsers['user']['user_vehicle_info'] as $userVehicleInfo) {
                            $cusomerVehicleInfo = CustomerVehicleInfo::where('live_id', $userVehicleInfo['id'])->first();
                            if (!$cusomerVehicleInfo) {
                                $cusomerVehicleInfo = new CustomerVehicleInfo();
                            }
                            $cusomerVehicleInfo->live_id = $userVehicleInfo['id'];
                            $cusomerVehicleInfo->customer_id = $customerId;
                            $cusomerVehicleInfo->name = $userVehicleInfo['name'];
                            $cusomerVehicleInfo->num_plate = $userVehicleInfo['num_plate'];
                            $cusomerVehicleInfo->save();
                        }
                    } else {
                        $customerId = $checkUserStatus->id;
                        $customer = Customer::find($customerId);
                        $customer->live_id = $whiteListUsers['user']['id'];
                        $customer->name = $whiteListUsers['user']['name'];
                        $customer->email = $whiteListUsers['user']['email'];
                        $customer->current_balance_amuount = $whiteListUsers['user']['current_balance_amuount'];
                        $customer->is_active = $whiteListUsers['user']['is_active'];
                        $customer->save();

                        $customerId = $customer->id;

                        $customerProfile = Profile::where('customer_id', $customerId)->first();
                        if (!$customerProfile) {
                            $customerProfile = new Profile();
                        }
                        $customerProfile->customer_id = $customerId;
                        $customerProfile->first_name = $whiteListUsers['user']['user_profile']['first_name'];
                        $customerProfile->last_name = $whiteListUsers['user']['user_profile']['last_name'];
                        $customerProfile->phone_num = $whiteListUsers['user']['user_profile']['phone_num'];
                        $customerProfile->pic = $whiteListUsers['user']['user_profile']['profile_picture'];
                        $customerProfile->is_customer = 1;
                        $customerProfile->save();
                        foreach ($whiteListUsers['user']['user_vehicle_info'] as $userVehicleInfo) {
                            $cusomerVehicleInfo = CustomerVehicleInfo::where('live_id', $userVehicleInfo['id'])->first();
                            if (!$cusomerVehicleInfo) {
                                $cusomerVehicleInfo = new CustomerVehicleInfo();
                            }
                            $cusomerVehicleInfo->live_id = $userVehicleInfo['id'];
                            $cusomerVehicleInfo->customer_id = $customerId;
                            $cusomerVehicleInfo->name = $userVehicleInfo['name'];
                            $cusomerVehicleInfo->num_plate = $userVehicleInfo['num_plate'];
                            $cusomerVehicleInfo->save();
                        }
                    }
                }
                $whiteList = WhitelistUsers::where('live_id', $whiteListUsers['id'])->first();
                if (!$whiteList) {
                    $whiteList = new WhitelistUsers();
                }
                $whiteList->live_id = $whiteListUsers['id'];
                $whiteList->customer_id = $customerId;
                $whiteList->email = $whiteListUsers['email'];
                $whiteList->is_ticket_generated = $whiteListUsers['is_ticket_generated'];
                $whiteList->save();
            }
            foreach ($whiteListUsersIdArray as $whiteListId) {
                WhitelistUsers::where('id', '=', $whiteListId)->forceDelete();
            }
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-whitelist', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }
    public function import_location_bookings($data)
    {
        try {
            $updated_records = array();
            foreach ($data['data']['bookings'] as $booking) {
                if ($booking['type'] == 6 || $booking['type'] == 7) {
                    if ($booking['checkout_time'] < date('Y-m-d H:i:s')) {
                        $updated_records[] = $booking['id'];
                        continue;
                    }
                    $bookings = Bookings::where('live_id', $booking['id'])->first();
                    if (!$bookings) {
                        $bookings = new Bookings();
                    }
                    if ($booking['live_id'] && $booking['type'] == 6) {
                        $bookings->ref_booking_id = $booking['live_id'];
                    }
                    $bookings->live_id = $booking['id'];
                    $bookings->checkin_time = $booking['checkin_time'];
                    $bookings->checkout_time = $booking['checkout_time'];
                    $bookings->type = $booking['type'];
                    $bookings->is_user_logged_in = $booking['is_user_logged_in'];
                    $bookings->vehicle_num = $booking['vehicle_num'];
                    $bookings->phone_number = $booking['phone_number'];
                    $bookings->first_name = $booking['first_name'];
                    $bookings->last_name = $booking['last_name'];
                    $bookings->email = $booking['email'];
                    $bookings->sender_name = $booking['sender_name'];
                    $bookings->message = $booking['message'];
                    $bookings->rating_id = $booking['rating_id'];
                    $bookings->is_cancelled = $booking['is_cancelled'];
                    $bookings->is_customer_left = $booking['is_customer_left'];
                    $bookings->customer_left_status = $booking['customer_left_status'];
                    $bookings->tommy_parent_id = $booking['tommy_parent_id'];
                    $bookings->tommy_children_dob = $booking['tommy_children_dob'];
                    $bookings->tommy_childeren_id = $booking['tommy_childeren_id'];
                    $bookings->is_tommy_online = $booking['is_tommy_online'];
					if (array_key_exists("product_id", $booking)) {
						$product = Products::where('live_id', $booking['product_id'])->first();
						if ($product) {
							$bookings->product_id = $product->id;
							}
                    }
                    $bookings->save();

                    $bookingsId = $bookings->id;
                    if ($booking['payment_details']) {
                        $bookingPayments = BookingPayments::where('booking_id', $bookingsId)->first();
                        if (!$bookingPayments) {
                            $bookingPayments = new BookingPayments();
                        }
                        $bookingPayments->live_id = $booking['payment_details']['id'];
                        $bookingPayments->customer_id = NULL;
                        $bookingPayments->card_type = $booking['payment_details']['card_type'];
                        $bookingPayments->amount = $booking['payment_details']['amount'];
                        $bookingPayments->booking_id = $bookingsId;
                        $bookingPayments->checkin_time = $booking['payment_details']['checkin_time'];
                        $bookingPayments->checkout_time = $booking['payment_details']['checkout_time'];
                        $bookingPayments->payment_id = $booking['payment_details']['payment_id'];
                        $bookingPayments->is_online = $booking['payment_details']['is_online'];
                        $bookingPayments->save();
                    }
                } else {
                    $customerId = NULL;
                    $cusomerVehicleInfo_id = NULL;
                    //                    if ($booking['user_id'] != '' || $booking['user_id'] > 0) {
                    //                      
                    //                        $checkUserStatus = Customer::where('live_id', '=', $booking['user_id'])
                    //                                ->first();
                    //                        if (!$checkUserStatus) {
                    //                            $customer = new Customer();
                    //                            $customer->live_id = $booking['user']['id'];
                    //                            $customer->name = $booking['user']['name'];
                    //                            $customer->email = $booking['user']['email'];
                    //                            $customer->current_balance_amuount = $booking['user']['current_balance_amuount'];
                    //                            $customer->is_active = $booking['user']['is_active'];
                    //                            $customer->save();
                    //
                    //                            $customerId = $customer->id;
                    //
                    //                            $customerProfile = new Profile();
                    //                            $customerProfile->customer_id = $customerId;
                    //                            $customerProfile->first_name = $booking['user']['user_profile']['first_name'];
                    //                            $customerProfile->last_name = $booking['user']['user_profile']['last_name'];
                    //                            $customerProfile->phone_num = $booking['user']['user_profile']['phone_num'];
                    //                            $customerProfile->pic = $booking['user']['user_profile']['profile_picture'];
                    //                            $customerProfile->is_customer = 1;
                    //                            $customerProfile->save();
                    //                            $cusomerVehicleInfo_id = NULL;
                    //                            foreach ($booking['user']['user_vehicle_info'] as $userVehicleInfo) {
                    //                                $cusomerVehicleInfo = CustomerVehicleInfo::where('live_id', $userVehicleInfo['id'])
                    //                                        ->first();
                    //                                if (!$cusomerVehicleInfo) {
                    //                                    $cusomerVehicleInfo = new CustomerVehicleInfo();
                    //                                }
                    //                                $cusomerVehicleInfo->live_id = $userVehicleInfo['id'];
                    //                                $cusomerVehicleInfo->customer_id = $customerId;
                    //                                $cusomerVehicleInfo->name = $userVehicleInfo['name'];
                    //                                $cusomerVehicleInfo->num_plate = $userVehicleInfo['num_plate'];
                    //                                $cusomerVehicleInfo->save();
                    //                                $cusomerVehicleInfo_id = $cusomerVehicleInfo->id;
                    //                            }
                    //                        } else {
                    //                            $customer = Customer::find($checkUserStatus->id);
                    //                            $customer->live_id = $booking['user']['id'];
                    //                            $customer->name = $booking['user']['name'];
                    //                            $customer->email = $booking['user']['email'];
                    //                            $customer->current_balance_amuount = $booking['user']['current_balance_amuount'];
                    //                            $customer->is_active = $booking['user']['is_active'];
                    //                            $customer->save();
                    //
                    //                            $customerId = $customer->id;
                    //
                    //                            $customerProfile = Profile::where('customer_id', $customer->id)->first();
                    //                            if (!$customerProfile) {
                    //                                $customerProfile = new Profile();
                    //                            }
                    //                            $customerProfile->customer_id = $customerId;
                    //                            $customerProfile->first_name = $booking['user']['user_profile']['first_name'];
                    //                            $customerProfile->last_name = $booking['user']['user_profile']['last_name'];
                    //                            $customerProfile->phone_num = $booking['user']['user_profile']['phone_num'];
                    ////                        $customerProfile->pic = $booking['user']['user_profile']['profile_picture'];
                    //                            $customerProfile->is_customer = 1;
                    //                            $customerProfile->save();
                    //                            $cusomerVehicleInfo_id = NULL;
                    //                            $customerId = $checkUserStatus->id;
                    //                            $cusomerVehicleInfo = CustomerVehicleInfo::where('live_id', $booking['vehicle_id'])
                    //                                    ->first();
                    //                            if ($cusomerVehicleInfo) {
                    //                                $cusomerVehicleInfo_id = $cusomerVehicleInfo->id;
                    //                            }
                    //                        }
                    //                    }
                    $promoId = NULL;
                    if ($booking['promo_id'] != NULL && $booking['promo_id'] != '') {
                        $PromoInfo = \App\Promo::where('live_id', $booking['promo_id'])->first();
                        if ($PromoInfo) {
                            $promoId = $PromoInfo->code;
                        }
                    }
                    $bookings = Bookings::where('live_id', $booking['id'])->first();
                    if (!$bookings) {
                        if (date('Y-m-d') == date('Y-m-d', strtotime($booking['checkin_time']))) {
                            $bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                $query->whereNull('check_out');
                            })->where('type', 4)
                                ->where('vehicle_num', $booking['vehicle_num'])->first();
                        }
                        if (!$bookings) {
                            $bookings = new Bookings();
                        }
                    }

                    $bookings->live_id = $booking['id'];
                    $bookings->customer_id = $customerId;
                    $bookings->customer_vehicle_info_id = $cusomerVehicleInfo_id;
                    $bookings->promo_code = $promoId;
                    $bookings->checkin_time = $booking['checkin_time'];
                    $bookings->checkout_time = $booking['checkout_time'];
                    $bookings->type = $booking['type'];
                    $bookings->is_user_logged_in = $booking['is_user_logged_in'];
                    $bookings->vehicle_num = $booking['vehicle_num'];
                    $bookings->phone_number = $booking['phone_number'];
                    $bookings->first_name = $booking['first_name'];
                    $bookings->last_name = $booking['last_name'];
                    $bookings->email = $booking['email'];
                    $bookings->sender_name = $booking['sender_name'];
                    $bookings->message = $booking['message'];
                    $bookings->rating_id = $booking['rating_id'];
                    if ($bookings->is_paid != 1) {
                        $bookings->is_paid = $booking['is_paid'];
                    }
                    $bookings->is_cancelled = $booking['is_cancelled'];
                    $bookings->is_customer_left = $booking['is_customer_left'];
                    $bookings->customer_left_status = $booking['customer_left_status'];
                    $bookings->is_user_ballance_adjustment = $booking['is_user_ballance_adjustment'];
					if (array_key_exists("product_id", $booking)) {
						$product = Products::where('live_id', $booking['product_id'])->first();
						if ($product) {
							$bookings->product_id = $product->id;
							}
                    }
                    $bookings->save();
                    $bookingsId = $bookings->id;
                    $booking_payment_live_id = FALSE;
                    if ($booking['payment_details']) {
                        $bookingPayments = BookingPayments::where('booking_id', $bookingsId)->first();
                        if (!$bookingPayments) {
                            $bookingPayments = new BookingPayments();
                        }
                        $booking_payment_live_id = $booking['payment_details']['id'];
                        $bookingPayments->live_id = $booking['payment_details']['id'];
                        $bookingPayments->customer_id = $customerId;
                        $bookingPayments->card_type = $booking['payment_details']['card_type'];
                        $bookingPayments->amount = $booking['payment_details']['amount'];
                        $bookingPayments->booking_id = $bookingsId;
                        $bookingPayments->checkin_time = $booking['payment_details']['checkin_time'];
                        $bookingPayments->checkout_time = $booking['payment_details']['checkout_time'];
                        $bookingPayments->payment_id = $booking['payment_details']['payment_id'];
                        $bookingPayments->is_online = $booking['payment_details']['is_online'];
                        $bookingPayments->save();
                    }
                    try {
                        $verify_vehicle = new \App\Http\Controllers\PlateReaderController\VerifyVehicle();
                        $verify_vehicle->check_is_booking_missed($booking['vehicle_num'], $bookings, $booking['id'], $booking_payment_live_id);
                    } catch (\Exception $ex) {
                    }

                    //                            if ($booking['attendant'] != "") {
                    //                                $attendant = Attendants::where('booking_id', $bookingsId)->first();
                    //                                if (!$attendant) {
                    //                                    $attendant = new Attendants();
                    //                                }
                    //                                $attendant->live_id = $booking['attendant']['id'];
                    //                                $attendant->booking_id = $bookingsId;
                    //                                $attendant->save();
                    //
                    //                                $attendantId = $attendant->id;
                    //
                    //                                $attendantTransaction = new AttendantTransactions();
                    //                                $attendantTransaction->attendant_id = $attendantId;
                    //                                $attendantTransaction->check_in = $booking['payment_details']['checkin_time'];
                    //                                $attendantTransaction->check_out = $booking['payment_details']['checkout_time'];
                    //                                $attendantTransaction->save();
                    //                            }
                }
                $updated_records[] = $booking['id'];
            }
            if (count($updated_records) > 0) {
                $Key = $this->key;
                $http = new Client();
                $response = $http->post($this->url . '/api/identify-local-bookings-updated', [
                    'form_params' => [
                        'token' => $Key,
                        'data' => $updated_records
                    ],
                ]);
                $data = json_decode((string) $response->getBody(), true);
            }
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-bookings', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }
    public function import_real_time_location_bookings(Request $request)
    {
        try {
            if (count($request->all()) > 0) {
                $updated_records = array();
                $data = $request->all();
                foreach ($data['data'] as $booking) {
                    if ($booking['type'] == 6 || $booking['type'] == 7) {
                        if ($booking['checkout_time'] < date('Y-m-d H:i:s')) {
                            $updated_records[] = $booking['id'];
                            continue;
                        }
                        $bookings = Bookings::where('live_id', $booking['id'])->first();
                        if (!$bookings) {
                            $bookings = new Bookings();
                        }
                        if ($booking['live_id'] && $booking['type'] == 6) {
                            $bookings->booking_ref_id = $booking['live_id'];
                        }
                        $bookings->live_id = $booking['id'];
                        $bookings->checkin_time = $booking['checkin_time'];
                        $bookings->checkout_time = $booking['checkout_time'];
                        $bookings->type = $booking['type'];
                        $bookings->is_user_logged_in = $booking['is_user_logged_in'];
                        $bookings->vehicle_num = $booking['vehicle_num'];
                        $bookings->phone_number = $booking['phone_number'];
                        $bookings->first_name = $booking['first_name'];
                        $bookings->last_name = $booking['last_name'];
                        $bookings->email = $booking['email'];
                        $bookings->sender_name = $booking['sender_name'];
                        $bookings->message = $booking['message'];
                        $bookings->rating_id = $booking['rating_id'];
                        $bookings->is_cancelled = $booking['is_cancelled'];
                        $bookings->is_customer_left = $booking['is_customer_left'];
                        $bookings->customer_left_status = $booking['customer_left_status'];
                        $bookings->tommy_parent_id = $booking['tommy_parent_id'];
                        $bookings->tommy_children_dob = $booking['tommy_children_dob'];
                        $bookings->tommy_childeren_id = $booking['tommy_childeren_id'];
                        $bookings->is_tommy_online = $booking['is_tommy_online'];
                        $product = Products::where('live_id', $booking['product_id'])->first();
                        if (array_key_exists("product_id", $booking)) {
                            $product = Products::where('live_id', $booking['product_id'])->first();
                            if ($product) {
                                $bookings->product_id = $product->id;
                            }
                        }
                        $bookings->save();

                        $bookingsId = $bookings->id;
                        if ($booking['payment_details']) {
                            $bookingPayments = BookingPayments::where('booking_id', $bookingsId)->first();
                            if (!$bookingPayments) {
                                $bookingPayments = new BookingPayments();
                            }
                            $bookingPayments->live_id = $booking['payment_details']['id'];
                            $bookingPayments->customer_id = NULL;
                            $bookingPayments->card_type = $booking['payment_details']['card_type'];
                            $bookingPayments->amount = $booking['payment_details']['amount'];
                            $bookingPayments->booking_id = $bookingsId;
                            $bookingPayments->checkin_time = $booking['payment_details']['checkin_time'];
                            $bookingPayments->checkout_time = $booking['payment_details']['checkout_time'];
                            $bookingPayments->payment_id = $booking['payment_details']['payment_id'];
                            $bookingPayments->is_online = $booking['payment_details']['is_online'];
                            $bookingPayments->save();
                        }
                    } else {
                        $customerId = NULL;
                        $cusomerVehicleInfo_id = NULL;
                        $promoId = NULL;
                        if ($booking['promo_id'] != NULL && $booking['promo_id'] != '') {
                            $PromoInfo = \App\Promo::where('live_id', $booking['promo_id'])->first();
                            if ($PromoInfo) {
                                $promoId = $PromoInfo->code;
                            }
                        }
                        $bookings = Bookings::where('live_id', $booking['id'])->first();
                        if (!$bookings) {
                            if (date('Y-m-d') == date('Y-m-d', strtotime($booking['checkin_time']))) {
                                $bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
                                    $query->whereNull('check_out');
                                })->where('type', 4)
                                    ->where('vehicle_num', $booking['vehicle_num'])->first();
                            }
                            if (!$bookings) {
                                $bookings = new Bookings();
                            }
                        }

                        $bookings->live_id = $booking['id'];
                        $bookings->customer_id = $customerId;
                        $bookings->customer_vehicle_info_id = $cusomerVehicleInfo_id;
                        $bookings->promo_code = $promoId;
                        $bookings->checkin_time = $booking['checkin_time'];
                        $bookings->checkout_time = $booking['checkout_time'];
                        $bookings->type = $booking['type'];
                        $bookings->is_user_logged_in = $booking['is_user_logged_in'];
                        $bookings->vehicle_num = $booking['vehicle_num'];
                        $bookings->phone_number = $booking['phone_number'];
                        $bookings->first_name = $booking['first_name'];
                        $bookings->last_name = $booking['last_name'];
                        $bookings->email = $booking['email'];
                        $bookings->sender_name = $booking['sender_name'];
                        $bookings->message = $booking['message'];
                        $bookings->rating_id = $booking['rating_id'];
                        if ($bookings->is_paid != 1) {
                            $bookings->is_paid = $booking['is_paid'];
                        }
                        $bookings->is_cancelled = $booking['is_cancelled'];
                        $bookings->is_customer_left = $booking['is_customer_left'];
                        $bookings->customer_left_status = $booking['customer_left_status'];
                        $bookings->is_user_ballance_adjustment = $booking['is_user_ballance_adjustment'];
                        if (array_key_exists("product_id", $booking)) {
                            $product = Products::where('live_id', $booking['product_id'])->first();
                            if ($product) {
                                $bookings->product_id = $product->id;
                            }
                        }
                        $bookings->save();
                        $bookingsId = $bookings->id;
                        $booking_payment_live_id = FALSE;
                        if ($booking['payment_details']) {
                            $bookingPayments = BookingPayments::where('booking_id', $bookingsId)->first();
                            if (!$bookingPayments) {
                                $bookingPayments = new BookingPayments();
                            }
                            $booking_payment_live_id = $booking['payment_details']['id'];
                            $bookingPayments->live_id = $booking['payment_details']['id'];
                            $bookingPayments->customer_id = $customerId;
                            $bookingPayments->card_type = $booking['payment_details']['card_type'];
                            $bookingPayments->amount = $booking['payment_details']['amount'];
                            $bookingPayments->booking_id = $bookingsId;
                            $bookingPayments->checkin_time = $booking['payment_details']['checkin_time'];
                            $bookingPayments->checkout_time = $booking['payment_details']['checkout_time'];
                            $bookingPayments->payment_id = $booking['payment_details']['payment_id'];
                            $bookingPayments->is_online = $booking['payment_details']['is_online'];
                            $bookingPayments->save();
                        }
                        try {
                            $verify_vehicle = new \App\Http\Controllers\PlateReaderController\VerifyVehicle();
                            $verify_vehicle->check_is_booking_missed($booking['vehicle_num'], $bookings, $booking['id'], $booking_payment_live_id);
                        } catch (\Exception $ex) {
                        }
                    }
                    $updated_records[] = $booking['id'];
                }
                if (count($updated_records) > 0) {
                    $Key = $this->key;
                    $http = new Client();
                    $response = $http->post($this->url . '/api/identify-local-bookings-updated', [
                        'form_params' => [
                            'token' => $Key,
                            'data' => $updated_records
                        ],
                    ]);
                    $data = json_decode((string) $response->getBody(), true);
                }
                return response()->json(['status' => true]);
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-real-time-bookings', $ex->getMessage(), $ex->getTraceAsString(),$ex->getLine(),$ex->getFile());
            return response()->json(['status' => false]);
        }
    }

    public function import_location_tommy_reservation($data)
    {
        try {

            foreach ($data['data']['tommyReservations'] as $tommyReservations) {
                $tommyReservationsParent = TommyReservationParents::where('live_id', $tommyReservations['id'])->first();
                if (!$tommyReservationsParent) {
                    $tommyReservationsParent = new TommyReservationParents();
                }
                $tommyReservationsParent->live_id = $tommyReservations['id'];
                $tommyReservationsParent->total_members = $tommyReservations['total_members'];
                $tommyReservationsParent->email = $tommyReservations['email'];
                $tommyReservationsParent->date_of_arrival = $tommyReservations['date_of_arrival'];
                $tommyReservationsParent->date_of_departure = $tommyReservations['date_of_departure'];
                $tommyReservationsParent->license_plate = $tommyReservations['license_plate'];
                $tommyReservationsParent->other_license_plate = $tommyReservations['other_license_plate'];
                $tommyReservationsParent->save();

                $tommyReservationsParentId = $tommyReservationsParent->id;

                foreach ($tommyReservations['tommy_reservation_child'] as $tommyReservationsChild) {
                    $tommyReservationsChilds = TommyReservationChildrens::where('live_id', $tommyReservationsChild['id'])->first();
                    if (!$tommyReservationsChilds) {
                        $tommyReservationsChilds = new TommyReservationChildrens();
                    }
                    $tommyReservationsChilds->live_id = $tommyReservationsChild['id'];
                    $tommyReservationsChilds->tommy_reservation_parent_id = $tommyReservationsParentId;
                    $tommyReservationsChilds->name = $tommyReservationsChild['name'];
                    $tommyReservationsChilds->family_status = $tommyReservationsChild['family_status'];
                    $tommyReservationsChilds->dob = $tommyReservationsChild['dob'];
                    $tommyReservationsChilds->first_name = $tommyReservationsChild['first_name'];
                    $tommyReservationsChilds->middle_name = $tommyReservationsChild['middle_name'];
                    $tommyReservationsChilds->last_name = $tommyReservationsChild['last_name'];
                    $tommyReservationsChilds->save();
                }
            }

            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-tommy_reservation', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function import_location_devices($data)
    {
        try {
            if (count($data['data']['devices']) > 0) {
                foreach ($data['data']['devices'] as $device) {
                    $is_new_device = FALSE;
                    $locationDevice = \App\LocationDevices::where('live_id', $device['id'])->first();
                    if (!$locationDevice) {
                        $is_new_device = TRUE;
                        $locationDevice = new \App\LocationDevices();
                    }
                    $locationDevice->live_id = $device['id'];
                    $locationDevice->device_name = $device['device_name'];
                    $locationDevice->available_device_id = $device['available_device_id'];
                    $locationDevice->device_direction = strtolower($device['device_direction']);
                    $locationDevice->device_ip = $device['device_ip'];
                    $locationDevice->device_port = $device['device_port'];
                    $locationDevice->anti_passback = $device['anti_passback'];
                    $locationDevice->time_passback = $device['time_passback'];
                    $locationDevice->save();
                    $device_id = $locationDevice->id;
                    $device_settings = \App\DeviceSettings::where('device_id', $device_id)->first();
                    if (!$device_settings) {
                        $device_settings = new \App\DeviceSettings();
                    }
                    $device_settings->device_id = $device_id;
                    $device_settings->save();
                    if ($is_new_device) {
                        $settings = new Settings();
                        $settings->run_socket_connection_command($device_id, 'all');
                    }
                }
            }
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('import-devices', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function importDetails()
    {
        try {
            if (!$this->key) {
                //                $error_log = new \App\Http\Controllers\LogController();
                //                $error_log->log_create('import-key', 'custom: Import key not found');
                return FALSE;
            }
            $Key = $this->key;
            $http = new Client();
            $response = $http->post($this->url . '/api/import-data', [
                'form_params' => [
                    'token' => $Key
                ],
            ]);
            $data = json_decode((string) $response->getBody(), true);
            //print_r($data);
            if (!array_key_exists('data', $data) || empty($data['data'])) {
                //                $error_log = new \App\Http\Controllers\LogController();
                //                $error_log->log_create('import', 'custom: Invalid Response');
                return FALSE;
            }
            //            if (!array_key_exists('adminUser', $data['data']) || empty($data['data']['adminUser'])) {
            //                $error_log = new \App\Http\Controllers\LogController();
            //                $error_log->log_create('import-admin', 'custom: admin details key not exists');
            //                return FALSE;
            //            }
            //            $import_admin_details = $this->import_admin_details($data['data']['adminUser']);
            //            if (!$import_admin_details) {
            //                return FALSE;
            //            }
            //            if (!array_key_exists('location', $data['data']) || empty($data['data']['location'])) {
            //                $error_log = new \App\Http\Controllers\LogController();
            //                $error_log->log_create('import-location', 'custom: location details key not exists');
            //                return FALSE;
            //            }
            //            $import_location_details = $this->import_location_details($data);
            //            if (!$import_location_details) {
            //                return FALSE;
            //            }
            //   $this->import_location_timings($data);
            //   $this->import_location_whitelist_timings($data);
            //            $this->import_location_userlist($data);
            //            $this->import_location_whitelist($data);
            $this->import_location_bookings($data);
            //  $this->import_location_tommy_reservation($data);
            //  $this->import_location_devices($data);
            return TRUE;
        } catch (\Exception $ex) {

            //            $error_log = new \App\Http\Controllers\LogController();
            //            $error_log->log_create('import', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function ImportOtherData()
    {
        try {
            if (!$this->key) {
                //                $error_log = new \App\Http\Controllers\LogController();
                //                $error_log->log_create('import-key', 'custom: Import key not found');
                return FALSE;
            }
            $Key = $this->key;
            $http = new Client();
            $response = $http->post($this->url . '/api/import-data', [
                'form_params' => [
                    'token' => $Key
                ],
            ]);
            $data = json_decode((string) $response->getBody(), true);

            if (!array_key_exists('data', $data) || empty($data['data'])) {
                //                $error_log = new \App\Http\Controllers\LogController();
                //                $error_log->log_create('import', 'custom: Invalid Response');
                return FALSE;
            }
            //            if (!array_key_exists('adminUser', $data['data']) || empty($data['data']['adminUser'])) {
            //                $error_log = new \App\Http\Controllers\LogController();
            //                $error_log->log_create('import-admin', 'custom: admin details key not exists');
            //                return FALSE;
            //            }
            //            $import_admin_details = $this->import_admin_details($data['data']['adminUser']);
            //            if (!$import_admin_details) {
            //                return FALSE;
            //            }
            //            if (!array_key_exists('location', $data['data']) || empty($data['data']['location'])) {
            //                $error_log = new \App\Http\Controllers\LogController();
            //                $error_log->log_create('import-location', 'custom: location details key not exists');
            //                return FALSE;
            //            }
            //            $import_location_details = $this->import_location_details($data);
            //            if (!$import_location_details) {
            //                return FALSE;
            //            }
            //   $this->import_location_timings($data);
            //   $this->import_location_whitelist_timings($data);
            //$this->import_location_userlist($data);
            //$this->import_location_whitelist($data);
            //            $this->import_location_bookings($data);
            //  $this->import_location_tommy_reservation($data);
            //  $this->import_location_devices($data);
            return TRUE;
        } catch (\Exception $ex) {

            //            $error_log = new \App\Http\Controllers\LogController();
            //            $error_log->log_create('import', $ex->getMessage(), $ex->getTraceAsString());
            return FALSE;
        }
    }

    public function importBookingDetails()
    {
        try {
            if (!$this->key) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('import-key', 'custom: Import key not found');
                return FALSE;
            }
            $Key = $this->key;
            $http = new Client();
            $response = $http->post($this->url . '/api/import-booking-data', [
                'form_params' => [
                    'token' => $Key
                ],
            ]);
            $data = json_decode((string) $response->getBody(), true);
            if (!array_key_exists('data', $data) || empty($data['data'])) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('import', 'custom: Invalid Response');
                return FALSE;
            }
            $this->import_location_bookings($data);
            return TRUE;
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('importDetails', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            return FALSE;
        }
    }

    function get_booking_statistics()
    {

        //        $existing_checked_in_unknown_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
        //                            $query->whereNull('check_out');
        //                        })
        //                        ->whereNull('checkout_time')->whereIn('type', array(4, 5))->count();
        //
        $type = [1, 2, 3, 4];
        $existing_checked_in_known_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
            $query->whereNull('check_out');
        })
            ->whereIn('type', $type)->pluck('id')->all();

        $existing_known_subscription_bookings = \App\Bookings::whereNotIn('id', $existing_checked_in_known_bookings)->where('checkout_time', '=', date('Y-m-d 23:59:59'))->where('checkin_time', '<=', date('Y-m-d H:i:s'))->where('type', 4)->count();

        $total_consumed_bookings = count($existing_checked_in_known_bookings) + $existing_known_subscription_bookings;

        $on_location_person_bookings = \App\AttendantTransactions::whereHas(
            'attendants.bookings',
            function ($query) {
                $query->whereIn('type', array(5, 6))->whereNull('vehicle_num')->where('is_tommy_online', 0)->whereNull('tommy_childeren_id');
            }
        )->whereNull('check_out')
        ->count();
        $on_location_person_empty_vehicle = \App\AttendantTransactions::whereHas(
            'attendants.bookings',
            function ($query) {
                $query->whereIn('type', array(5, 6))->where('vehicle_num', '')->where('is_tommy_online', 0)->whereNull('tommy_childeren_id');
            }
        )->whereNull('check_out')
            ->count();
        $on_location = $on_location_person_bookings + $on_location_person_empty_vehicle;

        $checked_in_person_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
            $query->whereNull('check_out');
        })
        ->whereIn('type', array(6))->where('is_tommy_online', 0)->whereNull('tommy_childeren_id')->where('checkout_time', '=', date('Y-m-d 23:59:59'))->pluck('id')->all();

        $not_checked_in_bookings = \App\Bookings::whereNotIn('id', $checked_in_person_bookings)->where('checkout_time', '=', date('Y-m-d 23:59:59'))->where('type', 6)->where('is_tommy_online', 0)->whereNull('tommy_childeren_id')->count();


        $total_consumed_person_bookings = $on_location + $not_checked_in_bookings;
        try {
            if (!$this->key) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('import-key', 'custom: Import key not found');
                return FALSE;
            }
            $Key = $this->key;
            $dataArray = array('consumed_person_bookings' => $total_consumed_person_bookings, 'consumed_parking_bookings' => $total_consumed_bookings);
            $http = new Client();
            $response = $http->post($this->url . '/api/update-booking-statistics', [
                'form_params' => [
                    'token' => $Key,
                    'data' => $dataArray
                ],
            ]);
            $responseData = json_decode((string) $response->getBody(), true);
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('get_booking_statistics', $ex->getMessage(), $ex->getTraceAsString());
        }
    }
}
