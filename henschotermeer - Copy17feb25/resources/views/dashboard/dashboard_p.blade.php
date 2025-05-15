@extends('layouts.master')

@push('css')
<link href='{{asset('plugins/components/fullcalendar/fullcalendar.css')}}' rel='stylesheet'>
<link href="{{asset('plugins/components/Magnific-Popup-master/dist/magnific-popup.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/owl.carousel/owl.carousel.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('plugins/components/owl.carousel/owl.theme.default.css')}}" rel="stylesheet" type="text/css"/>
<style>
    .carousel-inner img {
        width: 100%!important;
        /*height: 283px!important;*/
    }
    .temp-widget .left-part {
        width: 35%;
    }
    .temp-widget .right-part {
        margin-left: 35%;
        padding: 17px 17px 17px 17px;
    }
    .carousel .item{
        max-height: 100px;
    }
    .expected_booking_details,.arrived_booking_details{
        background-color: white;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 11111;
        padding-top: 10px;
        border: 1px solid #e5ebec;
        border-radius: 4px;
    }
    .cursor-pointer{
        cursor:pointer;
    }
    .small-box-widget .fa-chevron-down{
        position: absolute;
        top:5px;
        right:5px;

    }
    .device_item .bg-primary{
        height: 188px;
    }
    .carousel-control span {
        top: 42%!important;
        font-size: 16px!important;
    }
    .fa-warning{
        color:#dc3545!important
    }
    .text-out-of-order{
        font-weight: bolder!important;
        font-size: 14px;
    }
    .text-out-of-order {
        animation: blinker 2s linear infinite;
    }

    @keyframes blinker {
        90% {
            opacity: 0;
        }
    }
    .header-device-section{
        font-size: 20px!important;
        background-color: #4f5467;
    }
    .device_item .bg-primary{
        border-radius: 10px 10px 0px 0px;
    }
    .device_item .bg-danger-device{
        border-radius: 10px 10px 0px 0px;
        height: 188px;
    }
    .device_item .bg-primary .header-device-section{
        border-radius: 10px 10px 0px 0px;
    }
    .device_item .bg-danger-device .header-device-section{
        border-radius: 10px 10px 0px 0px;
    }
    .fa-warning{
        color: #dcd735!important;
    }
    .overlay_open_gate{
        position: absolute;
        top:0px;
        left:0px;
        bottom: 0px;
        right: 0px;
        background-color: #456bb338;
        z-index: 1;
    }
    .overlay_open_gate .fa{
        font-size: 50px;
        margin-left: 45%;
        margin-top: 16%;
        color: #456bb3;
    }
    .transactions_container{
        font-size: 12px;
    }
</style>
<style>
    .carousel-inner img {
        width: 100%!important;
        /*height: 283px!important;*/
    }
    .temp-widget .left-part {
        width: 35%;
    }
    .temp-widget .right-part {
        margin-left: 35%;
        padding: 17px 17px 17px 17px;
    }
    .carousel .item{
        max-height: 100px;
    }
    .carousel .item img{
        height:100px!important;
    }
    .expected_booking_details,.arrived_booking_details{
        background-color: white;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 11111;
        padding-top: 10px;
        border: 1px solid #e5ebec;
        border-radius: 4px;
    }
    .cursor-pointer{
        cursor:pointer;
    }
    .small-box-widget .fa-chevron-down{
        position: absolute;
        top:5px;
        right:5px;
    }
    .device_item .bg-primary{
        height: 188px;
    }
    .carousel-control span {
        top: 42%!important;
        font-size: 16px!important;
    }
    .fa-warning{
        color:#dc3545!important
    }
    .text-out-of-order{
        font-weight: bolder!important;
        font-size: 14px;
    }
    .text-out-of-order {
        animation: blinker 2s linear infinite;
    }
    @keyframes blinker {
        90% {
            opacity: 0;
        }
    }
    .header-device-section{
        font-size: 20px!important;
        background-color: #4f5467;
    }
    .device_item .bg-primary{
        border-radius: 10px 10px 0px 0px;
    }
    .device_item .bg-danger-device{
        border-radius: 10px 10px 0px 0px;
        height: 188px;
    }
    .device_item .bg-primary .header-device-section{
        border-radius: 10px 10px 0px 0px;
    }
    .device_item .bg-danger-device .header-device-section{
        border-radius: 10px 10px 0px 0px;
    }
    .fa-warning{
        color: #dcd735!important;
    }
    .overlay_open_gate{
        position: absolute;
        top:0px;
        left:0px;
        bottom: 0px;
        right: 0px;
        background-color: #456bb338;
        z-index: 1;
    }
    .overlay_open_gate .fa{
        font-size: 50px;
        margin-left: 45%;
        margin-top: 16%;
        color: #456bb3;
    }
    .btn-open-gate-group{
        background-color: white!important;
        color: #456bb3!important;
    }
    .btn-open-gate-group:hover{
        /*border: 2px solid #0283cc;*/
        opacity: .8;
    }
    .open_con .dropdown-menu>li>a {
        padding: 3px 15px;
    }
    .btn-close-gate-group{
        background-color: white!important;
        color: #456bb3!important;
    }
    .btn-close-gate-group:hover{
        /*border: 2px solid #0283cc;*/
        opacity: .8;
    }
    .close_con .dropdown-menu>li>a {
        padding: 3px 15px;
    }
    ul.dropdown-menu:hover{
        /*visibility: visible;*/
    }
    div.btn-danger:hover {
        opacity: 1; 
    }
    div.btn-primary:hover {
        opacity: 1; 
    }
    .barrier-open-lock{
        position:absolute;
        top:5px;
        right:30%;
    }
    .barrier-close-lock{
        position:absolute;
        top:23px;
        right:30%;
    }
    @media (max-width: 768px) {
        .barrier-open-lock{
            right:38%;
        }
        .barrier-close-lock{
            right:38%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid person_dashboard">
    <div class="row m-0">
        <div class="col-md-4 col-sm-12 col-xs-12">
            <div class="white-box bg-primary color-box color-white col-md-12 col-xs-12">
                <a href="#">
                    <div class="col-md-9 col-xs-9">
                        <h4 class="color-white">@lang('dashboard.bookings')</h4>
                        <h4 class="color-white">@lang('dashboard.arrivals_d')</h4>
                        <h4 class="color-white">@lang('dashboard.arrive_left')</h4>
                    </div>
                    <div class="col-md-3 col-xs-3 text-right widget1_con">
                        <h4 class="color-white">{{$widget_1->expected_bookings}}</h4>
						<h4 class="color-white">{{$widget_1->arrival_d}}</h4>
                        <h4 class="color-white">{{$widget_1->arrival_left}}</h4>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="white-box bg-success color-box color-white col-md-12 col-xs-12">
                <a href="#">
                    <div class="col-md-7 col-xs-7">
                        <h4 class="color-white">@lang('dashboard.on_location')</h4>
                        <h4 class="color-white">@lang('dashboard.total_bookings')</h4>
                        <h4 class="color-white">@lang('dashboard.booking_left')</h4>
                    </div>
                    <div class="col-md-5 col-xs-5 text-right widget2_con">
                        <h4 class="color-white">{{$widget_2->on_location}}</h4>
                        <h4 class="color-white">{{$widget_2->total}}</h4>
                        <h4 class="color-white">{{$widget_2->booking_left}}</h4>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="white-box bg-danger color-box col-md-12">
                <a href="#">
                    <div class="col-md-7 col-xs-7">
                        <h4 class="color-white">@lang('dashboard.total_spaces')</h4>
                        <h4 class="color-white">@lang('dashboard.available_spots')</h4>
                        <h4 class="color-white">@lang('dashboard.online_spots')</h4>
                    </div>
                    <div class="col-md-5 col-xs-5 text-right widget3_con">
                        <h4 class="color-white">{{$widget_1->total_spots}}</h4>
                        <h4 class="color-white">{{$widget_1->available_spots}}</h4>
                        <h4 class="color-white">{{$widget_1->online_person_spot}}</h4>
                    </div>
                </a>
            </div>
<!--            <div class="white-box bg-danger color-box col-md-12 widget3_con">
                @if(count($widget_3->device_alerts) > 0)
                <a href="{{url('details/widget3')}}">
                    @foreach($widget_3->device_alerts as $data)
                    <div class="col-md-6 col-xs-6">
                        <h4 class="{{$data->status == 0 ? 'color-red' : 'color-yellow'}} wrap-word-custom" 
                            data-toggle="tooltip" 
                            title="{{strtoupper($data->location_devices->device_name ? $data->location_devices->device_name : 'N/A')}}"
                            style="margin:6px 0;">{{$data->location_devices->device_name ? $data->location_devices->device_name : 'N/A'}}</h4>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <h4 class="{{$data->status == 0 ? 'color-red' : 'color-yellow'}} wrap-word-custom" 
                            data-toggle="tooltip" 
                            title="{{$data->message ? $data->message : 'N/A'}}"
                            style="margin:6px 0;">{{$data->message ? $data->message : 'N/A'}}</h4>
                    </div>
                    @endforeach
                </a>
                @else
                <div class="col-md-12 col-xs-12 text-center pr-0">
                    <i class="fa fa-check f-23" > </i>
                    <h3 class="color-white">No Alerts</h3>
                </div>
                @endif
            </div>-->
        </div>

    </div>
    <div class="row m-0">
        <div class="col-md-12">
            <div class="white-box device_heading color-box col-md-12 mb-0 p-10">
                <div class="col-md-9">
                    <h4 class="color-white">Devices</h4>
                </div>
                <div class="col-md-3 devices_collapse_actions pt-10 text-right">
                    <i class="fa fa-plus f-24 color-white cursor-pointer devices_collapse_actions_show"  data-toggle="collapse" data-target="#devices_section"></i>
                </div>
            </div>
        </div>
    </div>
    <div  id="devices_section" class="row  collapse in  m-0">
        <div class="devices_section  pt-10 ml-15 mr-15 pull-left" >
            @if(count($devices) > 0)
            @foreach($devices as $device)
            @if($device['available_device_id'] == 2)
            <div class="col-md-4 col-sm-12 col-xs-12 mb-20 device_item device_{{$device['id']}}">
                <div class="bg-primary color-white col-md-12 col-xs-12 pb-15 pl-0 pr-0" >
                    <div class="col-md-12 col-xs-12 pl-0 pr-0 pt-5 pb-5 header-device-section">
                        <div class="col-sm-6 col-xs-6">
                            <h6 class="box-title text-left color-white f-17 wrap-word-custom" data-toggle="tooltip" title="{{strtoupper($device['name'])}}">{{strtoupper($device['name'])}}</h6> 
                        </div>
                        <div class="col-sm-6 col-xs-6">
                            <h6 class="box-title text-right cursor-pointer f-17 color-white wrap-word-custom">@lang('dashboard.transaction') <i class="fa fa-chevron-down show_transactions"></i><i class="fa fa-chevron-up hide_transactions hidden"></i></h6> 
                        </div>
                    </div>
                    <div class="transactions_con transactions_container hidden">
                        @if(count($device['transactions']) > 0)
                        <div class="col-md-9 col-xs-9">
                            @foreach($device['transactions'] as  $ticket_reader_device_transactions)
                            <div class="col-md-12 col-xs-12 p-0">
                                <a style="color:white!important" target="_blank" href="{{url('/transaction/1/'.$ticket_reader_device_transactions['id'])}}">{{$ticket_reader_device_transactions['content']}}</a>
                            </div>
                            @endforeach
                        </div>
                        <div class="col-md-3 col-xs-3 text-center pt-35">
                            <a target="_blank" href="/device_transaction/ptr/{{$device['id']}}"><button class="btn btn-primary-transactions">More</button></a>
                        </div>

                        @else
                        <div class="col-md-12 col-xs-12">
                            <p>No Transactions</p>
                        </div>
                        @endif
                    </div>
                    <div class="other_content">
                        <div class="col-md-6 col-xs-6 gates h-100">
                            @if($device['barrier_status'] >= 1)
                            <div 
                                class="barier-locked-opened barier-locked-opened-{{$device['id']}} text-center {{$device['barrier_status'] == 1 ? '' : 'hidden'}}" 
                                data-device_id="{{$device['id']}}"
                                style="position:relative">
                                <img 
                                    class="filter" 
                                    width="100" 
                                    src="{{asset('plugins/images/icons/bo.png')}}" 
                                    alt="Transaction">
                                <i class="fa fa-lock f-35 color-red barrier-open-lock"></i>
                            </div>   
                            <div 
                                class="barier-locked-closed barier-locked-closed-{{$device['id']}} text-center pt-20 {{$device['barrier_status'] == 2 ? '' : 'hidden'}}" 
                                data-device_id="{{$device['id']}}"
                                style="position:relative">
                                <img 
                                    class="filter" 
                                    width="100" 
                                    src="{{asset('plugins/images/icons/b.png')}}" 
                                    alt="Transaction">
                                <i class="fa fa-lock f-35 color-red barrier-close-lock"></i>
                            </div>  
                            <div 
                                class="barier-always-access barier-always-access-{{$device['id']}} text-center pt-20 {{$device['barrier_status'] == 3 ? '' : 'hidden'}}" 
                                data-device_id="{{$device['id']}}"
                                style="position:relative">
                                <img 
                                    class="filter" 
                                    width="100" 
                                    src="{{asset('plugins/images/icons/b.png')}}" 
                                    alt="Transaction">
                                <i class="fa fa-unlock-alt f-35 color-red barrier-close-lock"></i>
                            </div>
                            @else
                            <div class="barier-closed barier-closed-{{$device['id']}} text-center pt-20 {{!$device['is_opened'] ? '' : 'hidden'}}" data-device_id="{{$device['id']}}">
                                <img class="filter" 
                                     width="100" 
                                     src="{{asset('plugins/images/icons/b.png')}}" 
                                     alt="Transaction">
                            </div>   
                            <div class="barier-opened barier-opened-{{$device['id']}} text-center {{$device['is_opened'] ? '' : 'hidden'}}" data-device_id="{{$device['id']}}">
                                <img 
                                    class="filter" 
                                    width="100" 
                                    src="{{asset('plugins/images/icons/bo.png')}}" 
                                    alt="Transaction">
                            </div>  
                            @endif
                        </div>
                        <div class="col-md-6 col-xs-6">
                            @if($device['is_synched'])
                            <div class="col-md-12 col-xs-12 text-center pt-10 gate_open_spinner overlay_open_gate hidden pr-0">
                                <i class="fa fa-spinner fa-spin f-60  "> </i>
                            </div>
                            @if($device['is_opened'])
                            <div class="col-md-12 col-xs-12 open_con text-right pt-5 pr-0 pl-0">
                                <div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-open-gate-vehicle-active-con hidden">
                                    <button 
                                        type="button" 
                                        data-device_id="{{$device['id']}}" 
                                        class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate btn-open-gate-vehicle-active"
                                        style="">@lang('dashboard.open')</button>
                                    <button type="button" 
                                            class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" 
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($device['has_always_access'])
                                        <li style="{{$device['barrier_status'] == 3 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 3)"> @lang('dashboard.always_access')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.verify_access')</a>
                                        </li>
                                        <li>
                                            <hr style="margin:5px;" />
                                        </li>
                                        @endif
                                        <li style="{{$device['barrier_status'] == 1 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 1)"> @lang('dashboard.locked_open')</a>
                                        </li>
                                        <li style="{{$device['barrier_status'] == 2 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 2)"> @lang('dashboard.locked_closed')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.unlock')</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-12 col-xs-12 btn-group p-0 pull-right {{$device['barrier_status'] > 0 ? 'btn-danger' : 'btn-primary'}} btn-open-gate-vehicle-non-active">
                                    @if($device['barrier_status'] < 1)
                                    <button 
                                        type="button" 
                                        data-device_id="{{$device['id']}}" 
                                        class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate btn-open-gate-vehicle-active"
                                        style="">@lang('dashboard.open')</button>
                                    @else
                                    <button class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate">
                                        @if($device['barrier_status'] == 1)
                                        @lang('dashboard.locked_open')
                                        @elseif($device['barrier_status'] == 2)
                                        @lang('dashboard.locked_closed')
                                        @elseif($device['barrier_status'] == 3)
                                        @lang('dashboard.always_access')
                                        @else
                                        @lang('dashboard.open')
                                        @endif
                                    </button>
                                    @endif
                                    <button type="button" 
                                            class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" 
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($device['has_always_access'])
                                        <li style="{{$device['barrier_status'] == 3 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 3)"> @lang('dashboard.always_access')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.verify_access')</a>
                                        </li>
                                        <li>
                                            <hr style="margin:5px;" />
                                        </li>
                                        @endif
                                        <li style="{{$device['barrier_status'] == 1 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 1)"> @lang('dashboard.locked_open')</a>
                                        </li>
                                        <li style="{{$device['barrier_status'] == 2 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 2)"> @lang('dashboard.locked_closed')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.unlock')</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 close_con text-right pt-5 pr-0 pl-0">
                                <div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-close-gate-vehicle-active-con">
                                    <button 
                                        type="button" 
                                        data-device_id="{{$device['id']}}" 
                                        class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate btn-close-gate-vehicle-active"
                                        style="">@lang('dashboard.close')</button>
                                    <button type="button" 
                                            class="col-md-2 col-xs-2 btn btn-close-gate-group p-3 dropdown-toggle dropdown-toggle-split" 
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($device['has_always_access'])
                                        <li style="{{$device['barrier_status'] == 3 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 3)"> @lang('dashboard.always_access')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.verify_access')</a>
                                        </li>
                                        <li>
                                            <hr style="margin:5px;" />
                                        </li>
                                        @endif
                                        <li style="{{$device['barrier_status'] == 1 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 1)"> @lang('dashboard.locked_open')</a>
                                        </li>
                                        <li style="{{$device['barrier_status'] == 2 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 2)"> @lang('dashboard.locked_closed')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.unlock')</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-12 col-xs-12 btn-group p-0 pull-right {{$device['barrier_status'] > 0 ? 'btn-danger' : 'btn-primary'}} btn-close-gate-vehicle-non-active hidden">
                                    @if($device['barrier_status'] < 1)
                                    <button 
                                        type="button" 
                                        data-device_id="{{$device['id']}}" 
                                        class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate btn-close-gate-vehicle-active">@lang('dashboard.close')</button>
                                    @else
                                    <button class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate">
                                        @if($device['barrier_status'] == 1)
                                        @lang('dashboard.locked_open')
                                        @elseif($device['barrier_status'] == 2)
                                        @lang('dashboard.locked_closed')
                                        @elseif($device['barrier_status'] == 3)
                                        @lang('dashboard.always_access')
                                        @else
                                        @lang('dashboard.close')
                                        @endif
                                    </button>
                                    @endif
                                    <button type="button" 
                                            class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" 
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($device['has_always_access'])
                                        <li style="{{$device['barrier_status'] == 3 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 3)"> @lang('dashboard.always_access')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.verify_access')</a>
                                        </li>
                                        <li>
                                            <hr style="margin:5px;" />
                                        </li>
                                        @endif
                                        <li style="{{$device['barrier_status'] == 1 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 1)"> @lang('dashboard.locked_open')</a>
                                        </li>
                                        <li style="{{$device['barrier_status'] == 2 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 2)"> @lang('dashboard.locked_closed')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.unlock')</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            @else
                            <div class="col-md-12 col-xs-12 open_con text-right pt-5 pr-0 pl-0">
                                <div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-open-gate-vehicle-active-con">
                                    <button 
                                        type="button" 
                                        data-device_id="{{$device['id']}}" 
                                        class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate btn-open-gate-vehicle-active"
                                        style="">@lang('dashboard.open')</button>
                                    <button type="button" 
                                            class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" 
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($device['has_always_access'])
                                        <li style="{{$device['barrier_status'] == 3 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 3)"> @lang('dashboard.always_access')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.verify_access')</a>
                                        </li>
                                        <li>
                                            <hr style="margin:5px;" />
                                        </li>
                                        @endif
                                        <li style="{{$device['barrier_status'] == 1 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 1)"> @lang('dashboard.locked_open')</a>
                                        </li>
                                        <li style="{{$device['barrier_status'] == 2 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 2)"> @lang('dashboard.locked_closed')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.unlock')</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-12 col-xs-12 btn-group p-0 pull-right {{$device['barrier_status'] > 0 ? 'btn-danger' : 'btn-primary'}} btn-open-gate-vehicle-non-active hidden">
                                    @if($device['barrier_status'] < 1)
                                    <button 
                                        type="button" 
                                        data-device_id="{{$device['id']}}" 
                                        class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate btn-open-gate-vehicle-active"
                                        style="">@lang('dashboard.open')</button>
                                    @else
                                    <button class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-open-gate">
                                        @if($device['barrier_status'] == 1)
                                        @lang('dashboard.locked_open')
                                        @elseif($device['barrier_status'] == 2)
                                        @lang('dashboard.locked_closed')
                                        @elseif($device['barrier_status'] == 3)
                                        @lang('dashboard.always_access')
                                        @else
                                        @lang('dashboard.open')
                                        @endif
                                    </button>
                                    @endif
                                    <button type="button" 
                                            class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" 
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($device['has_always_access'])
                                        <li style="{{$device['barrier_status'] == 3 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 3)"> @lang('dashboard.always_access')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.verify_access')</a>
                                        </li>
                                        <li>
                                            <hr style="margin:5px;" />
                                        </li>
                                        @endif
                                        <li style="{{$device['barrier_status'] == 1 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 1)"> @lang('dashboard.locked_open')</a>
                                        </li>
                                        <li style="{{$device['barrier_status'] == 2 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 2)"> @lang('dashboard.locked_closed')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.unlock')</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 close_con text-right pt-5 pr-0 pl-0">
                                <div class="col-md-12 col-xs-12 btn-group p-0 pull-right btn-close-gate-vehicle-active-con hidden">
                                    <button 
                                        type="button" 
                                        data-device_id="{{$device['id']}}" 
                                        class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate btn-close-gate-vehicle-active"
                                        style="">@lang('dashboard.close')</button>
                                    <button type="button" 
                                            class="col-md-2 col-xs-2 btn btn-close-gate-group p-3 dropdown-toggle dropdown-toggle-split" 
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($device['has_always_access'])
                                        <li style="{{$device['barrier_status'] == 3 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 3)"> @lang('dashboard.always_access')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.verify_access')</a>
                                        </li>
                                        <li>
                                            <hr style="margin:5px;" />
                                        </li>
                                        @endif
                                        <li style="{{$device['barrier_status'] == 1 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 1)"> @lang('dashboard.locked_open')</a>
                                        </li>
                                        <li style="{{$device['barrier_status'] == 2 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 2)"> @lang('dashboard.locked_closed')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.unlock')</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-12 col-xs-12 btn-group p-0 pull-right {{$device['barrier_status'] > 0 ? 'btn-danger' : 'btn-primary'}} btn-close-gate-vehicle-non-active">
                                    @if($device['barrier_status'] < 1)
                                    <button 
                                        type="button" 
                                        data-device_id="{{$device['id']}}" 
                                        class="col-md-10 col-xs-10 btn btn-close-gate-group p-3 btn-close-gate btn-close-gate-vehicle-active">@lang('dashboard.close')</button>
                                    @else
                                    <button class="col-md-10 col-xs-10 btn btn-open-gate-group p-3 btn-close-gate">
                                        @if($device['barrier_status'] == 1)
                                        @lang('dashboard.locked_open')
                                        @elseif($device['barrier_status'] == 2)
                                        @lang('dashboard.locked_closed')
                                        @elseif($device['barrier_status'] == 3)
                                        @lang('dashboard.always_access')
                                        @else
                                        @lang('dashboard.close')
                                        @endif
                                    </button>
                                    @endif
                                    <button type="button" 
                                            class="col-md-2 col-xs-2 btn btn-open-gate-group p-3 dropdown-toggle dropdown-toggle-split" 
                                            data-toggle="dropdown" 
                                            aria-haspopup="true" 
                                            aria-expanded="false">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($device['has_always_access'])
                                        <li style="{{$device['barrier_status'] == 3 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 3)"> @lang('dashboard.always_access')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.verify_access')</a>
                                        </li>
                                        <li>
                                            <hr style="margin:5px;" />
                                        </li>
                                        @endif
                                        <li style="{{$device['barrier_status'] == 1 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 1)"> @lang('dashboard.locked_open')</a>
                                        </li>
                                        <li style="{{$device['barrier_status'] == 2 ? 'display:none;' : ''}}">
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 2)"> @lang('dashboard.locked_closed')</a>
                                        </li>
                                        <li>
                                            <a 
                                                href="javascript:void(0)" 
                                                onclick="change_barrier_status({{$device['id']}}, 0)"> @lang('dashboard.unlock')</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            @endif
                            @else
                            <div class="col-md-12 col-xs-12 text-right pt-10 pr-0">
                                <i class="fa fa-warning f-90"></i>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-12 col-xs-12 pt-10" >
                            @if($device['is_synched'])
                            <p class="col-md-4 col-xs-4  text-left mb-0 pl-0" style="color: white"><b>{{$device['status']}}</b></p>
                            @else
                            <p class="col-md-4 col-xs-4 text-left mb-0 pl-0 text-out-of-order" style="color: {{$device['status_color']}}"><b>{{$device['status']}}</b></p>
                            @endif
                            <p class="col-md-8 col-xs-8 text-right mb-0 pr-0">
                                @if(count($device['transactions']) > 0)
                                <a style="color:white!important" target="_blank" href="{{url('/transaction/1/'.$device['transactions']['0']['id'])}}">{{$device['transactions']['0']['content']}}</a>
                                @else
                                @lang('dashboard.no_transactions')
                                @endif
                            </p>
                        </div>
                    </div>
                
                    </div>
            </div>
            @elseif($device['available_device_id'] == 6)
            <div class="col-md-4 col-sm-12 col-xs-12 mb-20 device_item  device_{{$device['id']}}">
                <div class="bg-primary color-white col-md-12 col-xs-12 pl-0 pr-0 pb-15" >
                    <div class="col-md-12 col-xs-12 pl-0 pt-5 pr-0 pb-5 header-device-section">
                        <div class="col-sm-6 col-xs-6">
                            <h6 class="box-title text-left  f-17  color-white wrap-word-custom" data-toggle="tooltip" title="{{strtoupper($device['name'])}}">{{strtoupper($device['name'])}}</h6> 
                        </div>
                        <div class="col-sm-6 col-xs-6">
                            @if($device['is_synched'])
                            <h6 class="box-title text-right cursor-pointer f-17 color-white wrap-word-custom " style="color: white!important">{{$device['status']}} <i class="fa fa-backward show_main_content hidden ml-10"></i> </h6> 
                            @else
                            <h6 class="box-title text-right cursor-pointer f-17 color-white wrap-word-custom  text-out-of-order" style="color: {{$device['status_color']}}!important">{{$device['status']}} <i class="fa fa-backward show_main_content hidden ml-10"></i> </h6> 
                            @endif
                        </div>
                    </div>
                    <div class="transactions_person_con transactions_container hidden">
                        @if(count($device['person_transactions']) > 0)
                        <div class="col-md-9 col-xs-9">
                            @foreach($device['person_transactions'] as  $payment_terminal_device_person_transactions)
                            <div class="col-md-12 col-xs-12">
                                <a style="color:white!important" target="_blank" href="{{url('/transaction/3/'.$payment_terminal_device_person_transactions['id'])}}">{{$payment_terminal_device_person_transactions['content']}}</a>
                            </div> 
                            @endforeach
                        </div>
                        <div class="col-md-3 col-xs-3 text-center pt-35">
                            <a target="_blank" href="/device_transaction/ptp/{{$device['id']}}"><button class="btn btn-primary-transactions">More</button></a>
                        </div>
                        @else
                        <div class="col-md-12 col-xs-12">
                            <p>No Transactions</p>
                        </div>
                        @endif
                    </div>
                    <div class="other_content">
                        <div class="col-md-6 col-xs-6 gates">
                            <div class="barier-closed pt-10 text-center" data-device_id="{{$device['id']}}">
                                <!--<i class="fa fa-credit-card-alt f-90"></i>-->
                                <img class="filter" width="100"  src="{{asset('plugins/images/icons/p1.png')}}" alt="Transaction"> 
                            </div> 
                        </div>
                        <div class="col-md-6 col-xs-6">
                            <div class="col-md-12 col-xs-12 close_con text-right pt-10 pr-0">
                                <button data-device_id="{{$device['id']}}" class="btn col-md-12 btn-primary btn-close-gate show_person_transactions">Persons</button>
                            </div>
                        </div>
                        <!--<hr class=" mt-10 mb-5" style="width:70%; float: none;" >-->
                        <div class="col-md-12 col-xs-12 pt-10" >
                            <p class="col-md-12 text-right mb-0 pr-0 footer-latest-transaction">
                                @if(count($device['person_transactions']) > 0)
                                <a style="color:white!important" target="_blank" href="{{url('/transaction/3/'.$device['person_transactions']['0']['id'])}}">{{$device['person_transactions']['0']['content']}}</a>
                                @else
                                No Transaction
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
            @else
            <p>No Device Added yet</p>
            @endif
        </div>
    </div>
    <div class="row m-t-20">
        <div class="col-sm-6  ">
            <div class="white-box persons_on_location_con">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="box-title">@lang('dashboard.persons_on_loc')</h4>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table color-table info-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('dashboard.name')</th>
                                <!--<th>@lang('dashboard.vehicle')</th>-->
                                <th>@lang('dashboard.check_in')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($at_location_person as $key => $bookings)
                            @if ($key > 4)
                            @break
                            @endif
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$bookings->name}}</td>
                                <!--<td><a href="{{ url('/booking/'.$bookings->id)}}" class="text-link">{{$bookings->name}}</a></td>-->
                                <!--<td>{{$bookings->vehicle_num}}</td>-->
                                <td>{{$bookings->checkin}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($at_location_person) > 0)
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <a href="{{url('/currently_on_location_persons')}}" class="btn btn-primary">@lang('dashboard.view_more')</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="col-sm-6  ">
            <div class="white-box transaction_on_location_con">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="box-title">@lang('dashboard.last5_transaction')</h4>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table color-table info-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('dashboard.transaction')</th>
                                <!--<th>@lang('dashboard.vehicle')</th>-->
                                <th>@lang('dashboard.check_in')</th>
                                <th>@lang('dashboard.check_out')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($last_5_transactions as $key => $transaction)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td><img src="{{$transaction->image}}" class="img img-responsive h-50 w-50"></td>
                                <td>{{$transaction->check_in}}</td>
                                <td>{{$transaction->check_out}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($last_5_transactions) > 0)
                <div class="row">
                    <div class="col-sm-12 text-center">
                        @if(auth()->user()->hasRole(['admin', 'manager', 'service']))
                        <a href="{{url('/transaction_details/person')}}" >
                            <button class="btn btn-primary">@lang('dashboard.view_more')</button>
                        </a>
                        @else
                        <a href="{{url('/transactions/person')}}" >
                            <button class="btn btn-primary">@lang('dashboard.view_more')</button>
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div id="view_calender_event" class="modal fade" tabindex="-1" role="dialog" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

            </div>
        </div>
    </div>
    <div id="edit_booking_vehicle_num" class="modal fade" tabindex="-1" role="dialog" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

            </div>
        </div>
    </div>
    <div id="edit_device_booking_vehicle_num" class="modal fade edit_device_booking_vehicle_num" tabindex="-1" role="dialog" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

            </div>
        </div>
    </div>
    <div id="open_gate_modal" class="modal fade open_gate_modal" tabindex="-1" role="dialog" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content col-md-12 p-0">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@lang('dashboard.ticket_reader_controller')</h4>
                </div>
                <div class="modal-body col-md-12">
                    <div class="overlay_open_gate hidden">
                        <i class="fa fa-spin fa-spinner"></i>
                    </div>
                    <div class="form_con col-md-12">
                        <div class="form-group mb-20 col-md-12 p-0">
                            <label class="col-md-3">Vehicle Number</label>
                            <div class="col-md-9">
                                <input 
                                    type="text" 
                                    class="form-control open_gate_vehcile_num" 
                                    name="vehicle_num"
                                    placeholder="@lang('dashboard.open_gate_vehcile_num_placeholder')">
                                <span class="help-block"></span>
                            </div>
                        </div>
                        <div class="form-group col-md-12 p-0">
                            <label class="col-md-3">Reason</label>
                            <div class="col-md-9">
                                <textarea 
                                    rows="7" 
                                    class="form-control open_gate_reason" 
                                    name="vehicle_num"
                                    placeholder="@lang('dashboard.open_gate_reason_placeholder')"></textarea>
                            </div>
                        </div>
                        <div class="form-group col-md-12 text-right p-0 mb-0">
                            <input type="hidden" class="device_id">
                            <button type="button" class="btn btn-primary submit_open_gate_modal">Submit</button>
                        </div>
                    </div>
                    <div class="confirm_con hidden col-md-12">
                        <div class="form-group mb-20 col-md-12 p-0">
                            <label class="col-md-3">Vehicle Number</label>
                            <div class="col-md-9">
                                <input type="text" readonly=""class="form-control open_gate_vehcile_num" name="vehicle_num">
                            </div>
                        </div>
                        <div class="form-group col-md-12 p-0">
                            <label class="col-md-3">Reason</label>
                            <div class="col-md-9">
                                <textarea rows="7" readonly="" class="form-control open_gate_reason" name="vehicle_num"></textarea>
                            </div>
                        </div>
                        <div class="form-group col-md-12 p-0">
                            <label class="col-md-12 text-danger message">Reason</label>

                        </div>
                        <div class="form-group col-md-12 text-right p-0 mb-0">
                            <input type="hidden" class="device_id">
                            <button type="button" class="btn btn-primary submit_open_gate_modal_confirm">Yes</button>
                            <button type="button" class="btn btn-danger submit_open_gate_cancel">No</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script src="{{asset('plugins/components/switchery/dist/switchery.min.js')}}"></script>
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
<script src="{{asset('plugins/components/Magnific-Popup-master/dist/jquery.magnific-popup.min.js')}}"></script>
<script src="{{asset('plugins/components/Magnific-Popup-master/dist/jquery.magnific-popup-init.js')}}"></script>
<script src="{{asset('plugins/components/owl.carousel/owl.carousel.min.js')}}"></script>
<script src="{{asset('plugins/components/owl.carousel/owl.custom.js')}}"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('js/db3_1.js')}}"></script>
<script type="text/javascript"> 
@if (\Session::has('message'))
    $.toast({
        heading: '{{session()->get('heading')}}',
        position: 'top-center',
        text: '{{session()->get('message')}}',
        loaderBg: '#ff6849',
        icon: '{{session()->get('icon')}}',
        hideAfter: 5000,
        stack: 6
    });
@endif
</script>
@endpush