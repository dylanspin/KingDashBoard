<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Dotenv;
use InvalidArgumentException;

class HomeController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        try {

            $DB_USERNAME = env('DB_USERNAME');
            $DB_PASSWORD = env('DB_PASSWORD');
            $DB_DATABASE = env('DB_DATABASE');
            if (!empty($DB_USERNAME) && !empty($DB_DATABASE)) {
                try {
                    $user_existed = \App\User::all();
                    if ($user_existed->count() > 0) {
                        if (\Illuminate\Support\Facades\Auth::check()) {
                            return redirect('/dashboard');
                        } else {
                            return view('auth.login');
                        }
                    }
                } catch (\Exception $ex) {
                    return view('wizard.step1');
                }
                return view('wizard.step1');
            }
            return view('wizard.step0');
        } catch (\Exception $ex) {
            return view('wizard.step0');
        }
    }

    /**
     * 
     * @param Request $request
     */
    public function change_language(Request $request,$locale) {
        \Illuminate\Support\Facades\Session::put('applocale', $locale);
        return redirect()->back();
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function wizard_step_1(Request $request) {
        return view('wizard.step1');
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function wizard_step_2(Request $request) {
        $device_details = array();
        try {
            $devices = \App\LocationDevices::get();
            if ($devices->count() > 0) {
                foreach ($devices as $device) {
                    $details = array();
                    $ip = $device->device_ip;
                    $port = $device->device_port;
                    $name = $device->device_name;
                    $is_synched = $device->is_synched;
                    if ($name == NULL) {
                        continue;
                    }
                    $details['name'] = $name;
                    if ($ip == '' || $port == NULL) {
                        $details['status'] = 1;
                        $device_details[] = $details;
                        continue;
                    }
                    if (!$is_synched) {
                        $details['status'] = 2;
                        $device_details[] = $details;
                        continue;
                    }
                    $details['status'] = 3;
                    $device_details[] = $details;
                }
            }
        } catch (\Exception $ex) {
            
        }
        return view('wizard.step2', [
            'device_details' => $device_details
        ]);

//        if ($devices->count() > 0) {
//            foreach ($devices as $device) {
//                $details = array();
//                $ip = $device->device_ip;
//                $port = $device->device_port;
//                $name = $device->device_name;
//                if ($name == NULL) {
//                    continue;
//                }
//                $details['name'] = $name;
//                if ($ip == '' || $port == NULL) {
//                    $details['status'] = 1;
//                    $device_details[] = $details;
//                }
//                $client = new Connection\Client($ip, $port);
//                $command = 'initialize_device';
//                $location = new Settings\LocationSettings();
//                $location_details = $location->get_location();
//                if (!$location_details) {
//                    $details['status'] = 1;
//                    $device_details[] = $details;
//                    continue;
//                }
//                $key = strtotime($location_details->created_at) . '-' . $device->id;
//                $settings = new Settings\Settings();
//                $endpoints = $settings->get_endpoints();
//                $data = '19:' . $key . ':' . json_encode($endpoints);
//                echo $data;
//                exit;
//                $connection = $client->send($command, $data);
//                if (!is_array($connection)) {
//                    $details['status'] = 2;
//                    $device_details[] = $details;
//                    continue;
//                }
//                if (!array_key_exists('status', $connection)) {
//                    $details['status'] = 2;
//                    $device_details[] = $details;
//                    continue;
//                }
//                if ($connection['status'] == 0 || $connection['status'] == 1 || $connection['status'] == 2) {
//                    $details['status'] = 2;
//                    $device_details[] = $details;
//                    continue;
//                }
//                $details['status'] = 3;
//                $device_details[] = $details;
//            }
//        }
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function wizard_step_3(Request $request) {

        return view('wizard.step3');
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function wizard_step_4(Request $request) {
        $ticket_readers = FALSE;
        if ($request->session()->has('ticket_readers')) {
            $ticket_readers = $request->session()->get('ticket_readers');
        }
        if (!$ticket_readers) {
            return redirect('/wizard-step-5 ');
        }
        return view('wizard.step4', [
            'ticket_readers' => $ticket_readers
        ]);
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function wizard_step_5(Request $request) {
        $person_ticket_readers = FALSE;
        if ($request->session()->has('person_ticket_readers')) {
            $person_ticket_readers = $request->session()->get('person_ticket_readers');
        }
        if (!$person_ticket_readers) {
            return redirect('/wizard-step-6');
        }
        return view('wizard.step5', [
            'person_ticket_readers' => $person_ticket_readers
        ]);
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function wizard_step_6(Request $request) {
        $plate_reader = FALSE;
        if ($request->session()->has('plate_reader')) {
            $plate_reader = $request->session()->get('plate_reader');
        }
        if (!$plate_reader) {
            return redirect('/wizard-step-7');
        }
        return view('wizard.step6', [
            'plate_reader' => $plate_reader
        ]);
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function wizard_step_7(Request $request) {
        $outdoor_display = FALSE;
        if ($request->session()->has('outdoor_display')) {
            $outdoor_display = $request->session()->get('outdoor_display');
        }
        if (!$outdoor_display) {
            return redirect('/wizard-step-8');
        }
        return view('wizard.step7', [
            'outdoor_display' => $outdoor_display
        ]);
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function wizard_step_8(Request $request) {

        return view('wizard.step8');
    }

    /**
     * wizard step0 submit
     * @return type
     */
    public function wizard_step_0_submit(Request $request) {
        $db_host = $request->db_host;
        $db_user = $request->db_user;
        $db_pass = $request->db_pass;
        $db_name = $request->db_name;
        $db_port = $request->db_port;
        $DB_HOST = env('DB_HOST');
        $DB_PORT = env('DB_PORT');
        $DB_USERNAME = env('DB_USERNAME');
        $DB_PASSWORD = env('DB_PASSWORD');
        $DB_DATABASE = env('DB_DATABASE');
        $DB_NAME = env('DB_NAME');
        try {
            if (empty($db_user) || empty($db_name) || empty($db_host) || empty($db_port)) {
//            if (empty($db_user) || empty($db_pass) || empty($db_name) || empty($db_host)|| empty($db_port)) {
                $request->session()->flash('alert-danger', 'Host or User or Database or Port is missing.');
                return redirect('/');
            }
            $path = base_path('.env');
            if (file_exists($path)) {
                file_put_contents($path, str_replace(
                                'DB_HOST' . '=' . $DB_HOST, 'DB_HOST' . '=' . $db_host, file_get_contents($path)
                ));
                file_put_contents($path, str_replace(
                                'DB_PORT' . '=' . $DB_PORT, 'DB_PORT' . '=' . $db_port, file_get_contents($path)
                ));
                file_put_contents($path, str_replace(
                                'DB_USERNAME' . '=' . $DB_USERNAME, 'DB_USERNAME' . '=' . $db_user, file_get_contents($path)
                ));
                file_put_contents($path, str_replace(
                                'DB_PASSWORD' . '=' . $DB_PASSWORD, 'DB_PASSWORD' . '=' . $db_pass, file_get_contents($path)
                ));
            }
            \Illuminate\Support\Facades\Config::set("database.connections.mysql.host", $db_host);
            \Illuminate\Support\Facades\Config::set("database.connections.mysql.username", $db_user);
            \Illuminate\Support\Facades\Config::set("database.connections.mysql.password", $db_pass);
            DB::purge();
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            DB::reconnect();
            try {
                DB::connection()->getPdo();
                try {
                    DB::statement('CREATE DATABASE IF NOT EXISTS ' . $db_name);
                } catch (\Exception $ex) {
                    $request->session()->flash('alert-danger', $ex->getMessage());
                    return redirect('/');
                }
                \Illuminate\Support\Facades\Config::set("database.connections.mysql.database", $db_name);
                file_put_contents($path, str_replace(
                                'DB_DATABASE' . '=' . $DB_DATABASE, 'DB_DATABASE' . '=' . $db_name, file_get_contents($path)
                ));
                DB::purge();
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                DB::reconnect();
                if (DB::connection()->getDatabaseName()) {
                    return redirect('/wizard-step-1');
                } else {
                    $request->session()->flash('alert-danger', "Could not open connection to database server.  Please check your configuration.");
                    return redirect('/');
                }
            } catch (\Exception $e) {
                file_put_contents($path, str_replace(
                                'DB_USERNAME' . '=' . $db_user, 'DB_USERNAME' . '=', file_get_contents($path)
                ));
                file_put_contents($path, str_replace(
                                'DB_PASSWORD' . '=' . $db_pass, 'DB_PASSWORD' . '=', file_get_contents($path)
                ));
                file_put_contents($path, str_replace(
                                'DB_DATABASE' . '=' . $db_name, 'DB_DATABASE' . '=', file_get_contents($path)
                ));
                file_put_contents($path, str_replace(
                                'DB_HOST' . '=' . $db_host, 'DB_HOST' . '=', file_get_contents($path)
                ));
                file_put_contents($path, str_replace(
                                'DB_PORT' . '=' . $db_port, 'DB_PORT' . '=', file_get_contents($path)
                ));
                $request->session()->flash('alert-danger', "Could not open connection to database server.  Please check your configuration.");
                return redirect('/');
            }
            return redirect('/');
        } catch (\Exception $ex) {
            file_put_contents($path, str_replace(
                            'DB_USERNAME' . '=' . $db_user, 'DB_USERNAME' . '=', file_get_contents($path)
            ));
            file_put_contents($path, str_replace(
                            'DB_PASSWORD' . '=' . $db_pass, 'DB_PASSWORD' . '=', file_get_contents($path)
            ));
            file_put_contents($path, str_replace(
                            'DB_DATABASE' . '=' . $db_name, 'DB_DATABASE' . '=', file_get_contents($path)
            ));
            file_put_contents($path, str_replace(
                            'DB_HOST' . '=' . $db_host, 'DB_HOST' . '=', file_get_contents($path)
            ));
            file_put_contents($path, str_replace(
                            'DB_PORT' . '=' . $db_port, 'DB_PORT' . '=', file_get_contents($path)
            ));
            $request->session()->flash('alert-danger', $ex->getMessage());
            return redirect('/');
        }
    }

    /**
     * wizard step1 submit
     * @return type
     */
    public function wizard_step_1_submit(Request $request) {
        try {
            $activation_key = $request->activation_key;
            if (empty($activation_key)) {
                $request->session()->flash('alert-danger', 'Activation key is required');
                return redirect('/wizard-step-1');
            }
            Artisan::call('migrate', array('--force' => true));
            Artisan::call('db:seed', array('--force' => true));
            $import_settings = new Settings\ImportLivetSetting($activation_key);
            $import_settings_call = $import_settings->importDetails();
            if (!$import_settings_call) {
                $request->session()->flash('alert-danger', 'Activation key is invalid');
                return redirect('/wizard-step-1');
            }
            return redirect('/wizard-step-2');
        } catch (\Exception $ex) {
            $request->session()->flash('alert-danger', $ex->getMessage());
            return redirect('/wizard-step-1');
        }
    }

    function wizard_step_3_submit(Request $request) {
        $ticket_readers = $request->ticket_readers;
        $person_ticket_readers = $request->person_ticket_readers;
        $plate_reader = $request->plate_reader;
        $outdoor_display = $request->outdoor_display;
        if (!empty($ticket_readers)) {
            $request->session()->put('ticket_readers', $ticket_readers);
        }
        if (!empty($person_ticket_readers)) {
            $request->session()->put('person_ticket_readers', $person_ticket_readers);
        }
        if (!empty($plate_reader)) {
            $request->session()->put('plate_reader', $plate_reader);
        }
        if (!empty($outdoor_display)) {
            $request->session()->put('outdoor_display', $outdoor_display);
        }
        return redirect('/wizard-step-4');
    }

    function wizard_step_4_submit(Request $request) {
        try {
            $device_name = $request->device_name;
            $device_direction = $request->device_direction;
            $ip = $request->ip;
            $port = $request->port;
            $import_settings_hidden = $request->import_settings_hidden;
            $added_devices = array();
            foreach ($device_name as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                $devices = new \App\LocationDevices();
                $devices->device_name = $value;
                $devices->available_device_id = 1;
                if (!empty($device_direction[$key])) {
                    $devices->device_direction = $device_direction[$key];
                }
                if (!empty($ip[$key])) {
                    $devices->device_ip = $ip[$key];
                }
                if (!empty($port[$key])) {
                    $devices->device_port = $port[$key];
                }
                $devices->is_synched = 2;
                $devices->save();
                $device_settings = \App\DeviceSettings::where('device_id', $devices->id)->first();
                if (!$device_settings) {
                    $device_settings = new \App\DeviceSettings();
                }
                $device_settings->device_id = $devices->id;
                $device_settings->save();
                if ($import_settings_hidden[$key]) {
                    $added_devices[] = $devices->id;
                }
            }
            if (count($added_devices) > 0) {
                foreach ($added_devices as $device) {
                    $settings = new Settings\Settings();
                    $settings->run_socket_connection_command($device, 'all');
                }
            }
            return redirect('/wizard-step-5');
        } catch (Exception $ex) {
            $request->session()->flash('alert-danger', 'Something went wrong please try again.');
            return redirect('/wizard-step-4');
        }
    }

    function wizard_step_5_submit(Request $request) {
        try {
            $device_name = $request->device_name;
            $device_direction = $request->device_direction;
            $ip = $request->ip;
            $port = $request->port;
            $import_settings_hidden = $request->import_settings_hidden;
            $added_devices = array();
            foreach ($device_name as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                $devices = new \App\LocationDevices();
                $devices->device_name = $value;
                $devices->available_device_id = 2;
                if (!empty($device_direction[$key])) {
                    $devices->device_direction = $device_direction[$key];
                }
                if (!empty($ip[$key])) {
                    $devices->device_ip = $ip[$key];
                }
                if (!empty($port[$key])) {
                    $devices->device_port = $port[$key];
                }
                $devices->is_synched = 2;
                $devices->save();
                $device_settings = \App\DeviceSettings::where('device_id', $devices->id)->first();
                if (!$device_settings) {
                    $device_settings = new \App\DeviceSettings();
                }
                $device_settings->device_id = $devices->id;
                $device_settings->save();
                if ($import_settings_hidden[$key]) {
                    $added_devices[] = $devices->id;
                }
            }
            if (count($added_devices) > 0) {
                foreach ($added_devices as $device) {
                    $settings = new Settings\Settings();
                    $settings->run_socket_connection_command($device, 'all');
                }
            }
            return redirect('/wizard-step-6');
        } catch (Exception $ex) {
            $request->session()->flash('alert-danger', 'Something went wrong please try again.');
            return redirect('/wizard-step-5');
        }
    }

    function wizard_step_6_submit(Request $request) {
        try {
            $device_name = $request->device_name;
            $device_direction = $request->device_direction;
            $ip = $request->ip;
            $port = $request->port;
            $import_settings_hidden = $request->import_settings_hidden;
            $added_devices = array();
            foreach ($device_name as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                $devices = new \App\LocationDevices();
                $devices->device_name = $value;
                $devices->available_device_id = 3;
                if (!empty($device_direction[$key])) {
                    $devices->device_direction = $device_direction[$key];
                }
                if (!empty($ip[$key])) {
                    $devices->device_ip = $ip[$key];
                }
                if (!empty($port[$key])) {
                    $devices->device_port = $port[$key];
                }
                $devices->is_synched = 2;
                $devices->save();
                $device_settings = \App\DeviceSettings::where('device_id', $devices->id)->first();
                if (!$device_settings) {
                    $device_settings = new \App\DeviceSettings();
                }
                $device_settings->device_id = $devices->id;
                $device_settings->save();
                if ($import_settings_hidden[$key]) {
                    $added_devices[] = $devices->id;
                }
            }
            if (count($added_devices) > 0) {
                foreach ($added_devices as $device) {
                    $settings = new Settings\Settings();
                    $settings->run_socket_connection_command($device, 'all');
                }
            }
            return redirect('/wizard-step-7');
        } catch (Exception $ex) {
            $request->session()->flash('alert-danger', 'Something went wrong please try again.');
            return redirect('/wizard-step-6');
        }
    }

    function wizard_step_7_submit(Request $request) {
        try {
            $device_name = $request->device_name;
            $ip = $request->ip;
            $port = $request->port;
            $import_settings_hidden = $request->import_settings_hidden;
            $added_devices = array();
            foreach ($device_name as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                $devices = new \App\LocationDevices();
                $devices->device_name = $value;
                $devices->available_device_id = 4;
                if (!empty($ip[$key])) {
                    $devices->device_ip = $ip[$key];
                }
                if (!empty($port[$key])) {
                    $devices->device_port = $port[$key];
                }
                $devices->is_synched = 0;
                $devices->save();
                $device_settings = \App\DeviceSettings::where('device_id', $devices->id)->first();
                if (!$device_settings) {
                    $device_settings = new \App\DeviceSettings();
                }
                $device_settings->device_id = $devices->id;
                $device_settings->save();
                if ($import_settings_hidden[$key]) {
                    $added_devices[] = $devices->id;
                }
            }
            if (count($added_devices) > 0) {
                foreach ($added_devices as $device) {
                    $settings = new Settings\Settings();
//                    $settings->run_socket_connection_command($device, 'all');
                }
            }
            return redirect('/wizard-step-8');
        } catch (Exception $ex) {
            $request->session()->flash('alert-danger', 'Something went wrong please try again.');
            return redirect('/wizard-step-7');
        }
    }

    function wizard_step_8_submit(Request $request) {
        return redirect('/');
    }

    function dashboard(Request $request) {
        return view('dashboard.dashboard');
    }

    function page_404(Request $request) {
        return view('errors.404');
    }

    function page_500(Request $request) {
        return view('errors.500');
    }

    function test_form(Request $request) {
        return view('test_form');
    }

    function set_nav_scroll_session(Request $request, $type) {
        if (!$type) {
            if (\Illuminate\Support\Facades\Session::has('nav-scroll-session')) {
                \Illuminate\Support\Facades\Session::forget('nav-scroll-session');
            }
        } else {
            \Illuminate\Support\Facades\Session::put('nav-scroll-session', $type);
        }
    }
    
    /**
     * 
     * @param Request $request
     */
    public function connection_test(Request $request) {
        //    $location = new \App\Http\Controllers\Settings\Settings();
//    echo'<pre>';
//    print_R(json_encode($location->get_endpoints()));
//    echo'</pre>';
//    $location = new App\Http\Controllers\Settings\Settings();
//    echo'<pre>';
//    print_R($location->send_message_od(8,'welcome'));
//    echo'</pre>';
    $location = new App\Http\Controllers\Settings\LocationSettings();
    echo'<pre>';
    print_R(strtotime($location->get_location()->created_at));
    echo'</pre>';
//    $host = "192.168.0.103";
//    $port = 8085;
//    $client = new \App\Http\Controllers\Connection\Client($host, $port);
//    $client->send();

    }

}
