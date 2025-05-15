<?php

namespace App\Http\Controllers;

use App\AvailableDevices;
use App\RegressionTestingLog;
use App\Language;
use Illuminate\Http\Request;
use App\LocationDevices;
use App\ParkingAccessRule;
use App\ParkingRulesName;
use App\LocationOptions;
use App\DeviceOds;
use App\DevicePort;
use App\DeviceSettings;
use App\DeviceTicketReaders;
use App\LightCondition;
use Exception;
use Session;
use Validator;
use File;
use Image;
use App\Http\Controllers\AccessCheckController;
use Illuminate\Support\Facades\Artisan;

class RegressionController extends Controller
{
    //
    public $accessController = false;
    public $location = false;
    public $disableRules = false;
    public $parkingRules = false;
    public function __construct()
    {
        $this->accessController = new AccessCheckController();
        $this->location = LocationOptions::first();
        $this->parkingRules = null;
        $this->disableRules = ParkingRulesName::wherehas('access', function ($query) {
            $query->where('enable', 0);
        })->whereIn('slug', ['comfort_security_check', 'has_always_access', 'pre_booking'])->orderBy('rule_sorting', 'ASC')->pluck('id')->all();
    }
    public function index()
    {
        $disableRules = ParkingRulesName::whereHas('access', function ($query) {
            $query->where('enable', 0);
        })->orderBy('rule_sorting', 'asc')->get();
        $enableRules = ParkingRulesName::whereHas('access', function ($query) {
            $query->where('enable', 1);
        })->orderBy('rule_sorting', 'asc')->get();
        $this->parkingRules['enable'] = $enableRules;
        $this->parkingRules['disable'] = $disableRules;
        $availableDeviceId = [1, 2, 3];
        $availablDevices = AvailableDevices::whereIn('id', [1, 2, 3])->get();
        $devices = LocationDevices::with('available_devices')->whereIn('available_device_id', $availableDeviceId)->get();
        $langs = Language::all();
        $is_imported_devices = LocationDevices::where('is_imported', 1)->get();
        $is_imported_rules = ParkingRulesName::with('access')->where('is_imported', 1)->get();
        $is_imported = false;
        if (count($is_imported_devices) > 0 && count($is_imported_rules) > 0) {
            $is_imported = true;
        }
        return view('regression.index', [
            'devices' => $devices,
            'availableDevices' => $availablDevices,
            'parkingRules' =>  $this->parkingRules,
            'langs' => $langs,
            'location' => $this->location,
            'is_imported' => $is_imported,
            'imported_devices' => $is_imported_devices,
            'imported_rules' => $is_imported_rules
        ]);
    }
    public function enableDisableRule($id)
    {
        try {
            $rule = ParkingRulesName::with('access')->find($id);
            $rule->access->enable = false;
            $rule->access->update();
            return response()->json($rule);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    public function runTest(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_id' => 'sometimes|required',
                'identifier' => 'required',
                'confidence' => 'required',
            ]);
            if (!$validator->passes()) {
                return response()->json(['denied' => 'Device and Identifier are required']);
            }
            $device = false;
            if ($request->device_id) {
                $device = $request->device_id;
            } else {
                $device = $request->import_device_id;
            }
            $key = strtotime($this->location->created_at) . '-' . $device;
            $identifier = $request->identifier;
            $country_code = $request->country_code;
            $confidence = $request->confidence;
            //dd($this->disableRules);
            $response = $this->accessController->verifyAccessRequest($request, $key, $device, $identifier, $confidence, $country_code);
            if (Session::has('testing_session_id')) {
                $value = Session::get('testing_session_id');
                $newarr = explode(',', $request->sorted_rules);
                $found = false;
                for ($i = 0; $i < count($this->disableRules); $i++) {
                    if (!in_array($this->disableRules[$i], $newarr)) {
                        $found = true;
                        array_push($newarr, $this->disableRules[$i]);
                    }
                }
                $sorted_rules=implode(",", $newarr);
                $testingLog = RegressionTestingLog::with('ruleLog')->where('testing_session_id', $value)->groupBy('rule_id')->orderByRaw(("FIELD(rule_id, $sorted_rules)"))->get();
                return response()->json(['response' => $response, 'logs' => $testingLog]);
            }
        } catch (Exception $ex) {
            return response()->json($ex->getMessage());
        }
    }
    public function fetchRelatedDevices(Request $request)
    {
        try {
            $device_id = false;
            if ($request->device_id == 4) {
                $device_id = 1;
            } else {
                $device_id = $request->device_id;
            }
            $relatedDevices = LocationDevices::where('available_device_id', $device_id)->get();
            return response()->json($relatedDevices);
        } catch (Exception $ex) {
            return response()->json($ex->getMessage());
        }
    }
    public function getLogDetail(Request $request)
    {
        try {
            $detail = RegressionTestingLog::where('testing_session_id', $request->session_id)->where('id', '>', 1)->where('rule_id', $request->rule_id)->get();
            return response()->json($detail);
        } catch (Exception $ex) {
            return response()->json($ex->getMessage());
        }
    }
    public function downloadJson(Request $request)
    {
        try {
            $availableDeviceId = [1, 2, 3];
            $availablDevices = AvailableDevices::whereIn('id', [1, 2, 3])->get();
            $devices = LocationDevices::with('available_devices', 'device_settings', 'relatedTicketReader', 'relatedOds', 'lightConditions')->whereIn('available_device_id', $availableDeviceId)->get();
            $parkingRules = ParkingRulesName::with('access')->get()->sortByDesc('access.enable');
            $data = json_encode(['devices' => $devices, 'parking-rules' => $parkingRules]);
            $fileName = time() . '_datafile.json';
            $fileStorePath = public_path('/uploads/json/' . $fileName);
            File::put($fileStorePath, $data);
            return response()->download($fileStorePath);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    public function importData(Request $request)
    {
        try {
            //dd($request->all());
            $availableDeviceId = [1, 2, 3];
            $imported_devices = LocationDevices::with('available_devices')->where('is_imported', 1)->get();
            $imported_rules = ParkingRulesName::with('access')->where('is_imported', 1)->get();
            $langs = Language::all();
            $availablDevices = AvailableDevices::whereIn('id', [1, 2, 3])->get();
            $this->parkingRules = ParkingRulesName::with('access')->whereNull('is_imported')->get()->sortByDesc('access.enable');
            $devices = LocationDevices::with('available_devices')->whereIn('available_device_id', $availableDeviceId)->get();
            if ((count($imported_devices) > 0) && count($imported_rules) > 0) {
                return view('regression.index', [
                    'devices' => $devices,
                    'availableDevices' => $availablDevices,
                    'parkingRules' =>  $this->parkingRules,
                    'langs' => $langs,
                    'location' => $this->location,
                    'is_imported' => true,
                    'imported_devices' => $imported_devices,
                    'imported_rules' => $imported_rules
                ]);
                return redirect()->back()->with('message', 'Please select a JSON file.');
            } else {
                Artisan::call('command:remove_other_location_data');
                if ($request->hasFile('json_file')) {
                    $file = $request->file('json_file');
                    $json = json_decode(file_get_contents($file), true);
                    if (!empty($json)) {
                        $imported_devices = $this->saveOtherLocationDevices($json['devices']);
                        $imported_rules = $this->saveOtherLocationRules($json['parking-rules']);
                        return view('regression.index', [
                            'devices' => $devices,
                            'availableDevices' => $availablDevices,
                            'parkingRules' =>  $this->parkingRules,
                            'langs' => $langs,
                            'location' => $this->location,
                            'is_imported' => true,
                            'imported_devices' => $imported_devices,
                            'imported_rules' => $imported_rules
                        ]);
                    } else {
                        return redirect()->back()->with('message', 'Invalid JSON data.');
                    }
                } else {
                    return redirect()->back()->with('message', 'Please select a JSON file.');
                }
            }
        } catch (Exception $ex) {
            return redirect()->back()->with('message', $ex->getMessage() . ' ' . $ex->getLine());
        }
    }
    public function saveOtherLocationDevices($devices)
    {
        try {
            foreach ($devices as $device) {
                $location_device = new LocationDevices();
                $location_device->device_name = $device['device_name'];
                $location_device->available_device_id = $device['available_device_id'];
                $location_device->device_ip = $device['device_ip'];
                if ($device['available_device_id'] != 12) {
                    $location_device['device_port'] = $device['device_port'];
                }
                if ($device['available_device_id'] == 1 || $device['available_device_id'] == 2) {
                    $location_device->enable_log = $device['enable_log'];
                    $location_device->enable_idle_screen = $device['enable_idle_screen'];
                    $location_device->qr_code_type = $device['qr_code_type'];
                    $location_device->focus_away = $device['focus_away'];
                    $location_device->opacity_input = $device['opacity_input'];
                    $location_device->device_direction = $device['device_direction'];
                    $location_device->anti_passback = $device['anti_passback'];
                    if ($device['anti_passback'] == 1) {
                        $location_device->time_passback = $device['anti_passback'];
                    } else {
                        $location_device->time_passback = null;
                    }
                    $location_device->od_enabled = $device['od_enabled'];
                    if ($device['has_gate'] == 1) {
                        $location_device->has_gate = $device['has_gate'];
                        $location_device->barrier_close_time = $device['barrier_close_time'];
                    }
                    $location_device->gate_close_transaction_enabled = $device['gate_close_transaction_enabled'];
                    if ($device['available_device_id'] == 1) {
                        $location_device->has_sdl = $device['has_sdl'];
                        $location_device->has_pdl = $device['has_pdl'];
                        $location_device->plate_correction_enabled = $device['plate_correction_enabled'];
                    }
                }
                if ($device['available_device_id'] == 3) {
                    $location_device->camera_enabled = 1;
                    $location_device->enable_log = $device['enable_log'];
                    $location_device->device_direction = $device['device_direction'];
                    if (array_key_exists('confidence_level', $device)) {
                        $location_device->confidence_level = $device['confidence_level'];
                    }
                    if (array_key_exists('num_tries', $device)) {
                        $location_device->retries = $device['num_tries'];
                    }
                    if (array_key_exists('confidence_level_lowest', $device)) {
                        $location_device->confidence_level_lowest = $device['confidence_level_lowest'];
                    }
                    if (array_key_exists('character_match_limit', $device)) {
                        $location_device->character_match_limit = $device['character_match_limit'];
                    }
                    $location_device->has_sdl = $device['has_sdl'];
                    $location_device->gate_close_transaction_enabled = $device['gate_close_transaction_enabled'];
                    $location_device->has_gate = $device['has_gate'];
                    $location_device->plate_length = $device['plate_length'];
                    $location_device->character_height = $device['character_height'];
                    $location_device->exposure_mode = $device['exposure_mode'];
                    if (array_key_exists('disable_night_mode', $device)) {
                        $location_device->disable_night_mode = $device['disable_night_mode'];
                    }
                    if (array_key_exists('emergency_entry_exit', $device)) {
                        $location_device->emergency_entry_exit = $device['emergency_entry_exit'];
                    }
                    if (array_key_exists('light_condition', $device)) {
                        $location_device->light_condition = $device['light_condition'];
                    }
                }
                if ($device['available_device_id'] == 4) {
                    $location_device->device_direction = 'out';
                    $location_device->enable_idle_screen = $device['enable_idle_screen'];
                    $location_device->message_text_size = $device['message_text_size'];
                    $location_device->time_text_size = $device['time_text_size'];
                    $location_device->bottom_tray_text_size = $device['bottom_tray_text_size'];
                    $location_device->date_text_size = $device['date_text_size'];
                }
                if ($device['available_device_id'] == 6) {
                    $location_device->enable_log = $device['enable_log'];
                    $location_device->ccv_pos_port = $device['ccv_pos_port'];
                    $location_device->ccv_pos_ip = $device['ccv_pos_ip'];
                    if (array_key_exists('has_enable_person_ticket', $device)) {
                        $location_device->has_enable_person_ticket = 1;
                    } else {
                        $location_device->has_enable_person_ticket = 0;
                    }
                    if (array_key_exists('has_enable_parking_ticket', $device)) {
                        $location_device->has_enable_parking_ticket = 1;
                    } else {
                        $location_device->has_enable_parking_ticket = 0;
                    }
                    $location_device->open_relay = $device['open_relay'];
                    $location_device->close_relay = $device['close_relay'];
                }
                if ($device['available_device_id'] == 12) {
                    $location_device->barrier_close_time = $device['barrier_close_time'];
                    $location_device->password = $device['password'];
                }
                if ($device['available_device_id'] != 12) {
                    $location_device->popup_time = $device['popup_time'];
                    $location_device->has_always_access = $device['has_always_access'];
                }
                // if (in_array($device['available_device_id'], array(1, 2, 4, 6))) {
                //     if ($file = $device['advert_image_file']) {
                //         $destinationPath = public_path('/uploads/devices');
                //         $safeName = time() . '.' . $file->getClientOriginalExtension();
                //         $img = Image::make($file->getPathName());
                //         $img->resize(480, 320, function ($constraint) {
                //             $constraint->aspectRatio();
                //             $constraint->upsize();
                //         });
                //         $img->save($destinationPath . '/' . $safeName);
                //         //save new file path into db
                //         $location_device->advert_image_path = '/uploads/devices/' . $safeName;
                //     }
                // }
                // if (in_array($device['available_device_id'], array(1, 2))) {
                //     if ($file = $device->file('idle_screen_image')) {
                //         $destinationPat = public_path('/uploads/devices');
                //         $safeNamee = 'idle' . time() . '.' . $file->getClientOriginalExtension();
                //         $imgg = Image::make($file->getPathName());
                //         $imgg->resize(480, 320, function ($constraint) {
                //             $constraint->aspectRatio();
                //             $constraint->upsize();
                //         });
                //         $imgg->save($destinationPat . '/' . $safeNamee);
                //         //delete old pic if exists
                //         if (File::exists(public_path($location_device['idle_screen_image']))) {
                //             File::delete(public_path($location_device['idle_screen_image']));
                //         }
                //         //save new file path into db
                //         $location_device->idle_screen_image = '/uploads/devices/' . $safeNamee;
                //     }
                // }
                $location_device->is_imported = true;
                $location_device->save();
                if ($device['available_device_id'] == 1 || $device['available_device_id'] == 2) {
                    if (array_key_exists('related_ods', $device['related_ods']) && !empty($device['related_ods'])) {
                        $related_ods = $device['related_ods'];
                        if (count($related_ods) > 0) {
                            foreach ($related_ods as $related_od) {
                                $device_od = new DeviceOds();
                                $device_od->device_id = $location_device->id;
                                $device_od->od_id = $related_od;
                                $device_od->save();
                            }
                        }
                    }
                } elseif ($device['available_device_id'] == 3) {
                    if (!empty($device['related_ticket_reader'])) {
                        $device_ticket_reader = new DeviceTicketReaders();
                        $device_ticket_reader->device_id = $location_device->id;
                        $device_ticket_reader->ticket_reader_id = $device['related_ticket_reader'][0]['ticket_reader_id'];
                        $device_ticket_reader->save();
                        $location_device->has_related_ticket_reader = true;
                        $location_device->save();
                    }
                    if (array_key_exists('light_condition', $device)) {
                        $location_device->light_condition = true;
                        $location_device->save();
                        $light_conditions = LightCondition::where('device_id', $location_device->id)->first();
                        if (!$light_conditions) {
                            $light_conditions = new LightCondition();
                        }
                        if (!is_null($device['light_conditions'])) {
                            $light_conditions->device_id = $location_device->id;
                            $light_conditions->gain = $device['light_conditions']['gain'];
                            $light_conditions->level = $device['light_conditions']['light_level'];
                            $light_conditions->exposure_time = $device['light_conditions']['exposure_time'];
                            $light_conditions->save();
                        }
                    }
                } elseif ($device['available_device_id'] == 6) {
                    if (!empty($device['related_switch'])) {
                        $device_ticket_reader = new DeviceTicketReaders();
                        $device_ticket_reader->device_id = $location_device->id;
                        $device_ticket_reader->ticket_reader_id = $device['related_switch'];
                        $device_ticket_reader->save();
                    }
                } elseif ($device['available_device_id'] == 12) {
                    if (!empty($device['relays'])) {
                        $relays = $device['relays'];
                        if (count($relays) > 0) {
                            foreach ($relays as $key => $relay) {
                                $switch_relay = new DevicePort();
                                $switch_relay->device_id = $location_device->id;
                                $switch_relay->relay = $relay;
                                $switch_relay->relay_number = $key;
                                $switch_relay->save();
                            }
                        }
                    }
                } elseif ($device->device_type == 4) {
                    if (array_key_exists('related_device', $device) && !empty($device['related_device'])) {
                        $releated_devices = $device['related_device'];
                        if (count($releated_devices) > 0) {
                            foreach ($releated_devices as $releated_device) {
                                $device_od = new DeviceOds();
                                $device_od->device_id = $releated_device;
                                $device_od->od_id = $location_device->id;
                                $device_od->save();
                            }
                        }
                    }
                }
                $device_settings = DeviceSettings::where('device_id', $location_device->id)->first();
                if (!$device_settings) {
                    $device_settings = new DeviceSettings();
                }
                $device_settings->device_id = $location_device->id;
                $device_settings->save();
            }
            $is_imported_devices = LocationDevices::where('is_imported', 1)->get();
            if ($is_imported_devices) {
                return $is_imported_devices;
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    public function saveOtherLocationRules($rules)
    {
        try {
            foreach ($rules as $rule) {
                $ruleName = ParkingRulesName::where('id', $rule['id'])->where('is_imported', 1)->first();
                if (!$ruleName) {
                    $ruleName = new ParkingRulesName();
                }
                $ruleName->name = $rule['name'];
                $ruleName->slug = $rule['slug'];
                $ruleName->rule_sorting = $rule['rule_sorting'];
                $ruleName->is_imported = true;
                if ($ruleName->exists()) {
                    $ruleName->update();
                }
                $ruleName->save();
                $ruleAccess = ParkingAccessRule::where('rule_id', $ruleName->id)->first();
                if (!$ruleAccess) {
                    $ruleAccess = new ParkingAccessRule();
                }
                $ruleAccess->rule_id = $ruleName->id;
                if ($rule['access']['enable']) {
                    $ruleAccess->enable = $rule['access']['enable'];
                }
                if ($rule['access']['match_distance']) {
                    $ruleAccess->match_distance = $rule['access']['match_distance'];
                }
                if ($rule['access']['barcode_type']) {
                    $ruleAccess->barcode_type = $rule['access']['barcode_type'];
                }
                if ($rule['access']['device_direction']) {
                    $ruleAccess->device_direction = $rule['access']['device_direction'];
                }
                if ($rule['access']['plate_match_mode']) {
                    $ruleAccess->plate_match_mode = $rule['access']['plate_match_mode'];
                }
                if ($ruleAccess->exists()) {
                    $ruleAccess->update();
                }
                $ruleAccess->save();
            }
            $rules = ParkingRulesName::with('access')->where('is_imported', 1)->get();
            if ($rules) {
                return $rules;
            }
        } catch (Exception $ex) {
            return false;
        }
    }
}
