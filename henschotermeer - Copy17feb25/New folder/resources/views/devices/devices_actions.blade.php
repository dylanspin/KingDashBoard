@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/owl.carousel/owl.carousel.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('plugins/components/owl.carousel/owl.theme.default.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('plugins/components/switchery/dist/switchery.min.css')}}" rel="stylesheet" /> 
<style>
    .carousel-inner img{
        width: 283px!important;
        height: 283px!important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="row el-element-overlay m-b-20">

        @foreach($dashboard_devices as $key => $dashboard_device)
        @if($dashboard_device->type == 1)
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <div class="white-box">
                <div class="el-card-item">

                    <div class="el-card-content mb-20">
                        <br/>
                        <div class="col-sm-12">
                            <h6 class="box-title f-14 text-left wrap-word-custom" data-toggle="tooltip" title="{{strtoupper($dashboard_device->details->device_name)}}">{{strtoupper($dashboard_device->details->device_name)}}</h6>
                        </div>
                        <br/> 
                    </div>
                    <div id="carousel-example-captions-{{$key}}" data-ride="carousel" class="carousel slide">
                        <div role="listbox" class="carousel-inner">
                            @if(count($dashboard_device->transactions) > 0)
                            @foreach($dashboard_device->transactions as $transaction_key => $transaction)
                            @if($transaction_key == 0)
                            <div class="item active  transaction_{{$transaction->id}}" data-transaction_id="{{$transaction->id}}"><img height="283" width="283" src="{{asset($transaction->image_path)}}" alt="Transaction">
                            </div>
                            @else
                            <div class="item transaction_{{$transaction->id}}" data-transaction_id="{{$transaction->id}}"><img height="283" width="283" src="{{asset($transaction->image_path)}}" alt="Transaction">
                            </div>
                            @endif
                            @endforeach
                            @else
                            <div class="item active ">
                                <img height="283" width="283" src="{{asset('uploads/devices/default.png')}}" alt="Transaction">
                            </div>
                            @endif
                        </div>
                        <a 
                            href="#carousel-example-captions-{{$key}}" 
                            role="button" 
                            data-slide="prev"
                            class="left carousel-control"
                            style="background-image:none;"> 
                            <span aria-hidden="true" class="fa fa-angle-left"></span>
                            <span class="sr-only">@lang('dashboard.prev')</span> 
                        </a>
                        <a 
                            href="#carousel-example-captions-{{$key}}" 
                            role="button" 
                            data-slide="next"
                            class="right carousel-control"
                            style="background-image:none;"> 
                            <span aria-hidden="true" class="fa fa-angle-right"></span>
                            <span class="sr-only">@lang('dashboard.nxt')</span> 
                        </a>
                    </div>
                    <div class="el-card-content">
                        <h5 class="box-title">@lang('dashboard.select_transaction')</h5> 
                        <div class="pl-20 pr-20">
                            <select class="form-control select2 transaction_type_dropdown" data-transaction_key="{{$key}}">
                                <option value="">@lang('dashboard.select_transaction')</option>
                                @if(count($dashboard_device->transactions) > 0)
                                @foreach($dashboard_device->transactions as $transaction_key => $transaction)
                                <option value="{{$transaction->id}}">@lang('dashboard.transaction_no') {{$transaction_key+1}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @elseif($dashboard_device->type == 2)
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <div class="white-box">
                <div class="el-card-item">

                    <div class="el-card-content mb-20">
                        <br/>
                        <div class="col-sm-9">
                            <h6 class="box-title f-14 text-left wrap-word-custom" data-toggle="tooltip" title="{{strtoupper($dashboard_device->details->device_name)}}">{{strtoupper($dashboard_device->details->device_name)}}

                            </h6>
                        </div>
                        <div class="col-sm-3 pt-7">
                            <span class="switchery_gate"  data-toggle="tooltip" title="Open Ticket Reader"><input data-device_id="{{$dashboard_device->details->id}}" type="checkbox" class="js-switch open_gate" data-color="#0078bc" data-size="small" /></span>
                            <i class="fa fa-spinner fa-spin hidden gate_open_spinner"></i>
                        </div>

                        <br/> 
                    </div>
                    <div id="carousel-example-captions-{{$key}}" data-ride="carousel" class="carousel slide">
                        <div role="listbox" class="carousel-inner">

                            <div class="item active ">
                                <img height="283" width="283" src="{{asset('uploads/devices/default.png')}}" alt="Transaction">
                            </div>
                        </div>
                    </div>
                    <div class="el-card-content">
                        <h5 class="box-title">@lang('dashboard.select_transaction')</h5> 
                        <div class="pl-20 pr-20">
                            <select class="form-control select2 transaction_type_dropdown" data-transaction_key="{{$key}}">
                                <option value="">@lang('dashboard.select_transaction')</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @endforeach
    </div>
</div>
<div id="open_gate_modal" class="modal fade open_gate_modal" tabindex="-1" role="dialog" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@lang('dashboard.ticket_reader_controller')</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('dashboard.close')</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script src="{{asset('plugins/components/switchery/dist/switchery.min.js')}}"></script>
<script src="{{asset('plugins/components/owl.carousel/owl.carousel.min.js')}}"></script>
<script src="{{asset('plugins/components/owl.carousel/owl.custom.js')}}"></script>
<script src="{{asset('js/db3.js')}}"></script>
@endpush