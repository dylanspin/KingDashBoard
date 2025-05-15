@extends('layouts.master')

@push('css')
    <link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/icheck/skins/all.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
    <!--{{-- <link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}"> --}}-->
    <link href="{{ asset('plugins/components/jqueryui/jquery-ui.min.css') }}" rel="stylesheet">
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

        .remove_port {
            position: absolute;
            right: 20px;
            top: 6px;
        }
    </style>
@endpush

@section('content')

    <div class="container-fluid">
        <!-- .row -->

        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <h3 class="box-title pull-left">@lang('devices.edit_device')</h3>
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
                    <form id="commentForm" action="{{ url('devices/edit/' . $locationDevices->id) }}" method="POST"
                        enctype="multipart/form-data" class="form-horizontal deviceForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="device_live_id" value="{{ $locationDevices->live_id }}" />
                        <input type="hidden" class="device_id_hidden" value="{{ $locationDevices->id }}" />
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
                                                value="{!! old('device_name', $locationDevices->device_name) !!}" />
                                            <small class="custom-help-block help-block text-danger"
                                                style="display:none"></small>
                                            {!! $errors->first('device_name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('device_type', 'has-error') }}">
                                        <label for="device_type" class="col-sm-2 control-label">@lang('devices.device_type') *</label>
                                        <div class="col-sm-10 text-left">
                                            <input type="hidden" name="device_type"
                                                value="{{ $locationDevices->available_device_id }}">
                                            @foreach ($deviceTypes as $deviceType)
                                                @if ($locationDevices->available_device_id == $deviceType->id)
                                                    <label
                                                        class="col-sm-12 pt-7 font-bold pl-0">{{ $deviceType->name }}</label>
                                                @endif
                                            @endforeach
                                        </div>
                                        <span class="help-block">{{ $errors->first('device_type', ':message') }}</span>
                                    </div>

                                    @if ($locationDevices->available_device_id == 1 || $locationDevices->available_device_id == 2)
                                        
                                        <div
                                            class="form-group related_od_con {{ $errors->first('related_od', 'has-error') }}">
                                            <label for="related_od" class="col-sm-2 control-label">@lang('devices.related_od')</label>
                                            <div class="col-sm-10">
                                                <select class="form-control select2" multiple="" title="Select"
                                                    name="related_od[]">
                                                    @if (count($devices_od_type) > 0)
                                                        @foreach ($devices_od_type as $device)
                                                            <option value="{{ $device->id }}"
                                                                @if (in_array($device->id, $device_ods)) selected="selected" @endif>
                                                                {{ $device->device_name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <span class="help-block">{{ $errors->first('related_od', ':message') }}</span>
                                        </div>
                                        <div class="form-group {{ $errors->first('device_direction', 'has-error') }}">
                                            <label for="device_direction" class="col-sm-2 control-label">@lang('devices.device_directions')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control " title="Select Device Direction..."
                                                    name="device_direction">
                                                    <option value="">@lang('devices.select')</option>
                                                    <option value="bi-directional"
                                                        @if (old('device_direction', $locationDevices->device_direction) == 'bi-directional') selected="selected" @endif>
                                                        @lang('devices.bi_directional')
                                                    </option>
                                                    <option value="in"
                                                        @if (old('device_direction', $locationDevices->device_direction) == 'in') selected="selected" @endif>
                                                        @lang('devices.in')
                                                    </option>
                                                    <option value="out"
                                                        @if (old('device_direction', $locationDevices->device_direction) == 'out') selected="selected" @endif>
                                                        @lang('devices.out')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('device_direction', ':message') }}</span>
                                        </div>
                                    @elseif($locationDevices->available_device_id == 6)
                                        @if (isset($switches))
                                            <div
                                                class="form-group related_switch {{ $errors->first('related_switch', 'has-error') }}">
                                                <label for="related_switch"
                                                    class="col-sm-2 control-label">@lang('devices.payment_terminal') *</label>
                                                <div class="col-sm-10">
                                                    <select class="form-control" title="Select" name="related_switch"
                                                         id="switch_id">
                                                        <option value="">@lang('devices.select')</option>
                                                        @if (count($switches) > 0)
                                                            @foreach ($switches as $switch)
                                                                <option value="{{ $switch->id }}"
                                                                    @if (in_array($switch->id, $related_switches)) selected="selected" @endif>
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
                                                class="form-group open_relays  {{ $errors->first('open_relays', 'has-error') }}">
                                                <label for="open_relays" class="col-sm-2 control-label">@lang('devices.open_relay')
                                                    *</label>
                                                <div class="col-sm-10">
                                                    <select name="open_relay" class="form-control" id="open_relay">
                                                        @if (isset($open_relay))
                                                            <option value="{{ $locationDevices->open_relay }}"
                                                                @if (in_array($locationDevices->open_relay, $db_relays)) selected="selected" @endif>
                                                                {{ $open_relay->relay ?? '' }}
                                                            </option>
                                                        @else
                                                            <option value="">@lang('devices.select')</option>
                                                        @endif
                                                    </select>
                                                    <small class="custom-help-block help-block text-danger"
                                                        style="display:none"></small>
                                                </div>
                                                <span
                                                    class="help-block">{{ $errors->first('open_relays', ':message') }}</span>
                                            </div>
                                            <div
                                                class="form-group close_relays  {{ $errors->first('close_relays', 'has-error') }}">
                                                <label for="related_switch"
                                                    class="col-sm-2 control-label">@lang('devices.close_relay') *</label>
                                                <div class="col-sm-10" id="close_realy_section">
                                                    <select name="close_relay" class="form-control" id="close_relay">
                                                        @if (isset($close_relay))
                                                            <option value="{{ $locationDevices->close_relay }}"
                                                                @if (in_array($locationDevices->close_relay, $db_relays)) selected="selected" @endif>
                                                                {{ $close_relay->relay ?? '' }}
                                                            </option>
                                                        @else
                                                            <option value="">@lang('devices.select')</option>
                                                        @endif

                                                    </select>
                                                    <small class="custom-help-block help-block text-danger"
                                                        style="display:none"></small>
                                                </div>
                                                <span
                                                    class="help-block">{{ $errors->first('close_relays', ':message') }}</span>
                                            </div>

                                        @endif
                                    @elseif($locationDevices->available_device_id == 3)
                                        <div
                                            class="form-group related_ticket_readers_con {{ $errors->first('related_ticket_readers', 'has-error') }}">
                                            <label for="related_ticket_readers"
                                                class="col-sm-2 control-label">@lang('devices.related_ticket_readers') *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select"
                                                    name="related_ticket_readers">
                                                    <option value="">@lang('devices.select')</option>
                                                    @if (count($devices_ticket_reader_type) > 0)
                                                        @foreach ($devices_ticket_reader_type as $device)
                                                            <option value="{{ $device->id }}"
                                                                @if (in_array($device->id, $device_ticket_readers)) selected="selected" @endif>
                                                                {{ $device->device_name }}

                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('related_ticket_readers', ':message') }}</span>
                                        </div>
                                        <div class="form-group {{ $errors->first('device_direction', 'has-error') }}">
                                            <label for="device_direction"
                                                class="col-sm-2 control-label">@lang('devices.device_directions')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select Device Direction..."
                                                    name="device_direction">
                                                    <option value="">@lang('devices.select')</option>
                                                    <option value="bi-directional"
                                                        @if (old('device_direction', $locationDevices->device_direction) == 'bi-directional') selected="selected" @endif>
                                                        @lang('devices.bi_directional')
                                                    </option>
                                                    <option value="in"
                                                        @if (old('device_direction', $locationDevices->device_direction) == 'in') selected="selected" @endif>
                                                        @lang('devices.in')
                                                    </option>
                                                    <option value="out"
                                                        @if (old('device_direction', $locationDevices->device_direction) == 'out') selected="selected" @endif>
                                                        @lang('devices.out')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('device_direction', ':message') }}</span>
                                        </div>
                                    @elseif($locationDevices->available_device_id == 4)
                                        <div
                                            class="form-group related_device_con {{ $errors->first('related_od', 'has-error') }}">
                                            <label for="related_device" class="col-sm-2 control-label">@lang('devices.relate_device')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control select2" multiple="" title="Select"
                                                    name="related_device[]">
                                                    @if (count($devices_not_od_type) > 0)
                                                        @foreach ($devices_not_od_type as $device)
                                                            <option value="{{ $device->id }}"
                                                                @if (in_array($device->id, $od_devices)) selected="selected" @endif>
                                                                {{ $device->device_name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('related_device', ':message') }}</span>
                                        </div>

                                    @endif
                                    <div class="form-group {{ $errors->first('device_ip', 'has-error') }}">
                                        <label for="device_ip" class="col-sm-2 control-label">@lang('devices.device_ip') *</label>
                                        <div class="col-sm-10">
                                            <input id="device_ip" name="device_ip" type="text" maxlength="15"
                                                placeholder="@lang('devices.device_ip')" class="form-control required"
                                                value="{!! old('device_ip', $locationDevices->device_ip) !!}" onkeyup="validateIp(this)" />
                                            <small class="custom-ip-help-block help-block d-none"></small>
                                            {!! $errors->first('device_ip', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    @if ($locationDevices->available_device_id != 12)
                                        <div class="form-group {{ $errors->first('relay', 'has-error') }}">
                                            <label for="device_port" class="col-sm-2 control-label">@lang('devices.device_port')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="device_port" name="device_port" type="number"
                                                    placeholder="@lang('devices.device_port')" class="form-control required"
                                                    value="{!! old('device_port', $locationDevices->device_port) !!}" />

                                                {!! $errors->first('device_port', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($locationDevices->available_device_id == 12)
                                        <div
                                            class="form-group device-password {{ $errors->first('device_password', 'has-error') }}">
                                            <label for="device_password" class="col-sm-2 control-label">@lang('devices.password')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="device_port" name="password" type="number"
                                                    placeholder="@lang('devices.password')" maxlength="4"
                                                    class="form-control required" value="{!! old('device_password', $locationDevices->password) !!}" />

                                                {!! $errors->first('device_password', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                    @endif



                                </div>

                                {{-- @if (!$locationDevices->available_device_id == 12) --}}
                                <div class="tab-pane " id="tab2">
                                    <h2 class="hidden">&nbsp;</h2>
                                    @if ($locationDevices->available_device_id == 1 || $locationDevices->available_device_id == 2)
                                        <div class="form-group {{ $errors->first('anti_passback', 'has-error') }}">
                                            <label for="anti_passback" class="col-sm-2 control-label">@lang('devices.anti_passback')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control anti_passback" title="Select Anti Passback..."
                                                    name="anti_passback">
                                                    <option value="1"
                                                        @if (old('anti_passback', $locationDevices->anti_passback) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('anti_passback', $locationDevices->anti_passback) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('anti_passback', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group time_passback_con {{ old('anti_passback', $locationDevices->anti_passback) == 0 ? 'hidden' : '' }} {{ $errors->first('time_passback', 'has-error') }}">
                                            <label for="time_passback" class="col-sm-2 control-label">@lang('devices.time_passback')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="time_passback" name="time_passback" type="text"
                                                    placeholder="@lang('devices.time_passback')" class="form-control time_passback"
                                                    value="{!! old('time_passback', $locationDevices->anti_passback == 1 ? $locationDevices->time_passback : '5') !!}"
                                                    {{ old('anti_passback', $locationDevices->anti_passback) == 0 ? '' : 'required=""' }} />

                                                {!! $errors->first('time_passback', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div
                                            class="form-group has_gate_con {{ $errors->first('has_gate', 'has-error') }}">
                                            <label for="has_gate" class="col-sm-2 control-label">@lang('devices.has_gate')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control has_gate" title="Select Has Gate"
                                                    name="has_gate">
                                                    <option value="0"
                                                        @if (old('has_gate', $locationDevices->has_gate) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                    <option value="1"
                                                        @if (old('has_gate', $locationDevices->has_gate) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                </select>
                                            </div>
                                            <span class="help-block">{{ $errors->first('has_gate', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group barrier_close_time_con {{ old('has_gate', $locationDevices->has_gate) == 1 ? '' : 'hidden' }} {{ $errors->first('barrier_close_time', 'has-error') }}">
                                            <label for="barrier_close_time"
                                                class="col-sm-2 control-label">@lang('devices.barrier_close_time') *</label>
                                            <div class="col-sm-10">
                                                <input id="barrier_close_time" name="barrier_close_time" type="text"
                                                    placeholder="@lang('devices.barrier_close_time')"
                                                    class="form-control barrier_close_time"
                                                    value="{!! old('barrier_close_time', $locationDevices->has_gate == 1 ? $locationDevices->barrier_close_time : '5') !!}"
                                                    {{ old('has_gate', $locationDevices->has_gate) == 1 ? 'required=""' : '' }} />

                                                {!! $errors->first('barrier_close_time', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->first('enable_log', 'has-error') }}">
                                            <label for="enable_log" class="col-sm-2 control-label">@lang('devices.enable_log')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select Enable Log" name="enable_log">
                                                    <option value="1"
                                                        @if (old('enable_log', $locationDevices->enable_log) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('enable_log', $locationDevices->enable_log) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span class="help-block">{{ $errors->first('enable_log', ':message') }}</span>
                                        </div>
                                        <div class="form-group {{ $errors->first('enable_idle_screen', 'has-error') }}">
                                            <label for="enable_idle_screen"
                                                class="col-sm-2 control-label">@lang('devices.enable_idle_screen') *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control enable_idle_screen"
                                                    title="Select Enable Idle Screen" name="enable_idle_screen">
                                                    <option value="0"
                                                        @if (old('enable_idle_screen', $locationDevices->enable_idle_screen) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                    <option value="1"
                                                        @if (old('enable_idle_screen', $locationDevices->enable_idle_screen) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('enable_idle_screen', ':message') }}</span>
                                        </div>
                                        <div class="form-group {{ $errors->first('qr_code_type', 'has-error') }}">
                                            <label for="qr_code_type" class="col-sm-2 control-label">@lang('devices.qr_code_type')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select QR Code Type"
                                                    name="qr_code_type">
                                                    <option value="csv"
                                                        @if (old('qr_code_type', $locationDevices->qr_code_type) == 'csv') selected="selected" @endif>
                                                        @lang('devices.csv')
                                                    </option>
                                                    <option value="xml"
                                                        @if (old('qr_code_type', $locationDevices->qr_code_type) == 'xml') selected="selected" @endif>
                                                        @lang('devices.xml')
                                                    </option>
                                                    <option value="json"
                                                        @if (old('qr_code_type', $locationDevices->qr_code_type) == 'json') selected="selected" @endif>
                                                        @lang('devices.json')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('qr_code_type', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group focus_away_con {{ $errors->first('focus_away', 'has-error') }}">
                                            <label for="focus_away" class="col-sm-2 control-label">@lang('devices.focus_away')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select Focus Away" name="focus_away">
                                                    <option value="1"
                                                        @if (old('focus_away', $locationDevices->focus_away) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('focus_away', $locationDevices->focus_away) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span class="help-block">{{ $errors->first('focus_away', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group opacity_input_con  {{ $errors->first('opacity_input', 'has-error') }}">
                                            <label for="opacity_input" class="col-sm-2 control-label">@lang('devices.opacity_in')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select Opacity Input"
                                                    name="opacity_input">
                                                    <option value="1"
                                                        @if (old('opacity_input', $locationDevices->opacity_input) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('opacity_input', $locationDevices->opacity_input) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('opacity_input', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group od_enabled_con  {{ $errors->first('od_enabled', 'has-error') }}">
                                            <label for="od_enabled" class="col-sm-2 control-label">@lang('devices.od_enabled')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select OD Enabled" name="od_enabled">
                                                    <option value="0"
                                                        @if (old('od_enabled', $locationDevices->od_enabled) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                    <option value="1"
                                                        @if (old('od_enabled', $locationDevices->od_enabled) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>

                                                </select>
                                            </div>
                                            <span class="help-block">{{ $errors->first('od_enabled', ':message') }}</span>
                                        </div>
                                        <div class="form-group has_sdl_con {{ $errors->first('has_sdl', 'has-error') }}">
                                            <label for="has_sdl" class="col-sm-2 control-label">@lang('devices.has_sdl')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control has_sdl" title="Select SDL" name="has_sdl">
                                                    <option value="1"
                                                        @if (old('has_sdl', $locationDevices->has_sdl) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('has_sdl', $locationDevices->has_sdl) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span class="help-block">{{ $errors->first('has_sdl', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group gate_close_transaction_enabled_con {{ $errors->first('gate_close_transaction_enabled', 'has-error') }}">
                                            <label for="gate_close_transaction_enabled"
                                                class="col-sm-2 control-label">@lang('devices.gate_close_transaction_enabled') *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control gate_close_transaction_enabled"
                                                    title="Enable Register Transaction on Gate Close"
                                                    name="gate_close_transaction_enabled">
                                                    <option value="1"
                                                        @if (old('gate_close_transaction_enabled', $locationDevices->gate_close_transaction_enabled) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('gate_close_transaction_enabled', $locationDevices->gate_close_transaction_enabled) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('gate_close_transaction_enabled', ':message') }}</span>
                                        </div>

                                        @if ($locationDevices->available_device_id == 1)
                                            <div
                                                class="form-group has_pdl_con {{ $errors->first('has_pdl', 'has-error') }}">
                                                <label for="has_pdl" class="col-sm-2 control-label">@lang('devices.has_pdl')
                                                    *</label>
                                                <div class="col-sm-10">
                                                    <select class="form-control has_pdl" title="Enable PDL"
                                                        name="has_pdl">
                                                        <option value="1"
                                                            @if (old('has_pdl', $locationDevices->has_pdl) == 1) selected="selected" @endif>
                                                            @lang('devices.yes')
                                                        </option>
                                                        <option value="0"
                                                            @if (old('has_pdl', $locationDevices->has_pdl) == 0) selected="selected" @endif>
                                                            @lang('devices.no')
                                                        </option>
                                                    </select>
                                                </div>
                                                <span
                                                    class="help-block">{{ $errors->first('has_pdl', ':message') }}</span>
                                            </div>
                                            <div
                                                class="form-group plate_correction_enabled_con {{ $errors->first('plate_correction_enabled', 'has-error') }}">
                                                <label for="plate_correction_enabled"
                                                    class="col-sm-2 control-label">@lang('devices.plate_correction_enabled') *</label>
                                                <div class="col-sm-10">
                                                    <select class="form-control plate_correction_enabled"
                                                        title="Enable Plate Correction" name="plate_correction_enabled">
                                                        <option value="1"
                                                            @if (old('plate_correction_enabled', $locationDevices->plate_correction_enabled) == 1) selected="selected" @endif>
                                                            @lang('devices.yes')
                                                        </option>
                                                        <option value="0"
                                                            @if (old('plate_correction_enabled', $locationDevices->plate_correction_enabled) == 0) selected="selected" @endif>
                                                            @lang('devices.no')
                                                        </option>
                                                    </select>
                                                </div>
                                                <span
                                                    class="help-block">{{ $errors->first('plate_correction_enabled', ':message') }}</span>
                                            </div>
                                            <div
                                                class="form-group tr_version_con {{ $errors->first('tr_version', 'has-error') }}">
                                                <label for="tr_version" class="col-sm-2 control-label">@lang('devices.tr_version')
                                                    *</label>
                                                <div class="col-sm-10">
                                                    <select class="form-control tr_version" name="tr_version"
                                                        required="">

                                                        <option value='1.0'
                                                            @if (old('tr_version') == '1.0' || $locationDevices->tr_version == '1.0') selected="selected" @endif>
                                                            @lang('devices.tr_version_old')</option>
                                                        <option value='2.0'
                                                            @if (old('tr_version') == '2.0' || $locationDevices->tr_version != '1.0') selected="selected" @endif>
                                                            @lang('devices.tr_version_new')</option>
                                                    </select>
                                                </div>
                                                <span
                                                    class="help-block">{{ $errors->first('is_advance_booking_limit', ':message') }}</span>
                                            </div>
                                        @endif
                                    @elseif($locationDevices->available_device_id == 3)
                                        <div class="form-group {{ $errors->first('enable_log', 'has-error') }}">
                                            <label for="enable_log" class="col-sm-2 control-label">@lang('devices.enable_log')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select Enable Log" name="enable_log">
                                                    <option value="1"
                                                        @if (old('enable_log', $locationDevices->enable_log) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('enable_log', $locationDevices->enable_log) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span class="help-block">{{ $errors->first('enable_log', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group confidence_level_con  {{ $errors->first('confidence_level', 'has-error') }}">
                                            <label for="confidence_level"
                                                class="col-sm-2 control-label">@lang('devices.confidence_level') *</label>
                                            <div class="col-sm-10">
                                                <input id="confidence_level" name="confidence_level" type="text"
                                                    placeholder="@lang('devices.confidence_level')" class="form-control confidence_level"
                                                    value="{!! old('confidence_level', $locationDevices->confidence != null ? $locationDevices->confidence : 80) !!}"
                                                    {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                                {!! $errors->first('confidence_level', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div
                                            class="form-group num_tries_con   {{ $errors->first('num_tries', 'has-error') }}">
                                            <label for="num_tries" class="col-sm-2 control-label">@lang('devices.num_tries')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="num_tries" name="num_tries" type="text"
                                                    placeholder="@lang('devices.num_tries')" class="form-control num_tries"
                                                    value="{!! old('num_tries', $locationDevices->retries != null ? $locationDevices->retries : 5) !!}"
                                                    {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                                {!! $errors->first('num_tries', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div class="form-group confidence_level_lowest_con hidden">
                                            <label for="message_text_size"
                                                class="col-sm-2 control-label">@lang('devices.confidence_level_lowest') *</label>
                                            <div class="col-sm-10">
                                                <input id="confidence_level_lowest" name="confidence_level_lowest"
                                                    type="text" placeholder="@lang('devices.confidence_level_lowest')"
                                                    class="form-control confidence_level_lowest"
                                                    value="{{ $locationDevices->confidence_level_lowest != null ? $locationDevices->confidence_level_lowest : 10 }}" />
                                            </div>
                                        </div>
                                        <div
                                            class="form-group matching_distance_con {{ $errors->first('matching_distance', 'has-error') }}">
                                            <label for="matching_distance"
                                                class="col-sm-2 control-label">@lang('devices.matching_distance') *</label>
                                            <div class="col-sm-10">
                                                <input id="matching_distance" name="matching_distance" type="text"
                                                    placeholder="@lang('devices.matching_distance')"
                                                    class="form-control matching_distance"
                                                    value="{!! old('matching_distance', $locationDevices->matching_distance != null ? $locationDevices->matching_distance : 5) !!}"
                                                    {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                                {!! $errors->first('matching_distance', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div class="form-group has_sdl_con {{ $errors->first('has_sdl', 'has-error') }}">
                                            <label for="has_sdl" class="col-sm-2 control-label">@lang('devices.has_sdl')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control has_sdl" title="Select SDL" name="has_sdl">
                                                    <option value="1"
                                                        @if (old('has_sdl', $locationDevices->has_sdl) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('has_sdl', $locationDevices->has_sdl) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span class="help-block">{{ $errors->first('has_sdl', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group gate_close_transaction_enabled_con {{ $errors->first('gate_close_transaction_enabled', 'has-error') }}">
                                            <label for="gate_close_transaction_enabled"
                                                class="col-sm-2 control-label">@lang('devices.gate_close_transaction_enabled') *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control gate_close_transaction_enabled"
                                                    title="Enable Register Transaction on Gate Close"
                                                    name="gate_close_transaction_enabled">
                                                    <option value="1"
                                                        @if (old('gate_close_transaction_enabled', $locationDevices->gate_close_transaction_enabled) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('gate_close_transaction_enabled', $locationDevices->gate_close_transaction_enabled) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('gate_close_transaction_enabled', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group has_gate_con {{ $errors->first('has_gate', 'has-error') }}">
                                            <label for="has_gate" class="col-sm-2 control-label">@lang('devices.has_gate')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control has_gate" title="Select Has Gate"
                                                    name="has_gate">
                                                    <option value="0"
                                                        @if (old('has_gate', $locationDevices->has_gate) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                    <option value="1"
                                                        @if (old('has_gate', $locationDevices->has_gate) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                </select>
                                            </div>
                                            <span class="help-block">{{ $errors->first('has_gate', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group has_emergency_con  {{ $errors->first('has_emergency_con', 'has-error') }}">
                                            <label for="has_emergency" class="col-sm-2 control-label">@lang('devices.has_emergency')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control has_emergency" name="has_emergency">
                                                    <option value="1"
                                                        @if (old('has_emergency', $locationDevices->emergency_entry_exit) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('has_emergency', $locationDevices->emergency_entry_exit) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('has_emergency_con', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group plate_length_con  {{ $errors->first('plate_length', 'has-error') }}">
                                            <label for="plate_length" class="col-sm-2 control-label">@lang('devices.plate_length')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="character_match_limit" name="character_match_limit"
                                                    type="text" placeholder="@lang('devices.plate_length')"
                                                    class="form-control character_match_limit"
                                                    value="{{ $locationDevices->character_match_limit != null ? $locationDevices->character_match_limit : 4 }}" />
                                                {!! $errors->first('plate_length', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div
                                            class="form-group character_height_con  {{ $errors->first('character_height', 'has-error') }}">
                                            <label for="character_height"
                                                class="col-sm-2 control-label">@lang('devices.character_height')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="character_height" name="character_height" type="number"
                                                    min="15" placeholder="@lang('devices.character_height')"
                                                    class="form-control character_height" value="{!! old('character_height',$locationDevices->character_height != null ? $locationDevices->character_height : '20') !!}"
                                                    {{ old('device_type') == 3 ? 'required=""' : '' }} />
                                                {!! $errors->first('character_height', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div
                                            class="form-group triple_exposure_con {{ $errors->first('exposure_mode', 'has-error') }}">
                                            <label for="triple_exposure"
                                                class="col-sm-2 control-label">@lang('devices.triple_exposure')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control exposure_mode" name="exposure_mode">
                                                    <option value="triple_exposure"
                                                        @if ($locationDevices->triple_exposure) selected="selected" @endif>
                                                        @lang('devices.triple_exposure')
                                                    </option>
                                                    <option value="auto"
                                                        @if ($locationDevices->auto) selected="selected" @endif>
                                                        @lang('devices.auto')
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        @if ($locationDevices->disable_night_mode)
                                            <div
                                                class="form-group disable_night_mode_con   {{ $errors->first('disable_night_mode', 'has-error') }}">
                                                <div class="col-sm-10 col-sm-offset-2">
                                                    <div class="checkbox checkbox-primary pull-left p-t-0">
                                                        <input type="checkbox" id="disable_night_mode"
                                                            class="form-control disable_night_mode"
                                                            name="disable_night_mode" checked>
                                                        <label for="disable_night_mode"> @lang('devices.disable_night_mode') ? </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div
                                                class="form-group disable_night_mode_con   {{ $errors->first('disable_night_mode', 'has-error') }}">
                                                <div class="col-sm-10 col-sm-offset-2">
                                                    <div class="checkbox checkbox-primary pull-left p-t-0">
                                                        <input type="checkbox" id="disable_night_mode"
                                                            class="form-control disable_night_mode"
                                                            name="disable_night_mode">
                                                        <label for="disable_night_mode"> @lang('devices.disable_night_mode') ? </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (isset($locationDevices->light_condition) && $locationDevices->light_condition != 0)
                                            <div
                                                class="form-group light_condition_con  {{ $errors->first('light_condition', 'has-error') }}">
                                                <div class="col-sm-10 col-sm-offset-2">
                                                    <div class="checkbox checkbox-primary pull-left p-t-0">
                                                        <input type="checkbox" id="light_condition"
                                                            class="form-control light_condition" name="light_condition"
                                                            checked onclick ="showAndHide()">
                                                        <label for="light_condition"> @lang('devices.light_condition') ? </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="form-group light_levels_on  {{ $errors->first('light_level', 'has-error') }}">
                                                <label for="light_level"
                                                    class="col-sm-2 control-label">@lang('devices.light_level')
                                                    *</label>
                                                <div class="col-sm-10">
                                                    <select class="form-control light_level" name="light_level">
                                                        <option value="1"
                                                            @if (isset($light_conditions) && $light_conditions->level == 1) selected="selected" @endif>
                                                            @lang('devices.light')
                                                        </option>
                                                        <option value="2"
                                                            @if (isset($light_conditions) && $light_conditions->level == 2) selected="selected" @endif>
                                                            @lang('devices.mid')
                                                        </option>
                                                        <option value="3"
                                                            @if (isset($light_conditions) && $light_conditions->level == 3) selected="selected" @endif>
                                                            @lang('devices.strong')
                                                        </option>
                                                    </select>
                                                </div>
                                                <span
                                                    class="help-block">{{ $errors->first('light_level', ':message') }}</span>
                                            </div>
                                            <div
                                                class="form-group light_gain_con{{ $errors->first('gain', 'has-error') }}">
                                                <label for="gain" class="col-sm-2 control-label">@lang('devices.gain')
                                                    *</label>
                                                <div class="col-sm-10">
                                                    <input id="gain" name="gain" type="number" min="10"
                                                        placeholder="@lang('devices.gain')" class="form-control gain"
                                                        value="{!! old('gain', isset($light_conditions) && $light_conditions->gain != null ? $light_conditions->gain : '10') !!}"
                                                        {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                                    {!! $errors->first('gain', '<span class="help-block">:message</span>') !!}
                                                </div>
                                            </div>
                                            <div
                                                class="form-group light_exposure_time  {{ $errors->first('exposure_time', 'has-error') }}">
                                                <label for="gain" class="col-sm-2 control-label">@lang('devices.exposure_time')
                                                    *</label>
                                                <div class="col-sm-10">
                                                    <input id="exposure_time" name="exposure_time" type="text"
                                                        placeholder="@lang('devices.exposure_time')"
                                                        class="form-control triple_exposure"
                                                        value="{!! old('gain', isset($light_conditions) && $light_conditions->exposure_time != null ? $light_conditions->exposure_time : '10') !!}"
                                                        {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                                    {!! $errors->first('exposure_time', '<span class="help-block">:message</span>') !!}
                                                </div>
                                            </div>
                                            {{-- @endif --}}
                                        @else
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
                                                <label for="light_level"
                                                    class="col-sm-2 control-label">@lang('devices.light_level')
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
                                                <span
                                                    class="help-block">{{ $errors->first('light_level', ':message') }}</span>
                                            </div>
                                            <div
                                                class="form-group light_gain_con hidden {{ $errors->first('gain', 'has-error') }}">
                                                <label for="gain" class="col-sm-2 control-label">@lang('devices.gain')
                                                    *</label>
                                                <div class="col-sm-10">
                                                    <input id="gain" name="gain" type="number" min="10"
                                                        placeholder="@lang('devices.gain')"
                                                        class="form-control triple_exposure"
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
                                                        placeholder="@lang('devices.exposure_time')"
                                                        class="form-control triple_exposure"
                                                        value="{!! old('exposure_time', 10) !!}"
                                                        {{ old('device_type') == 3 ? 'required=""' : '' }} />

                                                    {!! $errors->first('exposure_time', '<span class="help-block">:message</span>') !!}
                                                </div>
                                            </div>
                                        @endif
                                    @elseif($locationDevices->available_device_id == 6)
                                        <div class="form-group {{ $errors->first('enable_log', 'has-error') }}">
                                            <label for="enable_log" class="col-sm-2 control-label">@lang('devices.enable_log')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control" title="Select Enable Log" name="enable_log">
                                                    <option value="1"
                                                        @if (old('enable_log', $locationDevices->enable_log) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('enable_log', $locationDevices->enable_log) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('enable_log', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group ccv_pos_ip_con  {{ $errors->first('ccv_pos_ip', 'has-error') }}">
                                            <label for="ccv_pos_ip" class="col-sm-2 control-label">@lang('devices.ccv_pos_ip')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="ccv_pos_ip" name="ccv_pos_ip" type="text"
                                                    placeholder="@lang('devices.ccv_pos_ip')" class="form-control ccv_pos_ip"
                                                    value="{!! old('ccv_pos_ip', $locationDevices->ccv_pos_ip) !!}"
                                                    {{ old('device_type') == 6 ? 'required=""' : '' }} />

                                                {!! $errors->first('ccv_pos_ip', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div
                                            class="form-group ccv_pos_port_con {{ $errors->first('ccv_pos_port', 'has-error') }}">
                                            <label for="ccv_pos_port" class="col-sm-2 control-label">@lang('devices.ccv_pos_port')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="ccv_pos_port" name="ccv_pos_port" type="text"
                                                    placeholder="@lang('devices.ccv_pos_port')" class="form-control ccv_pos_port"
                                                    value="{!! old('ccv_pos_port', $locationDevices->ccv_pos_port) !!}"
                                                    {{ old('device_type') == 6 ? 'required=""' : '' }} />

                                                {!! $errors->first('ccv_pos_port', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div
                                            class="form-group has_enable_person_ticket_con {{ $errors->first('has_enable_person_ticket', 'has-error') }}">
                                            <div class="col-sm-10 col-sm-offset-2">
                                                <div class="checkbox checkbox-primary pull-left p-t-0">
                                                    <input type="checkbox" id="has_enable_person_ticket"
                                                        class="form-control has_enable_person_ticket"
                                                        name="has_enable_person_ticket"
                                                        {{ $locationDevices->has_enable_person_ticket == 1 ? 'checked' : '' }}>
                                                    <label for="has_enable_person_ticket"> @lang('devices.has_enable_person_ticket') ? </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="form-group has_enable_parking_ticket_con {{ $errors->first('has_enable_parking_ticket', 'has-error') }}">
                                            <div class="col-sm-10 col-sm-offset-2">
                                                <div class="checkbox checkbox-primary pull-left p-t-0">
                                                    <input type="checkbox" id="has_enable_parking_ticket"
                                                        class="form-control has_enable_parking_ticket"
                                                        name="has_enable_parking_ticket"
                                                        {{ $locationDevices->has_enable_parking_ticket == 1 ? 'checked' : '' }}>
                                                    <label for="has_enable_parking_ticket"> @lang('devices.has_enable_parking_ticket') ? </label>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($locationDevices->available_device_id == 4)
                                        <div class="form-group {{ $errors->first('enable_idle_screen', 'has-error') }}">
                                            <label for="enable_idle_screen"
                                                class="col-sm-2 control-label">@lang('devices.enable_idle_screen') *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control enable_idle_screen"
                                                    title="Select Enable Idle Screen" name="enable_idle_screen">
                                                    <option value="0"
                                                        @if (old('enable_idle_screen', $locationDevices->enable_idle_screen) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                    <option value="1"
                                                        @if (old('enable_idle_screen', $locationDevices->enable_idle_screen) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('enable_idle_screen', ':message') }}</span>
                                        </div>
                                        <div
                                            class="form-group message_text_size_con {{ $errors->first('message_text_size', 'has-error') }}">
                                            <label for="message_text_size"
                                                class="col-sm-2 control-label">@lang('devices.message_text_size') *</label>
                                            <div class="col-sm-10">
                                                <input id="message_text_size" name="message_text_size" type="text"
                                                    placeholder="@lang('devices.message_text_size')"
                                                    class="form-control message_text_size"
                                                    value="{!! old('message_text_size',$locationDevices->message_text_size != null ? $locationDevices->message_text_size : '60') !!}"
                                                    {{ old('device_type') == 3 ? 'required=""' : '' }} />
                                                {!! $errors->first('message_text_size', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div
                                            class="form-group time_text_size_con {{ $errors->first('time_text_size', 'has-error') }}">
                                            <label for="time_text_size" class="col-sm-2 control-label">@lang('devices.time_text_size')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="time_text_size" name="time_text_size" type="text"
                                                    placeholder="@lang('devices.time_text_size')" class="form-control time_text_size"
                                                    value="{!! old('time_text_size', $locationDevices->time_text_size != null ? $locationDevices->time_text_size : '190') !!}"
                                                    {{ old('device_type', $locationDevices->device_type) == 4 ? 'required=""' : '' }} />

                                                {!! $errors->first('time_text_size', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div
                                            class="form-group date_text_size_con {{ $errors->first('date_text_size', 'has-error') }}">
                                            <label for="date_text_size" class="col-sm-2 control-label">@lang('devices.date_text_size')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="date_text_size" name="date_text_size" type="text"
                                                    placeholder="@lang('devices.date_text_size')" class="form-control date_text_size"
                                                    value="{!! old('date_text_size', $locationDevices->date_text_size != null ? $locationDevices->date_text_size : '60') !!}"
                                                    {{ old('device_type', $locationDevices->device_type) == 4 ? 'required=""' : '' }} />

                                                {!! $errors->first('date_text_size', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div
                                            class="form-group bottom_tray_text_size_con {{ $errors->first('bottom_tray_text_size', 'has-error') }}">
                                            <label for="bottom_tray_text_size"
                                                class="col-sm-2 control-label">@lang('devices.bottom_tray_text_size') *</label>
                                            <div class="col-sm-10">
                                                <input id="bottom_tray_text_size" name="bottom_tray_text_size"
                                                    type="text" placeholder="@lang('devices.bottom_tray_text_size')"
                                                    class="form-control bottom_tray_text_size"
                                                    value="{!! old('bottom_tray_text_size',$locationDevices->bottom_tray_text_size != null ? $locationDevices->bottom_tray_text_size : '60') !!}"
                                                    {{ old('device_type', $locationDevices->device_type) == 4 ? 'required=""' : '' }} />

                                                {!! $errors->first('bottom_tray_text_size', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        
                                    @endif

                                    @if ($locationDevices->available_device_id != 12)
                                        <div
                                            class="form-group popup_time_con {{ $errors->first('popup_time', 'has-error') }}">
                                            <label for="popup_time" class="col-sm-2 control-label">@lang('devices.popup_time')
                                                *</label>
                                            <div class="col-sm-10">
                                                <input id="popup_time" name="popup_time" type="number"
                                                    placeholder="@lang('devices.popup_time')" class="form-control popup_time"
                                                    value="{!! old('popup_time', $locationDevices->popup_time) !!}" required="" />

                                                {!! $errors->first('popup_time', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->first('has_always_access', 'has-error') }}">
                                            <label for="has_always_access"
                                                class="col-sm-2 control-label">@lang('devices.has_always_access')
                                                *</label>
                                            <div class="col-sm-10">
                                                <select class="form-control has_always_access" name="has_always_access">
                                                    <option value="1"
                                                        @if (old('has_always_access', $locationDevices->has_always_access) == 1) selected="selected" @endif>
                                                        @lang('devices.yes')
                                                    </option>
                                                    <option value="0"
                                                        @if (old('has_always_access', $locationDevices->has_always_access) == 0) selected="selected" @endif>
                                                        @lang('devices.no')
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('has_always_access', ':message') }}</span>
                                        </div>
                                    @endif
                                    @if ($locationDevices->available_device_id == 12)

                                        <div
                                            class="form-group barrier_close_time_con {{ $errors->first('barrier_close_time', 'has-error') }}">
                                            <label for="barrier_close_time"
                                                class="col-sm-2 control-label">@lang('devices.barrier_close_time') *</label>
                                            <div class="col-sm-10">
                                                <input id="barrier_close_time" name="barrier_close_time" type="text"
                                                    placeholder="@lang('devices.barrier_close_time')"
                                                    class="form-control barrier_close_time"
                                                    value="{{ $locationDevices->barrier_close_time }}" />

                                                {!! $errors->first('barrier_close_time', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        @if (isset($related_ports) && !empty($related_ports))
                                            @foreach ($related_ports->ports as $related_port)
                                                <div
                                                    class="form-group mt-5 device-related-port-{{ $related_port->id }} {{ $errors->first('device-related-port', 'has-error') }}">
                                                    <label for="device-related-port"
                                                        class="col-sm-2 control-label device-label">@lang('devices.relay')
                                                    </label>
                                                    <div class="col-sm-10" style="margin-bottom: 1rem">
                                                        <input type="text" class="form-control"
                                                            value="{{ $related_port->relay }}"
                                                            name="relays[{{ $related_port->id }}]"
                                                            placeholder="@lang('devices.relay')">
                                                        <button class="remove_port delete-port"
                                                            data-id={{ $related_port->id }} data-toggle="modal"
                                                            data-target="#remove_port"><i
                                                                class="fa fa-trash"></i></button>
                                                        <input type="hidden" value={{ $related_port->device_id }}
                                                            id="device_related_switch">
                                                    </div>
                                                    <span
                                                        class="help-block">{{ $errors->first('device-related-port', ':message') }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                        <div
                                            class="form-group device-related-port {{ $errors->first('device-related-port', 'has-error') }}">
                                            <label for="device-related-port"
                                                class="col-sm-2 control-label">@lang('devices.relay')</label>
                                            <div class="col-sm-10" style="margin-bottom: 3rem">
                                                <input type="text" class="form-control" name="relays[]"
                                                    placeholder="@lang('devices.relay')">
                                                <button class="add_ports"><i class="fa fa-plus"></i></button>
                                            </div>
                                            <span
                                                class="help-block">{{ $errors->first('device-related-port', ':message') }}</span>
                                        </div>

                                    @endif
                                    @if (in_array($locationDevices->available_device_id, [1, 2, 4, 6]))
                                        <div class="form-group {{ $errors->first('advert_image_file', 'has-error') }}">
                                            <label for="advert_image_file"
                                                class="col-sm-2 control-label">@lang('devices.advert_image_pic')</label>
                                            <div class="col-sm-10">
                                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                                    <div class="fileinput-new thumbnail"
                                                        style="width: 200px; height: 200px;">
                                                        @if ($locationDevices->advert_image_path != null)
                                                            <img src="{{ asset($locationDevices->advert_image_path) }}"
                                                                alt="advert image pic">
                                                        @else
                                                            <img src="{{ asset('/uploads/devices/default.png') }}"
                                                                alt="advert image pic">
                                                        @endif
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
                                    @endif
                                    @if (in_array($locationDevices->available_device_id, [1, 2]))
                                        <div class="form-group {{ $errors->first('idle_screen_image', 'has-error') }}">
                                            <label for="idle_screen_image"
                                                class="col-sm-2 control-label">@lang('devices.idle_screen_image')</label>
                                            <div class="col-sm-10">
                                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                                    <div class="fileinput-new thumbnail"
                                                        style="width: 200px; height: 200px;">
                                                        @if ($locationDevices->idle_screen_image != null)
                                                            <img src="{{ asset($locationDevices->idle_screen_image) }}"
                                                                alt="Idle screen image">
                                                        @else
                                                            <img src="{{ asset('/uploads/devices/default.png') }}"
                                                                alt="Idle screen image">
                                                        @endif
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
                                    @endif
                                </div>
                                {{-- @endif --}}

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
        <div id="remove_port" class="modal fade">
            <div class="modal-dialog modal-confirm">
                <div class="modal-content">
                    <div class="modal-header flex-column">
                        <div class="icon-box">
                            <i class="fa fa-exclamation"></i>
                        </div>
                        <h4 class="text">@lang('reservations.sure')</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>@lang('reservations.really') </p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">@lang('reservations.cancel')</button>
                        <input type="hidden" id="device_switch">
                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="remove_relay"
                            onclick="remove_port(this)">@lang('reservations.delete')</button>
                    </div>
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
        function remove_port(handle) {
            let id = $(handle).data('id');
            let device = $("#device_switch").val();
            $(handle).parent().parent().remove();
            $(this).parent().parent().remove();
            $.ajax({
                url: "/devices/delete-port",
                type: "post",
                data: {
                    port: id,
                    device: device
                },
                success: function(_response) {
                    //console.log(_response);
                    window.location.reload();
                }
            });
        }
    </script>
@endpush
