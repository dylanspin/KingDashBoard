<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;

class SettingsController extends BaseController {

    public function health_check(Request $request) {
        try {
            $response_data = array();
            return $this->sendResponse($response_data, 'Success');
        } catch (\Exception $ex) {
            return $this->sendError('Exception', $ex->getMessage());
        }
    }

}
