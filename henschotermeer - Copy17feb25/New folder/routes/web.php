<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Auth::routes();
//Route::get('/test_form', 'HomeController@test_form');
Route::get('/404', 'HomeController@page_404');
Route::get('/500', 'HomeController@page_500');
Route::get('/', 'HomeController@index');
Route::get('/register', 'HomeController@page_404');
Route::post('wizard-step-0-submit', 'HomeController@wizard_step_0_submit')->name('wizard_step_0_submit');
Route::get('wizard-step-1', 'HomeController@wizard_step_1')->name('wizard_step_1');
Route::post('wizard-step-1-submit', 'HomeController@wizard_step_1_submit')->name('wizard_step_1_submit');
Route::get('wizard-step-2', 'HomeController@wizard_step_2')->name('wizard_step_2');
Route::get('wizard-step-3', 'HomeController@wizard_step_3')->name('wizard_step_3');
Route::post('wizard-step-3-submit', 'HomeController@wizard_step_3_submit')->name('wizard_step_3_submit');
Route::get('wizard-step-4', 'HomeController@wizard_step_4')->name('wizard_step_4');
Route::post('wizard-step-4-submit', 'HomeController@wizard_step_4_submit')->name('wizard_step_4_submit');
Route::get('wizard-step-5', 'HomeController@wizard_step_5')->name('wizard_step_5');
Route::post('wizard-step-5-submit', 'HomeController@wizard_step_5_submit')->name('wizard_step_5_submit');
Route::get('wizard-step-6', 'HomeController@wizard_step_6')->name('wizard_step_6');
Route::post('wizard-step-6-submit', 'HomeController@wizard_step_6_submit')->name('wizard_step_6_submit');
Route::get('wizard-step-7', 'HomeController@wizard_step_7')->name('wizard_step_7');
Route::post('wizard-step-7-submit', 'HomeController@wizard_step_7_submit')->name('wizard_step_7_submit');
Route::get('wizard-step-8', 'HomeController@wizard_step_8')->name('wizard_step_8');
Route::get('import-location', 'LocationController@importDetails');
Route::get('export-userlist-data', 'Settings\PushChangesLive@push_userlist');
Route::get('export-device-data', 'Settings\PushChangesLive@push_devices');
Route::post('dashboard/edit_vehicle_num', 'DashboardController@edit_vehicle_num');
Route::post('dashboard/update_vehicle', 'DashboardController@update_vehicle_booking');
Route::post('dashboard/manage_stucked_plate_readers', 'JsTimerController@manage_stucked_plate_readers');
Route::post('dashboard/edit_device_vehicle_num', 'DashboardController@edit_device_vehicle_num');
Route::post('dashboard/update_device_vehicle', 'DashboardController@update_device_vehicle');
Route::post('dashboard/no_entrance_transaction', 'DashboardController@no_entrance_transaction');
Route::post('check_latest_device_transactions', 'JsTimerController@check_latest_device_transactions');
Route::post('check_latest_device_transactions_p', 'JsTimerController@check_latest_device_transactions_p');
Route::post('check_changes_timer', 'JsTimerController@check_changes_timer');
Route::post('check_dashboard_changes_timer', 'JsTimerController@check_dashboard_changes_timer');
Route::post('check_person_dashboard_changes_timer', 'JsTimerController@check_person_dashboard_changes_timer');
Route::get('change-language/{locale}', function ($locale) {
    \Illuminate\Support\Facades\Session::put('applocale', $locale);
    return redirect()->back();
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager', 'operator']], function () {
#Barcodes Management routes
    Route::group(['prefix' => 'person/barcode'], function () {
        Route::get('/', 'PersonBarcodeController@index');
        Route::get('/create', 'PersonBarcodeController@create');
        Route::post('/create', 'PersonBarcodeController@store');
        Route::get('/edit/{id}', 'PersonBarcodeController@edit');
        Route::post('/edit/{id}', 'PersonBarcodeController@update');
        Route::get('/delete/{id}', 'PersonBarcodeController@destroy');
        Route::get('/download/{id}/{locale}', 'PersonBarcodeController@download');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager', 'operator']], function () {
    #Email Bounce Management routes
    Route::group(['prefix' => 'email/bounces'], function () {
        Route::get('/', 'EmailBounceController@index');
        Route::post('/', 'EmailBounceController@index');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager', 'operator', 'operator_basic', 'promo']], function () {
    Route::get('account-settings', 'UsersController@getSettings');
    Route::post('account-settings', 'UsersController@saveSettings');
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager', 'operator']], function () {
    Route::group(['prefix' => 'logs'], function () {
        Route::get('licence-plates', 'DeviceBookingsController@getIndex');
        Route::post('licence-plates', 'DeviceBookingsController@getIndex');
        Route::get('licence-plates/download/{id}', 'DeviceBookingsController@downloadImage');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager', 'operator', 'operator_basic']], function () {
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/', 'DashboardController@dashboard')->name('dashboard');
        Route::get('/person', 'DashboardController@p_dashboard')->name('person_dashboard');
        Route::get('/chart/revenue', 'DashboardController@revenue_chart')->name('revenue_chart');
        Route::get('/chart/visits', 'DashboardController@visits_chart')->name('visits_chart');
        Route::post('/calendar', 'DashboardController@bookings_calender')->name('calendar');
        Route::post('/calendar_event_details', 'DashboardController@calendar_event_details')->name('calendar_event_details');
        Route::post('/open_gate', 'DashboardController@open_gate')->name('open_gate');
        Route::post('/close_gate', 'DashboardController@close_gate')->name('close_gate');
        Route::post('/open_gate_person', 'DashboardController@open_gate_person')->name('open_gate_person');
        Route::post('/close_gate_person', 'DashboardController@close_gate_person')->name('close_gate_person');
        Route::post('/open_gate_vehicle', 'DashboardController@open_gate_person')->name('open_gate_vehicle');
        Route::post('/close_gate_vehicle', 'DashboardController@close_gate_person')->name('close_gate_vehicle');
        Route::post('/change_gate_barrier_status', 'DashboardController@change_gate_barrier_status')->name('change_gate_barrier_status');
    });
    Route::get('/currently_on_location', 'DashboardController@currently_on_location');
//    Route::get('/update_open_manual_transactions', 'DashboardController@update_open_manual_transactions');
    Route::get('/currently_on_location_persons', 'DashboardController@currently_on_location_persons');
    Route::get('/transactions', 'DashboardController@transactions');
    Route::get('/transaction_details', 'DashboardController@transaction_details');
    Route::post('/transaction_details', 'DashboardController@transaction_details');
    Route::get('/transaction_details/person', 'DashboardController@transaction_details_p');
    Route::post('/transaction_details/person', 'DashboardController@transaction_details_p');
    Route::get('/devices_actions', 'DashboardController@devices_actions');

    Route::get('checkout_persons', 'TommyReservationController@setCheckOut');
    Route::get('checkout_vehicles', 'TommyReservationController@setVehicleCheckOut');
    Route::get('booking/{id}', 'BookingController@details');
    Route::get('vehicle/{id}', 'VehicleController@details');
    Route::get('person/{id}', 'VehicleController@details_p');
    Route::get('transaction/{type}/{id}', 'TransactionController@details');
    Route::get('update-confidence/{id}', 'DashboardController@low_confience_details');
    Route::get('/device_transaction/{type}/{id}', 'DashboardController@device_transaction');
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager', 'operator', 'operator_basic']], function () {
#UserList Management routes
    Route::group(['prefix' => 'user-list'], function () {
        Route::get('/', 'UserListController@index');
        Route::post('/', 'UserListController@index');
        Route::get('/view-plates', 'UserListController@viewPlates');
        Route::get('/create', 'UserListController@create');
        Route::post('/create', 'UserListController@store');
        Route::post('/create-without-email', 'UserListController@store_without_email');
        Route::get('/edit/{id}', 'UserListController@edit');
        Route::post('/edit/{id}', 'UserListController@update');
        Route::post('/edit-without-email/{id}', 'UserListController@update_without_email');
        Route::post('/update/{id}', 'UserListController@update_quick');
        Route::post('/update-without-email/{id}', 'UserListController@update_quick_without_email');
        Route::get('/delete/{id}', 'UserListController@destroy');
        Route::get('/block-or-unbolock/{id}', 'UserListController@bockOrUnblockUser');
        Route::get('/send-instructions/{id}', 'UserListController@sendInstrucions');
        Route::get('/deleted/', 'UserListController@getDeletedUsers');
        Route::get('/restore/{id}', 'UserListController@restoreUser');
        Route::post('/check-user-status', 'UserListController@checkUserStatus');
        Route::post('/check-vehicle-status', 'UserListController@checkVehicleStatus');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager']], function () {
#WhiteList Management routes
    Route::group(['prefix' => 'white-list'], function () {
        Route::get('/', 'WhiteListController@index');
        Route::get('/create', 'WhiteListController@create');
        Route::post('/create', 'WhiteListController@store');
        Route::get('/edit/{id}', 'WhiteListController@edit');
        Route::post('/edit/{id}', 'WhiteListController@update');
        Route::get('/delete/{id}', 'WhiteListController@destroy');
        Route::get('/send-instructions/{id}', 'WhiteListController@sendInstrucions');
        Route::get('/deleted/', 'WhiteListController@getDeletedUsers');
        Route::get('/restore/{id}', 'WhiteListController@restoreUser');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager', 'operator']], function () {
#TommyReservations Management routes
    Route::group(['prefix' => 'tommy-reservations'], function () {
        Route::get('/', 'TommyReservationController@index');
        Route::post('/', 'TommyReservationController@index');
        Route::get('/import', 'TommyReservationController@create');
        Route::post('/create', 'TommyReservationController@store');
        Route::get('/edit/{id}', 'TommyReservationController@edit');
        Route::post('/edit/{id}', 'TommyReservationController@update');
        Route::get('/delete/{id}', 'TommyReservationController@destroy');
        Route::get('/send-ticket/{id}', 'TommyReservationController@sendTicket');
        Route::get('/print-ticket/{id}', 'TommyReservationController@printTicket');
        Route::get('/view-members/{id}', 'TommyReservationController@viewMembers');
        Route::get('/bookings', 'TommyReservationController@bookings');
        Route::post('/bookings', 'TommyReservationController@bookings');
        Route::get('/bookings/view-members/{id}', 'TommyReservationController@viewOnLineMembers');
        Route::get('/bookings/print-all/{id}', 'TommyReservationController@printTickekInOneGo');
        Route::get('/bookings/send-ticket/{id}', 'TommyReservationController@sendOnLineTicket');
        Route::get('/bookings/print-ticket/{id}', 'TommyReservationController@printOnLineTicket');
        Route::get('/bookings/delete/{id}', 'TommyReservationController@destroyOnLineBooking');
    });
});

Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager', 'operator', 'operator_basic']], function () {
#Location Management routes
    Route::group(['prefix' => 'location'], function () {
        Route::get('/', 'LocationController@index');
        Route::get('/create', 'LocationController@create');
        Route::post('/create', 'LocationController@store');
        Route::get('/edit', 'LocationController@edit');
        Route::post('/edit', 'LocationController@update');
        Route::get('/delete/{id}', 'LocationController@destroy');
        Route::get('/reviews', 'LocationController@getReviews');
        Route::get('/payments', 'LocationController@getPayments');
        Route::post('/payments', 'LocationController@getPayments');
        Route::get('/future/bookings', 'LocationController@getFutureBookings');
        Route::post('/future/bookings', 'LocationController@getFutureBookings');
//        Route::post('/increase/stops', 'LocationController@increaseStops');
        Route::get('/attendants', 'LocationController@getAttendants');
        Route::get('/all-transactions', 'LocationController@all_transactions');
    });
	#Manual Reservation Routes
		Route::get('/editreservation/{booking_id}','AddReservationController@edit');
		Route::get('/addreservationdetails','AddReservationController@show');
		Route::get('/manual-reservation','AddReservationController@index');
		Route::post('/manual-reservation','AddReservationController@index');
		Route::post('/store_reservation_info','AddReservationController@store');
		Route::get('reserve/delete/{id}','AddReservationController@destroy');
    #Person Manual Reservation Routes
        Route::get('/edit-person-reservation/{booking_id}','PersonManualReservationController@edit');
        Route::get('/addreservationdetails','PersonManualReservationController@show');
        Route::get('/person-manual-reservation','PersonManualReservationController@index');
        Route::post('/person-manual-reservation','PersonManualReservationController@index');
        Route::post('/store-person-reservation','PersonManualReservationController@store');
        Route::post('/update-person-reservation','PersonManualReservationController@update');
        Route::get('/download-ticket/{id}/{locale}','PersonManualReservationController@download');
        Route::get('person/delete/{id}','PersonManualReservationController@destroy');

});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'operator']], function () {
#Devices Management routes
    Route::group(['prefix' => 'devices'], function () {
        Route::get('/', 'DevicesController@index');
        Route::get('/sort', 'DevicesController@sorting_devices');
        Route::post('/sort/{type}', 'DevicesController@update_devices_ordering');
        Route::get('/sync/', 'DevicesController@sync_details');
        Route::post('/name_exist/', 'DevicesController@name_exist');
        Route::get('/sync/{status}/{id}', 'DevicesController@sync_device_command_call');
        Route::get('/create', 'DevicesController@create');
        Route::post('/create', 'DevicesController@store');
        Route::get('/edit/{id}', 'DevicesController@edit');
        Route::post('/edit/{id}', 'DevicesController@update');
        Route::get('/delete/{id}', 'DevicesController@destroy');
        Route::get('/initialize/{id}', 'DevicesController@initialize_device');
        Route::get('/generate-settings-ticket/{id}', 'DevicesController@sendTicket');
        Route::get('/update-server-time/{id}', 'DevicesController@updateServerTime');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service']], function () {
    #Logs Management routes
    Route::group(['prefix' => 'logs'], function () {
        Route::get('/', 'LogController@index');
        Route::get('/resolved/{id}', 'LogController@resolved');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager']], function () {
#Messages Management routes
    Route::group(['prefix' => 'messages'], function () {
        Route::get('/', 'MessageController@index');
        Route::get('/create', 'MessageController@create');
        Route::post('/create', 'MessageController@store');
        Route::get('/edit/{id}', 'MessageController@edit');
        Route::post('/edit/{id}', 'MessageController@update');
        Route::get('/delete/{id}', 'MessageController@destroy');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager', 'operator']], function () {
#Barcodes Management routes
    Route::group(['prefix' => 'barcode'], function () {
        Route::get('/', 'BarcodeController@index');
        Route::get('/create', 'BarcodeController@create');
        Route::post('/create', 'BarcodeController@store');
        Route::get('/edit/{id}', 'BarcodeController@edit');
        Route::post('/edit/{id}', 'BarcodeController@update');
        Route::get('/delete/{id}', 'BarcodeController@destroy');
        Route::get('/download/{id}/{locale}', 'BarcodeController@download');
        Route::get('/fail-safe/{type}', 'BarcodeController@createFailSafe');
        Route::post('/fail-safe/{type}', 'BarcodeController@storeFailSafe');
        Route::get('/fail-safe/edit/{type}/{id}', 'BarcodeController@editFailSafe');
        Route::post('/fail-safe/edit/{type}/{id}', 'BarcodeController@updateFailSafe');
        Route::get('/fail-safe/delete/{type}/{id}', 'BarcodeController@destroyFailSafe');
        Route::get('/fail-safe/download/{type}/{id}', 'BarcodeController@downloadFailSafe');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager']], function () {
#Send Ticket Management routes
    Route::group(['prefix' => 'send-ticket'], function () {
        Route::get('/', 'SendTicketController@create');
        Route::post('/', 'SendTicketController@store');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager']], function () {
    #Groups Management routes
    Route::group(['prefix' => 'group'], function () {
        Route::get('/', 'GroupController@index');
        Route::get('/viewgroupinfo/{id}', 'GroupController@viewgroup');
        Route::post('/update-promo-member', 'GroupController@updateGroupMember');
        Route::get('/edit-promo-member/{id}', 'GroupController@editGroupMember');
        Route::get('/delete-promo-member/{id}', 'GroupController@deleteGroupMember');
        Route::get('/create', 'GroupController@create');
        Route::post('/create', 'GroupController@store');
        Route::get('/edit/{id}', 'GroupController@edit');
        Route::post('/edit/{id}', 'GroupController@update');
        Route::get('/delete/{id}', 'GroupController@destroy');
        Route::get('/deleted/', 'GroupController@getDeletedgroup');
        Route::get('/restore/{id}', 'GroupController@restoregroup');
    });
    #Bookings Management routes
    Route::group(['prefix' => 'details'], function () {
        Route::get('/widget1', 'BookingController@widget1_details');
        Route::get('/widget2', 'BookingController@widget2_details');
        Route::get('/widget3', 'BookingController@widget3_details');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service', 'manager']], function () {
#Products Management routes
    Route::group(['prefix' => 'products'], function () {
        Route::get('/person_ticket', 'ProductController@person_ticket');
        Route::post('/person_ticket', 'ProductController@person_ticket_store');
        Route::get('/day_ticket', 'ProductController@day_ticket');
        Route::post('/day_ticket', 'ProductController@day_ticket_store');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'service']], function () {
#User Management routes
    Route::get('users', 'UsersController@getIndex');
    Route::get('user/create', 'UsersController@create');
    Route::post('user/create', 'UsersController@save');
    Route::get('user/edit/{id}', 'UsersController@edit');
    Route::post('user/edit/{id}', 'UsersController@update');
    Route::get('user/delete/{id}', 'UsersController@delete');
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'promo']], function () {
#Promos Management routes
    Route::group(['prefix' => 'promo'], function () {
        Route::get('/', 'PromoController@index');
        Route::get('/create', 'PromoController@create');
        Route::post('/create', 'PromoController@store');
        Route::get('/edit/{id}', 'PromoController@edit');
        Route::post('/edit/{id}', 'PromoController@update');
        Route::post('/send/{id}', 'PromoController@sendCode');
        Route::get('/delete/{id}', 'PromoController@destroy');
    });
});
Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['admin', 'promo']], function () {
#Promos Management routes
    Route::group(['prefix' => 'value-card'], function () {
        Route::get('/', 'ValueCardController@index');
        Route::get('/create', 'ValueCardController@create');
        Route::post('/create', 'ValueCardController@store');
        Route::get('/edit/{id}', 'ValueCardController@edit');
        Route::post('/edit/{id}', 'ValueCardController@update');
        Route::get('/delete/{id}', 'ValueCardController@destroy');
        Route::get('/download/{id}/{locale}/{no}', 'ValueCardController@download');
    });
});
Route::group(['prefix' => 'seasonal'], function () {
    Route::get('/person', 'SeasonalSubscriptionsController@person_subscriptions');
    Route::post('/person', 'SeasonalSubscriptionsController@person_subscriptions');
    Route::get('/edit-person-ticket/{id}', 'SeasonalSubscriptionsController@editPersonTicket');
    Route::get('/edit-parking-ticket/{id}', 'SeasonalSubscriptionsController@editParkingTicket');
    Route::post('/update', 'SeasonalSubscriptionsController@update');
    Route::get('/delete/{id}', 'SeasonalSubscriptionsController@delete');
    Route::get('/parking', 'SeasonalSubscriptionsController@parking_subscriptions');
    Route::post('/parking', 'SeasonalSubscriptionsController@parking_subscriptions');
    Route::get('/download/{id}', 'SeasonalSubscriptionsController@download');
    Route::get('/resend/{id}', 'SeasonalSubscriptionsController@resend');
});
Route::group(['prefix' => 'day'], function () {
    Route::get('/person', 'DaySubscriptionsController@person_subscriptions');
    Route::get('/edit-person-ticket/{id}', 'DaySubscriptionsController@editPersonTicket');
    Route::post('/person', 'DaySubscriptionsController@person_subscriptions');
    Route::post('/update', 'DaySubscriptionsController@update');
    Route::get('/edit-parking-ticket/{id}', 'DaySubscriptionsController@editParkingTicket');
    Route::get('/parking', 'DaySubscriptionsController@parking_subscriptions');
    Route::post('/parking', 'DaySubscriptionsController@parking_subscriptions');
    Route::get('/download/{id}', 'DaySubscriptionsController@download');
    Route::get('/delete/{id}', 'DaySubscriptionsController@delete');
    Route::get('/resend/{id}', 'DaySubscriptionsController@resend');
});
Route::get('auth/{provider}/', 'Auth\SocialLoginController@redirectToProvider');
Route::get('{provider}/callback', 'Auth\SocialLoginController@handleProviderCallback');
Route::get('logout', 'Auth\LoginController@logout');
Route::get('set-nav-scroll-session/{type}', 'HomeController@set_nav_scroll_session');
#API routes
Route::group(['prefix' => 'api'], function () {
    Route::post('/initialize_settings_status/{key}/{id?}/{status}', 'Settings\Settings@initialize_settings_status');
    Route::post('/location_settings/{key}/{id?}/', 'Settings\LocationSettings@generate_location_settings');
    Route::post('/location_settings_status/{key}/{id?}/{status}', 'Settings\LocationSettings@location_settings_status');
    Route::post('/location_timings/{key}/{id?}/', 'Settings\LocationSettings@generate_location_timings');
    Route::post('/location_timings_status/{key}/{id?}/{status}', 'Settings\LocationSettings@location_timings_status');
    Route::post('/location_whitelist_timings/{key}/{id?}/', 'Settings\LocationSettings@generate_location_whitelist_timings');
    Route::post('/location_whitelist_timings_status/{key}/{id?}/{status}', 'Settings\LocationSettings@location_whitelist_timings_status');
    Route::post('/whitelist_settings/{key}/{id?}/', 'Settings\WhiteListSettings@generate_whitelist_settings');
    Route::post('/whitelist_settings_status/{key}/{id?}/{status}', 'Settings\WhiteListSettings@whitelist_settings_status');
    Route::post('/userlist_settings/{key}/{id?}/', 'Settings\UserListSettings@generate_userlist_settings');
    Route::post('/userlist_settings_status/{key}/{id?}/{status}', 'Settings\UserListSettings@userlist_settings_status');
    Route::post('/device_settings/{key}/{id?}/', 'Settings\DeviceSettings@generate_device_settings');
    Route::post('/device_settings_status/{key}/{id?}/{status}', 'Settings\DeviceSettings@device_settings_status');
    Route::post('/get_messages/{key}/{id}/', 'Settings\DeviceSettings@get_device_messages');
    Route::post('/other_settings_status/{key}/{id?}/{status}', 'Settings\DeviceSettings@other_settings_status');
    Route::post('/verify_access/{key}/{id}/{type}/{val}/{ticket_type?}', 'Settings\VerifyBookings@verify_booking');
    Route::post('/verify_access_status/{key}/{id}/{booking_id}/{status}', 'Settings\VerifyBookings@verify_booking_status');
    Route::post('/health_check/{key}/{id}/', 'Settings\Settings@health_check');
    Route::post('/check_status/{key}/{id}/{booking_id}/{ticket_type?}/{vehicle_num?}/', 'Settings\VerifyBookings@check_status');
    Route::post('/bar_code/{key}/{id}/{barcode}/', 'Settings\BarcodeSettings@is_valid_barcode');
    Route::post('/get_products/{key}/{id}/', 'Settings\PaymentTerminalControllers@get_products');
    Route::get('/initialize_settings_status/{key}/{id?}/{status}', 'Settings\Settings@initialize_settings_status');
    Route::get('/location_settings/{key}/{id?}/', 'Settings\LocationSettings@generate_location_settings');
    Route::get('/location_settings_status/{key}/{id?}/{status}', 'Settings\LocationSettings@location_settings_status');
    Route::get('/location_timings/{key}/{id?}/', 'Settings\LocationSettings@generate_location_timings');
    Route::get('/location_timings_status/{key}/{id?}/{status}', 'Settings\LocationSettings@location_timings_status');
    Route::get('/location_whitelist_timings/{key}/{id?}/', 'Settings\LocationSettings@generate_location_whitelist_timings');
    Route::get('/location_whitelist_timings_status/{key}/{id?}/{status}', 'Settings\LocationSettings@location_whitelist_timings_status');
    Route::get('/whitelist_settings/{key}/{id?}/', 'Settings\WhiteListSettings@generate_whitelist_settings');
    Route::get('/whitelist_settings_status/{key}/{id?}/{status}', 'Settings\WhiteListSettings@whitelist_settings_status');
    Route::get('/userlist_settings/{key}/{id?}/', 'Settings\UserListSettings@generate_userlist_settings');
    Route::get('/userlist_settings_status/{key}/{id?}/{status}', 'Settings\UserListSettings@userlist_settings_status');
    Route::get('/device_settings/{key}/{id?}/', 'Settings\DeviceSettings@generate_device_settings');
    Route::get('/device_settings_status/{key}/{id?}/{status}', 'Settings\DeviceSettings@device_settings_status');
    Route::get('/other_settings_status/{key}/{id?}/{status}', 'Settings\DeviceSettings@other_settings_status');
    Route::get('/verify_access/{key}/{id}/{type}/{val}/{ticket_type?}', 'Settings\VerifyBookings@verify_booking');
    Route::get('/verify_access_status/{key}/{id}/{booking_id}/{status}', 'Settings\VerifyBookings@verify_booking_status');
    Route::get('/health_check/{key}/{id}/', 'Settings\Settings@health_check');
    Route::get('/check_status/{key}/{id}/{booking_id}/{ticket_type?}', 'Settings\VerifyBookings@check_status');
    Route::get('/bar_code/{key}/{id}/{barcode}/', 'Settings\BarcodeSettings@is_valid_barcode');
    Route::get('/get_products/{key}/{id}/', 'Settings\PaymentTerminalControllers@get_products');
    Route::get('/door_close/{key}/{id}/', 'Settings\Settings@door_close');
    Route::post('/door_close/{key}/{id}/', 'Settings\Settings@door_close');

    Route::get('/device_alerts/{key}/{device_id}/{message}/{error_id}/{status}', 'Settings\DeviceAlertsController@generate_device_alerts_settings');
    Route::post('/device_alerts/{key}/{device_id}/{message}/{error_id}/{status}', 'Settings\DeviceAlertsController@generate_device_alerts_settings');

    Route::get('/remove_prending_transaction/{key}/{id}/{vehicle}', 'Settings\Settings@remove_prending_transaction');
    Route::post('/remove_prending_transaction/{key}/{id}/{vehicle}', 'Settings\Settings@remove_prending_transaction');

    Route::post('/release_plate_reader/{key}/{id}', 'Settings\Settings@release_plate_reader');
    Route::get('/release_plate_reader/{key}/{id}', 'Settings\Settings@release_plate_reader');

    Route::get('/get_related_plate_reader_state/{key}/{id}/', 'Settings\Settings@get_related_plate_reader_state');
    Route::post('/get_related_plate_reader_state/{key}/{id}/', 'Settings\Settings@get_related_plate_reader_state');


    Route::post('/verify_plate_num/{key}/{id}/{vehicle}/{confidence}/{country_code?}', 'PlateReaderController\VerifyVehicle@verify_plate_num');
    Route::get('/verify_plate_num/{key}/{id}/{vehicle}/{confidence}/{country_code?}', 'PlateReaderController\VerifyVehicle@verify_plate_num');
    Route::post('/verify_plate_num_base64/{key}/{id}/{vehicle}/{confidence}', 'PlateReaderController\VerifyVehicle@verify_plate_num_image');
    Route::get('/verify_plate_num_status/{key}/{id}/{vehicle}/{status}/{booking?}', 'PlateReaderController\VerifyVehicle@verify_plate_num_status');
    Route::post('/verify_plate_num_status/{key}/{id}/{vehicle}/{status}/{booking?}', 'PlateReaderController\VerifyVehicle@verify_plate_num_status');

    Route::get('/open_gate_status/{key}/{id}/{vehicle}/', 'PlateReaderController\OpenGateController@open_gate_status');
    Route::post('/open_gate_status/{key}/{id}/{vehicle}/', 'PlateReaderController\OpenGateController@open_gate_status');

    Route::get('/verify_low_confidence_vehicle/{key}/{id}/{vehicle}/{confidence}/{country_code?}', 'PlateReaderController\OpenGateController@verify_low_confidence_vehicle');
    Route::post('/verify_low_confidence_vehicle/{key}/{id}/{vehicle}/{confidence}/{country_code?}', 'PlateReaderController\OpenGateController@verify_low_confidence_vehicle');

    Route::get('/sendCancelManualAccessControl/{key}/{id}/{device_booking}', 'PlateReaderController\OpenGateController@send_cancel_manual_access_control');
    Route::post('/sendCancelManualAccessControl/{key}/{id}/{device_booking}', 'PlateReaderController\OpenGateController@send_cancel_manual_access_control');


    Route::get('/get_vehicle_ticket_price/{key}/{id}/{vehicle}/{type_id}/{language_id?}', 'PlateReaderController\PaymentTerminal@get_vehicle_ticket_price');
    Route::get('/get_vehicle_ticket_price_status/{key}/{id}/{booking}/{price}/{status}/{transaction_reference}/{language_id?}', 'PlateReaderController\PaymentTerminal@get_vehicle_ticket_price_status');
    Route::get('/get_vehicle_ticket_price_status/{key}/{id}/{booking}/{price}/{status}/{payment_type}/{transaction_reference}/{language_id?}', 'PlateReaderController\PaymentTerminal@get_vehicle_ticket_price_payment_status');
    Route::post('/get_vehicle_ticket_price/{key}/{id}/{vehicle}/{type_id}/{language_id?}', 'PlateReaderController\PaymentTerminal@get_vehicle_ticket_price');
    Route::get('/get_vehicle_ticket_price_status/{key}/{id}/{booking}/{price}/{status}/{transaction_reference}/{transaction_id?}/{language_id?}', 'PlateReaderController\PaymentTerminal@get_vehicle_ticket_price_status');
    Route::post('/get_vehicle_ticket_price_status/{key}/{id}/{booking}/{price}/{status}/{transaction_reference}/{transaction_id?}/{language_id?}', 'PlateReaderController\PaymentTerminal@get_vehicle_ticket_price_status');
    Route::get('/get_vehicle_ticket_price_status/{key}/{id}/{booking}/{price}/{status}/{payment_type}/{transaction_reference}/{transaction_id?}/{language_id?}/{promo_card?}/{discount?}/{amount?}', 'PlateReaderController\PaymentTerminal@get_vehicle_ticket_price_payment_status');
    Route::post('/get_vehicle_ticket_price_status/{key}/{id}/{booking}/{price}/{status}/{payment_type}/{transaction_reference}/{transaction_id?}/{language_id?}/{promo_card?}/{discount?}/{amount?}', 'PlateReaderController\PaymentTerminal@get_vehicle_ticket_price_payment_status');

    Route::get('/search_plate/{key}/{id}/{vehicle}/{language_id?}', 'PlateReaderController\PaymentTerminal@search_on_plate');
    Route::post('/search_plate/{key}/{id}/{vehicle}/{language_id?}', 'PlateReaderController\PaymentTerminal@search_on_plate');
    Route::get('/validate_value_card/{key}/{id}/{barcode}/{price}/{vehicle_number}', 'PlateReaderController\PaymentTerminal@validate_value_card');
    Route::post('/validate_value_card/{key}/{id}/{barcode}/{price}/{vehicle_number}', 'PlateReaderController\PaymentTerminal@validate_value_card');

    Route::get('/get_person_ticket_price/{key}/{id}/', 'PlateReaderController\PaymentTerminal@get_person_ticket_price');
    Route::get('/get_person_ticket_price_transaction/{key}/{id}/{quantity}', 'PlateReaderController\PaymentTerminal@get_person_ticket_price_transaction');
    Route::post('/get_person_ticket_price_transaction/{key}/{id}/{quantity}', 'PlateReaderController\PaymentTerminal@get_person_ticket_price_transaction');
    Route::get('/get_person_ticket_price_status/{key}/{id}/{quantity}/{status}/{transaction_reference}/{transaction_id?}/{$language_id?}', 'PlateReaderController\PaymentTerminal@get_person_ticket_price_status');
    Route::post('/get_person_ticket_price/{key}/{id}/', 'PlateReaderController\PaymentTerminal@get_person_ticket_price');
    Route::get('/bar_code/{key}/{id}/{barcode}/{vehicle_number}', 'Settings\BarcodeSettings@validate_barcode_exit');
    Route::post('/bar_code/{key}/{id}/{barcode}/{vehicle_number}', 'Settings\BarcodeSettings@validate_barcode_exit');
    Route::post('/get_person_ticket_price_status/{key}/{id}/{quantity}/{status}/{transaction_reference}/{transaction_id?}', 'PlateReaderController\PaymentTerminal@get_person_ticket_price_status');
    Route::get('/verify_low_confidence_vehicle/{key}/{id}/{vehicle}/{confidence}/{country_code?}', 'PlateReaderController\OpenGateController@verify_low_confidence_vehicle');
    Route::post('/verify_low_confidence_vehicle/{key}/{id}/{vehicle}/{confidence}/{country_code?}', 'PlateReaderController\OpenGateController@verify_low_confidence_vehicle');

    Route::get('/sendCancelManualAccessControl/{key}/{id}/{device_booking}', 'PlateReaderController\OpenGateController@send_cancel_manual_access_control');
    Route::post('/sendCancelManualAccessControl/{key}/{id}/{device_booking}', 'PlateReaderController\OpenGateController@send_cancel_manual_access_control');
//    Route::get('/delayed_bookings/{key}/{id}/{vehicle}/{url_encoded_date}/{confidence?}', 'PlateReaderController\DelayedBookingController@delayed_bookings');
//    Route::post('/delayed_bookings/{key}/{id}/{vehicle}/{url_encoded_date}/{confidence?}', 'PlateReaderController\DelayedBookingController@delayed_bookings');
});
Route::get('get/operator/bookings', 'PlateReaderController\OpenGateController@get_operator_bookings');
Route::post('access/allow', 'PlateReaderController\OpenGateController@vehicle_access_allow');
Route::post('access/denied', 'PlateReaderController\OpenGateController@vehicle_access_denied');
Route::post('update/device/vehicle', 'PlateReaderController\OpenGateController@update_device_vehicle');
Route::get('connection-test/{status}', function (Request $request, $status) {
    \Illuminate\Support\Facades\Artisan::call('command:OdSendMessage', [
        'device' => '2', 'message' => $status
    ]);
});
Route::get('import_call', function (Request $request) {
//    $push_live_changes = new App\Http\Controllers\PushChangesLive\PushWhitelistController();
//    $data = $push_live_changes->get_whitelist_data();
//    echo '<pre>';
//    print_r($data);
//    echo '</pre>';
//    exit;
//    \Illuminate\Support\Facades\Artisan::call('command:PushLiveChanges');
//    \Illuminate\Support\Facades\Artisan::call('command:CheckLiveChanges');
});
Route::get('connection-test', function () {

    $location = new App\Http\Controllers\Settings\LocationSettings();
    echo'<pre>';
    print_R(strtotime($location->get_location()->created_at));
    echo'</pre>';

//    $location = new \App\Http\Controllers\Settings\Settings();
//    echo'<pre>';
//    print_R(json_encode($location->get_endpoints()));
//    echo'</pre>';
//    $location = new App\Http\Controllers\Settings\Settings();
//    echo'<pre>';
//    print_R($location->send_message_od(8,'welcome'));
//    echo'</pre>';
//    $attendants = array();
//    $existing_checked_in_bookings = \App\Bookings::whereHas('attendant_transactions', function ($query) {
//                        $query->whereNull('check_out');
//                    })
//                    ->where('vehicle_num', 'PL221D')->get();
//    foreach ($existing_checked_in_bookings as $booking_close) {
//        $attendants[] = $booking_close->attendants->id;
//    }
//    $device_bookings = \App\DeviceBookings::whereIn('device_id', [5, 25])
//                    ->whereDate('created_at', \Carbon\Carbon::today())
//                    ->orderBy('created_at', 'desc')->get();
//
//    foreach ($device_bookings as $device_booking) {
//        $booking = \App\Bookings::whereHas('attendant_transactions', function ($query) {
//                    $query->whereDate('check_in', \Carbon\Carbon::today());
//                })
//                ->where('vehicle_num', $device_booking->vehicle_num)
//                ->first();
//        if ($booking) {
//            //print_r('already exist first'.$booking->id);
//            continue;
//        }
//        $booking = \App\Bookings::where('vehicle_num', $device_booking->vehicle_num)
//                ->where('checkin_time', '<=', date('Y-m-d H:i:s'))
//                ->where('checkout_time', '>', date('Y-m-d H:i:s'))
//                ->first();
//        if (!$booking) {
//            print_r('not exist');
//            $dataArray = array(
//                'first_name' => 'Paid Vehicle',
//                'vehicle_num' => $device_booking->vehicle_num,
//                'type' => 4,
//                'is_paid' => 0,
//                'checkin_time' => date('Y-m-d H:i:s', strtotime($device_booking->created_at)),
//                'amount' => 0,
//                'payment_id' => 'Paid Vehicle'
//            );
//
//            $booking = new \App\Bookings();
//            $booking->type = $dataArray['type'];
//            $booking->first_name = $dataArray['first_name'];
//            $booking->vehicle_num = $dataArray['vehicle_num'];
//            $booking->checkin_time = $dataArray['checkin_time'];
//            $booking->confidence = $device_booking->confidence;
//            $booking->country_code = $device_booking->country_code;
//            $booking->image_path = $device_booking->file_path;
//            $booking->save();
//            $bookingId = $booking->id;
//            print $bookingId;
//            $booking_payment = new \App\BookingPayments();
//            $booking_payment->booking_id = $bookingId;
//            $booking_payment->amount = $dataArray['amount'];
//            $booking_payment->payment_id = $dataArray['payment_id'];
//            $booking_payment->checkin_time = $dataArray['checkin_time'];
//            $booking_payment->save();
//            try {
//                $Key = 'MTk3Nl8yODI=';
//                $http = new \GuzzleHttp\Client();
//                $response = $http->post(env('API_BASE_URL').'/api/store-booking-info', [
//                    'form_params' => [
//                        'token' => $Key,
//                        'data' => $dataArray
//                    ],
//                ]);
//                $responseData = json_decode((string) $response->getBody(), true);
//                if (is_array($responseData) && array_key_exists('booking_info_live_id', $responseData['data'])) {
//                    $booking->live_id = $responseData['data']['booking_info_live_id'];
//                    $booking->save();
//                }
//                if (is_array($responseData) && array_key_exists('booking_payment_live_id', $responseData['data'])) {
//                    $booking_payment->live_id = $responseData['data']['booking_payment_live_id'];
//                    $booking_payment->save();
//                }
//            } catch (\Exception $ex) {
//                print 'error at server';
//                $error_log = new \App\Http\Controllers\LogController();
//                $error_log->log_create('get_vehicle_booking', $ex->getMessage(), $ex->getTraceAsString());
//            }
//        } else {
//            print_r('already exist last' . $booking->id);
//            $bookingId = $booking->id;
//            $booking->confidence = $device_booking->confidence;
//            $booking->country_code = $device_booking->country_code;
//            $booking->image_path = $device_booking->file_path;
//            $booking->save();
//        }
//        $attendant = \App\Attendants::where('booking_id', $booking->id)->first();
//        if (!$attendant) {
//            $attendant = new \App\Attendants();
//        }
//        $attendant->booking_id = $booking->id;
//        $attendant->save();
//        $attendant_id = $attendant->id;
//        $attendant_transaction = new \App\AttendantTransactions();
//        $attendant_transaction->attendant_id = $attendant_id;
//        $attendant_transaction->check_in = date('Y-m-d H:i:s', strtotime($device_booking->created_at));
//        $attendant_transaction->save();
//        $transaction_images = new \App\TransactionImages();
//        $transaction_images->image_path = $device_booking->file_path;
//        $transaction_images->device_id = $device_booking->device_id;
//        $transaction_images->transaction_id = $attendant_id;
//        $transaction_images->type = 'in';
//        $transaction_images->save();
//        $device_booking->delete();
//    }
    //$transaction = $location->attendant_transactions()->where('check_out');
//    echo'<pre>';
//    print_r($attendants);
//    echo'</pre>';
//    die();
    $location = new App\Http\Controllers\Settings\LocationSettings();
    echo'<pre>';
    print_R(strtotime($location->get_location()->created_at));
//    echo'</pre>';
//    $host = "192.168.0.103";
//    $port = 8085;
//    $client = new \App\Http\Controllers\Connection\Client($host, $port);
//    $client->send();
});
Route::get('import_location_settings', function (Request $request) {
//    Illuminate\Support\Facades\DB::enableQueryLog();
//    $transactions = Illuminate\Support\Facades\DB::table('attendant_transactions')
//            ->join('attendants', 'attendants.id', '=', 'attendant_transactions.attendant_id')
//            ->join('bookings', 'bookings.id', '=', 'attendants.booking_id')
//            ->join('booking_payments', 'bookings.id', '=', 'booking_payments.booking_id')
//            ->select('bookings.first_name as first_name', 'bookings.last_name as last_name', ''
//                    . 'bookings.email as email', 'bookings.phone_number as phone_number', ''
//                    . 'bookings.vehicle_num as vehicle_num', 'bookings.type as type', ''
//                    . 'attendant_transactions.check_in as check_in', 'attendant_transactions.check_out as check_out')
////            ->orderBy('attendant_transactions.updated_at', 'desc')
//            ->paginate(5);
//    $rows = App\TransactionView::orderBy('check_out', 'desc')
//            ->paginate(5);
////    dd(Illuminate\Support\Facades\DB::getQueryLog());
//    echo '<pre>';
//    print_R($rows);
//    echo '</pre>';
//    $booking = \App\Bookings::find(1);
//    $payment_terminal = new App\Http\Controllers\PlateReaderController\PaymentTerminal();
//    echo $payment_terminal->get_at_location_time($booking);
//    $import = new App\Http\Controllers\Settings\ImportLivetSetting();
//    $import->importDetails();
});
