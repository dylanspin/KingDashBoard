<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\LocationOptions;
use App\LocationDevices;
use App\AvailableDevices;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use File;
use Image;

class DevicesController extends Controller {

    public $controller = 'App\Http\Controllers\DevicesController';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
        $devices = LocationDevices::with('available_devices')->get();
        return view('devices.index', compact('devices'));
    }

    /**
     * Device Ordering
     * @return type
     */
    public function sorting_devices() {
        //
        $vehicleDevices = LocationDevices::with('available_devices')
                ->whereIn('available_device_id', [1, 3, 6])
                ->orderBy('vehicle_device_sorting', 'asc')
                ->get();
        $personDevices = LocationDevices::with('available_devices')
                ->whereIn('available_device_id', [2, 6])
                ->orderBy('person_device_sorting', 'asc')
                ->get();
        return view('devices.order', compact('vehicleDevices', 'personDevices'));
    }

    /**
     * Update Device Ordering
     * @return type
     */
    public function update_devices_ordering(Request $request, $type) {
        $validator = Validator::make($request->all(), [
        ]);
        if ($validator->passes()) {
            try {
                $data = $request->all();
                $items = explode(",", $data['itemOrder']);
                foreach ($items as $index => $item) {
                    $device = LocationDevices::find($item);
                    if ($device) {
                        $orderNumber = $index + 1;
                        if ($type == 'vehicle') {
                            $device->vehicle_device_sorting = $orderNumber;
                        } else {
                            $device->person_device_sorting = $orderNumber;
                        }
                        $device->save();
                    }
                }
                return response()->json([
                            'success' => array(
                                'updated' => 'Devices has been Sorted Successfully.'
                            )
                ]);
            } catch (\Exception $ex) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('update_devices_ordering', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);

                return response()->json(['error' => $validator->errors()->all()]);
            }
        } else {
            return response()->json(['error' => $validator->errors()->all()]);
        }
    }

    /**
     * Add new Device
     * @return type
     */
    public function create() {
        //
        $deviceTypes = AvailableDevices::all();
        $devices_ticket_reader_type = LocationDevices::where('available_device_id', 1)
                ->orWhere('available_device_id', 2)
                ->get();
        $devices_od_type = LocationDevices::where('available_device_id', 4)->get();
        $devices_not_od_type = LocationDevices::where('available_device_id', '<>', 4)->get();
        return view('devices.create', compact('devices_ticket_reader_type', 'deviceTypes', 'devices_od_type', 'devices_not_od_type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
        $this->validate($request, [
            'device_name' => 'required',
            'device_type' => 'required',
            'device_ip' => 'required',
            'device_port' => 'required',
        ]);
        if ($request->device_type == 1 || $request->device_type == 2) {
            $this->validate($request, [
                'device_direction' => 'required',
                'anti_passback' => 'required',
                'enable_log' => 'required',
                'enable_idle_screen' => 'required',
                'focus_away' => 'required',
                'opacity_input' => 'required',
                'od_enabled' => 'required',
                'has_gate' => 'required',
            ]);
            if ($request->anti_passback == 1) {
                $this->validate($request, [
                    'time_passback' => 'required'
                ]);
            }
            if ($request->has_gate == 1) {
                $this->validate($request, [
                    'barrier_close_time' => 'required'
                ]);
            }
        }
        if ($request->device_type == 3) {
            $this->validate($request, [
                'device_direction' => 'required',
                'confidence_level' => 'required',
                'num_tries' => 'required',
            ]);
        }
        if ($request->device_type == 6) {
            $this->validate($request, [
                'enable_log' => 'required',
                'ccv_pos_ip' => 'required',
                'ccv_pos_port' => 'required',
            ]);
        }
        if ($request->device_type == 4) {
            $this->validate($request, [
                'enable_idle_screen' => 'required',
                'message_text_size' => 'required',
                'time_text_size' => 'required',
                'date_text_size' => 'required',
                'bottom_tray_text_size' => 'required'
            ]);
        }
        $device_name = $request->device_name;

        $devices_count = LocationDevices::where([
                            ['device_name', $device_name]
                        ])
                        ->get()->count();
        if ($devices_count > 0) {
            Session::flash('heading', 'Error!');
            Session::flash('message', 'Device with same name already exists.');
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
        $location_device = new LocationDevices();
        try {
            $data = $request->all();
            $locationOption = LocationOptions::find(1);
            $locationId = $locationOption->live_id;
            $responseData = array();
            $responseData['success'] = FALSE;
            try {
                $http = new Client();
                $response = $http->post(env('API_BASE_URL') . '/api/store-single-device-data', [
                    'form_params' => [
                        'location_id' => $locationId,
                        'data' => (array) $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
                if ($responseData['success'] && isset($responseData['data']['device_live_id'])) {
                    $location_device->live_id = $responseData['data']['device_live_id'];
                }
            } catch (\Exception $ex) {
                
            }
            $location_device->device_name = $data['device_name'];
            $location_device->available_device_id = $data['device_type'];
            $location_device->device_ip = $data['device_ip'];
            $location_device->device_port = $data['device_port'];

            if ($data['device_type'] == 1 || $data['device_type'] == 2) {
                $location_device->enable_log = $data['enable_log'];
                $location_device->enable_idle_screen = $data['enable_idle_screen'];
                $location_device->qr_code_type = $data['qr_code_type'];
                $location_device->focus_away = $data['focus_away'];
                $location_device->opacity_input = $data['opacity_input'];
                $location_device->device_direction = $data['device_direction'];
                $location_device->anti_passback = $data['anti_passback'];
                if ($data['anti_passback'] == 1) {
                    $location_device->time_passback = $data['time_passback'];
                } else {
                    $location_device->time_passback = NULL;
                }
                $location_device->od_enabled = $data['od_enabled'];
                if ($data['has_gate'] == 1) {
                    $location_device->has_gate = $data['has_gate'];
                    $location_device->barrier_close_time = $data['barrier_close_time'];
                }
                $location_device->gate_close_transaction_enabled = $data['gate_close_transaction_enabled'];
                if ($data['device_type'] == 1) {
                    $location_device->has_sdl = $data['has_sdl'];
                    $location_device->has_pdl = $data['has_pdl'];
                    $location_device->plate_correction_enabled = $data['plate_correction_enabled'];
                }
            }
            if ($data['device_type'] == 3) {
                $location_device->camera_enabled = 1;
                $location_device->enable_log = $data['enable_log'];
                $location_device->device_direction = $data['device_direction'];
                $location_device->confidence = $data['confidence_level'];
                $location_device->retries = $data['num_tries'];
                if (array_key_exists('confidence_level_lowest', $data)) {
                    $location_device->confidence_level_lowest = $data['confidence_level_lowest'];
                }
                if (array_key_exists('character_match_limit', $data)) {
                    $location_device->character_match_limit = $data['character_match_limit'];
                }
                $location_device->has_sdl = $data['has_sdl'];
                $location_device->gate_close_transaction_enabled = $data['gate_close_transaction_enabled'];
                $location_device->has_gate = $data['has_gate'];
            }
            if ($data['device_type'] == 4) {
                $location_device->device_direction = 'out';
                $location_device->enable_idle_screen = $data['enable_idle_screen'];
                $location_device->message_text_size = $data['message_text_size'];
                $location_device->time_text_size = $data['time_text_size'];
                $location_device->bottom_tray_text_size = $data['bottom_tray_text_size'];
                $location_device->date_text_size = $data['date_text_size'];
            }
            if ($data['device_type'] == 6) {
                $location_device->enable_log = $data['enable_log'];
                $location_device->ccv_pos_port = $data['ccv_pos_port'];
                $location_device->ccv_pos_ip = $data['ccv_pos_ip'];
                if (array_key_exists('has_enable_person_ticket', $data)) {
                    $location_device->has_enable_person_ticket = 1;
                } else {
                    $location_device->has_enable_person_ticket = 0;
            }
                if (array_key_exists('has_enable_parking_ticket', $data)) {
                    $location_device->has_enable_parking_ticket = 1;
                } else {
                    $location_device->has_enable_parking_ticket = 0;
                }
            }
            $location_device->popup_time = $data['popup_time'];
            $location_device->has_always_access = $data['has_always_access'];
            if (in_array($data['device_type'], array(1, 2, 4, 6))) {
                if ($file = $request->file('advert_image_file')) {
                    $destinationPath = public_path('/uploads/devices');
                    $safeName = time() . '.' . $file->getClientOriginalExtension();
                    $img = Image::make($file->getPathName());
                    $img->resize(480, 320, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->save($destinationPath . '/' . $safeName);
                    //save new file path into db
                    $location_device->advert_image_path = '/uploads/devices/' . $safeName;
                }
            }
            if (in_array($data['device_type'], array(1, 2))) {
                if ($file = $request->file('idle_screen_image')) {
                    $destinationPat = public_path('/uploads/devices');
                    $safeNamee = 'idle' . time() . '.' . $file->getClientOriginalExtension();
                    $imgg = Image::make($file->getPathName());
                    $imgg->resize(480, 320, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $imgg->save($destinationPat . '/' . $safeNamee);
                    //delete old pic if exists
                    if (File::exists(public_path($location_device->idle_screen_image))) {
                        File::delete(public_path($location_device->idle_screen_image));
                    }
                    //save new file path into db
                    $location_device->idle_screen_image = '/uploads/devices/' . $safeNamee;
                }
            }
            $location_device->save();
            if ($data['device_type'] == 1 || $data['device_type'] == 2) {
                if (array_key_exists('related_od', $data) && !empty($data['related_od'])) {
                    $related_ods = $data['related_od'];
                    if (count($related_ods) > 0) {
                        foreach ($related_ods as $related_od) {
                            $device_od = new \App\DeviceOds();
                            $device_od->device_id = $location_device->id;
                            $device_od->od_id = $related_od;
                            $device_od->save();
                        }
                    }
                }
            } elseif ($data['device_type'] == 3) {
                if (!empty($data['related_ticket_readers'])) {
                    $device_ticket_reader = new \App\DeviceTicketReaders();
                    $device_ticket_reader->device_id = $location_device->id;
                    $device_ticket_reader->ticket_reader_id = $data['related_ticket_readers'];
                    $device_ticket_reader->save();
                }
            } elseif ($data['device_type'] == 4) {
                if (array_key_exists('related_device', $data) && !empty($data['related_device'])) {
                    $releated_devices = $data['related_device'];
                    if (count($releated_devices) > 0) {
                        foreach ($releated_devices as $releated_device) {
                            $device_od = new \App\DeviceOds();
                            $device_od->device_id = $releated_device;
                            $device_od->od_id = $location_device->id;
                            $device_od->save();
                        }
                    }
                }
            }
            $device_settings = \App\DeviceSettings::where('device_id', $location_device->id)->first();
            if (!$device_settings) {
                $device_settings = new \App\DeviceSettings();
            }
            $device_settings->device_id = $location_device->id;
            $device_settings->save();
            if ($responseData['success']) {
                $settings = new Settings\Settings();
                $settings->run_socket_connection_command($location_device->id, 'all');
                Session::flash('heading', 'Success!');
                Session::flash('message', __('devices.device_add'));
                Session::flash('icon', 'success');
                return redirect('/devices');
            } else {
                $settings = new Settings\Settings();
                $settings->run_socket_connection_command($location_device->id, 'all');
                Session::flash('heading', 'Warning!');
                Session::flash('message', __('devices.device_add_localy'));
                Session::flash('icon', 'warning');
                return redirect('/devices');
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
        $deviceTypes = AvailableDevices::all();
        $locationDevices = LocationDevices::find($id);
        $devices_ticket_reader_type = LocationDevices::where('available_device_id', 1)
                ->orWhere('available_device_id', 2)
                ->get();
        $devices_od_type = LocationDevices::where('available_device_id', 4)->get();
        $devices_not_od_type = LocationDevices::where('available_device_id', '<>', 4)->get();
        $device_ods = array();
        $od_devices = array();
        $device_ods_db = \App\DeviceOds::where('device_id', $id)
                ->orWhere('od_id', $id)
                ->get();
        if ($device_ods_db->count() > 0) {
            foreach ($device_ods_db as $device) {
                $od_devices[] = $device->device_id;
                $device_ods[] = $device->od_id;
            }
        }
        $device_ticket_readers = array();
        $device_ticket_readers_db = \App\DeviceTicketReaders::where('device_id', $id)
                ->get();
        if ($device_ticket_readers_db->count() > 0) {
            foreach ($device_ticket_readers_db as $device) {
                $device_ticket_readers[] = $device->ticket_reader_id;
            }
        }
        return view('devices.edit', compact('device_ticket_readers', 'devices_ticket_reader_type', 'device_ods', 'od_devices', 'deviceTypes', 'locationDevices', 'devices_od_type', 'devices_not_od_type'));
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
        $this->validate($request, [
            'device_name' => 'required',
            'device_type' => 'required',
            'device_ip' => 'required',
            'device_port' => 'required',
        ]);
        if ($request->device_type == 1 || $request->device_type == 2) {
            $this->validate($request, [
                'device_direction' => 'required',
                'anti_passback' => 'required',
                'enable_log' => 'required',
                'enable_idle_screen' => 'required',
                'focus_away' => 'required',
                'opacity_input' => 'required',
                'od_enabled' => 'required',
                'has_gate' => 'required',
            ]);
            if ($request->anti_passback == 1) {
                $this->validate($request, [
                    'time_passback' => 'required'
                ]);
            }
            if ($request->has_gate == 1) {
                $this->validate($request, [
                    'barrier_close_time' => 'required'
                ]);
            }
        }
        if ($request->device_type == 3) {
            $this->validate($request, [
                'device_direction' => 'required',
                'enable_log' => 'required',
                'confidence_level' => 'required',
                'num_tries' => 'required',
            ]);
        }
        if ($request->device_type == 6) {
            $this->validate($request, [
                'enable_log' => 'required',
                'ccv_pos_port' => 'required',
                'ccv_pos_ip' => 'required',
            ]);
        }
        if ($request->device_type == 4) {
            $this->validate($request, [
                'enable_idle_screen' => 'required',
                'message_text_size' => 'required',
                'time_text_size' => 'required',
                'date_text_size' => 'required',
                'bottom_tray_text_size' => 'required'
            ]);
        }
        $device_name = $request->device_name;

        $devices_count = LocationDevices::where([
                            ['device_name', $device_name],
                            ['id', '<>', $id],
                        ])
                        ->get()->count();
        if ($devices_count > 0) {
            Session::flash('heading', 'Error!');
            Session::flash('message', 'Device with same name already exists.');
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
        try {
            $location_device = LocationDevices::find($id);

            $data = $request->all();
            $locationOption = LocationOptions::find(1);
            $locationId = $locationOption->live_id;
            $responseData = array();
            $responseData['success'] = 0;
            try {
                $http = new Client();
                $response = $http->post(env('API_BASE_URL') . '/api/update-single-device-data', [
                    'form_params' => [
                        'location_id' => $locationId,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
                if ($responseData['success']) {
                    $location_device->live_id = $responseData['data']['device_live_id'];
                }
            } catch (\Exception $ex) {
                
            }
            $location_device->device_name = $data['device_name'];
            $location_device->available_device_id = $data['device_type'];
            $location_device->device_ip = $data['device_ip'];
            $location_device->device_port = $data['device_port'];

            if ($data['device_type'] == 1 || $data['device_type'] == 2) {
                $location_device->enable_log = $data['enable_log'];
                $location_device->enable_idle_screen = $data['enable_idle_screen'];
                $location_device->qr_code_type = $data['qr_code_type'];
                $location_device->focus_away = $data['focus_away'];
                $location_device->opacity_input = $data['opacity_input'];
                $location_device->device_direction = $data['device_direction'];
                $location_device->anti_passback = $data['anti_passback'];
                if ($data['anti_passback'] == 1) {
                    $location_device->time_passback = $data['time_passback'];
                } else {
                    $location_device->time_passback = NULL;
                }
                $location_device->od_enabled = $data['od_enabled'];
                if ($data['has_gate'] == 1) {
                    $location_device->has_gate = $data['has_gate'];
                    $location_device->barrier_close_time = $data['barrier_close_time'];
                }
                $location_device->gate_close_transaction_enabled = $data['gate_close_transaction_enabled'];
                if ($data['device_type'] == 1) {
                    $location_device->has_sdl = $data['has_sdl'];
                    $location_device->has_pdl = $data['has_pdl'];
                    $location_device->plate_correction_enabled = $data['plate_correction_enabled'];
                }
            }
            if ($data['device_type'] == 3) {
                $location_device->camera_enabled = 1;
                $location_device->enable_log = $data['enable_log'];
                $location_device->device_direction = $data['device_direction'];
                $location_device->confidence = $data['confidence_level'];
                $location_device->retries = $data['num_tries'];
                if (array_key_exists('confidence_level_lowest', $data)) {
                    $location_device->confidence_level_lowest = $data['confidence_level_lowest'];
                }
                if (array_key_exists('character_match_limit', $data)) {
                    $location_device->character_match_limit = $data['character_match_limit'];
                }
                $location_device->has_sdl = $data['has_sdl'];
                $location_device->gate_close_transaction_enabled = $data['gate_close_transaction_enabled'];
                $location_device->has_gate = $data['has_gate'];
            }
            if ($data['device_type'] == 4) {
                $location_device->device_direction = 'out';
                $location_device->enable_idle_screen = $data['enable_idle_screen'];
                $location_device->message_text_size = $data['message_text_size'];
                $location_device->time_text_size = $data['time_text_size'];
                $location_device->bottom_tray_text_size = $data['bottom_tray_text_size'];
                $location_device->date_text_size = $data['date_text_size'];
            }
            if ($data['device_type'] == 6) {
                $location_device->enable_log = $data['enable_log'];
                $location_device->ccv_pos_port = $data['ccv_pos_port'];
                $location_device->ccv_pos_ip = $data['ccv_pos_ip'];
                if (array_key_exists('has_enable_person_ticket', $data)) {
                    $location_device->has_enable_person_ticket = 1;
                } else {
                    $location_device->has_enable_person_ticket = 0;
            }
                if (array_key_exists('has_enable_parking_ticket', $data)) {
                    $location_device->has_enable_parking_ticket = 1;
                } else {
                    $location_device->has_enable_parking_ticket = 0;
                }
            }
            $location_device->popup_time = $data['popup_time'];
            $location_device->has_always_access = $data['has_always_access'];
            if (in_array($data['device_type'], array(1, 2, 4, 6))) {
                if ($file = $request->file('advert_image_file')) {
                    $destinationPath = public_path('/uploads/devices');
                    $safeName = time() . '.' . $file->getClientOriginalExtension();
                    $img = Image::make($file->getPathName());
                    $img->resize(480, 320, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->save($destinationPath . '/' . $safeName);
                    //delete old pic if exists
                    if (File::exists(public_path($location_device->advert_image_path))) {
                        File::delete(public_path($location_device->advert_image_path));
                    }
                    //save new file path into db
                    $location_device->advert_image_path = '/uploads/devices/' . $safeName;
                }
            }
            if (in_array($data['device_type'], array(1, 2))) {
                if ($file = $request->file('idle_screen_image')) {
                    $destinationPat = public_path('/uploads/devices');
                    $safeNamee = 'idle' . time() . '.' . $file->getClientOriginalExtension();

                    $imgg = Image::make($file->getPathName());
                    $imgg->resize(480, 320, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    $imgg->save($destinationPat . '/' . $safeNamee);
                    //delete old pic if exists
                    if (File::exists(public_path($location_device->idle_screen_image))) {
                        File::delete(public_path($location_device->idle_screen_image));
                    }
                    //save new file path into db
                    $location_device->idle_screen_image = '/uploads/devices/' . $safeNamee;
                    //print_r($safeNamee);die();
                }
            }
            $location_device->save();
            if ($data['device_type'] == 1 || $data['device_type'] == 2) {
                if (array_key_exists('related_od', $data) && !empty($data['related_od'])) {
                    $related_ods = $data['related_od'];
                    if (count($related_ods) > 0) {
                        foreach ($related_ods as $related_od) {
                            $device_od = \App\DeviceOds::where([
                                        ['device_id', $location_device->id],
                                        ['od_id', $related_od],
                                    ])
                                    ->first();
                            if (!$device_od) {
                                $device_od = new \App\DeviceOds();
                            }
                            $device_od->device_id = $location_device->id;
                            $device_od->od_id = $related_od;
                            $device_od->save();
                        }
                    }
                } else {
                    $device_od = \App\DeviceOds::where('device_id', $location_device->id)->forceDelete();
                }
            } elseif ($data['device_type'] == 3) {
                if (!empty($data['related_ticket_readers'])) {
                    $device_ticket_reader = \App\DeviceTicketReaders::where([
                                ['device_id', $location_device->id],
                            ])
                            ->first();
                    if (!$device_ticket_reader) {
                        $device_ticket_reader = new \App\DeviceTicketReaders();
                    }
                    $device_ticket_reader->device_id = $location_device->id;
                    $device_ticket_reader->ticket_reader_id = $data['related_ticket_readers'];
                    $device_ticket_reader->save();
                } else {
                    $device_od = \App\DeviceTicketReaders::where('device_id', $location_device->id)->forceDelete();
                }
            } elseif ($data['device_type'] == 4) {
                if (array_key_exists('related_device', $data) && !empty($data['related_device'])) {
                    $releated_devices = $data['related_device'];
                    if (count($releated_devices) > 0) {
                        foreach ($releated_devices as $releated_device) {
                            $device_od = \App\DeviceOds::where([
                                        ['device_id', $releated_device],
                                        ['od_id', $location_device->id],
                                    ])
                                    ->first();
                            if (!$device_od) {
                                $device_od = new \App\DeviceOds();
                            }
                            $device_od->device_id = $releated_device;
                            $device_od->od_id = $location_device->id;
                            $device_od->save();
                        }
                    } else {
                        $device_od = \App\DeviceOds::where('od_id', $location_device->id)->forceDelete();
                    }
                }
            }
            $device_settings = \App\DeviceSettings::where('device_id', $location_device->id)->first();
            if (!$device_settings) {
                $device_settings = new \App\DeviceSettings();
            }
            $device_settings->device_id = $location_device->id;
            $device_settings->save();
            if ($responseData['success']) {
                $settings = new Settings\Settings();
                $settings->run_socket_connection_command($location_device->id, 'all');
                Session::flash('heading', 'Success!');
                Session::flash('message', __('devices.device_update'));
                Session::flash('icon', 'success');
                return redirect('devices');
            } else {
                $settings = new Settings\Settings();
                $settings->run_socket_connection_command($location_device->id, 'all');
                Session::flash('heading', 'Warning!');
                Session::flash('message', __('devices.device_update_localy'));
                Session::flash('icon', 'warning');
                return redirect('devices');
            }
        } catch (\Exception $e) {
//            echo $e->getMessage();
//            exit;
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
            $locationDevice = LocationDevices::find($id);
            if ($locationDevice != null) {
                $data['device_live_id'] = $locationDevice->live_id;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $responseData = array();
                $responseData['success'] = 0;
                try {
                    $http = new Client();
                    $response = $http->post(env('API_BASE_URL') . '/api/destroy-single-device-data', [
                        'form_params' => [
                            'location_id' => $locationId,
                            'data' => $data
                        ],
                    ]);
                    $responseData = json_decode((string) $response->getBody(), true);
                } catch (\Exception $ex) {
                    
                }

                $locationDevice->delete();
                if ($responseData['success'] && isset($responseData['data'])) {
                    Session::flash('heading', 'Success!');
                    Session::flash('message', __('devices.device_delete'));
                    Session::flash('icon', 'success');
                    return redirect('/devices');
                } else {
                    Session::flash('heading', 'Warning!');
                    Session::flash('message', __('devices.device_delete_localy'));
                    Session::flash('icon', 'warning');
                    return redirect('/devices');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('devices.device_not_found'));
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
    }

    /**
     * Send Instructions the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendTicket($id) {
        //
        try {
            $locationDevice = LocationDevices::find($id);
            if ($locationDevice != null) {
                $data['device_live_id'] = $locationDevice->live_id;
                $locationOption = LocationOptions::find(1);
                $locationId = $locationOption->live_id;
                $http = new Client();
                $response = $http->post(env('API_BASE_URL') . '/api/send-ticket-device-settings', [
                    'form_params' => [
                        'location_id' => $locationId,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);

                if ($responseData['success'] && isset($responseData['data'])) {
                    Session::flash('heading', 'Success!');
                    Session::flash('message', __('devices.ticket_sent'));
                    Session::flash('icon', 'success');
                    return redirect('devices');
                } else {
                    Session::flash('heading', 'Error!');
                    Session::flash('message', __('devices.went_wrong'));
                    Session::flash('icon', 'error');
                    return redirect('devices');
                }
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('devices.device_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } catch (\Exception $e) {
//            echo $e->getMessage();
//            exit;
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }

    function sync_details(Request $request) {
        $data_details = array();
        if (empty($request->device)) {
            return 'Somthing went wrong';
        }
        $device_id = $request->device;
        $device_details = \App\LocationDevices::find($device_id);
        if (!$device_details) {
            return 'Somthing went wrong';
        }
        $device_settings = \App\DeviceSettings::where('device_id', $device_id)->first();
        if (!$device_settings) {
            return 'Somthing went wrong';
        }
        $data_details['location_settings'] = array(
            'status' => $device_settings->location_settings,
            'message' => $device_settings->location_settings_details,
        );
        $data_details['location_timings_settings'] = array(
            'status' => $device_settings->location_timings_settings,
            'message' => $device_settings->location_timings_settings_details,
        );
        $data_details['location_whitelist_timings_settings'] = array(
            'status' => $device_settings->location_whitelist_timings_settings,
            'message' => $device_settings->location_whitelist_timings_settings_details,
        );
        $data_details['whitelist_settings'] = array(
            'status' => $device_settings->whitelist_settings,
            'message' => $device_settings->whitelist_settings_details,
        );
        $data_details['userlist_settings'] = array(
            'status' => $device_settings->userlist_settings,
            'message' => $device_settings->userlist_settings_details,
        );
        $data_details['device_settings'] = array(
            'status' => $device_settings->device_settings,
            'message' => $device_settings->device_settings_details,
        );
        $data_details['other_settings'] = array(
            'status' => $device_settings->other_settings,
            'message' => $device_settings->other_settings_details,
        );
        ob_start();
        ?>
        <div class="modal-header">
            <button type="button" class="close" aria-hidden="true" data-dismiss="modal">x</button>
            <h4 class="modal-title"><?php echo $device_details->device_name ?> Details</h4>
        </div>
        <div class="modal-body col-md-12">
            <?php
            if ($device_details->available_device_id == 1) {
                ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Timings</th>
                                <th>Whitelist Timings</th>
                                <th>Whitelist </th>
                                <th>Userlist</th>
                                <th>Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php if ($data_details['location_settings']['status']) { ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['location_settings']['message'] == NULL ? 'Not Synched' : $data_details['location_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                <?php } ?>
                                <?php
                                if ($data_details['location_timings_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['location_timings_settings']['message'] == NULL ? 'Not Synched' : $data_details['location_timings_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                if ($data_details['location_whitelist_timings_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['location_whitelist_timings_settings']['message'] == NULL ? 'Not Synched' : $data_details['location_whitelist_timings_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                if ($data_details['whitelist_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['whitelist_settings']['message'] == NULL ? 'Not Synched' : $data_details['whitelist_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                if ($data_details['userlist_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['userlist_settings']['message'] == NULL ? 'Not Synched' : $data_details['userlist_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                ?> 
                                <?php if ($data_details['device_settings']['status']) { ?>
                                    <td class="text-center text-success"><i data-toggle="tooltip" title="Synched"  class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['device_settings']['message'] == NULL ? 'Not Synched' : $data_details['device_settings']['message'] ?>" class="fa fa-times"></i></td>
                                <?php } ?>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/location/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/timings/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/whitelist_timings/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/whitelist/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/userlist/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/device/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>    
                <?php
            } elseif ($device_details->available_device_id == 2) {
                ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Timings</th>
                                <th>Whitelist Timings</th>
                                <th>Whitelist </th>
                                <th>Userlist</th>
                                <th>Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php if ($data_details['location_settings']['status']) { ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['location_settings']['message'] == NULL ? 'Not Synched' : $data_details['location_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                <?php } ?>
                                <?php
                                if ($data_details['location_timings_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['location_timings_settings']['message'] == NULL ? 'Not Synched' : $data_details['location_timings_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                if ($data_details['location_whitelist_timings_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['location_whitelist_timings_settings']['message'] == NULL ? 'Not Synched' : $data_details['location_whitelist_timings_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                if ($data_details['whitelist_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['whitelist_settings']['message'] == NULL ? 'Not Synched' : $data_details['whitelist_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                if ($data_details['userlist_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['userlist_settings']['message'] == NULL ? 'Not Synched' : $data_details['userlist_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                ?> 
                                <?php if ($data_details['device_settings']['status']) { ?>
                                    <td class="text-center text-success"><i data-toggle="tooltip" title="Synched"  class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['device_settings']['message'] == NULL ? 'Not Synched' : $data_details['device_settings']['message'] ?>" class="fa fa-times"></i></td>
                                <?php } ?>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/location/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/timings/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/whitelist_timings/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/whitelist/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/userlist/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/device/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>    
                <?php
            } elseif ($device_details->available_device_id == 3) {
                ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Timings</th>
                                <th>Whitelist Timings</th>
                                <th>Whitelist </th>
                                <th>Userlist</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php if ($data_details['location_settings']['status']) { ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['location_settings']['message'] == NULL ? 'Not Synched' : $data_details['location_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                <?php } ?>
                                <?php
                                if ($data_details['location_timings_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['location_timings_settings']['message'] == NULL ? 'Not Synched' : $data_details['location_timings_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                if ($data_details['location_whitelist_timings_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['location_whitelist_timings_settings']['message'] == NULL ? 'Not Synched' : $data_details['location_whitelist_timings_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                if ($data_details['whitelist_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['whitelist_settings']['message'] == NULL ? 'Not Synched' : $data_details['whitelist_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                if ($data_details['userlist_settings']['status']) {
                                    ?>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                <?php } else { ?>
                                    <td  class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['userlist_settings']['message'] == NULL ? 'Not Synched' : $data_details['userlist_settings']['message'] ?>" class="fa fa-times"></i></td>
                                    <?php
                                }
                                ?> 
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/location/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/timings/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/whitelist_timings/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/whitelist/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a  class="btn btn-primary btn-sm" href="<?php echo url('/devices/sync/userlist/' . $device_details->id) ?>" >
                                        <i class="fa fa-wifi"></i> Sync
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>    
                <?php
            } elseif ($device_details->available_device_id == 4) {
                if ($data_details['other_settings']['status']) {
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Properties</th>
                                    <th>Status</th>
                                </tr>
                                <tr>
                                    <th class="pl-20">Message Text Size</th>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">Time Text Size</th>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">Date Text Size</th>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">Bottom Array Text Size</th>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>    
                    <?php
                } else {
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th class="pl-20 pt-15 pb-15">Properties</th>
                                    <th class="pl-20 pt-15 pb-15">Status</th>
                                </tr>
                                <tr>
                                    <th class="pl-20">Message Text Size</th>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['other_settings']['message'] == NULL ? 'Not Synched' : $data_details['other_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">Time Text Size</th>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['other_settings']['message'] == NULL ? 'Not Synched' : $data_details['other_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">Date Text Size</th>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['other_settings']['message'] == NULL ? 'Not Synched' : $data_details['other_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">Bottom Array Text Size</th>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['other_settings']['message'] == NULL ? 'Not Synched' : $data_details['other_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>    
                    <?php
                }
            } elseif ($device_details->available_device_id == 6) {
                if ($data_details['other_settings']['status']) {
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th class="pl-20 pt-15 pb-15">Properties</th>
                                    <th class="pl-20 pt-15 pb-15">Status</th>
                                </tr>
                                <tr>
                                    <th class="pl-20">CCV POS Port</th>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">CCV POS IP</th>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">Enable Log </th>
                                    <td  class="text-center text-success"><i data-toggle="tooltip" title="Synched" class="fa fa-check"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>    
                    <?php
                } else {
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th class="pl-20 pt-15 pb-15">Properties</th>
                                    <th class="pl-20 pt-15 pb-15">Status</th>
                                </tr>
                                <tr>
                                    <th class="pl-20">CCV POS IP</th>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['other_settings']['message'] == NULL ? 'Not Synched' : $data_details['other_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">CCV POS Port</th>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['other_settings']['message'] == NULL ? 'Not Synched' : $data_details['other_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                </tr>
                                <tr>
                                    <th class="pl-20">Enable Log</th>
                                    <td class="text-center text-danger"><i data-toggle="tooltip" title="<?php echo $data_details['other_settings']['message'] == NULL ? 'Not Synched' : $data_details['other_settings']['message'] ?>"  class="fa fa-times"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>    
                    <?php
                }
            } else {
                ?>
                <div class="col-md-12 text-center">Don't try to cheat us.</div>
            <?php }
            ?>
            <div class="col-md-12 p-0">
                <a  class="btn btn-primary btn-sm pull-right" href="<?php echo url('/devices/initialize/' . $device_details->id) ?>">
                    <i class="fa fa-wifi"></i> Reinitialize
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    function sync_device_command_call(Request $request, $status, $id) {
        if (empty($id)) {
            Session::flash('heading', 'Error!');
            Session::flash('message', __('devices.went_wrong'));
            Session::flash('icon', 'error');
            return redirect('devices');
        }
        if (empty($status)) {
            Session::flash('heading', 'Error!');
            Session::flash('message', __('devices.went_wrong'));
            Session::flash('icon', 'error');
            return redirect('devices');
        }
        $setting_pushed = FALSE;
        if ($status == 'location') {
            $settings = new Settings\Settings();
            $settings->run_socket_connection_command($id, 'location_setting');
            $device_settings = \App\DeviceSettings::where('location_settings', $id)->first();
            if ($device_settings) {
                if ($device_settings->location_settings) {
                    $setting_pushed = TRUE;
                }
            }
        } elseif ($status == 'timings') {
            $settings = new Settings\Settings();
            $settings->run_socket_connection_command($id, 'timings');
            $device_settings = \App\DeviceSettings::where('location_settings', $id)->first();
            if ($device_settings) {
                if ($device_settings->location_timings_settings) {
                    $setting_pushed = TRUE;
                }
            }
        } elseif ($status == 'whitelist_timings') {
            $settings = new Settings\Settings();
            $settings->run_socket_connection_command($id, 'whitelist_tiings');
            $device_settings = \App\DeviceSettings::where('location_settings', $id)->first();
            if ($device_settings) {
                if ($device_settings->location_whitelist_timings_settings) {
                    $setting_pushed = TRUE;
                }
            }
        } elseif ($status == 'whitelist') {
            $settings = new Settings\Settings();
            $settings->run_socket_connection_command($id, 'whitelist_users');
            $device_settings = \App\DeviceSettings::where('location_settings', $id)->first();
            if ($device_settings) {
                if ($device_settings->whitelist_settings) {
                    $setting_pushed = TRUE;
                }
            }
        } elseif ($status == 'userlist') {
            $settings = new Settings\Settings();
            $settings->run_socket_connection_command($id, 'userlist_users');
            $device_settings = \App\DeviceSettings::where('location_settings', $id)->first();
            if ($device_settings) {
                if ($device_settings->userlist_settings) {
                    $setting_pushed = TRUE;
                }
            }
        } elseif ($status == 'device') {
            $settings = new Settings\Settings();
            $settings->run_socket_connection_command($id, 'device_settings');
            $device_settings = \App\DeviceSettings::where('location_settings', $id)->first();
            if ($device_settings) {
                if ($device_settings->device_settings) {
                    $setting_pushed = TRUE;
                }
            }
        } else {
            Session::flash('heading', 'Error!');
            Session::flash('message', __('devices.dont_cheat_us'));
            Session::flash('icon', 'error');
            return redirect('devices');
        }
        if ($setting_pushed) {
            $status = strtoupper($status);
            Session::flash('heading', 'Success!');
            Session::flash('message', $status . ' ' . __('devices.settings_pushed'));
            Session::flash('icon', 'success');
            return redirect('devices');
        } else {
            Session::flash('heading', 'Warning!');
            Session::flash('message', __('devices.settings_in_progress'));
            Session::flash('icon', 'warning');
            return redirect('devices');
        }
    }

    public function initialize_device(Request $request, $id) {
        $settings = new Settings\Settings();
        $device_details = LocationDevices::find($id);
        if (!$device_details) {
            Session::flash('heading', 'Error!');
            Session::flash('message', __('devices.try_again'));
            Session::flash('icon', 'error');
            return redirect('devices');
        }
        if ($device_details->available_device_id == 4) {
            $settings->run_socket_connection_command($id, 'od');
        } else {
            $settings->run_socket_connection_command($id, 'all');
        }

        if ($device_details) {
            if ($device_details->is_synched) {
                Session::flash('heading', 'Success!');
                Session::flash('message', __('devices.intialized_success'));
                Session::flash('icon', 'success');
                return redirect('devices');
            }
            Session::flash('heading', 'Warning!');
            Session::flash('message', __('devices.intialized_in_progress'));
            Session::flash('icon', 'warning');
            return redirect('devices');
        }
        Session::flash('heading', 'Error!');
        Session::flash('message', __('devices.try_again'));
        Session::flash('icon', 'error');
        return redirect('devices');
    }

    public function name_exist(Request $request) {
        try {
            $device_name = $request->device_name;
            $device_id = $request->device_id;
            if ($device_id != 0) {
                $devices_count = LocationDevices::where([
                                    ['device_name', $device_name],
                                    ['id', '<>', $device_id],
                                ])
                                ->get()->count();
                return $devices_count;
            }
            $devices_count = LocationDevices::where([
                                ['device_name', $device_name],
                            ])
                            ->get()->count();
            return $devices_count;
        } catch (\Exception $ex) {
            return 0;
        }
    }

    public function updateServerTime(Request $request, $id) {
        try {
            \Illuminate\Support\Facades\Artisan::call('command:ServerTime', [
                'device' => $id
            ]);
            Session::flash('heading', 'Warning!');
            Session::flash('message', __('devices.intialized_in_progress'));
            Session::flash('icon', 'warning');
            return redirect('devices');
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            exit;
            Session::flash('heading', 'Error!');
            Session::flash('message', __('devices.try_again'));
            Session::flash('icon', 'error');
            return redirect('devices');
        }
    }

}
