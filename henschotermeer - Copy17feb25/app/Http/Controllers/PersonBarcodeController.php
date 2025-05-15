<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Barcode;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;

class PersonBarcodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $barcodes = Barcode::with('bookings')->where('type', 'person')->get();
        return view('barcode.person.index', compact('barcodes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('barcode.person.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'barcode' => 'required'
        ]);
        try {
            $data = $request->all();
            $barcode = new Barcode();
            $barcode->type = 'person';
            $barcode->barcode = $data['barcode'];
             if (!empty($request->name)) {
                $barcode->name = $data['name'];
            }
            if($data['message'] != ''){
                $barcode->message = $data['message'];
            }
            if (!empty($request->valid_till)) {
                $barcode->valid_till = $data['valid_till'];
            }
            if (!empty($request->multiple_times)) {
                $barcode->use_barcode_multiple_time = 1;
            }
            $barcode->save();

            Session::flash('heading', 'Success!');
            Session::flash('message', __('barcode.barcode_add'));
            Session::flash('icon', 'success');
            return redirect('person/barcode');
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $barcode = Barcode::find($id);
        return view('barcode.person.edit', compact('barcode'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $this->validate($request, [
            'barcode' => 'required'
        ]);
        try {
            $data = $request->all();
            $barcode = Barcode::find($id);
            $barcode->barcode = $data['barcode'];
             if (!empty($request->name)) {
                $barcode->name = $data['name'];
            }
            if($data['message'] != ''){
                $barcode->message = $data['message'];
            }
            if (!empty($request->multiple_times)) {
                $barcode->use_barcode_multiple_time = true;
            } else {
                $barcode->use_barcode_multiple_time = null;
            }
            $barcode->save();

            Session::flash('heading', 'Success!');
            Session::flash('message', __('barcode.barcode_update'));
            Session::flash('icon', 'success');
            return redirect('person/barcode');
        } catch (\Exception $e) {
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
    public function destroy($id)
    {
        //
        try {
            $barcode = Barcode::find($id);
            if ($barcode) {
                $barcode->forceDelete();
                Session::flash('heading', 'Success!');
                Session::flash('message', __('barcode.barcode_delete'));
                Session::flash('icon', 'success');
                return redirect('person/barcode');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('barcode.barcode_not_found'));
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
     * Download the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function download($id, $locale) {
        //
        try {
            $current_locale = \App::getLocale();
            \App::setLocale($locale);
            $barcode = Barcode::find($id);
            if (!$barcode) {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('barcode.barcode_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back();
            }
            $barcode_number = $barcode->barcode;
            $location_details = \App\LocationOptions::first();
            if (!$location_details) {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('barcode.barcode_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back();
            }
            $location_timings = \App\LocationTimings::where('is_whitelist', 0)
                    ->where('is_person', 1)->get();
            if (!$location_timings) {
                Session::flash('heading', 'Error!');
                Session::flash('message', __('barcode.barcode_not_found'));
                Session::flash('icon', 'error');
                return redirect()->back();
            }
            $location_timings_array = array();
            foreach ($location_timings as $location_timing) {
                if ($location_timing->week_day_num == 0) {
                    $day = __('pdf.sunday');
                } elseif ($location_timing->week_day_num == 1) {
                    $day = __('pdf.monday');
                } elseif ($location_timing->week_day_num == 2) {
                    $day = __('pdf.tuesday');
                } elseif ($location_timing->week_day_num == 3) {
                    $day = __('pdf.wednesday');
                } elseif ($location_timing->week_day_num == 4) {
                    $day = __('pdf.thursday');
                } elseif ($location_timing->week_day_num == 5) {
                    $day = __('pdf.friday');
                } elseif ($location_timing->week_day_num == 6) {
                    $day = __('pdf.saturday');
                }

                $location_timings_array[] = (object) array(
                            'day' => $day,
                            'start' => $location_timing->opening_time,
                            'end' => $location_timing->closing_time,
                );
            }
            $pdf = \PDF::loadView('barcode.person.card', [
                'barcode' => $barcode,
                'location_details' => $location_details,
                'barcode_number' => $barcode_number,
                'location_timings_array' => $location_timings_array
            ])->setPaper([0, 0, 263.78, 200.91], 'potrait');
            \App::setLocale($current_locale);
            return $pdf->stream('BarCode.pdf');
        } catch (\Exception $e) {
             Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }
}
