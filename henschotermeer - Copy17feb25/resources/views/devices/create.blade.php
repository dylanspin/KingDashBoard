@extends('layouts.master')

@push('css')
    <link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/icheck/skins/all.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
    <!--{{-- <link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}"> --}}-->
    <link href="{{ asset('plugins/components/jqueryui/jquery-ui.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
    <link href="{{ asset('plugins/components/clockpicker/dist/jquery-clockpicvicker.min.css') }}" rel="stylesheet">
    <style>
        #rootwizard .nav.nav-pills {
            margin-bottom: 25px;
        }

        .help-block {
            display: block;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .nav-pills>li>a {
            cursor: default;
            ;
            background-color: inherit;
        }

        .nav-pills>li.active>a,
        .nav-pills>li.active>a:focus,
        .nav-pills>li.active>a:hover {
            background: #0283cc !important;
            color: #fff !important;
        }

        .nav-pills>li>a:focus,
        .nav-tabs>li>a:focus,
        .nav-pills>li>a:hover,
        .nav-tabs>li>a:hover {
            border: 1px solid transparent !important;
            background-color: inherit !important;
        }

        .has-error .help-block {
            color: #EF6F6C;
        }

        .select2 {
            width: 100% !important;
        }

        .error-block {
            background-color: #ff9d9d;
            color: red;
        }

        .add_ports {
            position: absolute;
            right: 20px;
            top: 6px;
        }

        .delete_ports {
            position: absolute;
            right: 5.6rem;
            margin-top: 6px;
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid">
        <!-- .row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <h3 class="box-title pull-left">@lang('devices.create_device')</h3>
                    <div class="clearfix"></div>
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="commentForm" action="{{ url('devices/create') }}" method="POST" enctype="multipart/form-data"
                        data-toggle="validator" class="form-horizontal deviceForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab1" class="tab1_t" data-toggle="tab"
                                        style="cursor:pointer;">@lang('devices.details')</a>
                                </li>
                                <li>
                                    <a href="#tab2" class="tab2_t" data-toggle="tab"
                                        style="cursor:pointer;">@lang('devices.settings')</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div
                                        class="form-group device_name_con {{ $errors->first('device_name', 'has-error') }}">
                                        <label for="device_name" class="col-sm-2 control-label">@lang('devices.device_name') *</label>
                                        <div class="col-sm-10">
                                            <input id="device_name" name="device_name" type="text"
                                                placeholder="@lang('devices.device_name')" class="form-control required"
                                                value="{!! old('device_name') !!}" />
                                            <small class="custom-help-block help-block text-danger"
                                                style="display:none"></small>
                                            {!! $errors->first('device_name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div
                                        class="form-group device_type_con {{ $errors->first('device_type', 'has-error') }}">
                                        <label for="device_type" class="col-sm-2 control-label">@lang('devices.device_type') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control device_type" title="@lang('devices.device_type')"
                                                name="device_type" >
                                                <option value="">@lang('devices.select')</option>
                                                @foreach ($deviceTypes as $deviceType)
                                                    <option value="{{ $deviceType->id }}"
                                                        @if (old('device_type') == $deviceType->id) selected="selected" @endif>
                                                        {{ $deviceType->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('device_type', ':message') }}</span>
                                    </div>

                                    <div
                                        class="form-group related_ticket_readers_con {{ old('device_type') == 3 ? '' : 'hidden' }} {{ $errors->first('related_ticket_readers', 'has-error') }}">
                                        <label for="related_ticket_readers" class="col-sm-2 control-label">@lang('devices.related_ticket_readers')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="Select" name="related_ticket_readers">
                                                <option value="">@lang('devices.select')</option>
                                                @if (count($devices_ticket_reader_type) > 0)
                                                    @foreach ($devices_ticket_reader_type as $device)
                                                        <option value="{{ $device->id }}">{{ $device->device_name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('related_ticket_readers', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group related_device_con {{ old('device_type') == 4 ? '' : 'hidden' }} {{ $errors->first('related_od', 'has-error') }}">
                                        <label for="related_device" class="col-sm-2 control-label">@lang('devices.related_device')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control select2" multiple="" title="Select"
                                                name="related_device[]">
                                                @if (count($devices_not_od_type) > 0)
                                                    @foreach ($devices_not_od_type as $device)
                                                        <option value="{{ $device->id }}">{{ $device->device_name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('related_device', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group device_direction_con {{ $errors->first('device_direction', 'has-error') }}">
                                        <label for="device_direction" class="col-sm-2 control-label">@lang('devices.device_directions')
                                            *</label>
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
                                    {{-- <div
                                        class="form-group related_payment_terminal {{ old('device_type') == 12 ? '' : 'hidden' }} {{ $errors->first('related_ticket_readers', 'has-error') }}">
                                        <label for="related_payment_terminal"
                                            class="col-sm-2 control-label">@lang('devices.payment_terminal') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="Select" id="payment_terminal_id" name="payment_terminal_id"
                                                required>
                                                <option value="">@lang('devices.select')</option>
                                                @if (count($payment_terminals) > 0)
                                                    @foreach ($payment_terminals as $payment_terminal)
                                                        <option value="{{ $payment_terminal->id }}">
                                                            {{ $payment_terminal->device_name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <small class="custom-help-block help-block text-danger"
                                                style="display:none"></small>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('related_payment_terminal', ':message') }}</span>
                                    </div> --}}
                                    @if (isset($switches))
                                        <div
                                            class="form-group related_switch {{ old('device_type') == 6 ? '' : 'hidden' }} {{ $errors->first('related_switch', 'has-error') }}">
                                            <label for="related_switch" class="col-sm-2 control-label">@lang('devices.payment_terminal')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select" id="switch_id"
                                                    name="related_switch">
                                                    <option value="">@lang('devices.select')</option>
                                                    @if (count($switches) > 0)
                                                        @foreach ($switches as $switch)
                                                            <option value="{{ $switch->id }}">
                                                                {{ $switch->device_name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <small class="custom-help-block help-block text-danger"
                                                    style="display:none"></small>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('related_switch', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group open_relays hidden {{ $errors->first('open_relays', 'has-error') }}">
                                            <label for="open_relays" class="col-sm-2 control-label">@lang('devices.open_relay')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select name="open_relay" class="form-control" id="open_relay"></select>
                                                <small class="custom-help-block help-block text-danger"
                                                    style="display:none"></small>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('open_relays', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group close_relays hidden {{ $errors->first('close_relays', 'has-error') }}">
                                            <label for="related_switch" class="col-sm-2 control-label">@lang('devices.close_relay')
                                                *</label>
                                            <div class="col-sm-10" id="close_realy_section">
                                                <select name="close_relay" class="form-control"
                                                    id="close_relay"></select>
                                                <small class="custom-help-block help-block text-danger"
                                                    style="display:none"></small>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('close_relays', ':message') }}</span>
                                        </div>

                                    @endif
                                    <div class="form-group check-ip {{ $errors->first('device_ip', 'has-error') }}">
                                        <label for="device_ip" class="col-sm-2 control-label">@lang('devices.device_ip') *</label>
                                        <div class="col-sm-10">
                                            <input id="device_ip" name="device_ip" type="text" maxlength="15"
                                                placeholder="@lang('devices.device_ip')" class="form-control device_ip required"
                                                value="{!! old('device_ip') !!}" />
                                            <small class="custom-ip-help-block help-block d-none"></small>
                                            {!! $errors->first('device_ip', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group device-password {{ old('device_type') == 12 ? '' : 'hidden' }} {{ $errors->first('device_password', 'has-error') }}">
                                        <label for="device_password" class="col-sm-2 control-label">@lang('devices.password')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="device_port" name="password" type="number"
                                                placeholder="@lang('devices.password')" maxlength="4"
                                                class="form-control required" value="{!! old('device_password') !!}" />

                                            {!! $errors->first('device_password', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group single_device_port {{ $errors->first('device_port', 'has-error') }}">
                                        <label for="device_port" class="col-sm-2 control-label">@lang('devices.device_port')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="device_port" name="device_port" type="number"
                                                placeholder="@lang('devices.device_port')" class="form-control required"
                                                value="{!! old('device_port') !!}" />

                                            {!! $errors->first('device_port', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div
                                        class="form-group related_od_con {{ old('device_type') == 1 || old('device_type') == 2 ? '' : 'hidden' }} {{ $errors->first('related_od', 'has-error') }}">
                                        <label for="related_od" class="col-sm-2 control-label">@lang('devices.related_od')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control select2" multiple="" title="Select"
                                                name="related_od[]">
                                                @if (count($devices_od_type) > 0)
                                                    @foreach ($devices_od_type as $device)
                                                        <option value="{{ $device->id }}">{{ $device->device_name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('related_od', ':message') }}</span>
                                    </div>


                                </div>
                                <div class="tab-pane " id="tab2">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div
                                        class="form-group anti_passback_con {{ $errors->first('anti_passback', 'has-error') }}">
                                        <label for="anti_passback" class="col-sm-2 control-label">@lang('devices.anti_passback')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control anti_passback" title="Select Anti Passback..."
                                                name="anti_passback">
                                                <option value="1"
                                                    @if (old('anti_passback') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('anti_passback') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('anti_passback', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group time_passback_con {{ old('anti_passback') == 0 ? 'hidden' : '' }} {{ $errors->first('time_passback', 'has-error') }}">
                                        <label for="time_passback" class="col-sm-2 control-label">@lang('devices.time_passback')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="time_passback" name="time_passback" type="text"
                                                placeholder="@lang('devices.time_passback')" class="form-control time_passback"
                                                value="{!! old('time_passback', '5') !!}"
                                                {{ old('anti_passback') == 0 ? '' : 'required=""' }} />

                                            {!! $errors->first('time_passback', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group has_gate_con {{ $errors->first('has_gate', 'has-error') }}">
                                        <label for="has_gate" class="col-sm-2 control-label">@lang('devices.has_gate') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control has_gate" title="Select Has Gate"
                                                name="has_gate">
                                                <option value="0"
                                                    @if (old('has_gate') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                                <option value="1"
                                                    @if (old('has_gate') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('has_gate', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group barrier_close_time_con  {{ old('has_gate') == 1 ? '' : 'hidden' }} {{ $errors->first('barrier_close_time', 'has-error') }}">
                                        <label for="barrier_close_time" class="col-sm-2 control-label">@lang('devices.barrier_close_time')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="barrier_close_time" name="barrier_close_time" type="text"
                                                placeholder="@lang('devices.barrier_close_time')" class="form-control barrier_close_time"
                                                value="{!! old('barrier_close_time', '5') !!}"
                                                {{ old('has_gate') === 1 ? 'required=""' : '' }} />

                                            {!! $errors->first('barrier_close_time', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group enable_log_con {{ $errors->first('enable_log', 'has-error') }}">
                                        <label for="enable_log" class="col-sm-2 control-label">@lang('devices.enable_log')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="Select Enable Log" name="enable_log">
                                                <option value="1"
                                                    @if (old('enable_log') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('enable_log') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('enable_log', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group enable_idle_screen_con {{ $errors->first('enable_idle_screen', 'has-error') }}">
                                        <label for="enable_idle_screen" class="col-sm-2 control-label">@lang('devices.enable_idle_screen')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control enable_idle_screen"
                                                title="Select Enable Idle Screen" name="enable_idle_screen">
                                                <option value="0"
                                                    @if (old('enable_idle_screen') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                                <option value="1"
                                                    @if (old('enable_idle_screen') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('enable_idle_screen', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group qr_code_type_con {{ $errors->first('qr_code_type', 'has-error') }}">
                                        <label for="qr_code_type" class="col-sm-2 control-label">@lang('devices.qr_code_type')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="Select QR Code Type" name="qr_code_type">
                                                <option value="csv"
                                                    @if (old('qr_code_type') == 'csv') selected="selected" @endif>
                                                    @lang('devices.csv')
                                                </option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('qr_code_type', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group focus_away_con {{ old('device_type') == 1 || old('device_type') == 2 ? '' : 'hidden' }} {{ $errors->first('focus_away', 'has-error') }}">
                                        <label for="focus_away" class="col-sm-2 control-label">@lang('devices.focus_away')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="Select Focus Away" name="focus_away">
                                                <option value="1"
                                                    @if (old('focus_away') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('focus_away') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('focus_away', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group opacity_input_con {{ old('device_type') == 1 || old('device_type') == 2 ? '' : 'hidden' }} {{ $errors->first('opacity_input', 'has-error') }}">
                                        <label for="opacity_input" class="col-sm-2 control-label">@lang('devices.opacity_in')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="Select Opacity Input"
                                                name="opacity_input">
                                                <option value="1"
                                                    @if (old('opacity_input') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('opacity_input') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('opacity_input', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group od_enabled_con {{ old('device_type') == 1 || old('device_type') == 2 ? '' : 'hidden' }} {{ $errors->first('od_enabled', 'has-error') }}">
                                        <label for="od_enabled" class="col-sm-2 control-label">@lang('devices.od_enabled')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="Select OD Enabled" name="od_enabled">
                                                <option value="0"
                                                    @if (old('od_enabled') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                                <option value="1"
                                                    @if (old('od_enabled') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>

                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('od_enabled', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group has_sdl_con hidden {{ $errors->first('has_sdl', 'has-error') }}">
                                        <label for="has_sdl" class="col-sm-2 control-label">@lang('devices.has_sdl') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control has_sdl" title="Select SDL" name="has_sdl">
                                                <option value="1"
                                                    @if (old('has_sdl') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('has_sdl') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('has_sdl', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group gate_close_transaction_enabled_con hidden {{ $errors->first('gate_close_transaction_enabled', 'has-error') }}">
                                        <label for="gate_close_transaction_enabled"
                                            class="col-sm-2 control-label">@lang('devices.gate_close_transaction_enabled') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control gate_close_transaction_enabled"
                                                title="Enable Register Transaction on Gate Close"
                                                name="gate_close_transaction_enabled">
                                                <option value="1"
                                                    @if (old('gate_close_transaction_enabled') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('gate_close_transaction_enabled') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('gate_close_transaction_enabled', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group has_pdl_con hidden {{ $errors->first('has_pdl', 'has-error') }}">
                                        <label for="has_pdl" class="col-sm-2 control-label">@lang('devices.has_pdl') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control has_pdl" title="Enable PDL" name="has_pdl">
                                                <option value="1"
                                                    @if (old('has_pdl') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('has_pdl') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('has_pdl', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group plate_correction_enabled_con hidden {{ $errors->first('plate_correction_enabled', 'has-error') }}">
                                        <label for="plate_correction_enabled"
                                            class="col-sm-2 control-label">@lang('devices.plate_correction_enabled') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control plate_correction_enabled"
                                                title="Enable Plate Correction" name="plate_correction_enabled">
                                                <option value="1"
                                                    @if (old('plate_correction_enabled') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('plate_correction_enabled') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('plate_correction_enabled', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group message_text_size_con  {{ old('device_type') === 4 ? '' : 'hidden' }} {{ $errors->first('message_text_size', 'has-error') }}">
                                        <label for="message_text_size" class="col-sm-2 control-label">@lang('devices.message_text_size')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="message_text_size" name="message_text_size" type="text"
                                                placeholder="@lang('devices.message_text_size')" class="form-control message_text_size"
                                                value="{!! old('message_text_size', '60') !!}"
                                                {{ old('device_type') == 4 ? 'required=""' : '' }} />

                                            {!! $errors->first('message_text_size', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div
                                        class="form-group time_text_size_con  {{ old('device_type') === 4 ? '' : 'hidden' }} {{ $errors->first('time_text_size', 'has-error') }}">
                                        <label for="time_text_size" class="col-sm-2 control-label">@lang('devices.time_text_size')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="time_text_size" name="time_text_size" type="text"
                                                placeholder="@lang('devices.time_text_size')" class="form-control time_text_size"
                                                value="{!! old('time_text_size', '190') !!}"
                                                {{ old('device_type') == 4 ? 'required=""' : '' }} />

                                            {!! $errors->first('time_text_size', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group date_text_size_con  {{ old('device_type') === 4 ? '' : 'hidden' }} {{ $errors->first('date_text_size', 'has-error') }}">
                                        <label for="date_text_size" class="col-sm-2 control-label">@lang('devices.date_text_size')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="date_text_size" name="date_text_size" type="text"
                                                placeholder="@lang('devices.date_text_size')" class="form-control date_text_size"
                                                value="{!! old('date_text_size', '60') !!}"
                                                {{ old('device_type') == 4 ? 'required=""' : '' }} />

                                            {!! $errors->first('date_text_size', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group bottom_tray_text_size_con  {{ old('device_type') === 4 ? '' : 'hidden' }} {{ $errors->first('bottom_tray_text_size', 'has-error') }}">
                                        <label for="bottom_tray_text_size"
                                            class="col-sm-2 control-label">@lang('devices.bottom_tray_text_size') *</label>
                                        <div class="col-sm-10">
                                            <input id="bottom_tray_text_size" name="bottom_tray_text_size" type="text"
                                                placeholder="@lang('devices.bottom_tray_text_size')"
                                                class="form-control bottom_tray_text_size"
                                                value="{!! old('bottom_tray_text_size', '60') !!}"
                                                {{ old('device_type') == 4 ? 'required=""' : '' }} />

                                            {!! $errors->first('bottom_tray_text_size', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group confidence_level_con  {{ old('device_type') === 3 ? '' : 'hidden' }} {{ $errors->first('confidence_level', 'has-error') }}">
                                        <label for="confidence_level" class="col-sm-2 control-label">@lang('devices.confidence_level')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="confidence_level" name="confidence_level" type="text"
                                                placeholder="@lang('devices.confidence_level')" class="form-control confidence_level"
                                                value="{!! old('confidence_level', 80) !!}"
                                                {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                            {!! $errors->first('confidence_level', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group num_tries_con  {{ old('device_type') === 3 ? '' : 'hidden' }} {{ $errors->first('num_tries', 'has-error') }}">
                                        <label for="num_tries" class="col-sm-2 control-label">@lang('devices.num_tries') *</label>
                                        <div class="col-sm-10">
                                            <input id="num_tries" name="num_tries" type="text"
                                                placeholder="@lang('devices.num_tries')" class="form-control num_tries"
                                                value="{!! old('num_tries', 5) !!}"
                                                {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                            {!! $errors->first('num_tries', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                     {{-- <div class="form-group character_match_limit_con">
                                            <label for="character_match_limit"
                                                class="col-sm-2 control-label">@lang('devices.character_match_limit') *</label>
                                            <div class="col-sm-10">
                                                <input id="character_match_limit" name="character_match_limit"
                                                    type="text" placeholder="@lang('devices.character_match_limit')"
                                                    class="form-control character_match_limit"
                                                    value="{!! old('character_match_limit', 4) !!}"{{ old('device_type') == 3 ? 'required=""' : '' }} />
                                            </div>
                                        </div> --}}
                                    <div
                                        class="form-group matching_distance_con hidden {{ $errors->first('matching_distance', 'has-error') }}">
                                        <label for="matching_distance" class="col-sm-2 control-label">@lang('devices.matching_distance') *</label>
                                        <div class="col-sm-10">
                                            <input id="matching_distance" name="matching_distance" type="text"
                                                placeholder="@lang('devices.matching_distance')" class="form-control matching_distance"
                                                value="{!! old('matching_distance', 2) !!}"
                                                {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                            {!! $errors->first('matching_distance', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group has_emergency_con  {{ $errors->first('has_emergency_con', 'has-error') }}">
                                        <label for="has_emergency" class="col-sm-2 control-label">@lang('devices.has_emergency')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control has_emergency" name="has_emergency">
                                                <option value="1"
                                                    @if (old('has_emergency') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('has_emergency') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('has_emergency_con', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group plate_length_con  {{ $errors->first('plate_length', 'has-error') }}">
                                        <label for="character_match_limit" class="col-sm-2 control-label">@lang('devices.plate_length')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="character_match_limit" name="character_match_limit" type="number"
                                                placeholder="@lang('devices.plate_length')" class="form-control character_match_limit"
                                                value="{!! old('character_match_limit', 4) !!}"
                                                {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                            {!! $errors->first('character_match_limit', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group character_height_con  {{ $errors->first('character_height', 'has-error') }}">
                                        <label for="character_height" class="col-sm-2 control-label">@lang('devices.character_height')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="character_height" name="character_height" type="number"
                                                min="15" placeholder="@lang('devices.character_height')"
                                                class="form-control character_height" value="{!! old('character_height', 20) !!}"
                                                {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                            {!! $errors->first('character_height', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group triple_exposure_con {{ $errors->first('exposure_mode', 'has-error') }}">
                                        <label for="triple_exposure" class="col-sm-2 control-label">@lang('devices.triple_exposure')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control exposure_mode" name="exposure_mode">
                                                <option value="triple_exposure"
                                                    @if (old('triple_exposure') == 'auto') selected="selected" @endif>
                                                    @lang('devices.auto')
                                                </option>
                                                <option value="auto"
                                                    @if (old('triple_exposure') == 'triple_exposure') selected="selected" @endif>
                                                    @lang('devices.triple_exposure')
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group confidence_level_lowest_con hidden">
                                        <label for="message_text_size" class="col-sm-2 control-label">@lang('devices.confidence_level_lowest')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="confidence_level_lowest" name="confidence_level_lowest"
                                                type="text" placeholder="@lang('devices.confidence_level_lowest')"
                                                class="form-control confidence_level_lowest" value="10" />
                                        </div>
                                    </div>
                                    <div class="form-group character_match_limit_con hidden ">
                                        <label for="character_match_limit"
                                            class="col-sm-2 control-label">@lang('devices.character_match_limit') *</label>
                                        <div class="col-sm-10">
                                            <input id="character_match_limit" name="character_match_limit" type="text"
                                                placeholder="@lang('devices.character_match_limit')"
                                                class="form-control character_match_limit" value="4" />
                                        </div>
                                    </div>
                                    <div
                                        class="form-group ccv_pos_ip_con  {{ old('device_type') === 6 ? '' : 'hidden' }} {{ $errors->first('ccv_pos_ip', 'has-error') }}">
                                        <label for="ccv_pos_ip" class="col-sm-2 control-label">@lang('devices.ccv_pos_ip')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="ccv_pos_ip" name="ccv_pos_ip" type="text"
                                                placeholder="@lang('devices.ccv_pos_ip')" class="form-control ccv_pos_ip"
                                                value="{!! old('ccv_pos_ip') !!}"
                                                {{ old('device_type') == 6 ? 'required=""' : '' }} />

                                            {!! $errors->first('ccv_pos_ip', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group ccv_pos_port_con  {{ old('device_type') === 6 ? '' : 'hidden' }} {{ $errors->first('ccv_pos_port', 'has-error') }}">
                                        <label for="ccv_pos_port" class="col-sm-2 control-label">@lang('devices.ccv_pos_port')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="ccv_pos_port" name="ccv_pos_port" type="text"
                                                placeholder="@lang('devices.ccv_pos_port')" class="form-control ccv_pos_port_con"
                                                value="{!! old('ccv_pos_port') !!}"
                                                {{ old('device_type') == 6 ? 'required=""' : '' }} />

                                            {!! $errors->first('ccv_pos_port', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group has_enable_person_ticket_con hidden {{ $errors->first('has_enable_person_ticket', 'has-error') }}">
                                        <div class="col-sm-10 col-sm-offset-2">
                                            <div class="checkbox checkbox-primary pull-left p-t-0">
                                                <input type="checkbox" id="has_enable_person_ticket"
                                                    class="form-control has_enable_person_ticket"
                                                    name="has_enable_person_ticket">
                                                <label for="has_enable_person_ticket"> @lang('devices.has_enable_person_ticket') ? </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="form-group has_enable_parking_ticket_con hidden {{ $errors->first('has_enable_parking_ticket', 'has-error') }}">
                                        <div class="col-sm-10 col-sm-offset-2">
                                            <div class="checkbox checkbox-primary pull-left p-t-0">
                                                <input type="checkbox" id="has_enable_parking_ticket"
                                                    class="form-control has_enable_parking_ticket"
                                                    name="has_enable_parking_ticket">
                                                <label for="has_enable_parking_ticket"> @lang('devices.has_enable_parking_ticket') ? </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="form-group popup_time_con {{ $errors->first('popup_time', 'has-error') }}">
                                        <label for="popup_time" class="col-sm-2 control-label">@lang('devices.popup_time')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="popup_time" name="popup_time" type="number"
                                                placeholder="@lang('devices.popup_time')" class="form-control popup_time"
                                                value="{!! old('popup_time', '5') !!}" required="" />

                                            {!! $errors->first('barrier_close_time', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group tr_version_con hidden {{ $errors->first('tr_version', 'has-error') }}">
                                        <label for="tr_version" class="col-sm-2 control-label">@lang('devices.tr_version')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control tr_version" name="tr_version" required="">

                                                <option value='0'
                                                    @if (old('tr_version') == '1.0') selected="selected" @endif>
                                                    @lang('devices.tr_version_old')</option>
                                                <option value='1'
                                                    @if (old('tr_version') == '2.0') selected="selected" @endif>
                                                    @lang('devices.tr_version_new')</option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('is_advance_booking_limit', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group has_always_con {{ $errors->first('has_always_access', 'has-error') }}">
                                        <label for="has_always_access" class="col-sm-2 control-label">@lang('devices.has_always_access')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control has_always_access" name="has_always_access">
                                                <option value="1"
                                                    @if (old('has_always_access') == 1) selected="selected" @endif>
                                                    @lang('devices.yes')
                                                </option>
                                                <option value="0"
                                                    @if (old('has_always_access') == 0) selected="selected" @endif>
                                                    @lang('devices.no')
                                                </option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('has_always_access', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group device-related-port {{ old('device_type') == 12 ? '' : 'hidden' }} {{ $errors->first('related_ticket_readers', 'has-error') }}">
                                        <label for="device-related-port-label"
                                            class="col-sm-2 control-label">@lang('devices.relay') *</label>
                                        <div class="col-sm-10" style="margin-bottom: 3rem">
                                            <input type="text" class="form-control required" name="relays[]" required
                                                placeholder="@lang('devices.relay')">
                                            <button class="add_ports"><i class="fa fa-plus"></i></button>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('related_payment_terminal', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group disable_night_mode_con   {{ $errors->first('disable_night_mode', 'has-error') }}">
                                        <div class="col-sm-10 col-sm-offset-2">
                                            <div class="checkbox checkbox-primary pull-left p-t-0">
                                                <input type="checkbox" id="disable_night_mode"
                                                    class="form-control disable_night_mode" name="disable_night_mode">
                                                <label for="disable_night_mode"> @lang('devices.disable_night_mode') ? </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="form-group light_condition_con  {{ $errors->first('light_condition', 'has-error') }}">
                                        <div class="col-sm-10 col-sm-offset-2">
                                            <div class="checkbox checkbox-primary pull-left p-t-0">
                                                <input type="checkbox" id="light_condition"
                                                    class="form-control light_condition" name="light_condition"
                                                    onclick ="showAndHide()">
                                                <label for="light_condition"> @lang('devices.light_condition') ? </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="form-group light_levels_on hidden {{ $errors->first('light_level', 'has-error') }}">
                                        <label for="light_level" class="col-sm-2 control-label">@lang('devices.light_level')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control light_level" name="light_level">
                                                <option value="1"
                                                    @if (old('light_level') == 1) selected="selected" @endif>
                                                    @lang('devices.light')
                                                </option>
                                                <option value="2"
                                                    @if (old('light_level') == 2) selected="selected" @endif>
                                                    @lang('devices.mid')
                                                </option>
                                                <option value="3"
                                                    @if (old('light_level') == 3) selected="selected" @endif>
                                                    @lang('devices.strong')
                                                </option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('light_level', ':message') }}</span>
                                    </div>
                                    <div
                                        class="form-group light_gain_con hidden {{ $errors->first('gain', 'has-error') }}">
                                        <label for="gain" class="col-sm-2 control-label">@lang('devices.gain')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="gain" name="gain" type="number" min="10"
                                                placeholder="@lang('devices.gain')" class="form-control triple_exposure"
                                                value="{!! old('gain', 10) !!}"
                                                {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                            {!! $errors->first('gain', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group light_exposure_time hidden {{ $errors->first('exposure_time', 'has-error') }}">
                                        <label for="gain" class="col-sm-2 control-label">@lang('devices.exposure_time')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="exposure_time" name="exposure_time" type="text"
                                                placeholder="@lang('devices.exposure_time')" class="form-control triple_exposure"
                                                value="{!! old('exposure_time', 10) !!}"
                                                {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                            {!! $errors->first('exposure_time', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div
                                        class="form-group advert_image_file_con hidden {{ $errors->first('advert_image_file', 'has-error') }}">
                                        <label for="advert_image_file"
                                            class="col-sm-2 control-label">@lang('devices.advert_image_pic')</label>
                                        <div class="col-sm-10">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail" style="width: 200px; height: 200px;">
                                                    <img src="{{ asset('/uploads/devices/default.png') }}"
                                                        alt="advert image pic">
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail"
                                                    style="max-width: 200px; max-height: 200px;"></div>
                                                <div>
                                                    <span class="btn btn-default btn-file">
                                                        <span class="fileinput-new">@lang('account-setting.select_img')</span>
                                                        <span class="fileinput-exists">@lang('account-setting.change')</span>
                                                        <input id="pic" name="advert_image_file" type="file"
                                                            class="form-control" />
                                                    </span>
                                                    <a href="#" class="btn btn-danger fileinput-exists"
                                                        data-dismiss="fileinput">@lang('account-setting.remove')</a>
                                                </div>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('advert_image_file', ':message') }}</span>
                                        </div>
                                    </div>


                                    <div
                                        class="form-group ideal_screen_image hidden {{ $errors->first('idle_screen_image', 'has-error') }}">
                                        <label for="idle_screen_image"
                                            class="col-sm-2 control-label">@lang('devices.idle_screen_image')</label>
                                        <div class="col-sm-10">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail" style="width: 200px; height: 200px;">
                                                    <img src="{{ asset('/uploads/devices/default.png') }}"
                                                        alt="Idle Screen Image">
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail"
                                                    style="max-width: 200px; max-height: 200px;"></div>
                                                <div>
                                                    <span class="btn btn-default btn-file">
                                                        <span class="fileinput-new">@lang('account-setting.select_img')</span>
                                                        <span class="fileinput-exists">@lang('account-setting.change')</span>
                                                        <input id="idle_screen_image" name="idle_screen_image"
                                                            type="file" class="form-control" />
                                                    </span>
                                                    <a href="#" class="btn btn-danger fileinput-exists"
                                                        data-dismiss="fileinput">@lang('account-setting.remove')</a>
                                                </div>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('idle_screen_image', ':message') }}</span>
                                        </div>
                                    </div>


                                </div>
                                <ul class="pager wizard">
                                    <li class="previous"><a href="#">@lang('devices.prev')</a></li>
                                    <li class="next"><a href="#">@lang('devices.nxt')</a></li>
                                    <li class="next finish" style="display:none;"><a
                                            href="javascript:;">@lang('devices.finish')</a></li>
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
    <script src="{{ asset('plugins/components/icheck/icheck.min.js') }}"></script>
    <script src="{{ asset('plugins/components/icheck/icheck.init.js') }}"></script>
    <script src="{{ asset('plugins/components/moment/moment.js') }}"></script>
    <!--{{-- <script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script> --}}-->
    <script src="{{ asset('plugins/components/jqueryui/jquery-ui.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js" type="text/javascript">
    </script>
    <script src="{{ asset('plugins/components/toast-master/js/jquery.toast.js') }}"></script>
    <script src="{{ asset('/js/jquery.mask.js') }}"></script>
    <script src="{{ asset('/js/device.js') }}"></script>

    <script>
        @if (\Session::has('message'))
            $.toast({
                heading: '{{ session()->get('heading') }}',
                position: 'top-center',
                text: '{{ session()->get('message') }}',
                loaderBg: '#ff6849',
                icon: '{{ session()->get('icon') }}',
                hideAfter: 5000,
                stack: 6
            });
        @endif
    </script>
@endpush
