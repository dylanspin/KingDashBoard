<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Order;
use App\UserRequest;
use Illuminate\Support\Str;

class LoginController extends BaseController {

    public function verify_user(Request $request) {
        try {
            $data = $request->all();
            if (Auth::attempt(array('email' => $data['email'], 'password' => $data['password']))) {
                $user = Auth::user();
                if(!$user->hasRole('operator') && !$user->hasRole('manager')){
                    return $this->sendError('Exception', 'Not valid user');
                }
                if ($user->api_token == null) {
                    $user->api_token = Str::random(60);
                    $user->save();
                }
                $response_data = array(
                    'user' => $user->name,
                    'email' => $user->email,
                    'api_token' => $user->api_token,
                    'user_id' => $user->id,
                    'joining_data' => date('F Y', strtotime($user->created_at)),
                    'profile' => $user->profile
                );
                return $this->sendResponse($response_data, 'User verified successfully.');
            } else {
                return $this->sendError('Exception', 'could not find record');
            }
        } catch (\Exception $ex) {
            return $this->sendError('Exception', $ex->getMessage());
        }
    }

}
