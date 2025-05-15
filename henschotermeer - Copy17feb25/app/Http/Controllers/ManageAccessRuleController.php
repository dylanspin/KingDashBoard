<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LocationOptions;
use App\ParkingAccessRule;
use App\ParkingRulesName;
use Exception;
use GuzzleHttp\Client;
use App\Http\Controllers\LogController;
use Session;
use Illuminate\Support\Facades\Validator;

class ManageAccessRuleController extends Controller
{
    //
    public function index()
    {
        $location = LocationOptions::first();
        $parkingRules = ParkingRulesName::with('access')->whereNull('is_imported')->orWhere('is_imported', 0)->orderBy('rule_sorting', 'ASC')->get();
        $enable = ParkingRulesName::whereHas('access', function ($query) {
            $query->where('enable', 1);
        })->whereNull('is_imported')->orWhere('is_imported', 0)->count();
        $disable = ParkingRulesName::whereHas('access', function ($query) {
            $query->where('enable', 0);
        })->whereNull('is_imported')->count();
        return view('manage-rules.index', [
            'location' => $location,
            'rules' => $parkingRules,
            'enable' => $enable,
            'disable' => $disable
        ]);
    }
    public function create()
    {
        $location = LocationOptions::first();
        return view('manage-rules.create', [
            'location' => $location
        ]);
    }
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $parkingRule = ParkingRulesName::where('name', $data['name'])->first();
            if (!$parkingRule) {
                $parkingRule = new ParkingRulesName();
            }
            $parkingRule->name = $data['name'];
            $modifiedName = preg_replace('~[\\\\/:*?"<>|+-]~', '', $data['name']);
            $parkingRule->slug = strtolower(str_replace(" ", "_", $modifiedName));
            if ($parkingRule->exists()) {
                $parkingRule->update();
            }
            $parkingRule->save();
            if ($parkingRule) {
                $parkingAccess = new ParkingAccessRule();
                $parkingAccess->rule_id = $parkingRule->id;
                $parkingAccess->enable = $data['status'];
                $parkingAccess->device_direction = $data['device_direction'];
                $parkingAccess->barcode_type = $data['barcode_type'];
                $parkingAccess->plate_match_mode = $data['plate_match_mode'];
                $parkingAccess->save();
            }
            // $data['language_live_id'] = $language->live_id;
            // $location = LocationOptions::find(1);
            // $locationId = $location->live_id;
            // $user_id = auth()->user()->live_id;
            // $Key = base64_encode($locationId . '_' . $user_id);
            // $responseData['success'] = 0;
            // try {
            //     $http = new Client();
            //     $response = $http->post(env('API_BASE_URL') . '/api/update-location-data', [
            //         'form_params' => [
            //             'token' => $Key,
            //             'data' => $data
            //         ],
            //     ]);
            //     $responseData = json_decode((string) $response->getBody(), true);
            // } catch (Exception $ex) {
            //     $error_log = new \App\Http\Controllers\LogController();
            //     $error_log->log_create('update-location-data', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            // }
            if ($parkingRule) {
                Session::flash('heading', 'Success!');
                Session::flash('message', __('access_rules.add_message'));
                Session::flash('icon', 'success');
                return redirect('manage-rules');
            }
        } catch (Exception $ex) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();;
        }
    }
    public function edit($id)
    {
        $location = LocationOptions::first();
        $parkingRule = ParkingRulesName::with('access')->where('id', $id)->first();
        return view('manage-rules.edit', [
            'location' => $location,
            'rule' => $parkingRule
        ]);
    }
    public function update(Request $request)
    {
        try {
            $specialRule = ['comfort_security_check', 'has_always_access', 'pre_booking'];
            $data = $request->all();
            $parkingRule = ParkingRulesName::where('id', $data['rule_id'])->first();
            if (!$parkingRule) {
                $parkingRule = new ParkingRulesName();
            }
            $maxOrder = ParkingRulesName::max('rule_sorting');
            $parkingRule->name = $data['name'];
            if (!isset($parkingRule->slug)) {
                $modifiedName = preg_replace('~[\\\\/:*?"<>|+-]-~', '', $data['name']);
                $parkingRule->slug = strtolower(str_replace(" ", "_", $modifiedName));
            }
            if ($data['status'] != 1) {
                if (in_array($parkingRule->slug, $specialRule)) {
                    $parkingRule->rule_sorting = $maxOrder + 1;
                }
            }
            if ($parkingRule->exists()) {
                $parkingRule->update();
            }
            $parkingRule->save();
            if ($parkingRule) {
                $parkingAccess = ParkingAccessRule::where('rule_id', $parkingRule->id)->first();
                if (!$parkingAccess) {
                    $parkingAccess = new ParkingAccessRule();
                }
                if ($request->rule_slug == "post_booking") {
                    $preBooking = ParkingRulesName::where('slug', 'pre_booking')->first();
                    if ($parkingRule->slug == "post_booking" && $data['status'] == 1) {
                        $preBookingAccess = ParkingAccessRule::where('rule_id', $preBooking->id)->first();
                        $preBookingAccess->enable = 0;
                        $preBookingAccess->update();
                    } elseif ($parkingRule->slug == "post_booking" && $data['status'] == 0) {
                        $preBookingAccess = ParkingAccessRule::where('rule_id', $preBooking->id)->first();
                        $preBookingAccess->enable = 1;
                        $preBookingAccess->update();
                    }
                }
                if ($request->rule_slug == "pre_booking") {
                    $postBooking = ParkingRulesName::where('slug', 'post_booking')->first();
                    if ($parkingRule->slug == "pre_booking" && $data['status'] == 1) {
                        $postBookingAccess = ParkingAccessRule::where('rule_id', $postBooking->id)->first();
                        $postBookingAccess->enable = 0;
                        $postBookingAccess->update();
                    } elseif ($parkingRule->slug == "pre_booking" && $data['status'] == 0) {
                        $postBookingAccess = ParkingAccessRule::where('rule_id', $postBooking->id)->first();
                        $postBookingAccess->enable = 1;
                        $postBookingAccess->update();
                    }
                }

                $parkingAccess->rule_id = $parkingRule->id;
                $parkingAccess->enable = $data['status'];
                $parkingAccess->device_direction = $data['device_direction'];
                $parkingAccess->barcode_type = $data['barcode_type'];
                $parkingAccess->plate_match_mode = $data['plate_match_mode'];
                $parkingAccess->update();
                if ($parkingAccess->exists()) {
                    $parkingAccess->update();
                }
                $parkingAccess->save();
            }
            // $data['language_live_id'] = $language->live_id;
            // $location = LocationOptions::find(1);
            // $locationId = $location->live_id;
            // $user_id = auth()->user()->live_id;
            // $Key = base64_encode($locationId . '_' . $user_id);
            // $responseData['success'] = 0;
            // try {
            //     $http = new Client();
            //     $response = $http->post(env('API_BASE_URL') . '/api/update-location-data', [
            //         'form_params' => [
            //             'token' => $Key,
            //             'data' => $data
            //         ],
            //     ]);
            //     $responseData = json_decode((string) $response->getBody(), true);
            // } catch (Exception $ex) {
            //     $error_log = new \App\Http\Controllers\LogController();
            //     $error_log->log_create('update-location-data', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            // }
            if ($parkingRule) {
                Session::flash('heading', 'Success!');
                Session::flash('message', __('access_rules.update_message'));
                Session::flash('icon', 'success');
                return redirect('manage-rules');
            }
        } catch (Exception $ex) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();;
        }
    }
    public function delete($id)
    {
        $parkingRule = ParkingRulesName::where('id', $id)->first();
        if ($parkingRule) {
            $parkingRule->delete();
            Session::flash('heading', 'Success!');
            Session::flash('message', __('access_rules.delete_message'));
            Session::flash('icon', 'success');
            return redirect('manage-rules');
        }
    }
    public function ruleSorting()
    {
        $accessRules = ParkingAccessRule::where('enable', 1)->get();
        $disableRules = ParkingRulesName::whereIn('slug', ['comfort_security_check', 'has_always_access', 'pre_booking', 'post_booking'])->orderBy('rule_sorting', 'ASC')->pluck('id')->all();
        $rules  = ParkingRulesName::whereHas('access', function ($query) {
            $query->where('enable', 1)->where('slug', '!=', 'matching_enable');
        })->whereNull('is_imported')->orWhere('is_imported', 0)->orderBy('rule_sorting', 'ASC')->get();
        return view('manage-rules.order', compact('rules'));
    }
    public function updateSorting(Request $request)
    {
        $validator = Validator::make($request->all(), []);
        if ($validator->passes()) {
            try {
                $data = $request->all();
                $items = explode(",", $data['itemOrder']);
                foreach ($items as $index => $item) {
                    $rule = ParkingRulesName::find($item);
                    if ($rule) {
                        $orderNumber = $index + 1;
                        $rule->rule_sorting = $orderNumber;
                        $rule->save();
                    }
                }
                return response()->json([
                    'success' => array(
                        'updated' => 'Devices has been Sorted Successfully.'
                    )
                ]);
            } catch (Exception $ex) {
                return $ex->getLine();
                $error_log = new LogController();
                $error_log->log_create('update_devices_ordering', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine());

                return response()->json(['error' => $validator->errors()->all()]);
            }
        } else {
            return response()->json(['error' => $validator->errors()->all()]);
        }
    }
}
