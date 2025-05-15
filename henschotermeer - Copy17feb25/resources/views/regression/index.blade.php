@extends('layouts.master')

@push('css')
    <link href="{{ asset('plugins/components/datatables/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/components/footable/css/footable.bootstrap.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/icheck/skins/all.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
    <!--<link href="{{ asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">-->
    <link href="{{ asset('plugins/components/jqueryui/jquery-ui.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
    <link href="{{ asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.skinModern.css') }}" rel="stylesheet">
    <style>
        .sortable {
            list-style-type: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .checkbox input[type=checkbox] {
            width: 24px
        }

        /*
                .sortable li {
                    margin: 0 5px 5px 5px;
                    padding: 10px;
                    font-size: 1.2em;
                }

                .ui-state-highlight {
                    height: 3.5em;
                    line-height: 1.2em;
                    background-color: lightgray;
                }

                .drop-placeholder {
                    background-color: lightgray;
                    height: 3.5em;
                    padding-top: 12px;
                    padding-bottom: 12px;
                    line-height: 1.2em;
                } */

        .checkbox {
            width: 100%;
            display: flex;
            flex-wrap: wrap
        }

        .checkbox_wrap {
            margin-bottom: 35px;
            width: 25%;
        }

        .checkbox_wrap .check-inner {
            width: 80%;
        }

        .ruleorder {
            counter-increment: css-counter 1;
            /* Increase the counter by 1. */
        }

        #rules_setting ul {
            list-style: none;
            counter-reset: li
        }

        #import_rules_setting ul {
            list-style: none;
            counter-reset: li
        }

        #import_rules_setting li::before {
            content: counter(li);
            color: #456bb3 !important display: inline-block;
            width: 1em;
            margin-left: -42px;
            position: absolute;
        }

        #rules_setting li::before {
            content: counter(li);
            color: #456bb3 !important display: inline-block;
            width: 1em;
            margin-left: -42px;
            position: absolute;
        }

        .ruleorder li {
            counter-increment: li
        }

        .card {
            border-radius: 4px;
            background: #fff;
            box-shadow: 0 6px 10px rgba(0, 0, 0, .08), 0 0 6px rgba(0, 0, 0, .05);
            transition: .3s transform cubic-bezier(.155, 1.105, .295, 1.12), .3s box-shadow, .3s -webkit-transform cubic-bezier(.155, 1.105, .295, 1.12);
            padding: 20px;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, .12), 0 4px 8px rgba(0, 0, 0, .06);
        }

        .card h3 {
            font-weight: 600;
        }

        .dasher {
            border: 1px dashed #ced4da;
            max-width: 1rem;
        }

        .custom-border {
            padding: 10px;
            border: 1px solid lightgray;
            border-radius: 10px;
            text-align: left !important;

        }

        .custom-danger {
            background: #e74a25 !important;
        }

        .custom-success {
            background: #2ecc71 !important;
        }

        .custom-secondary {
            background: #808080 !important;
        }

        .booking-info {
            display: flex;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .booking-box-wrapper {
            display: flex;
            align-items: center;
            margin-right: 8px;
        }

        .booked {
            background: #f36152bd;
        }

        .bDetials {
            font-size: 1.3rem;
            font-weight: 400;
        }

        .booking-box-wrapper {
            display: flex;
            align-items: center;
            margin-right: 8px;
        }

        .bBox {
            width: 18px;
            height: 18px;
            display: inline-block;
            margin-right: 8px;
            border-radius: 50%;
        }

        .available {
            background: #2ecc71;
        }

        .out-order {
            background: #e74a25 !important;

        }

        .charge-in {
            background: #808080 !important;
        }

        .info {
            background: #456bb3 !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid" id="reload">
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <h3 class="box-title pull-left">@lang('regression.regression')</h3>
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
                    <hr>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="form-group col-md-3 col-3">
                                <select class="form-control" name="location_setting" id="location_setting">
                                    <option value="select_location">@lang('regression.choose_setting')</option>
                                    <option value="current_location">@lang('regression.current_location')</option>
                                    @if (Request::path() == 'regression' && (isset($is_imported) && $is_imported == 1))
                                        <option value="other_location">@lang('regression.other_location')</option>
                                    @else
                                        {{-- <option value="other_location"
                                            @if (isset($is_imported) && $is_imported == 1) selected="selected" @endif>@lang('regression.other_location')
                                        </option> --}}
                                    @endif

                                </select>
                            </div>
                            <div class="form-group col-md-offset-6 col-md-3">
                                @if (Request::path() != 'regression')
                                    <a href="{{ url('regression') }}" class=" mb-5 btn btn-primary pull-right">Back</a>
                                @endif
                            </div>
                        </div>
                        <form method="POST" enctype="multipart/form-data" id="current-location-test"
                            class="regression-test current-location hidden">
                            <input type="hidden" name="regression_testing" value= "yes">
                            <div class="row">
                                <div class="form-group col-md-3 col-3">
                                    <select class="form-control location_devices" name="device_id" id="location_devices">
                                        <option value="">@lang('regression.choose_devices')</option>
                                        @if (isset($availableDevices))
                                            @foreach ($availableDevices as $availableDevice)
                                                <optgroup label="{{ $availableDevice->name }}"
                                                    id={{ $availableDevice->id }} class="device-group">
                                                    @foreach ($devices as $device)
                                                        @if ($availableDevice->id == $device->available_device_id)
                                                            <option value="{{ $device->id }}">
                                                                {{ $device->device_name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group col-md-3 col-3">
                                    <input id="identifier" name="identifier" type="text"
                                        placeholder="@lang('regression.identifier') *" class="form-control required"
                                        value="{!! old('regression') !!}" />
                                    <small class="custom-help-block help-block text-danger" style="display:none"></small>
                                    {!! $errors->first('regression', '<span class="help-block">:message</span>') !!}
                                </div>
                                <div class="form-group col-md-3 col-3">
                                    <input id="confidence" name="confidence" type="number" min="0" max="100"
                                        placeholder="@lang('regression.confidence') *" class="form-control required"
                                        value="{!! old('confidence') !!}" />
                                    <small class="custom-help-block help-block text-danger" style="display:none"></small>
                                    {!! $errors->first('confidence', '<span class="help-block">:message</span>') !!}
                                </div>
                                <div class="form-group col-md-3 col-3">
                                    <select class="form-control" name="country_code" id="lang">
                                        <option value="">@lang('regression.choose_lang')</option>
                                        @if (isset($langs))
                                            @foreach ($langs as $lang)
                                                <option value="{{ $lang->code }}">{{ $lang->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-3 col-3 hidden" id="ticket_vehicle_number">
                                    <input id="vehicle_number" name="vehicle_number" type="text"
                                        placeholder="@lang('regression.vehicle_number')" class="form-control required"
                                        value="{!! old('vehicle_number') !!}" />
                                    <small class="custom-help-block help-block text-danger" style="display:none"></small>
                                    {!! $errors->first('vehicle_number', '<span class="help-block">:message</span>') !!}
                                </div>
                                <div class="form-group col-md-3 col-3 hidden" id="vehicle_image_type">
                                    <select class="form-control" name="image_type" id="image_type">
                                        <option value="">@lang('regression.image_type')</option>
                                        <option value="file">@lang('regression.file')</option>
                                        <option value="base_encoded">@lang('regression.base_64')</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 col-3 hidden" id="vehicle_image">
                                    <input id="file" name="file" type="file" placeholder="@lang('regression.file')"
                                        class="form-control required" value="{!! old('file') !!}" />
                                    <small class="custom-help-block help-block text-danger" style="display:none"></small>
                                    {!! $errors->first('file', '<span class="help-block">:message</span>') !!}
                                </div>
                                <div class="form-group col-md-3 col-3 hidden " id="base_encoded">
                                    <input id="file" name="file" type="text" placeholder="@lang('regression.base_64')"
                                        class="form-control " value="{!! old('regression') !!}" />
                                    <small class="custom-help-block help-block text-danger" style="display:none"></small>
                                    {!! $errors->first('file', '<span class="help-block">:message</span>') !!}
                                </div>

                                <div class="form-group col-md-3 col-3 " id="regression_test">
                                    <button id="run_test" class="btn btn-primary ">@lang('regression.submit')</button>
                                </div>
                            </div>
                            <div class="row ml-5" id="rules_setting">
                                <div class="col-md-12 col-12 form-group">
                                    <ul class="checkbox checkbox-primary ruleorder sortable" id="importruleListId">
                                        @if (isset($parkingRules))
                                            @foreach ($parkingRules as $key => $rules)
                                                @if ($key == 'enable')
                                                    @foreach ($rules as $rule)
                                                        <li class="checkbox_wrap rules-group" id="{{ $rule->id }}">
                                                            <div class="check-inner">
                                                                @if (isset($rule->access->enable) && $rule->access->enable != 0)
                                                                    <input type="checkbox"
                                                                        class="form-control parking-rules" name="rules[]"
                                                                        value="{{ $rule->id }}" checked>
                                                                    <label
                                                                        for="parking-rules">{{ ucfirst($rule->name) }}</label>
                                                                @else
                                                                    <input type="checkbox"
                                                                        class="form-control parking-rules no-remove"
                                                                        name="rules[]" value="{{ $rule->id }}">
                                                                    <label
                                                                        for="parking-rules">{{ ucfirst($rule->name) }}</label>
                                                                @endif
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                @elseif($key == 'disable')
                                                    @foreach ($rules as $rule)
                                                        <li class="checkbox_wrap rules-group" id="{{ $rule->id }}">
                                                            <div class="check-inner">
                                                                @if (isset($rule->access->enable) && $rule->access->enable != 0)
                                                                    <input type="checkbox"
                                                                        class="form-control parking-rules" name="rules[]"
                                                                        value="{{ $rule->id }}" checked>
                                                                    <label
                                                                        for="parking-rules">{{ ucfirst($rule->name) }}</label>
                                                                @else
                                                                    <input type="checkbox"
                                                                        class="form-control parking-rules no-remove"
                                                                        name="rules[]" value="{{ $rule->id }}">
                                                                    <label
                                                                        for="parking-rules">{{ ucfirst($rule->name) }}</label>
                                                                @endif
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <p>No Rules</p>
                                                @endif
                                            @endforeach
                                        @endif

                                    </ul>
                                    <input type="hidden" name="sorted_rules" id="sorted_rules" />
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="container-fluid hidden" id="info">
                        <div class="row">
                            <div class="col-md-4 col-4">
                                <div class="booking-info">
                                    <div class="booking-box-wrapper">
                                        <span class="bBox info"></span>
                                        <span class="bDetials">Info</span>
                                    </div>
                                    <div class="booking-box-wrapper">
                                        <span class="bBox available"></span>
                                        <span class="bDetials">Success</span>
                                    </div>
                                    <div class="booking-box-wrapper">
                                        <span class="bBox booked"></span>
                                        <span class="bDetials">Denied</span>
                                    </div>
                                    <div class="booking-box-wrapper">
                                        <span class="bBox charge-in"></span>
                                        <span class="bDetials">Not Applicable</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        @if (isset($is_imported))
                            <span id="is_imported" class="hidden">{{ $is_imported ?? '' }}</span>
                            <div class="row hidden" id="other_location">
                                <form method="POST" enctype="multipart/form-data"
                                    action="{{ url('regression/import-json') }}" id="location-json">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="form-group col-md-3">
                                        <input id="json_file" name="json_file" type="file"
                                            placeholder="@lang('regression.import_file')" class="form-control "
                                            value="{!! old('regression') !!}" />
                                        <small class="custom-help-block help-block text-danger"
                                            style="display:none"></small>
                                        {!! $errors->first('file', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <div class="form-group col-md-3 "id="json_file">
                                        <button id="import-json-data" type="submit"
                                            class="btn btn-primary ">@lang('regression.import-json-data')</button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="row hidden" id="other_location">
                                <form method="POST" enctype="multipart/form-data"
                                    action="{{ url('regression/import-json') }}" id="import-location-json">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="form-group col-md-3">
                                        <input id="json_file" name="json_file" type="file"
                                            placeholder="@lang('regression.import_file')" class="form-control "
                                            value="{!! old('regression') !!}" />
                                        <small class="custom-help-block help-block text-danger"
                                            style="display:none"></small>
                                        {!! $errors->first('file', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <div class="form-group col-md-3 "id="json_file">
                                        <button id="import-json-data" type="submit"
                                            class="btn btn-primary ">@lang('regression.import-json-data')</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                        @if (isset($is_imported) && $is_imported == 1)
                            <form method="POST" enctype="multipart/form-data" id="imported-location-test"
                                class="regression-test imported-location hidden">
                                <input type="hidden" name="regression_testing" value= "yes">
                                <input type="hidden" name="location" value= "import">
                                <div class="row">
                                    <div class="form-group col-md-3 col-3">
                                        <select class="form-control" name="import_device_id"
                                            id="import_location_devices">
                                            <option value="">@lang('regression.choose_devices')</option>
                                            @if (isset($imported_devices))
                                                @foreach ($availableDevices as $availableDevice)
                                                    <optgroup label="{{ $availableDevice->name }}"
                                                        id={{ $availableDevice->id }} class="device-group">
                                                        @foreach ($imported_devices as $device)
                                                            @if ($availableDevice->id == $device->available_device_id)
                                                                <option value="{{ $device->id }}">
                                                                    {{ $device->device_name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3 col-3">
                                        <input id="import_identifier" name="identifier" type="text"
                                            placeholder="@lang('regression.identifier') *" class="form-control required"
                                            value="{!! old('regression') !!}" />
                                        <small class="custom-help-block help-block text-danger"
                                            style="display:none"></small>
                                        {!! $errors->first('regression', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <div class="form-group col-md-3 col-3">
                                        <input id="import_confidence" name="confidence" type="number" min="0"
                                            max="100" placeholder="@lang('regression.confidence') *"
                                            class="form-control required" value="{!! old('confidence') !!}" />
                                        <small class="custom-help-block help-block text-danger"
                                            style="display:none"></small>
                                        {!! $errors->first('confidence', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <div class="form-group col-md-3 col-3">
                                        <select class="form-control" name="country_code" id="import_lang">
                                            <option value="">@lang('regression.choose_lang')</option>
                                            @if (isset($langs))
                                                @foreach ($langs as $lang)
                                                    <option value="{{ $lang->code }}">{{ $lang->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-3 col-3 hidden" id="import_ticket_vehicle_number">
                                        <input id="import_vehicle_number" name="vehicle_number" type="text"
                                            placeholder="@lang('regression.vehicle_number')" class="form-control required"
                                            value="{!! old('vehicle_number') !!}" />
                                        <small class="custom-help-block help-block text-danger"
                                            style="display:none"></small>
                                        {!! $errors->first('vehicle_number', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <div class="form-group col-md-3 col-3 hidden" id="import_vehicle_image_type">
                                        <select class="form-control" name="image_type" id="import_image_type">
                                            <option value="">@lang('regression.image_type')</option>
                                            <option value="file">@lang('regression.file')</option>
                                            <option value="base_encoded">@lang('regression.base_64')</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3 col-3 hidden" id="import_vehicle_image">
                                        <input id="import_file" name="file" type="file"
                                            placeholder="@lang('regression.file')" class="form-control required"
                                            value="{!! old('file') !!}" />
                                        <small class="custom-help-block help-block text-danger"
                                            style="display:none"></small>
                                        {!! $errors->first('file', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <div class="form-group col-md-3 col-3 hidden " id="import_base_encoded">
                                        <input id="import_base_encode" name="file" type="text"
                                            placeholder="@lang('regression.base_64')" class="form-control "
                                            value="{!! old('regression') !!}" />
                                        <small class="custom-help-block help-block text-danger"
                                            style="display:none"></small>
                                        {!! $errors->first('file', '<span class="help-block">:message</span>') !!}
                                    </div>

                                    <div class="form-group col-md-3 col-3 " id="import_regression_test">
                                        <button id="import_run_test" class="btn btn-primary ">@lang('regression.submit')</button>
                                    </div>
                                </div>
                                <div class="row ml-5" id="import_rules_setting">
                                    <div class="col-md-12 col-12 form-group">
                                        <ul class="checkbox checkbox-primary ruleorder sortable" id="ruleListId">
                                            @if (isset($imported_rules))
                                                @foreach ($imported_rules as $key => $rule)
                                                    <li class="checkbox_wrap rules-group" id="{{ $rule->id }}">
                                                        <div>
                                                            @if (isset($rule->access->enable))
                                                                <input type="checkbox"
                                                                    class="form-control import-parking-rules"
                                                                    name="rules[]" value="{{ $rule->id }}" checked>
                                                                <label
                                                                    for="parking-rules">{{ ucfirst($rule->name) }}</label>
                                                            @else
                                                                <input type="checkbox"
                                                                    class="form-control parking-rules no-remove"
                                                                    name="rules[]" value="{{ $rule->id }}">
                                                                <label
                                                                    for="parking-rules">{{ ucfirst($rule->name) }}</label>
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endforeach
                                            @endif

                                        </ul>
                                        <input type="hidden" name="sorted_rules" id="sorted_rules" />
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                    <div class="container-fluid text-center hidden" id="testing_response">
                    </div>
                    <div class="container-fluid text-center hidden mt-5" id="end_response">
                        <div class="row">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    @include('layouts.partials.right-sidebar')
    </div>
@endsection

@push('js')
    <script src="{{ asset('plugins/components/moment/moment.js') }}"></script>
    <script src="{{ asset('plugins/components/toast-master/js/jquery.toast.js') }}"></script>
    <script src="{{ asset('plugins/components/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/components/footable/js/footable.min.js') }}"></script>
    <script src="{{ asset('/js/datatable_lang.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>

    <script src="{{ asset('plugins/components/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
    <script src="{{ asset('plugins/components/icheck/icheck.min.js') }}"></script>
    <script src="{{ asset('plugins/components/icheck/icheck.init.js') }}"></script>
    <!--<script src="{{ asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>-->
    <script src="{{ asset('plugins/components/jqueryui/jquery-ui.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js" type="text/javascript">
    </script>
    <script src="{{ asset('plugins/components/toast-master/js/jquery.toast.js') }}"></script>
    <!-- Clock Plugin JavaScript -->
    <script src="{{ asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.js') }}"></script>
    <!-- Color Picker Plugin JavaScript -->
    <script src="{{ asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asColor.js') }}"></script>
    <script src="{{ asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asGradient.js') }}"></script>
    <script src="{{ asset('plugins/components/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js') }}"></script>
    <script src="{{ asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider-init.js') }}"></script>
    <script src="{{ asset('/js/regression.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.resolved', function(e) {
                var id = $(this).data('id');
                bootbox.confirm({
                    title: "Resolved Issue?",
                    message: "Are you sure want to resolved this issue?",
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancel',
                            className: 'btn-danger'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirm',
                            className: 'btn-success'
                        }
                    },
                    callback: function(result) {
                        if (result) {
                            window.location.href = "{{ url('logs/resolved') }}/" + id;
                        }
                    }
                });
            });

            @if (\Session::has('message'))
                $.toast({
                    heading: '{{ session()->get('heading') }}',
                    position: 'top-center',
                    text: '{{ session()->get('message') }}',
                    loaderBg: '#ff6849',
                    icon: '{{ session()->get('icon') }}',
                    hideAfter: 3000,
                    stack: 6
                });
            @endif
        });
    </script>
@endpush
