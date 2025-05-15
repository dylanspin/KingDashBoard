@extends('layouts.master')

@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet"/>
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css"/>
<link href="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet" />
<style>

    #rootwizard .nav.nav-pills {
        margin-bottom: 25px;
    }

    .help-block {
        display: block;
        margin-top: 5px;
        margin-bottom: 10px;
    }
    .nav-pills>li>a{
        cursor: default;;
        background-color: inherit;
    }
    .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover {
        background: #0283cc!important;
        color: #fff!important;
    }
    .nav-pills>li>a:focus,.nav-tabs>li>a:focus, .nav-pills>li>a:hover, .nav-tabs>li>a:hover {
        border: 1px solid transparent!important;
        background-color: inherit!important;
    }

    .has-error .help-block {
        color: #EF6F6C;
    }

    .select2 {
        width: 100% !important;
    }
    .error-block{
        background-color: #ff9d9d;
        color: red;
    }
</style>
@endpush
@section('content')
    <div class="container-fluid">
        <!-- .row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <h3 class="box-title pull-left">@lang('send-ticket.send_ticket')</h3>
                    <div class="clearfix"></div>
                    @if(count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <form 
                        id="commentForm" 
                        action="{{url('send-ticket/')}}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="form-horizontal sendTicketForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab1" data-toggle="tab">@lang('send-ticket.details')</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group {{ $errors->first('first_name', 'has-error') }}">
                                        <label for="first_name" class="col-sm-2 control-label">@lang('send-ticket.first_name') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="first_name" 
                                                name="first_name" 
                                                type="text" 
                                                placeholder="@lang('send-ticket.first_name')" 
                                                class="form-control required" 
                                                value="{!! old('first_name') !!}"/>

                                            {!! $errors->first('first_name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('last_name', 'has-error') }}">
                                        <label for="last_name" class="col-sm-2 control-label">@lang('send-ticket.last_name') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="last_name" 
                                                name="last_name" 
                                                type="text" 
                                                placeholder="@lang('send-ticket.last_name')" 
                                                class="form-control required" 
                                                value="{!! old('last_name') !!}"/>

                                            {!! $errors->first('last_name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('email', 'has-error') }}">
                                        <label for="email" class="col-sm-2 control-label">@lang('send-ticket.email') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="email" 
                                                name="email" 
                                                type="email" 
                                                placeholder="@lang('send-ticket.email')" 
                                                class="form-control required" 
                                                value="{!! old('email') !!}"/>

                                            {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('phone_num', 'has-error') }}">
                                        <label for="phone_num" class="col-sm-2 control-label">@lang('send-ticket.phone_num') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="phone_num" 
                                                name="phone_num" 
                                                type="text" 
                                                placeholder="@lang('send-ticket.phone_num')" 
                                                class="form-control phone_mask required" 
                                                value="{!! old('phone_num') !!}"/>

                                            {!! $errors->first('phone_num', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('checkin_date', 'has-error') }}">
                                        <label for="checkin_date" class="col-sm-2 control-label">@lang('send-ticket.checkin_date') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="checkin_date" 
                                                name="checkin_date" 
                                                type="text" 
                                                placeholder="@lang('send-ticket.checkin_date')" 
                                                class="form-control mydatepicker checkin_date required" 
                                                value="{!! old('checkin_date') !!}"/>
                                            <i class="icon-calender" style="float:right;margin-top:-26px;margin-right:10px;"></i>

                                            {!! $errors->first('checkin_date', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('checkin_time', 'has-error') }}">
                                        <label for="checkin_time" class="col-sm-2 control-label">@lang('send-ticket.checkin_time') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="checkin_time" 
                                                name="checkin_time" 
                                                type="text" 
                                                placeholder="@lang('send-ticket.checkin_time')" 
                                                class="form-control clockpicker required" 
                                                value="{!! old('checkin_time') !!}"/>
                                            <i class="glyphicon glyphicon-time" style="float:right;margin-top:-26px;margin-right:10px;"></i>

                                            {!! $errors->first('checkin_time', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('checkout_date', 'has-error') }}">
                                        <label for="checkout_date" class="col-sm-2 control-label">@lang('send-ticket.checkout_date') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="checkout_date" 
                                                name="checkout_date" 
                                                type="text" 
                                                placeholder="@lang('send-ticket.checkout_date')" 
                                                class="form-control checkout_date mydatepicker required" 
                                                value="{!! old('checkout_date') !!}"/>
                                            <i class="icon-calender" style="float:right;margin-top:-26px;margin-right:10px;"></i>

                                            {!! $errors->first('checkout_date', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('checkout_time', 'has-error') }}">
                                        <label for="checkout_time" class="col-sm-2 control-label">@lang('send-ticket.checkout_time') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="checkout_time" 
                                                name="checkout_time" 
                                                type="text" 
                                                placeholder="@lang('send-ticket.checkout_time')" 
                                                class="form-control clockpicker required" 
                                                value="{!! old('checkout_time') !!}"/>
                                            <i class="glyphicon glyphicon-time" style="float:right;margin-top:-26px;margin-right:10px;"></i>

                                            {!! $errors->first('checkout_time', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('vehicle_num', 'has-error') }}">
                                        <label for="vehicle_num" class="col-sm-2 control-label">@lang('send-ticket.vehicle_num') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="vehicle_num" 
                                                name="vehicle_num" 
                                                type="text" 
                                                placeholder="@lang('send-ticket.vehicle_num')" 
                                                class="form-control required" 
                                                value="{!! old('vehicle_num') !!}"/>

                                            {!! $errors->first('vehicle_num', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('sender_name', 'has-error') }}">
                                        <label for="sender_name" class="col-sm-2 control-label">@lang('send-ticket.sender_name') </label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="sender_name" 
                                                name="sender_name" 
                                                type="text" 
                                                placeholder="@lang('send-ticket.sender_name')" 
                                                class="form-control" 
                                                value="{!! old('sender_name') !!}"/>

                                            {!! $errors->first('sender_name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="message" class="col-sm-2 control-label">@lang('send-ticket.message')
                                        </label>
                                        <div class="col-sm-10">
                                            <textarea 
                                                name="message" 
                                                id="message" 
                                                class="form-control resize_vertical message" 
                                                rows="3" 
                                                maxlength="50">{!! old('message') !!}</textarea>
                                            <p>Note: Maximum 50 Characters Allow.</p>
                                        </div>

                                        {!! $errors->first('message', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                <ul class="pager wizard">
                                    <li class="next finish" ><a href="javascript:;">@lang('send-ticket.submit')</a></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('layouts.partials.right-sidebar')
    </div>
@endsection

@push('js')
<script src="{{ asset('plugins/components/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
<script src="{{asset('plugins/components/icheck/icheck.min.js')}}"></script>
<script src="{{asset('plugins/components/icheck/icheck.init.js')}}"></script>
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"
type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<!-- Clock Plugin JavaScript -->
<script src="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.js')}}"></script>
<!-- Date Picker Plugin JavaScript -->
<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
<!-- Masking Plugin JavaScript -->
<script src="{{ asset('/js/jquery.mask.js') }}"></script>
<!-- Custom Plugin JavaScript -->
<script src="{{ asset('/js/send-ticket.js') }}"></script>

<script>
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