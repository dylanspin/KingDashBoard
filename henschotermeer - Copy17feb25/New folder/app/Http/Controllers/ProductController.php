<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Barcode;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller {

    public function person_ticket(Request $request) {
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

    public function day_ticket(Request $request) {
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

    public function person_ticket_store(Request $request) {
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

    public function day_ticket_store(Request $request) {
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

}
