<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\EmailBounce;

class EmailBounceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search_type = '';
        $search_val = '';
        $email_bounces = EmailBounce::sortable();
        if ((isset($request->search_type) && !empty($request->search_type)) || (isset($request->search_val) && !empty($request->search_val))) {
            if (isset($request->search_btn)) {
                $search_type = $request->search_type;
                $search_val = $request->search_val;
                if (!empty($request->search_type)) {
                    if ($request->search_type == 'email') {
                        $customers = $customers->where('email', 'LIKE', "%{$request->search_val}%");
                    } else {
                        $customers = $customers->where('email', 'LIKE', "%{$request->search_val}%");
                    }
                } else {
                    $customers = $customers->where('email', 'LIKE', "%{$request->search_val}%");
                }
            }
        }
        $email_bounces = $email_bounces->orderBy('created_at', 'desc')
                ->paginate(25);
        return view('email-bounce.index', compact('email_bounces', 'search_type', 'search_val'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    }
}
