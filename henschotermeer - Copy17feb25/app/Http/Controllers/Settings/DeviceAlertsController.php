<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeviceAlertsController extends Controller {

    public function generate_device_alerts_settings(Request $request, $key, $device_id, $message, $error_id, $status) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $device_id);
            if (!$valid_settings) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Access ',
                    'data' => FALSE,
                );
            }
            $device_alerts = new \App\DeviceAlerts();
            if ($device_id) {
                $device_alerts->location_device_id = $device_id;
            }
            if ($error_id) {
                $device_alerts->device_error_id = $error_id;
            }
            if ($message != '') {
                $device_alerts->message = $message;
            }
            if ($status != '') {
                $device_alerts->status = $status;
            }
            $device_alerts->save();

            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $device_alerts,
            );
        } catch (Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

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
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
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
