<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Barcode;
use App\LocationOptions;
use App\Products;
use Illuminate\Support\Facades\Session;
use Exception;

class ProductController extends Controller
{

    public function index()
    {
        $products = Products::all();
        return view('products.index', [
            'products' => $products
        ]);
    }
    public function create()
    {
        return view('products.create');
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'title_nl' => 'required',
            'price' => 'required'
        ]);
        try {
            $is_added = FALSE;
            $product_settings = Products::where('type', $request->type)->first();
            if (!$product_settings) {
                $is_added = TRUE;
                $product_settings = new Products();
            }
            $location = LocationOptions::find(1);
            $locationId = $location->live_id;
            $user_id = auth()->user()->live_id;
            $Key = base64_encode($locationId . '_' . $user_id);
            $responseData['success'] = 0;
            try {
                $data = $request->all();
                $http = new Client();
                $response = $http->post(env('API_BASE_URL') . 'api/update-product-data', [
                    'form_params' => [
                        'token' => $Key,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
            } catch (Exception $ex) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('update-product-data', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine(), $this->controller);
            }
            $product_settings->type = $request->type;
            $product_settings->title = $request->title;
            $product_settings->title_nl = $request->title_nl;
            $product_settings->price = $request->price;
            if ($request->ticket_count) {
                $product_settings->no_of_time = 150;
            }
            if ($request->vehicle_count) {
                $product_settings->no_of_vehicle = 150;
            }
            $product_settings->save();
            Session::flash('heading', 'Success!');
            if ($is_added) {
                if ($responseData['success'] && isset($responseData['data'])) {
                    $product_settings->live_id = $responseData['data']['id'];
                    $product_settings->update();
                }
                Session::flash('message', __('products.product_added'));
            } else {
                if ($responseData['success'] && isset($responseData['data'])) {
                    $product_settings->live_id = $responseData['data']['id'];
                    $product_settings->update();
                }
                Session::flash('message', __('products.already_exist'));
            }
            Session::flash('icon', 'success');
            return redirect('/products');
        } catch (Exception $ex) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }
    public function edit($id)
    {
        $product = Products::find($id);
        return view('products.edit', [
            'product' => $product
        ]);
    }
    public function update(Request $request)
    {

        $this->validate($request, [
            'title' => 'required',
            'title_nl' => 'required',
            'price' => 'required'
        ]);
        try {
            $is_updated = FALSE;
            $product_settings = Products::where('id', $request->product_id)->first();
            if (!$product_settings) {
                $is_updated = TRUE;
                $product_settings = new Products();
            }
            $location = LocationOptions::find(1);
            $locationId = $location->live_id;
            $user_id = auth()->user()->live_id;
            $Key = base64_encode($locationId . '_' . $user_id);
            $responseData['success'] = 0;
            try {
                $data = $request->all();
                $http = new Client();
                $response = $http->post(env('API_BASE_URL') . '/api/update-product-data', [
                    'form_params' => [
                        'token' => $Key,
                        'data' => $data
                    ],
                ]);
                $responseData = json_decode((string) $response->getBody(), true);
            } catch (Exception $ex) {
                $error_log = new \App\Http\Controllers\LogController();
                $error_log->log_create('update-product-data', $ex->getMessage(), $ex->getTraceAsString(), $ex->getLine());
            }
            $product_settings->type = $request->type;
            $product_settings->title = $request->title;
            $product_settings->title_nl = $request->title_nl;
            $product_settings->price = $request->price;
            if (isset($request->ticket_count) && $request->ticket_count) {
                $product_settings->no_of_time = 150;
            } else {
                $product_settings->no_of_time = null;
            }
            if (isset($request->no_of_vehicle) && $request->vehicle_count) {
                $product_settings->no_of_vehicle = 150;
            } else {
                $product_settings->no_of_vehicle = null;
            }
            if ($is_updated) {
                $product_settings->save();
            } else {
                $product_settings->update();
            }
            
            Session::flash('heading', 'Success!');
            if ($is_updated) {
                if ($responseData['success'] && isset($responseData['data'])) {
                    $product_settings->live_id = $responseData['data']['id'];
                    $product_settings->update();
                }
                Session::flash('message', __('products.product_added'));
            } else {
                if ($responseData['success'] && isset($responseData['data'])) {
                    $product_settings->live_id = $responseData['data']['id'];
                    $product_settings->update();
                }
                Session::flash('message', __('products.product_updated'));
            }
            Session::flash('icon', 'success');
            return redirect('/products');
        } catch (\Exception $ex) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }
    public function person_ticket(Request $request)
    {
        $title = NULL;
        $price = NULL;
        $title_nl = NULL;
        $product_settings = \App\Products::where('type', 'person_ticket')->first();
        if ($product_settings) {
            $title = $product_settings->title;
            $title_nl = $product_settings->title_nl;
            $price = $product_settings->price;
        }
        return view('products.person_ticket', [
            'title' => $title,
            'title_nl' => $title_nl,
            'price' => $price,
        ]);
    }

    public function day_ticket(Request $request)
    {
        $title = NULL;
        $price = NULL;
        $title_nl = NULL;
        $product_settings = \App\Products::where('type', 'day_ticket')->first();
        if ($product_settings) {
            $title = $product_settings->title;
            $title_nl = $product_settings->title_nl;
            $price = $product_settings->price;
        }
        return view('products.day_ticket', [
            'title' => $title,
            'title_nl' => $title_nl,
            'price' => $price,
        ]);
    }

    public function person_ticket_store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'title_nl' => 'required',
            'price' => 'required'
        ]);
        try {
            $is_added = FALSE;
            $product_settings = \App\Products::where('type', 'person_ticket')->first();
            if (!$product_settings) {
                $is_added = TRUE;
                $product_settings = new \App\Products();
            }
            $product_settings->type = 'person_ticket';
            $product_settings->title = $request->title;
            $product_settings->title_nl = $request->title_nl;
            $product_settings->price = $request->price;
            $product_settings->save();
            Session::flash('heading', 'Success!');
            if ($is_added) {
                Session::flash('message', __('products.person_ticket_added'));
            } else {
                Session::flash('message', __('products.person_ticket_updated'));
            }
            Session::flash('icon', 'success');
            return redirect('/products/person_ticket');
        } catch (\Exception $ex) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }

    public function day_ticket_store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'title_nl' => 'required',
            'price' => 'required'
        ]);
        try {
            $is_added = FALSE;
            $product_settings = \App\Products::where('type', 'day_ticket')->first();
            if (!$product_settings) {
                $is_added = TRUE;
                $product_settings = new \App\Products();
            }
            $product_settings->type = 'day_ticket';
            $product_settings->title = $request->title;
            $product_settings->title_nl = $request->title_nl;
            $product_settings->price = $request->price;
            $product_settings->save();
            Session::flash('heading', 'Success!');
            if ($is_added) {
                Session::flash('message', __('products.day_ticket_added'));
            } else {
                Session::flash('message', __('products.day_ticket_updated'));
            }
            Session::flash('icon', 'success');
            return redirect('/products/day_ticket');
        } catch (\Exception $ex) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }
    public function delete($id)
    {
        $product = Products::find($id);
        $is_delete = false;
        if ($product) {
            $is_delete = true;
            $product->delete();
        }
        Session::flash('heading', 'Success!');
        if ($is_delete) {
            Session::flash('message', __('products.product_delete'));
        } else {
            Session::flash('message', __('products.not_deleted'));
        }
        Session::flash('icon', 'error');
        return redirect('/products');
    }
}
