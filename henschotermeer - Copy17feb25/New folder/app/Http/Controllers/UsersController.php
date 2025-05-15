<?php

namespace App\Http\Controllers;

use App\Profile;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Cache\RetrievesMultipleKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use File;
use Illuminate\Support\Str;

class UsersController extends Controller {

    public $controller = 'App\Http\Controllers\UsersController';

    public function getIndex() {
        $valid_users = array();
        $users = User::get();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                if ($user->hasRole('admin') || $user->hasRole('service')) {
                    continue;
                }
                $valid_users[] = $user;
            }
        }

        return view('users.index', [
            'users' => $valid_users
        ]);
    }

    public function create() {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function save(Request $request) {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required',
        ]);
        $user = User::firstOrCreate(['name' => $request->name, 'email' => $request->email]);
        $user->status = 1;
        $user->password = bcrypt($request->password);
        $user->api_token = Str::random(60);
        $user->save();
        $profile = $user->profile;
        if ($user->profile == null) {
            $profile = new Profile();
        }
        $profile->user_id = $user->id;
        $profile->save();
        $role = Role::find($request->role);
        $user->assignRole($role->name);
        Session::flash('message', 'User has been added');
        return redirect('users');
    }

    public function edit(Request $request) {
        $user = User::findOrfail($request->id);
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request) {

        $this->validate($request, [
            'name' => 'required',
            'role' => 'required',
        ]);
        if (!empty($request->password)) {
            $this->validate($request, [
                'password' => 'required|min:6|confirmed',
            ]);
        }
        $user = User::findOrfail($request->id);

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        if($user->api_token == null){
            $user->api_token = Str::random(60);
        }
        $user->name = $request->name;
        $user->save();

        $profile = $user->profile;
        if ($user->profile == null) {
            $profile = new Profile();
        }
        $profile->user_id = $user->id;
        $profile->save();
        $role = Role::find($request->role);
        \Illuminate\Support\Facades\DB::table('role_user')->where('user_id', $user->id)->delete();
        $user->assignRole($role->name);

        Session::flash('message', 'User has been updated');
        return redirect('users');
    }

    public function delete($id) {
        $user = User::findOrfail($id);
        $user->delete();
        Session::flash('message', 'User has been deleted');
        return back();
    }

    public function getDeletedUsers() {
        $users = User::onlyTrashed()->get();
        return view('users.deleted', compact('users'));
    }

    public function restoreUser(Request $request) {
        $user = User::onlyTrashed()->where('id', '=', $request->id);
        $user->restore();
        Session::flash('message', 'User has been restored');
        return back();
    }

    public function getSettings() {
        $user = auth()->user();
        return view('users.account-settings', compact('user'));
    }

    public function saveSettings(Request $request) {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'pic_file' => 'mimes:jpeg,png,bmp,tiff |max:4096',
        ]);

        $user = auth()->user();

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        if($user->api_token == null){
            $user->api_token = Str::random(60);
        }
        $user->email = $request->email;
        $user->name = $request->name;
        $user->save();

        $profile = $user->profile;
        if ($user->profile == null) {
            $profile = new Profile();
        }
        if ($request->dob != null) {
            $date = date('Y-m-d', strtotime($request->dob));
        } else {
            $date = $request->dob;
        }


        if ($file = $request->file('pic_file')) {
            $extension = $file->extension() ?: 'png';
            $destinationPath = public_path('/uploads/users');
            $safeName = str_random(10) . '.' . $extension;
            $file->move($destinationPath, $safeName);
            //delete old pic if exists
            if (File::exists($destinationPath . $user->pic)) {
                File::delete($destinationPath . $user->pic);
            }
            //save new file path into db
            $profile->pic = $safeName;
        }


        $profile->user_id = $user->id;
        $profile->bio = $request->bio;
        $profile->gender = $request->gender;
        $profile->dob = $date;
        $profile->country = $request->country;
        $profile->state = $request->state;
        $profile->city = $request->city;
        $profile->address = $request->address;
        $profile->postal = $request->postal;
        $profile->save();

        Session::flash('message', __('user.account_update'));
        return redirect()->back();
    }

}
