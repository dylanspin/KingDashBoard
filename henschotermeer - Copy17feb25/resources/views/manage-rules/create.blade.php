@extends('layouts.master')

@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
{{--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">--}}
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
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
                    <h3 class="box-title pull-left">@lang('access-rules.add_rule')</h3>
                    <a  class="btn btn-success pull-right" href="{{url('manage-rules')}}">
                        <i class="icon-list"></i> @lang('access-rules.view_rule')
                    </a>
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
                        id="parkingRule" 
                        action="{{url('manage-rules/create')}}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="form-horizontal manageRule">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab1" data-toggle="tab">@lang('access-rules.manage_rule')</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group {{ $errors->first('name', 'has-error') }}">
                                        <label for="name" class="col-sm-2 control-label">@lang('barcode.name')</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="name" 
                                                name="name" 
                                                type="text" 
                                                placeholder="@lang('barcode.name')" 
                                                class="form-control required" 
                                                value="{!! old('name') !!}"/>

                                            {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('status', 'has-error') }}">
                                        <label for="status" class="col-sm-2 control-label">@lang('access-rules.choose_status')</label>
                                        <div class="col-sm-10">
                                            <select name="status" id="status" class="form-control">
                                                <option value="">@lang('access-rules.choose_status')</option>
                                                <option value="1">@lang('access-rules.status_enable')</option>
                                                <option value="0">@lang('access-rules.status_disable')</option>
                                            </select>

                                            {!! $errors->first('status', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group device_direction_con {{ $errors->first('device_direction', 'has-error') }}">
                                        <label for="device_direction" class="col-sm-2 control-label">@lang('devices.device_directions')
                                            </label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="@lang('devices.device_directions')" name="device_direction">
                                                <option value="">@lang('devices.select')</option>
                                                <option value="bi-directional"
                                                    @if (old('device_direction') == 'bi-directional') selected="selected" @endif>
                                                    @lang('devices.bi_directional')
                                                </option>
                                                <option value="in"
                                                    @if (old('device_direction') == 'in') selected="selected" @endif>
                                                    @lang('devices.in')
                                                </option>
                                                <option value="out"
                                                    @if (old('device_direction') == 'out') selected="selected" @endif>
                                                    @lang('devices.out')
                                                </option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('device_direction', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group barcode-type {{ $errors->first('barcode_type', 'has-error') }}">
                                        <label for="barcode_type" class="col-sm-2 control-label">@lang('access-rules.barcode_enable')
                                            </label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="@lang('devices.barcode_type')" name="barcode_type">
                                                <option value="">@lang('devices.select')</option>
                                                <option value="parking_barcode"
                                                    @if (old('barcode_type') == 'Parking Barcode') selected="selected" @endif>
                                                    @lang('access-rules.parking_barcode')
                                                </option>
                                                <option value="person_barcode"
                                                    @if (old('barcode_type') == 'Person Barcode') selected="selected" @endif>
                                                    @lang('access-rules.person_barcode')
                                                </option>
                                                <option value="pos_barcode"
                                                    @if (old('barcode_type') == 'Pos Barcode') selected="selected" @endif>
                                                    @lang('access-rules.pos_barcode')
                                                </option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('barcode_type', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group match-mode {{ $errors->first('plate_match_mode', 'has-error') }}">
                                        <label for="plate_match_mode" class="col-sm-2 control-label">@lang('access-rules.plate_match_mode')
                                            </label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="@lang('devices.barcode_type')" name="plate_match_mode">
                                                <option value="">@lang('devices.select')</option>
                                                 {{-- <option value="disable"
                                                    @if (old('plate_match_mode') == 'disable') selected="selected" @endif>
                                                    @lang('access-rules.disable')
                                                </option> --}}
                                                <option value="low"
                                                    @if (old('plate_match_mode') == 'low') selected="selected" @endif>
                                                    @lang('access-rules.low')
                                                </option>
                                                <option value="medium"
                                                    @if (old('plate_match_mode') == 'medium') selected="selected" @endif>
                                                    @lang('access-rules.medium')
                                                </option>
                                                <option value="high"
                                                    @if (old('plate_match_mode') == 'high') selected="selected" @endif>
                                                    @lang('access-rules.high')
                                                </option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('plate_match_mode', ':message') }}</span>
                                    </div>
                                </div>
                                <ul class="pager wizard">
                                    <li class="next finish" ><a href="javascript:;">@lang('barcode.submit')</a></li>
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
{{--<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>--}}
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"
type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{ asset('/js/jquery.mask.js') }}"></script>
<script src="{{ asset('/js/manage-rules.js') }}"></script>

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