<?php

namespace App\Http\Controllers;

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
use App\TommyReservationParents;
use App\TommyReservationChildrens;
use App\LocationExtraSpots;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Excel;

class LocationController extends Controller {

    public $controller = 'App\Http\Controllers\LocationController';

    /**
     * Import Details From Live Server
     * @param type $Key
     * @return boolean
     */
    public function importDetails($Key) {
        try {
            $http = new Client();
            $response = $http->post(env('API_BASE_URL') . '/api/import-data', [
                'form_params' => [
                    'token' => $Key
                ],
            ]);
            $data = json_decode((string) $response->getBody(), true);

            if (isset($data['data']['adminUser'])) {

                $checkUserStatus = User::where(
                                'live_id', '=', $data['data']['adminUser']['id']
                        )->first();
                if (!$checkUserStatus) {
                    $adminUser = new User();
                    $adminUser->live_id = $data['data']['adminUser']['id'];
                    $adminUser->name = $data['data']['adminUser']['name'] != '' ? $data['data']['adminUser']['name'] : $data['data']['adminUser']['user_profile']['first_name'] . ' ' . $data['data']['adminUser']['user_profile']['last_name'];
                    $adminUser->email = $data['data']['adminUser']['email'];
                    $adminUser->password = $data['data']['adminUser']['password'];
                    $adminUser->status = 1;
                    $adminUser->save();

                    $adminUserId = $adminUser->id;

                    $admin = User::first();
//        Creating Roles
                    $admin_role = \App\Role::firstOrcreate(['name' => 'admin']);
                    $permission = \App\Permission::firstOrcreate(['name' => 'All Permission']);
//
                    if ($admin) {
                        if (!$admin->hasRole('admin')) {
                            $admin->assignRole('admin');
                            $admin_role->givePermissionTo($permission);
                        }
                    }

                    $profile = new Profile();
                    $profile->user_id = $adminUserId;
                    $profile->first_name = $data['data']['adminUser']['user_profile']['first_name'];
                    $profile->last_name = $data['data']['adminUser']['user_profile']['last_name'];
                    $profile->phone_num = $data['data']['adminUser']['user_profile']['phone_num'];
                    $pic = $data['data']['adminUser']['user_profile']['profile_picture'];
                    if ($pic != Null || $pic != "") {
                        $imageContent = file_get_contents($data['data']['site_url'] . '/images/' . $pic);
                        file_put_contents(public_path('/uploads/users/' . $pic), $imageContent);
                        $profile->pic = $pic;
                    }
                    $profile->is_customer = 0;
                    $profile->save();
                } else {
                    $adminUser = User::first();
                    if ($adminUser) {
                        $adminUser->live_id = $data['data']['adminUser']['id'];
                        $adminUser->name = $data['data']['adminUser']['name'] != '' ? $data['data']['adminUser']['name'] : $data['data']['adminUser']['user_profile']['first_name'] . ' ' . $data['data']['adminUser']['user_profile']['last_name'];
                        $adminUser->email = $data['data']['adminUser']['email'];
                        $adminUser->password = $data['data']['adminUser']['password'];
                        $adminUser->status = 1;
                        $adminUser->save();
                    }
                    $profile = Profile::where('user_id', $adminUser->id)->first();
                    if ($profile) {
                        $profile->first_name = $data['data']['adminUser']['user_profile']['first_name'];
                        $profile->last_name = $data['data']['adminUser']['user_profile']['last_name'];
                        $profile->phone_num = $data['data']['adminUser']['user_profile']['phone_num'];
                        $pic = $data['data']['adminUser']['user_profile']['profile_picture'];
                        if ($pic != Null || $pic != "") {
                            $imageContent = file_get_contents($data['data']['site_url'] . '/images/' . $pic);
                            file_put_contents(public_path('/uploads/users/' . $pic), $imageContent);
                            $profile->pic = $pic;
                        }
                        $profile->is_customer = 0;
                        $profile->save();
                    }
                }

                if (isset($data['data']['location'])) {
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
                    $locationOptions->height_restriction_value = $data['data']['location']['details']['height_restriction_value'];
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

                    foreach ($data['data']['location']['timings']['whitelistTimings'] as $whitelistTimings) {
                        $locationTimings = new LocationTimings();
                        $locationTimings->live_id = $whitelistTimings['id'];
                        $locationTimings->week_day_num = $whitelistTimings['week_day_num'];
                        $locationTimings->opening_time = $whitelistTimings['opening_time'];
                        $locationTimings->closing_time = $whitelistTimings['closing_time'];
                        $locationTimings->is_whitelist = 1;
                        $locationTimings->save();
                    }

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
                            $checkUserStatus = Customer::where('live_id', '=', $userListUsers['location_userlist_user']['user_id'])
                                    ->first();
                            if (!$checkUserStatus) {
                                $customer = new Customer();
                                $customer->live_id = $userListUsers['location_userlist_user']['user']['id'];
                                $customer->name = $userListUsers['location_userlist_user']['user']['name'];
                                $customer->email = $userListUsers['location_userlist_user']['user']['email'];
                                $customer->current_balance_amuount = $userListUsers['location_userlist_user']['user']['current_balance_amuount'];
                                $customer->is_active = $userListUsers['location_userlist_user']['user']['is_active'];
                                $customer->save();

                                $customerId = $customer->id;

                                $customerProfile = new Profile();
                                $customerProfile->customer_id = $customerId;
                                $customerProfile->first_name = $userListUsers['location_userlist_user']['user']['user_profile']['first_name'];
                                $customerProfile->last_name = $userListUsers['location_userlist_user']['user']['user_profile']['last_name'];
                                $customerProfile->phone_num = $userListUsers['location_userlist_user']['user']['user_profile']['phone_num'];
                                $pic = $userListUsers['location_userlist_user']['user']['user_profile']['profile_picture'];
                                if ($pic != Null || $pic != "") {
                                    $imageContent = file_get_contents($data['data']['site_url'] . '/images/' . $pic);
                                    file_put_contents(public_path('/uploads/users/' . $pic), $imageContent);
                                    $customerProfile->pic = $pic;
                                }
                                $customerProfile->is_customer = 1;
                                $customerProfile->save();

                                foreach ($userListUsers['location_userlist_user']['user']['user_vehicle_info'] as $userVehicleInfo) {
                                    $cusomerVehicleInfo = new CustomerVehicleInfo();
                                    $cusomerVehicleInfo->live_id = $userVehicleInfo['id'];
                                    $cusomerVehicleInfo->customer_id = $customerId;
                                    $cusomerVehicleInfo->name = $userVehicleInfo['name'];
                                    $cusomerVehicleInfo->num_plate = $userVehicleInfo['num_plate'];
                                    $cusomerVehicleInfo->save();
                                }
                            } else {
                                $customerId = $checkUserStatus->id;
                            }
                            $userList = UserlistUsers::where('live_id', $userListUsers['userlist_id'])->first();
                            if (!$userList) {
                                $userList = new UserlistUsers();
                            }
                            $userList->live_id = $userListUsers['userlist_id'];
                            $userList->customer_id = $customerId;
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
//                            $userList->user_vehicle = $userListUsers['location_userlist_user']['user_vehicle'];
                            $userList->is_blocked = $userListUsers['location_userlist_user']['is_blocked'];
                            $pic = $userListUsers['location_userlist_user']['profile_image'];
                            if ($pic != Null || $pic != "") {
                                $imageContent = file_get_contents($data['data']['site_url'] . '/images/' . $pic);
                                file_put_contents(public_path('/uploads/users/' . $pic), $imageContent);
                                $customerProfile->pic = $pic;
                            }
                            $userList->profile_image = $userListUsers['location_userlist_user']['profile_image'];
                            $userList->energy_limit = $userListUsers['location_userlist_user']['energy_limit'];
                            $userList->save();
                        }
                    }
                    foreach ($userListUsersIdArray as $userListId) {
                        UserlistUsers::where('id', '=', $userListId)->forceDelete();
                    }

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
                                $pic = $whiteListUsers['user']['user_profile']['profile_picture'];
                                if ($pic != Null || $pic != "") {
                                    $imageContent = file_get_contents($data['data']['site_url'] . '/images/' . $pic);
                                    file_put_contents(public_path('/uploads/users/' . $pic), $imageContent);
                                    $customerProfile->pic = $pic;
                                }
                                $customerProfile->is_customer = 1;
                                $customerProfile->save();

                                foreach ($whiteListUsers['user']['user_vehicle_info'] as $userVehicleInfo) {
                                    $cusomerVehicleInfo = new CustomerVehicleInfo();
                                    $cusomerVehicleInfo->live_id = $userVehicleInfo['id'];
                                    $cusomerVehicleInfo->customer_id = $customerId;
                                    $cusomerVehicleInfo->name = $userVehicleInfo['name'];
                                    $cusomerVehicleInfo->num_plate = $userVehicleInfo['num_plate'];
                                    $cusomerVehicleInfo->save();
                                }
                            } else {
                                $customerId = $checkUserStatus->id;
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

                    foreach ($data['data']['bookings'] as $booking) {
                        if ($booking['user_id'] != '' || $booking['user_id'] > 0) {
                            $checkUserStatus = Customer::where('live_id', '=', $booking['user_id'])
                                    ->first();
                            if (!$checkUserStatus) {
                                $customer = new Customer();
                                $customer->live_id = $booking['user']['id'];
                                $customer->name = $booking['user']['name'];
                                $customer->email = $booking['user']['email'];
                                $customer->current_balance_amuount = $booking['user']['current_balance_amuount'];
                                $customer->is_active = $booking['user']['is_active'];
                                $customer->save();

                                $customerId = $customer->id;

                                $customerProfile = new Profile();
                                $customerProfile->customer_id = $customerId;
                                $customerProfile->first_name = $booking['user']['user_profile']['first_name'];
                                $customerProfile->last_name = $booking['user']['user_profile']['last_name'];
                                $customerProfile->phone_num = $booking['user']['user_profile']['phone_num'];
                                $pic = $booking['user']['user_profile']['profile_picture'];
                                if ($pic != Null || $pic != "") {
                                    $imageContent = file_get_contents($data['data']['site_url'] . '/images/' . $pic);
                                    file_put_contents(public_path('/uploads/users/' . $pic), $imageContent);
                                    $customerProfile->pic = $pic;
                                }
                                $customerProfile->is_customer = 1;
                                $customerProfile->save();

                                foreach ($booking['user']['user_vehicle_info'] as $userVehicleInfo) {
                                    $cusomerVehicleInfo = new CustomerVehicleInfo();
                                    $cusomerVehicleInfo->live_id = $userVehicleInfo['id'];
                                    $cusomerVehicleInfo->customer_id = $customerId;
                                    $cusomerVehicleInfo->name = $userVehicleInfo['name'];
                                    $cusomerVehicleInfo->num_plate = $userVehicleInfo['num_plate'];
                                    $cusomerVehicleInfo->save();
                                }
                            } else {
                                $customerId = $checkUserStatus->id;
                            }
                            $bookings = Bookings::where('live_id', $booking['id'])->first();
                            if (!$bookings) {
                                $bookings = new Bookings();
                            }
                            $bookings->live_id = $booking['id'];
                            $bookings->customer_id = $customerId;
                            $bookings->customer_vehicle_info_id = $booking['vehicle_id'];
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
                            $bookings->is_user_ballance_adjustment = $booking['is_user_ballance_adjustment'];
                            $bookings->save();

                            $bookingsId = $bookings->id;
                            if ($booking['payment_details']) {
                                $bookingPayments = BookingPayments::where('booking_id', $bookingsId)->first();
                                if (!$bookingPayments) {
                                    $bookingPayments = new BookingPayments();
                                }
                                $bookingPayments->live_id = $booking['payment_details']['id'];
                                $bookingPayments->customer_id = $customerId;
                                $bookingPayments->card_type = $booking['payment_details']['card_type'];
                                $bookingPayments->amount = $booking['payment_details']['amount'];
                                $bookingPayments->booking_id = $bookingsId;
                                $bookingPayments->checkin_time = $booking['payment_details']['checkin_time'];
                                $bookingPayments->checkout_time = $booking['payment_details']['checkout_time'];
                                $bookingPayments->payment_id = $booking['payment_details']['payment_id'];
                                $bookingPayments->save();
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
                    }

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
                                $settings = new Settings\Settings();
                                $settings->run_socket_connection_command($device_id, 'all');
                            }
                        }
                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('importDetails', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

//            echo $ex->getMessage();
//            exit;
            return $ex->getMessage();
        }
    }

    /**
     * Get All Booking Payments
     * @return type
     */
    public function getPayments(Request $request) {
        $dublicate_booking_ids = array();
        if (Session::has('dublicate_booking_ids')) {
            $dublicate_booking_ids = Session::get('dublicate_booking_ids');
        } else {
            $get_person_seasonal_duplicate_records = \App\BookingPaymentsView::selectRaw('group_concat(booking_id) AS duplicate_booking_ids')
                    ->where('type', 6)
                    ->where('check_in', '<', date('Y-m-d H:i:s'))
                    ->where('check_out', date('Y-12-31 23:59:59'))
                    ->groupBy(['email', 'first_name', 'dob'])
                    ->havingRaw('count(email) > 1')
                    ->havingRaw('count(first_name) > 1')
                    ->havingRaw('count(dob) > 1')
                    ->get();
            foreach ($get_person_seasonal_duplicate_records as $get_person_duplicate_record) {
                $get_duplicates_person = explode(',', $get_person_duplicate_record->duplicate_booking_ids);
                foreach ($get_duplicates_person as $index => $get_duplicate) {
                    if ($index == 0) {
                        continue;
                    }
                    $dublicate_booking_ids[] = $get_duplicate;
                }
            }
            $get_parking_seasonal_duplicate_records = \App\BookingPaymentsView::selectRaw('group_concat(booking_id) AS duplicate_booking_ids')
                    ->where('type', 4)
                    ->where('check_in', '<', date('Y-m-d H:i:s'))
                    ->where('check_out', date('Y-12-31 23:59:59'))
                    ->groupBy(['email', 'vehicle_num'])
                    ->havingRaw('count(email) > 1')
                    ->havingRaw('count(vehicle_num) > 1')
                    ->get();
            foreach ($get_parking_seasonal_duplicate_records as $get_parking_duplicate_record) {
                $get_duplicates_parking = explode(',', $get_parking_duplicate_record->duplicate_booking_ids);
                foreach ($get_duplicates_parking as $index => $get_duplicate) {
                    if ($index == 0) {
                        continue;
                    }
                    $dublicate_booking_ids[] = $get_duplicate;
                }
            }
            Session::put('dublicate_booking_ids', $dublicate_booking_ids);
        }
        $search_type = '';
        $search_val = '';
        $filter_booking_online = 'all';
        $filter_booking_type = 'all';
        $filter_ticket_type = 'all';
        $filter_valid_dates = '';
        $bookingPayments = \App\BookingPaymentsView::sortable();

        $bookingPayments = $bookingPayments->whereNotIn('booking_id', $dublicate_booking_ids);
        if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn)) {
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_type)) {
                    if ($request->search_type == 'first_name') {
                        $bookingPayments = $bookingPayments->where('first_name', 'LIKE', "%{$request->search_val}%");
                    } elseif ($request->search_type == 'vehicle') {
                        $bookingPayments = $bookingPayments->where('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    }
                    elseif ($request->search_type == 'email') {
                        $bookingPayments = $bookingPayments->where('email', 'LIKE', "%{$request->search_val}%");
                    } else {
                        $bookingPayments = $bookingPayments->where('first_name', 'LIKE', "%{$request->search_val}%");
                        $bookingPayments = $bookingPayments->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                        $bookingPayments = $bookingPayments->orWhere('email', 'LIKE', "%{$request->search_val}%");
                    }
                } else {
                    $bookingPayments = $bookingPayments->where('first_name', 'LIKE', "%{$request->search_val}%");
                    $bookingPayments = $bookingPayments->orWhere('vehicle_num', 'LIKE', "%{$request->search_val}%");
                    $bookingPayments = $bookingPayments->orWhere('email', 'LIKE', "%{$request->search_val}%");
                }
            }
        }
        if (isset($request->filter_booking_online) && $request->filter_booking_online != $filter_booking_online) {
            $filter_booking_online = $request->filter_booking_online;
            if ($filter_booking_online == 0) {
                $bookingPayments = $bookingPayments->whereNull('is_online');
                // $bookingPayments = $bookingPayments->orWhere('is_online', 0);
            } else {
                $bookingPayments = $bookingPayments->whereNotNull('is_online');
                $bookingPayments = $bookingPayments->where('is_online', '>', 0);
            }
        }
        if (isset($request->filter_booking_type) && $request->filter_booking_type != $filter_booking_type) {
            $filter_booking_type = $request->filter_booking_type;
            if ($filter_booking_type == 'person') {
                $bookingPayments = $bookingPayments->whereIn('type', array(6, 7));
            } else {
                $bookingPayments = $bookingPayments->where('type', 4);
            }
        }
        if (isset($request->filter_ticket_type) && $request->filter_ticket_type != $filter_ticket_type) {
            $filter_ticket_type = $request->filter_ticket_type;
            if ($filter_ticket_type == 'seasonal') {
                $bookingPayments = $bookingPayments->where('check_in', '<', date('Y-m-d H:i:s'));
                $bookingPayments = $bookingPayments->where('check_out', date('Y-12-31 23:59:59'));
            } else {
                $bookingPayments = $bookingPayments->where('check_in', '<=', date('Y-m-d 00:00:00'));
                $bookingPayments = $bookingPayments->where('check_out', '<=', date('Y-m-d 23:59:59'));
            }
        }
        if (isset($request->filter_valid_dates) && $request->filter_valid_dates != $filter_valid_dates) {
            $filter_valid_dates = $request->filter_valid_dates;
            $filter_valid_dates_array = explode(' - ', $filter_valid_dates);
            $datefrom = date('Y-m-d 00:00:00', strtotime($filter_valid_dates_array[0]));
            $dateto = date('Y-m-d 23:59:59', strtotime($filter_valid_dates_array[1]));
            $bookingPayments = $bookingPayments->whereBetween('check_in', array($datefrom, $dateto));
        }
        $totalBookings = $bookingPayments->count();
        $totalAmount = $bookingPayments->sum('amount');
        $BookingPaymentsArray = array();
        if (isset($request->export_btn)) {
            $exportBookingPayments = $bookingPayments->get()->toArray();
            $BookingPaymentsArray[] = array(
                __('payments.name'),
                __('payments.vehicle'),
                __('payments.amount'),
                __('payments.arrival'),
                __('payments.departure'),
                __('payments.type'),
                __('payments.status'),
            );
            foreach ($exportBookingPayments as $exportBooking) {
                if ($exportBooking['type'] == 0) {
                    $type = 'N/A';
                } else if ($exportBooking['type'] == 1) {
                    $type = 'Send Ticket';
                } else if ($exportBooking['type'] == 2) {
                    $type = 'White List';
                } else if ($exportBooking['type'] == 3) {
                    $type = 'User List';
                } else if ($exportBooking['type'] == 4) {
                    if (date('d/m/Y H:i', strtotime($exportBooking['check_out'])) == date('31/12/Y 23:59')) {
                        $type = 'Annual parking subscription';
                    } else {
                        $type = 'Day ticket parking';
                    }
                } else if ($exportBooking['type'] == 5) {
                    $type = 'BarCode';
                } else if ($exportBooking['type'] == 6 || $exportBooking['type'] == 7) {
                    if (date('d/m/Y H:i', strtotime($exportBooking['check_out'])) == date('31/12/Y 23:59')) {
                        $type = 'Seasonal subscription person';
                    } else {
                        $type = 'Day ticket person';
                    }
                } else {
                    $type = 'N/A';
                }
                if ($exportBooking['is_online']) {
                    $status = __('payments.online_payment');
                } else {
                    $status = __('payments.payment_terminal');
                }
                $BookingPaymentsArray[] = array(
                    __('payments.name') => !empty($exportBooking['first_name']) ? $exportBooking['first_name'] : $exportBooking['email'],
                    __('payments.vehicle') => $exportBooking['vehicle_num'] ? $exportBooking['vehicle_num'] : 'N/A',
                    __('payments.amount') => number_format($exportBooking['amount'], 2, ',', '.'),
                    __('payments.arrival') => date('d/m/Y H:i', strtotime($exportBooking['check_in'])),
                    __('payments.departure') => date('d/m/Y H:i', strtotime($exportBooking['check_out'])),
                    __('payments.type') => $type,
                    __('payments.status') => $status,
                );
            }
            $BookingPaymentsArray[] = array(
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            );
            $BookingPaymentsArray[] = array(
                '',
                '',
                '',
                __('payments.t_bookings') . ' ' . $totalBookings,
                '',
                __('payments.t_amount') . ' ' . number_format($totalAmount, 2, ',', '.') . '€',
                ''
            );
            Excel::create(__('payments.payments'), function($excel) use($BookingPaymentsArray) {
                $excel->setTitle(__('payments.payments'));
                $excel->sheet(__('payments.payments'), function($sheet) use($BookingPaymentsArray) {
                    $sheet->fromArray(
                            $BookingPaymentsArray, null, 'A1', false, false
                    );
                });
            })->download('xlsx');
        }
        $bookingPayments = $bookingPayments->paginate(25);
        $currentPageTotalCount = $bookingPayments->count();
        $currentPageTotalAmount = $bookingPayments->sum('amount');
        $todayBookingPayments = BookingPayments::with('booking')
                ->where('amount', '>', 0)
                ->whereDate('created_at', \Illuminate\Support\Carbon::today())
                ->get();
        $todayTotalAmount = BookingPayments::with('booking')
                ->where('amount', '>', 0)
                ->whereDate('created_at', \Illuminate\Support\Carbon::today())
                ->sum('amount');
        return view('location.booking-payments', compact(
                        'todayTotalAmount', 'todayBookingPayments', 'bookingPayments', 'totalAmount', 'currentPageTotalCount', 'currentPageTotalAmount', 'search_type', 'search_val', 'filter_booking_online', 'filter_booking_type', 'filter_ticket_type',
//                'filter_time_type', 
                        'filter_valid_dates', 'totalBookings'));
    }

    /**
     * Get All Booking Transactions
     * @return type
     */
    public function all_transactions() {
        $bookingPayments = BookingPayments::with('booking')->orderBy('created_at', 'desc')->get();
        $totalAmount = BookingPayments::with('booking')->sum('amount');
        $transactionPaymentPersons = \App\TransactionPaymentPersons::with('location_devices')
                ->orderBy('created_at', 'desc')
                ->get();
        $transactionPaymentVehicles = \App\TransactionPaymentVehicles::with('bookings', 'location_devices')
                ->orderBy('created_at', 'desc')
                ->get();
        $personTicket = \App\Products::where('type', 'person_ticket')->first();
        return view('location.all_payments', compact(
                        'bookingPayments', 'totalAmount', 'transactionPaymentPersons', 'transactionPaymentVehicles', 'personTicket'
        ));
    }

    /**
     * Get All Booking Attendants
     * @return type
     */
    public function getAttendants() {
        $attendants = array();
        $bookings = Bookings::where('checkout_time', '>', \Carbon\Carbon::now())
                ->where('is_cancelled', '=', 0)
                ->whereIn('type', [1, 4, 5])
                ->groupBy('vehicle_num')
                ->get();
        foreach ($bookings as $booking) {
            $attendants = Attendants::where('booking_id', $booking->id)->first();
            if ($attendants) {
                continue;
            }
            if ($booking->type == 1) {
                $type = 'Send Ticket';
            } else if ($booking->type == 4) {
                if ($booking->checkout_time == date('Y-12-31 23:59:59')) {
                    $type = 'Seasonal';
                } else {
                    $type = 'Day';
                }
            } else if ($booking->type == 5) {
                $type = 'Barcode';
            } else {
                $type = 'N/A';
            }
            $user_name = 'N/A';
            if ($booking->type == 5) {
                $user_name = $booking->barcode;
            } else {
                if ($booking->first_name || $booking->last_name) {
                    $user_name = ucfirst($booking->first_name . ' ' . $booking->last_name);
                }
            }
            $attendants[] = (object) array(
                        'users' => $user_name,
                        'vehicle' => $booking->vehicle_num ? $booking->vehicle_num : 'N/A',
                        'phone' => $booking->phone_number ? $booking->phone_number : 'N/A',
                        'type' => $type
            );
        }
        $user_bookings = Bookings::where('is_cancelled', '=', 0)
                ->whereIn('type', [2, 3])
                ->groupBy('vehicle_num')
                ->get();
        foreach ($user_bookings as $booking) {
            $attendants = Attendants::where('booking_id', $booking->id)->first();
            if ($attendants) {
                continue;
            }
            if ($booking->type == 2) {
                $type = 'White List';
            } else if ($booking->type == 3) {
                $type = 'User List';
            } else {
                $type = 'N/A';
            }
            $user_name = 'N/A';
            if ($booking->first_name || $booking->last_name) {
                $user_name = ucfirst($booking->first_name . ' ' . $booking->last_name);
            }
            $attendants[] = (object) array(
                        'users' => $user_name,
                        'vehicle' => $booking->vehicle_num ? $booking->vehicle_num : 'N/A',
                        'phone' => $booking->phone_number ? $booking->phone_number : 'N/A',
                        'type' => $type
            );
        }
        return view('location.attendants', compact('attendants'));
    }

    /**
     * Get Location Reviews
     * @return type
     */
    public function getReviews() {
        $locationOptions = LocationOptions::find(1);
        $average_rating_round = round($locationOptions->star_rank);
        $rating_html = '';
        for ($i = 0; $i < 5; $i++) {
            if ($i < $average_rating_round) {
                $rating_html = $rating_html . '<i class="fa fa-star color-star-fill" aria-hidden="true"></i>';
            } else {
                $rating_html = $rating_html . '<i class="fa fa-star-o" aria-hidden="true"></i>';
            }
        }
        $locationOptions->rating_html = $rating_html;
        return view('location.index', compact('locationOptions'));
    }

    /**
     * Get Location Edit Page
     * @return type
     */
    public function edit() {
        $location = LocationOptions::find(1);
        $locationImages = LocationImages::all();
        $locationTimings = LocationTimings::all();
        $timings['weekDaysTimings'] = array();
        $timings['whiteListWeekDaysTimings'] = array();
        $timings['personWeekDaysTimings'] = array();
        $dowMap = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        foreach ($locationTimings as $locationTiming) {
            if ($locationTiming->is_whitelist == 0 && $locationTiming->is_person == 0) {
                $timings['weekDaysTimings'][$locationTiming->week_day_num] = array(
                    'id' => $locationTiming->id,
                    'live_id' => $locationTiming->live_id,
                    'week_day_num' => $locationTiming->week_day_num,
                    'opening_time' => date('H:i', strtotime($locationTiming->opening_time)),
                    'closing_time' => date('H:i', strtotime($locationTiming->closing_time))
                );
            } elseif ($locationTiming->is_whitelist == 1 && $locationTiming->is_person == 0) {
                $timings['whiteListWeekDaysTimings'][$locationTiming->week_day_num] = array(
                    'id' => $locationTiming->id,
                    'live_id' => $locationTiming->live_id,
                    'week_day_num' => $locationTiming->week_day_num,
                    'opening_time' => date('H:i', strtotime($locationTiming->opening_time)),
                    'closing_time' => date('H:i', strtotime($locationTiming->closing_time))
                );
            } elseif ($locationTiming->is_whitelist == 0 && $locationTiming->is_person == 1) {
                $timings['personWeekDaysTimings'][$locationTiming->week_day_num] = array(
                    'id' => $locationTiming->id,
                    'live_id' => $locationTiming->live_id,
                    'week_day_num' => $locationTiming->week_day_num,
                    'opening_time' => date('H:i', strtotime($locationTiming->opening_time)),
                    'closing_time' => date('H:i', strtotime($locationTiming->closing_time))
                );
            }
        }
        $languages = Language::all();
        $todayParkingSpots = $location->total_spots;
        $todayPersonSpots = $location->total_spots_person;
        $locationExtraSpots = LocationExtraSpots::where('date', date('Y-m-d'))
                ->orderBy('created_at', 'desc')
                ->first();
        if ($locationExtraSpots) {
            if (!empty($locationExtraSpots->avaialable_spots)) {
                $todayParkingSpots = $locationExtraSpots->avaialable_spots;
            }
            if (!empty($locationExtraSpots->person_avaialable_spots)) {
                $todayPersonSpots = $locationExtraSpots->person_avaialable_spots;
            }
        }
        return view('location.edit', compact(
                        'location', 'locationImages', 'timings', 'languages', 'dowMap', 'todayParkingSpots', 'todayPersonSpots'));
    }

    /**
     * Update Location Data
     * @param Request $request
     * @return type
     */
    public function update(Request $request) {
        try {
            $this->validate($request, [
                'title' => 'required|min:3'
            ]);
            $data = $request->all();
            $language = Language::find($data['language']);
            $data['language_live_id'] = $language->live_id;
            $location = LocationOptions::find(1);
            $locationId = $location->live_id;
            $user_id = auth()->user()->live_id;
            $Key = base64_encode($locationId . '_' . $user_id);
            $responseData['success'] = 0;
            try {
                $http = new Client();
                $response = $http->post(env('API_BASE_URL') . '/api/update-location-data', [
                    'form_params' => [
                        'token' => $Key,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
            } catch (\Exception $ex) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('update-location-data', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            }

            $location->online_booking_stop_parking = $data['online_booking_stop_parking'];
            $location->total_spots_person = $data['total_spots_person'];
            $location->online_booking_stop_person = $data['online_booking_stop_person'];
            $location->is_covered = $data['is_covered'];
            $location->is_gated = $data['is_gated'];
            $location->description = $data['description'];
            $location->total_spots = $data['total_spots'];
            $location->title = $data['title'];
            $location->extra_features = $data['extra_features'];
            $location->height_restriction_value = $data['height_restriction'] == 1 ? $data['height_restriction_value'] : '0';
            $location->owner_phone_num = $data['owner_phone_num'];
            $location->location_type = $data['location_type'];
            $location->owner_operator_name = $data['owner_operator_name'];
            $location->max_stay = $data['is_max_stay'] == 1 ? $data['maximum_stay'] : '0';
            $location->advance_booking_limit = $data['is_advance_booking_limit'] == 1 ? $data['advance_booking_time'] : '0';
            $location->barcode_series = $data['is_barcode_series_available'] == 1 ? $data['barcode_series'] : null;
            $location->is_whitelist = $data['is_whitelist'];
            $location->is_bikes = $data['is_bikes'];
            if ($data['is_bikes'] == 1) {
                $location->bike_range_start = $data['bike_selector_from'];
                $location->bike_range_end = $data['bike_selector_to'];
            } else {
                $location->bike_range_start = 0;
                $location->bike_range_end = 0;
            }
            $location->is_doors = $data['is_doors'];
            if ($data['is_doors'] == 1) {
                $location->door_range_start = $data['door_selector_from'];
                $location->door_range_end = $data['door_selector_to'];
            } else {
                $location->door_range_start = 0;
                $location->door_range_end = 0;
            }
            $location->ev_charger = $data['is_ev_charger_available'];
            if ($data['is_ev_charger_available'] == 1) {
                $location->ev_charger_range_start = $data['ev_charger_range_from'];
                $location->ev_charger_range_end = $data['ev_charger_range_to'];
                $location->ev_charger_energy = $data['ev_charger_energy'];
            } else {
                $location->ev_charger_range_start = 0;
                $location->ev_charger_range_end = 0;
                $location->ev_charger_energy = 0;
            }
            $location->language_id = $data['language'];
            $location->price_per_hour = $data['price_per_hour'];
            $location->price_per_day = $data['price_per_day'];
            if (array_key_exists('time_lag', $data)) {
                $location->time_lag = $data['time_lag'];
            }
            $location->save();
            if (isset($data['today_spots']) || isset($data['today_spots_person'])) {
                $locationExtraSpots = LocationExtraSpots::where('date', date('Y-m-d'))
                        ->orderBy('created_at', 'desc')
                        ->first();
                if (!$locationExtraSpots) {
                    $locationExtraSpots = new LocationExtraSpots();
                    $locationExtraSpots->date = date('Y-m-d');
                }
                if ($data['today_spots_person'] > 0) {
                    $addSpots = $data['today_spots_person'];
                    $locationExtraSpots->person_avaialable_spots = $addSpots;
                }
                if ($data['today_spots'] > 0) {
                    $addSpots = $data['today_spots'];
                    $locationExtraSpots->avaialable_spots = $addSpots;
                }
                $locationExtraSpots->save();
            }

            foreach ($data['weekday_id'] as $index => $weekday_id) {
                $weekdayTiming = LocationTimings::find($weekday_id);
                if ($weekdayTiming) {
                    if (array_key_exists($index, $data['weekday_checkbox']) && $data['weekday_checkbox'][$index]) {
                        if ($responseData['success']) {
                            $weekdayTiming->live_id = $responseData['data']['weekday_live_id'][$index];
                        }
                        $weekdayTiming->week_day_num = $index;
                        $weekdayTiming->opening_time = date('H:i:s', strtotime($data['opening_time_day'][$index]));
                        $weekdayTiming->closing_time = date('H:i:s', strtotime($data['closing_time_day'][$index]));
                        $weekdayTiming->save();
                    } else {
                        $weekdayTiming->forceDelete();
                    }
                } else {
                    if (array_key_exists($index, $data['weekday_checkbox']) && $data['weekday_checkbox'][$index]) {
                        $weekdayTiming = new LocationTimings();
                        if ($responseData['success']) {
                            $weekdayTiming->live_id = $responseData['data']['weekday_live_id'][$index];
                        }
                        $weekdayTiming->week_day_num = $index;
                        $weekdayTiming->opening_time = date('H:i:s', strtotime($data['opening_time_day'][$index]));
                        $weekdayTiming->closing_time = date('H:i:s', strtotime($data['closing_time_day'][$index]));
                        $weekdayTiming->save();
                    }
                }
            }

            foreach ($data['w_weekday_id'] as $index => $w_weekday_id) {
                $wWeekdayTiming = LocationTimings::find($w_weekday_id);
                if ($wWeekdayTiming) {
                    if (array_key_exists($index, $data['w_weekday_checkbox']) && $data['w_weekday_checkbox'][$index]) {
                        if ($responseData['success']) {
                            $wWeekdayTiming->live_id = $responseData['data']['w_weekday_live_id'][$index];
                        }
                        $wWeekdayTiming->week_day_num = $index;
                        $wWeekdayTiming->is_whitelist = 1;
                        $wWeekdayTiming->opening_time = date('H:i:s', strtotime($data['w_opening_time_day'][$index]));
                        $wWeekdayTiming->closing_time = date('H:i:s', strtotime($data['w_closing_time_day'][$index]));
                        $wWeekdayTiming->save();
                    } else {
                        $wWeekdayTiming->forceDelete();
                    }
                } else {
                    if (array_key_exists($index, $data['w_weekday_checkbox']) && $data['w_weekday_checkbox'][$index]) {
                        $wWeekdayTiming = new LocationTimings();
                        if ($responseData['success']) {
                            $wWeekdayTiming->live_id = $responseData['data']['w_weekday_live_id'][$index];
                        }
                        $wWeekdayTiming->week_day_num = $index;
                        $wWeekdayTiming->is_whitelist = 1;
                        $wWeekdayTiming->opening_time = date('H:i:s', strtotime($data['w_opening_time_day'][$index]));
                        $wWeekdayTiming->closing_time = date('H:i:s', strtotime($data['w_closing_time_day'][$index]));
                        $wWeekdayTiming->save();
                    }
                }
            }

            foreach ($data['p_weekday_id'] as $index => $p_weekday_id) {
                $pWeekdayTiming = LocationTimings::find($p_weekday_id);
                if ($pWeekdayTiming) {
                    if (array_key_exists($index, $data['p_weekday_checkbox']) && $data['p_weekday_checkbox'][$index]) {
                        if ($responseData['success']) {
                            $pWeekdayTiming->live_id = $responseData['data']['p_weekday_live_id'][$index];
                        }
                        $pWeekdayTiming->week_day_num = $index;
                        $pWeekdayTiming->is_person = 1;
                        $pWeekdayTiming->opening_time = date('H:i:s', strtotime($data['p_opening_time_day'][$index]));
                        $pWeekdayTiming->closing_time = date('H:i:s', strtotime($data['p_closing_time_day'][$index]));
                        $pWeekdayTiming->save();
                    } else {
                        $pWeekdayTiming->forceDelete();
                    }
                } else {
                    if (array_key_exists($index, $data['p_weekday_checkbox']) && $data['p_weekday_checkbox'][$index]) {
                        $pWeekdayTiming = new LocationTimings();
                        if ($responseData['success']) {
                            $pWeekdayTiming->live_id = $responseData['data']['p_weekday_live_id'][$index];
                        }
                        $pWeekdayTiming->week_day_num = $index;
                        $pWeekdayTiming->is_person = 1;
                        $pWeekdayTiming->opening_time = date('H:i:s', strtotime($data['p_opening_time_day'][$index]));
                        $pWeekdayTiming->closing_time = date('H:i:s', strtotime($data['p_closing_time_day'][$index]));
                        $pWeekdayTiming->save();
                    }
                }
            }

            $device_settings = \App\DeviceSettings::get();
            if ($device_settings->count() > 0) {
                foreach ($device_settings as $device_details) {
                    $device = \App\DeviceSettings::find($device_details->id);
                    $device->location_settings = 0;
                    $device->location_timings_settings = 0;
                    $device->location_whitelist_timings_settings = 0;
                    $device->location_person_timings_settings = 0;
                    $device->location_settings_details = NULL;
                    $device->location_timings_settings_details = NULL;
                    $device->location_whitelist_timings_settings_details = NULL;
                    $device->location_person_timings_settings_details = NULL;
                    $device->save();
                }
            }
            if ($responseData['success'] && isset($responseData['data'])) {
                $settings = new Settings\Settings();
                $settings->settings_updated('location_setting');
                Session::flash('heading', 'Success!');
                Session::flash('message', __('location-setting.location_update'));
                Session::flash('icon', 'success');
                return redirect()->back()->withInput();
            } else {
                $settings = new Settings\Settings();
                $settings->settings_updated('location_setting');
                Session::flash('heading', 'Warning!');
                Session::flash('message', __('location-setting.location_update_localy'));
                Session::flash('icon', 'warning');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('update', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');

            return redirect()->back()->withInput();
        }
    }

    /**
     * Get All Future Bookings
     * @return type
     */
    public function getFutureBookings(Request $request) {
        $filter_booking_type = 'all';
        $filter_valid_dates = '';
        $totalPersonBookings = 0;
        $totalBookings = 0;
        if (isset($request->filter_valid_dates) && $request->filter_valid_dates != $filter_valid_dates) {
            $filter_valid_dates = $request->filter_valid_dates;
            $query_valid_dates = str_replace("/", "-", $filter_valid_dates);
            if (isset($request->filter_booking_type) && $request->filter_booking_type != $filter_booking_type) {
                $filter_booking_type = $request->filter_booking_type;
                if ($filter_booking_type == 'person') {
                    $totalPersonBookings = Bookings::whereHas('booking_payments', function($q) {
                                $q->where('is_online', '=', 1);
                            })
                            ->where('type', 6)
                            ->where('checkin_time', '>=', date('Y-m-d 00:00:00', strtotime($query_valid_dates)))
                            ->where('checkout_time', '<=', date('Y-m-d 23:59:59', strtotime($query_valid_dates)))
                            ->where('is_tommy_online', 0)
                            ->count();
                } else {
                    $totalBookings = Bookings::whereHas('booking_payments', function($q) {
                                $q->where('is_online', '=', 1);
                            })
                            ->where('type', 4)
                            ->where('checkin_time', '>=', date('Y-m-d 00:00:00', strtotime($query_valid_dates)))
                            ->where('checkout_time', '<=', date('Y-m-d 23:59:59', strtotime($query_valid_dates)))
                            ->count();
                }
            } else {
                $totalPersonBookings = Bookings::whereHas('booking_payments', function($q) {
                            $q->where('is_online', '=', 1);
                        })
                        ->where('type', 6)
                        ->where('checkin_time', '>=', date('Y-m-d 00:00:00', strtotime($query_valid_dates)))
                        ->where('checkout_time', '<=', date('Y-m-d 23:59:59', strtotime($query_valid_dates)))
                        ->where('is_tommy_online', 0)
                        ->count();
                $totalBookings = Bookings::whereHas('booking_payments', function($q) {
                            $q->where('is_online', '=', 1);
                        })
                        ->where('type', 4)
                        ->where('checkin_time', '>=', date('Y-m-d 00:00:00', strtotime($query_valid_dates)))
                        ->where('checkout_time', '<=', date('Y-m-d 23:59:59', strtotime($query_valid_dates)))
                        ->count();
            }
        }
        return view('location.future-bookings', compact(
                        'filter_booking_type', 'filter_valid_dates', 'totalBookings', 'totalPersonBookings'));
    }

    function increaseStops(Request $request) {
        try {
            $data = $request->all();
            $location = LocationOptions::find(1);
            $locationId = $location->live_id;
            $user_id = auth()->user()->live_id;
            $Key = base64_encode($locationId . '_' . $user_id);
            $responseData['success'] = 0;
            try {
                $http = new Client();
                $response = $http->post('http://dev.parkingshop-cloud.com/api/update-increase-stops-data', [
                    'form_params' => [
                        'token' => $Key,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
                if ($responseData['success']) {
                    $add_spots = LocationExtraSpots::where('date', date('Y-m-d', strtotime($data['date'])))
                                    ->orderBy('created_at', 'desc')->first();
                    if (!$add_spots) {
                        $add_spots = new LocationExtraSpots();
                        $add_spots->date = date('Y-m-d', strtotime($data['date']));
                    }
                    if ($data['type'] == 'person') {
                        $spots = $location->total_spots_person + $data['spots'];
                        $add_spots->person_avaialable_spots = $spots;
                    } else {
                        $spots = $location->total_spots + $data['spots'];
                        $add_spots->avaialable_spots = $spots;
                    }
                    $add_spots->save();
                    return json_encode(array('status' => 1, 'message' => 'Stops added successfully.'));
                } else {
                    return json_encode(array('status' => 0, 'message' => 'Data not stored.'));
                }
            } catch (\Exception $ex) {
                throw $ex;
            }
        } catch (\Exception $ex) {
            $error_log = new \App\Http\Controllers\LogController();
            $error_log->log_create('increaseStops', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            return json_encode(array('status' => 0, 'message' => $ex->getMessage()));
        }
    }

}
