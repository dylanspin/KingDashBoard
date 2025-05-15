<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AvailableDevices;
use App\Group;
use App\GroupDevices;
use App\Promo;
use Illuminate\Support\Facades\Session;

class GroupController extends Controller {

    public $controller = 'App\Http\Controllers\GroupController';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewgroup($id)
    {
         $promos = Promo::with('promo_type', 'bookings.customer')->where('id', '=', $id)->first();
         return view('groups.view', compact('promos'));
    }
     
    public function editGroupMember($booking_id)
     {
        $booking= \App\Bookings::find($booking_id);
        return response()->json($booking);
     
     }
    public function updateGroupMember(Request $request){
        $booking=Bookings::where('id',$request->booking_number)->first();
        if(!empty($booking)){
            $booking->vehicle_num=$request->plate;
            $booking->checkin_time= Carbon::parse($request->check_in)->format('Y-m-d H:i:s');
            $booking->checkout_time= Carbon::parse($request->check_out)->format('Y-m-d H:i:s');
            $booking->save();
            Session::flash('heading', 'Success!');
            Session::flash('success', __('Update Successfully'));
            Session::flash('icon', 'success');
            return redirect()->back(); 
        }
        else{
            Session::flash('heading', 'Success!');
            Session::flash('success', __('No Record Find'));
            Session::flash('icon', 'success');
            return redirect()->back();
        }
        
         
    }
    public function index() {
        $groups = array();
        $groups_db = Group::with('group_devices')->get();
        if ($groups_db->count() > 0) {
            foreach ($groups_db as $group) {
                $array = array();
                $is_first = TRUE;

                $devices_names = 'N/A';
                $group_devices = $group->group_devices;
                if ($group_devices->count() > 0) {
                    foreach ($group_devices as $devices) {
                        $device_details = \App\LocationDevices::find($devices->device_id);
                        if (!$device_details) {
                            continue;
                        }
                        if ($is_first) {
                            $devices_names = $device_details->device_name;
                            $is_first = FALSE;
                            continue;
                        }
                        $devices_names .= ', ' . $device_details->device_name;
                    }
                }
                $array['id'] = $group->id;
                $array['name'] = $group->name;
                $array['devices'] = $devices_names;
                $groups[] = (object) $array;
            }
        }
        return view('groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
        $devices = \App\LocationDevices::where('available_device_id', '<>', 4)->get();
        return view('groups.create', compact('devices'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
        try {
            $this->validate($request, [
                'name' => 'required',
                'device' => 'required',
            ]);
            $data = $request->all();
            $group = Group::where([
                        ['name', $data['name']]
                    ])->first();
            if ($group == null) {
                $addGroup = new Group();
                $addGroup->name = $data['name'];
                if(array_key_exists('has_anti_pass_back', $data) && $data['has_anti_pass_back']){
                    $addGroup->has_anti_pass_back = 1;
                }
                if (array_key_exists('no_of_vehicle', $data) && $data['no_of_vehicle']) {
                    $addGroup->group_max = $request->no_of_vehicle;
                }
                $addGroup->save();

                $groupId = $addGroup->id;

                foreach ($data['device'] as $device) {
                    $groupDevices = new GroupDevices();
                    $groupDevices->group_id = $groupId;
                    $groupDevices->device_id = $device;
                    $groupDevices->save();
                }

                Session::flash('heading', 'Success!');
                Session::flash('message', __('groups.group_added'));
                Session::flash('icon', 'success');
                return redirect('group');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('groups.group_already_added'));
                Session::flash('icon', 'error');
                return redirect('group');
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function show($id) {
//        //
//    }
//
//    /**
//     * Show the form for editing the specified resource.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
    public function edit($id) {
        //
        $group = Group::with('group_devices')->findOrfail($id);
        $groupr_devices = array();
        if ($group && $group->group_devices->count() > 0) {
            foreach ($group->group_devices as $device) {
                $groupr_devices[] = $device->device_id;
            }
        }
        $devices = \App\LocationDevices::where('available_device_id', '<>', 4)->get();
        return view('groups.edit', compact('groupr_devices', 'devices', 'group'));
    }

//    /**
//     * Update the specified resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
    public function update(Request $request, $id) {
        //
        try {
            $this->validate($request, [
                'name' => 'required',
                'device' => 'required',
            ]);
            $data = $request->all();
            $group = Group::find($id);
            if ($group != null) {
                $group->name = $data['name'];
                if(array_key_exists('has_anti_pass_back', $data) && $data['has_anti_pass_back']){
                    $group->has_anti_pass_back = 1;
                }
                else{
                    $group->has_anti_pass_back = 0;
                }
                if (array_key_exists('no_of_vehicle', $data) && $data['no_of_vehicle']) {
                    $group->group_max = $request->no_of_vehicle;
                } else {
                    $group->group_max = null;
                }
                $group->save();

                $groupDevices = GroupDevices::where([
                            ['group_id', '=', $id]
                        ])->get();
                foreach ($groupDevices as $groupDevice) {
                    $groupDevice->delete();
                }

                foreach ($data['device'] as $device) {
                    $groupDevice = new GroupDevices();
                    $groupDevice->group_id = $id;
                    $groupDevice->device_id = $device;
                    $groupDevices->total_spots = $request->no_of_vehicle;
                    $groupDevice->save();
                }

                Session::flash('heading', 'Success!');
                Session::flash('message', __('groups.group_updated'));
                Session::flash('icon', 'success');
                return redirect('group');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('groups.group_not_found'));
                Session::flash('icon', 'error');
                return redirect('group');
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }

//    /**
//     * Remove the specified resource from storage.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
    public function destroy($id) {
        //
        try {
            $group = Group::find($id);
            if ($group != null) {

                $group->delete();

                $groupDevices = GroupDevices::where([
                            ['group_id', '=', $id]
                        ])->get();
                foreach ($groupDevices as $groupDevice) {
                    $groupDevice->delete();
                }

                Session::flash('heading', 'Success!');
                Session::flash('message', __('groups.group_deleted'));
                Session::flash('icon', 'success');
                return redirect('group');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('groups.group_not_found'));
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

}
