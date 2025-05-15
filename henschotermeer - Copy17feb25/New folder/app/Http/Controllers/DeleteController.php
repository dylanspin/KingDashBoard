<?php

namespace App\Http\Controllers;

use App\Blog;
use App\BlogCategory;
use App\BlogComment;
use App\Tag;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;

class DeleteController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    function delete_whitelist_bookings($email) {
        \App\Bookings::where([
            ['email', $email],
            ['type', 2],
        ])->forceDelete();
    }

    function delete_whitelist_user($email) {
        $is_booking_exists = FALSE;
        $is_userlist_exists = FALSE;
        $booking_exists = \App\Bookings::where([
                    ['email', $email],
                ])
                ->whereNotIn('type', [2, 3])
                ->first();
        if (!$booking_exists) {
            $is_booking_exists = TRUE;
        }
        $userlist = \App\WhitelistUsers::where('email', $email)->first();
        if (!$userlist) {
            $is_userlist_exists = TRUE;
        }
        if (!$is_booking_exists && !$is_userlist_exists) {
            $customer = \App\Customer::where('email', $email)->first();
            $customer_id = $customer->id;
            $booking_exists = \App\Bookings::where([
                        ['customer_id', $customer_id],
                    ])
                    ->whereNotIn('type', [2, 3])
                    ->first();
            if (!$booking_exists) {
                $customer->forceDelete();
                \App\Profile::where('customer_id', $customer_id)->forceDelete();
                \App\CustomerVehicleInfo::where('customer_id', $customer_id)->forceDelete();
                \App\Bookings::where('email', $email)->forceDelete();
            }
        }
    }

    function delete_userlist_bookings($email) {
        if (!empty($email)) {
            \App\Bookings::where([
                ['email', $email],
                ['type', 3],
            ])->forceDelete();
        }
    }

    function delete_userlist_bookings_without_email($customer_vehicle_info_id) {
        \App\Bookings::where([
            ['customer_vehicle_info_id', $customer_vehicle_info_id],
            ['type', 3],
        ])->forceDelete();
    }

    function delete_userlist_user($userlist_user) {
        $vehicle_ids = $userlist_user->customer_vehicle_info()->pluck('id')->toArray();
        \App\Bookings::whereIn('customer_vehicle_info_id', $vehicle_ids)->forceDelete();
        \App\CustomerVehicleInfo::whereIn('id', $vehicle_ids)->forceDelete();
    }

}
