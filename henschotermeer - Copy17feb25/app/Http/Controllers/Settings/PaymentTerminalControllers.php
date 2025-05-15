<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use GuzzleHttp\Client;

class PaymentTerminalControllers extends Controller {

    public function __construct() {
        
    }

    public function get_products(Request $request, $key, $id = null) {
        try {
            $settings = new Settings();
            $valid_settings = $settings->is_valid_call($key, $id);
            if (!$valid_settings) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Access ',
                    'data' => FALSE,
                );
            }
            $record = \App\LocationOptions::first();
            if (!$record) {
                return array(
                    'status' => 0,
                    'message' => 'Invalid Access',
                    'data' => FALSE,
                );
            }
            $products_details = array();
            $products = \App\Products::get();
            if ($products->count() > 0) {
                foreach ($products as $product) {
                    $products_details[] = $product;
                }
            }
            return array(
                'status' => 1,
                'message' => 'Success',
                'data' => $products_details,
                'hourly_price' => $record->price_per_hour ? "$record->price_per_hour" : "2.5"
            );
        } catch (\Exception $ex) {
            return array(
                'status' => 0,
                'message' => $ex->getMessage(),
                'data' => FALSE,
            );
        }
    }

}
