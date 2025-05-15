@extends('layouts.master')

@push('css')
    <link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/icheck/skins/all.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
    <!--{{-- <link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}"> --}}-->
    <link href="{{ asset('plugins/components/jqueryui/jquery-ui.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
    <link href="{{ asset('plugins/components/dropzone-master/dist/dropzone.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.skinModern.css') }}" rel="stylesheet">
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
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- .row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <h3 class="box-title pull-left">@lang('location-setting.loc_setting')</h3>
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
                    <div class="error_message_con"></div>
                    <form id="commentForm" action="{{ url('location/edit') }}" method="POST" enctype="multipart/form-data"
                        class="form-horizontal locationForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="location_live_id" value="{{ $location->live_id }}" />

                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab1" class="address-panel" data-toggle="tab"
                                        style="cursor:pointer;">@lang('location-setting.address')</a>
                                </li>
                                <li>
                                    <a href="#tab2" class="settings-panel" data-toggle="tab"
                                        style="cursor:pointer;">@lang('location-setting.settings')</a>
                                </li>
                                {{-- <li>
                                    <a href="#tab3" class="shop-hours-panel" data-toggle="tab"
                                        style="cursor:pointer;">@lang('location-setting.shop_hours')</a>
                                </li> --}}
                                <li>
                                    <a href="#tab4" class="working-hours-panel" data-toggle="tab"
                                        style="cursor:pointer;">@lang('location-setting.shop_hours')</a>
                                </li>
                                <li>
                                    <a href="#tab7" class="working-hours-panel" data-toggle="tab"
                                        style="cursor:pointer;">@lang('location-setting.person_hours')</a>
                                </li>
                                <li>
                                    <a href="#tab8" class="working-hours-panel" data-toggle="tab"
                                        style="cursor:pointer;">@lang('location-setting.today_hours')</a>
                                </li>
                                <li>
                                    <a href="#tab5" class="price-details-panel" data-toggle="tab"
                                        style="cursor:pointer;">@lang('location-setting.price_details')</a>
                                </li>
                                {{-- <li>
                                    <a href="#tab6" class="extra-feature-panel" data-toggle="tab"
                                        style="cursor:pointer;">@lang('location-setting.extra_features')</a>
                                </li> --}}
                                <li>
                                    <a href="#tab9" class="email-pdf-settings-panel" data-toggle="tab"
                                        style="cursor:pointer;">@lang('location-setting.email_pdf_settings')</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group {{ $errors->first('title', 'has-error') }}">
                                        <label for="title" class="col-sm-2 control-label">@lang('location-setting.title') *</label>
                                        <div class="col-sm-10">
                                            <input id="title" name="title" type="text"
                                                placeholder="@lang('location-setting.title')" class="form-control title"
                                                value="{!! old('title', $location->title) !!}" required="" />

                                            {!! $errors->first('title', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('address', 'has-error') }}">
                                        <label for="email" class="col-sm-2 control-label">@lang('location-setting.address') *</label>
                                        <div class="col-sm-10">
                                            <input id="address" name="address" placeholder="@lang('location-setting.address')"
                                                type="text" class="form-control address" value="{!! old('address', $location->address) !!}"
                                                readonly="" />

                                            {!! $errors->first('address', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('postal_code', 'has-error') }}">
                                        <label for="postal_code" class="col-sm-2 control-label">@lang('location-setting.postal_code')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="postal_code" name="postal_code" type="text"
                                                placeholder="@lang('location-setting.postal_code')" class="form-control"
                                                value="{!! old('postal_code', $location->postal_code) !!}" readonly="" />

                                            {!! $errors->first('postal_code', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('city_country', 'has-error') }}">
                                        <label for="city_country"
                                            class="col-sm-2 control-label">@lang('location-setting.city_country')*</label>
                                        <div class="col-sm-10">
                                            <input id="city_country" name="city_country" type="text"
                                                placeholder="@lang('location-setting.city_country')" class="form-control city_country"
                                                value="{!! old('city_country', $location->city_country) !!}" readonly="" />

                                            {!! $errors->first('city_country', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="description" class="col-sm-2 control-label">@lang('location-setting.desc')
                                            <small>(@lang('location-setting.brief_intro')) </small>
                                        </label>
                                        <div class="col-sm-10">
                                            <textarea name="description" id="description" class="form-control resize_vertical description" rows="6"
                                                maxlength="500">{!! old('description', $location->description) !!}</textarea>
                                            <small>@lang('location-setting.max_char')</small>
                                        </div>

                                        {!! $errors->first('description', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab2" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group {{ $errors->first('is_gated', 'has-error') }}">
                                        <label for="is_gated" class="col-sm-2 control-label">@lang('location-setting.gated') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="is_gated" required="">

                                                <option value='0'
                                                    @if (old('is_gated') === 0 || $location->is_gated === 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('is_gated') === 1 || $location->is_gated === 1) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('is_gated', ':message') }}</span>
                                    </div>

                                    <div class="form-group {{ $errors->first('is_covered', 'has-error') }}">
                                        <label for="is_covered" class="col-sm-2 control-label">@lang('location-setting.covered')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="is_covered" required="">

                                                <option value='0'
                                                    @if (old('is_covered') == 0 || $location->is_covered == 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('is_covered') == 1 || $location->is_covered == 1) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('is_covered', ':message') }}</span>
                                    </div>

                                    <div class="form-group {{ $errors->first('total_spots', 'has-error') }}">
                                        <label for="total_spots" class="col-sm-2 control-label">@lang('location-setting.total_spots')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="total_spots" name="total_spots" type="number"
                                                placeholder="@lang('location-setting.total_spots')" class="form-control"
                                                value="{!! old('total_spots', $location->total_spots) !!}" required="" />

                                            {!! $errors->first('total_spots', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('today_spots', 'has-error') }}">
                                        <label for="today_spots" class="col-sm-2 control-label">@lang('location-setting.today_spots')
                                        </label>
                                        <div class="col-sm-10">
                                            <input id="today_spots" name="today_spots" type="number"
                                                placeholder="@lang('location-setting.today_spots')" class="form-control"
                                                value="{!! old('today_spots', $todayParkingSpots) !!}" required="" />

                                            {!! $errors->first('today_spots', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div
                                        class="form-group {{ $errors->first('online_booking_stop_parking', 'has-error') }}">
                                        <label for="online_booking_stop_parking"
                                            class="col-sm-2 control-label">@lang('location-setting.available_spots') *</label>
                                        <div class="col-sm-10">
                                            <input id="online_booking_stop_parking" name="online_booking_stop_parking"
                                                type="text" placeholder="@lang('location-setting.available_spots')" class="form-control"
                                                value="{!! old('online_booking_stop_parking', $location->online_booking_stop_parking) !!}" required="" />

                                            {!! $errors->first('online_booking_stop_parking', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('total_spots_person', 'has-error') }}">
                                        <label for="total_spots_person" class="col-sm-2 control-label">@lang('location-setting.total_spots_person')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="total_spots_person" name="total_spots_person" type="number"
                                                placeholder="@lang('location-setting.total_spots_person')" class="form-control"
                                                value="{!! old('total_spots_person', $location->total_spots_person) !!}" required="" />

                                            {!! $errors->first('total_spots_person', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('today_spots_person', 'has-error') }}">
                                        <label for="today_spots_person" class="col-sm-2 control-label">@lang('location-setting.today_spots_person')
                                        </label>
                                        <div class="col-sm-10">
                                            <input id="today_spots_person" name="today_spots_person" type="number"
                                                placeholder="@lang('location-setting.today_spots_person')" class="form-control"
                                                value="{!! old('today_spots_person', $todayPersonSpots) !!}" required="" />

                                            {!! $errors->first('today_spots_person', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div
                                        class="form-group {{ $errors->first('online_booking_stop_person', 'has-error') }}">
                                        <label for="online_booking_stop_person"
                                            class="col-sm-2 control-label">@lang('location-setting.online_booking_stop_person') *</label>
                                        <div class="col-sm-10">
                                            <input id="online_booking_stop_person" name="online_booking_stop_person"
                                                type="text" placeholder="@lang('location-setting.online_booking_stop_person')" class="form-control"
                                                value="{!! old('online_booking_stop_person', $location->online_booking_stop_person) !!}" required="" />

                                            {!! $errors->first('online_booking_stop_person', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('owner_operator_name', 'has-error') }}">
                                        <label for="owner_operator_name" class="col-sm-2 control-label">@lang('location-setting.owner_op_name')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="owner_operator_name" name="owner_operator_name" type="text"
                                                placeholder="@lang('location-setting.owner_op_name')" class="form-control"
                                                value="{!! old('owner_operator_name', $location->owner_operator_name) !!}" required="" />

                                            {!! $errors->first('owner_operator_name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('owner_phone_num', 'has-error') }}">
                                        <label for="owner_phone_num" class="col-sm-2 control-label">@lang('location-setting.owner_ph_no')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="owner_phone_num" name="owner_phone_num" type="text"
                                                placeholder="@lang('location-setting.owner_ph_no')" class="form-control phone_mask"
                                                value="{!! old('owner_phone_num', $location->owner_phone_num) !!}" required="" />

                                            {!! $errors->first('owner_phone_num', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('location_type', 'has-error') }}">
                                        <label for="location_type" class="col-sm-2 control-label">@lang('location-setting.loc_type')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="location_type" required="">

                                                <option @if (old('location_type') == 'public' || $location->location_type == 'public') selected="selected" @endif
                                                    value="public">@lang('location-setting.public')</option>
                                                <option @if (old('location_type') == 'private' || $location->location_type == 'private') selected="selected" @endif
                                                    value="private">@lang('location-setting.private')</option>
                                                <option @if (old('location_type') == 'goverment' || $location->location_type == 'goverment') selected="selected" @endif
                                                    value="goverment">@lang('location-setting.govt')</option>
                                                <option @if (old('location_type') == 'business' || $location->location_type == 'business') selected="selected" @endif
                                                    value="business">@lang('location-setting.business')</option>
                                                <option @if (old('location_type') == 'event' || $location->location_type == 'event') selected="selected" @endif
                                                    value="event">@lang('location-setting.event')</option>
                                                <option @if (old('location_type') == 'retail' || $location->location_type == 'retail') selected="selected" @endif
                                                    value="retail">@lang('location-setting.retail')</option>
                                                <option @if (old('location_type') == 'hospital' || $location->location_type == 'hospital') selected="selected" @endif
                                                    value="hospital">@lang('location-setting.hospital')</option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('location_type', ':message') }}</span>
                                    </div>

                                    <div class="form-group {{ $errors->first('language', 'has-error') }}">
                                        <label for="language" class="col-sm-2 control-label">@lang('location-setting.lang') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="language" required="">

                                                @foreach ($languages as $language)
                                                    <option value="{{ $language->id }}"
                                                        @if (old('language') === $language->id || $location->language_id === $language->id) selected="selected" @endif>
                                                        {{ $language->code . ' ' . $language->name . ',' . $language->country }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('language', ':message') }}</span>
                                    </div>

                                    <div class="form-group {{ $errors->first('is_whitelist', 'has-error') }}">
                                        <label for="is_whitelist" class="col-sm-2 control-label">@lang('location-setting.allowed_hours')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control is_whitelist" name="is_whitelist" required="">

                                                <option value='0'
                                                    @if (old('is_whitelist') == 0 || $location->is_whitelist == 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('is_whitelist') == 1 || $location->is_whitelist == 1) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('is_whitelist', ':message') }}</span>
                                    </div>

                                    <div class="form-group {{ $errors->first('is_max_stay', 'has-error') }}">
                                        <label for="is_max_stay" class="col-sm-2 control-label">@lang('location-setting.max_stay_limit')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control is_max_stay" name="is_max_stay" required="">

                                                <option value='0'
                                                    @if (old('is_max_stay') == 0 || $location->max_stay == 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('is_max_stay') == 1 || $location->max_stay != 0) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('is_max_stay', ':message') }}</span>
                                    </div>

                                    <div
                                        class="form-group maximum_stay_con {{ $location->max_stay == 0 ? 'hidden' : '' }} {{ $errors->first('maximum_stay', 'has-error') }}">
                                        <label for="maximum_stay" class="col-sm-2 control-label">@lang('location-setting.max_limit_hours')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="maximum_stay" name="maximum_stay" type="number"
                                                placeholder="@lang('location-setting.max_limit_hours')" class="form-control maximum_stay"
                                                value="{!! old('maximum_stay', $location->max_stay) !!}"
                                                @if ($location->max_stay != 0) required="" @endif />

                                            {!! $errors->first('maximum_stay', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('is_advance_booking_limit', 'has-error') }}">
                                        <label for="is_advance_booking_limit"
                                            class="col-sm-2 control-label">@lang('location-setting.adv_reserve_limit') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control is_advance_booking_limit"
                                                name="is_advance_booking_limit" required="">

                                                <option value='0'
                                                    @if (old('is_advance_booking_limit') == 0 || $location->advance_booking_limit == 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('is_advance_booking_limit') == 1 || $location->advance_booking_limit != 0) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('is_advance_booking_limit', ':message') }}</span>
                                    </div>

                                    <div
                                        class="form-group advance_booking_time_con {{ $location->advance_booking_limit == 0 ? 'hidden' : '' }} {{ $errors->first('advance_booking_time', 'has-error') }}">
                                        <label for="advance_booking_time"
                                            class="col-sm-2 control-label">@lang('location-setting.adv_reserve_limit_hrs') *</label>
                                        <div class="col-sm-10">
                                            <input id="advance_booking_time" name="advance_booking_time" type="number"
                                                placeholder="@lang('location-setting.adv_reserve_limit_hrs')" class="form-control advance_booking_time"
                                                value="{!! old('advance_booking_time', $location->advance_booking_limit) !!}"
                                                @if ($location->is_advance_booking_limit != 0) required="" @endif />

                                            {!! $errors->first('advance_booking_time', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div
                                        class="form-group {{ $errors->first('is_barcode_series_available', 'has-error') }}">
                                        <label for="is_barcode_series_available"
                                            class="col-sm-2 control-label">@lang('location-setting.barcode') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control is_barcode_series_available"
                                                name="is_barcode_series_available" required="">

                                                <option value='0'
                                                    @if (old('is_barcode_series_available') == 0 || $location->barcode_series == null) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('is_barcode_series_available') == 1 || $location->barcode_series != null) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('is_barcode_series_available', ':message') }}</span>
                                    </div>

                                    <div
                                        class="form-group barcode_series_con {{ $location->barcode_series == null ? 'hidden' : '' }} {{ $errors->first('barcode_series', 'has-error') }}">
                                        <label for="barcode_series" class="col-sm-2 control-label">@lang('location-setting.barcode_series')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="barcode_series" name="barcode_series" type="text"
                                                placeholder="@lang('location-setting.barcode_series')" class="form-control barcode_series"
                                                value="{!! old('barcode_series', $location->barcode_series) !!}"
                                                @if ($location->is_barcode_series_available != '0') required="" @endif />

                                            {!! $errors->first('barcode_series', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('height_restriction', 'has-error') }}">
                                        <label for="height_restriction" class="col-sm-2 control-label">@lang('location-setting.height_rest')
                                            *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control height_restriction" name="height_restriction"
                                                required="">

                                                <option value='0'
                                                    @if (old('height_restriction') == 0 || $location->height_restriction_value == 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('height_restriction') == 1 || $location->height_restriction_value != 0) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('height_restriction', ':message') }}</span>
                                    </div>

                                    <div
                                        class="form-group height_resstriction_value_con {{ $location->height_restriction_value == 0 ? 'hidden' : '' }} {{ $errors->first('height_restriction_value', 'has-error') }}">
                                        <label for="height_restriction_value"
                                            class="col-sm-2 control-label">@lang('location-setting.height_rest_mtr') *</label>
                                        <div class="col-sm-10">
                                            <input id="height_restriction_value" name="height_restriction_value"
                                                type="number" placeholder="@lang('location-setting.height_rest_mtr')"
                                                class="form-control height_resstriction_value"
                                                value="{!! old('height_resstriction_value', $location->height_restriction_value) !!}"
                                                @if ($location->height_restriction != '0') required="" @endif />

                                            {!! $errors->first('height_restriction_value', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('time_lag', 'has-error') }}">
                                        <label for="time_lag" class="col-sm-2 control-label">@lang('location-setting.time_lag') *</label>
                                        <div class="col-sm-10">
                                            <input id="time_lag" name="time_lag" type="text"
                                                placeholder="@lang('location-setting.time_lag')" class="form-control"
                                                value="{!! old('time_lag', $location->time_lag) !!}" required="" />

                                            {!! $errors->first('time_lag', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('real_time_payments', 'has-error') }}">
                                        <label for="real_time_payments" class="col-sm-2 control-label">@lang('location-setting.real_time_payments') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="real_time_payments" required="">

                                                <option value='0'
                                                    @if (old('real_time_payments') === 0 || $location->real_time_payments === 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('real_time_payments') === 1 || $location->real_time_payments === 1) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('is_gated', ':message') }}</span>
                                    </div>
									<div class="form-group {{ $errors->first('map_enable', 'has-error') }}">
                                        <label for="map_enable" class="col-sm-2 control-label">@lang('location-setting.map_enable') *</label>
                                        <div class="col-sm-10">
                                           
                                            <select class="form-control" name="map_enable" required="">
                                                <option value='0'
                                                    @if (old('map_enable') === 0 || $location->map_enable === 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('map_enable') === 1 || $location->map_enable === 1) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('is_gated', ':message') }}</span>
                                    </div>
                                    
                                </div>

                                <div class="tab-pane" id="tab3" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="col-sm-12 text-center p-b-10">
                                        <div class="col-sm-8">
                                            <div class="col-sm-2"></div>
                                            <div class="col-sm-5">@lang('location-setting.open_time')</div>
                                            <div class="col-sm-5">@lang('location-setting.close_time')</div>
                                        </div>
                                    </div>
                                    @for ($i = 1; $i <= 6; $i++)
                                        <div class="col-sm-12 text-center p-b-10">
                                            <input type="hidden" name="weekday_id[{{ $i }}]"
                                                value="{!! empty($timings['weekDaysTimings'][$i]) ? '0' : $timings['weekDaysTimings'][$i]['id'] !!}" />
                                            <input type="hidden" name="weekday_live_id[{{ $i }}]"
                                                value="{!! empty($timings['weekDaysTimings'][$i]) ? '0' : $timings['weekDaysTimings'][$i]['live_id'] !!}" />
                                            <div class="col-sm-9">
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <div class="checkbox checkbox-info text-left">
                                                            <input type="checkbox"
                                                                name="weekday_checkbox[{{ $i }}]"
                                                                class="weekdays_checkbox weekday_checkbox_{{ $i }}"
                                                                data-week_day_num='{{ $i }}'
                                                                onclick="return add_location_weekdays_checkboxes(this)"
                                                                @if (!empty($timings['weekDaysTimings'][$i])) checked @endif>
                                                            <label for="checkbox7"> {{ $dowMap[$i] }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div
                                                        class="form-group {{ $errors->first('opening_time_day[' . $i . ']', 'has-error') }}">
                                                        <div class="input-group clockpicker">
                                                            <input type="text"
                                                                name="opening_time_day[{{ $i }}]"
                                                                class="form-control opening_time_day opening_time_day{{ $i }}"
                                                                value="{!! old(
                                                                    'opening_time_day[{{ $i }}]',
                                                                    empty($timings['weekDaysTimings'][$i]) ? '00:00' : $timings['weekDaysTimings'][$i]['opening_time']
                                                                ) !!}"
                                                                @if (!empty($timings['weekDaysTimings'][$i])) required="" @endif>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-time"></span>
                                                            </span>

                                                            {!! $errors->first('opening_time_day[$i]', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div
                                                        class="form-group {{ $errors->first('closing_time_day[' . $i . ']', 'has-error') }}">
                                                        <div class="input-group clockpicker">
                                                            <input type="text"
                                                                name="closing_time_day[{{ $i }}]"
                                                                class="form-control closing_time_day closing_time_day{{ $i }}"
                                                                value="{!! old(
                                                                    'closing_time_day[{{ $i }}]',
                                                                    empty($timings['weekDaysTimings'][$i]) ? '00:00' : $timings['weekDaysTimings'][$i]['closing_time']
                                                                ) !!}"
                                                                @if (!empty($timings['weekDaysTimings'][$i])) required="" @endif>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-time"></span>
                                                            </span>

                                                            {!! $errors->first('closing_time_day[$i]', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if ($i == 1)
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <div class="checkbox checkbox-info text-left">
                                                            <input type="checkbox" name="sametime_for_days"
                                                                class="sametime_for_days"
                                                                onclick="sametime_for_all_weekdays(this)">
                                                            <label for="checkbox7"> @lang('location-setting.same_time') </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endfor
                                    <div class="col-sm-12 text-center p-b-10">
                                        <input type="hidden" name="weekday_id[0]" value="{!! empty($timings['weekDaysTimings'][0]) ? '0' : $timings['weekDaysTimings'][0]['id'] !!}" />
                                        <input type="hidden" name="weekday_live_id[0]"
                                            value="{!! empty($timings['weekDaysTimings'][0]) ? '0' : $timings['weekDaysTimings'][0]['live_id'] !!}" />
                                        <div class="col-sm-9">
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <div class="checkbox checkbox-info text-left">
                                                        <input type="checkbox" name="weekday_checkbox[0]"
                                                            class="weekdays_checkbox weekday_checkbox_0"
                                                            data-week_day_num='0'
                                                            onclick="return add_location_weekdays_checkboxes(this)"
                                                            {{ empty($timings['weekDaysTimings'][0]) ? '' : 'checked' }}>
                                                        <label for="checkbox7"> {{ $dowMap[0] }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <div
                                                    class="form-group {{ $errors->first('opening_time_day[0]', 'has-error') }}">
                                                    <div class="input-group clockpicker">
                                                        <input type="text" name="opening_time_day[0]"
                                                            class="form-control opening_time_day opening_time_day0"
                                                            value="{!! old(
                                                                'opening_time_day[0]',
                                                                empty($timings['weekDaysTimings'][0]) ? '00:00' : $timings['weekDaysTimings'][0]['opening_time']
                                                            ) !!}"
                                                            {{ empty($timings['weekDaysTimings'][0]) ? '' : 'required=""' }}>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </span>

                                                        {!! $errors->first('closing_time_day[0]', '<span class="help-block">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <div
                                                    class="form-group {{ $errors->first('closing_time_day[0]', 'has-error') }}">
                                                    <div class="input-group clockpicker">
                                                        <input type="text" name="closing_time_day[0]"
                                                            class="form-control closing_time_day closing_time_day0"
                                                            value="{!! old(
                                                                'closing_time_day[0]',
                                                                empty($timings['weekDaysTimings'][0]) ? '00:00' : $timings['weekDaysTimings'][0]['closing_time']
                                                            ) !!}"
                                                            {{ empty($timings['weekDaysTimings'][0]) ? '' : 'required=""' }}>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </span>

                                                        {!! $errors->first('closing_time_day[0]', '<span class="help-block">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab4" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="col-sm-12 text-center p-b-10">
                                        <div class="col-sm-8">
                                            <div class="col-sm-2"></div>
                                            <div class="col-sm-5">@lang('location-setting.open_time')</div>
                                            <div class="col-sm-5">@lang('location-setting.close_time')</div>
                                        </div>
                                    </div>
                                    @for ($i = 1; $i <= 6; $i++)
                                        <div class="col-sm-12 text-center p-b-10">
                                            <input type="hidden" name="w_weekday_id[{{ $i }}]"
                                                value="{!! empty($timings['whiteListWeekDaysTimings'][$i]) ? '0' : $timings['whiteListWeekDaysTimings'][$i]['id'] !!}" />
                                            <input type="hidden" name="w_weekday_live_id[{{ $i }}]"
                                                value="{!! empty($timings['whiteListWeekDaysTimings'][$i]) ? '0' : $timings['whiteListWeekDaysTimings'][$i]['live_id'] !!}" />
                                            <div class="col-sm-9">
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <div class="checkbox checkbox-info text-left">
                                                            <input type="checkbox"
                                                                name="w_weekday_checkbox[{{ $i }}]"
                                                                class="w_weekdays_checkbox w_weekday_checkbox_{{ $i }}"
                                                                data-week_day_num='{{ $i }}'
                                                                onclick="return add_location_whitelist_weekdays_checkboxes(this)"
                                                                {{ empty($timings['whiteListWeekDaysTimings'][$i]) ? '' : 'checked' }}>
                                                            <label for="checkbox7"> {{ $dowMap[$i] }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div
                                                        class="form-group {{ $errors->first('w_opening_time_day[' . $i . ']', 'has-error') }}">
                                                        <div class="input-group clockpicker">
                                                            <input type="text"
                                                                name="w_opening_time_day[{{ $i }}]"
                                                                class="form-control w_opening_time_day w_opening_time_day{{ $i }}"
                                                                value="{!! old(
                                                                    'w_opening_time_day[{{ $i }}]',
                                                                    empty($timings['whiteListWeekDaysTimings'][$i])
                                                                        ? '00:00'
                                                                        : $timings['whiteListWeekDaysTimings'][$i]['opening_time']
                                                                ) !!}"
                                                                {{ empty($timings['whiteListWeekDaysTimings'][$i]) ? '' : 'required=""' }}>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-time"></span>
                                                            </span>

                                                            {!! $errors->first('w_opening_time_day[$i]', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div
                                                        class="form-group {{ $errors->first('w_closing_time_day[' . $i . ']', 'has-error') }}">
                                                        <div class="input-group clockpicker">
                                                            <input type="text"
                                                                name="w_closing_time_day[{{ $i }}]"
                                                                class="form-control w_closing_time_day w_closing_time_day{{ $i }}"
                                                                value="{!! old(
                                                                    'w_closing_time_day[{{ $i }}]',
                                                                    empty($timings['whiteListWeekDaysTimings'][$i])
                                                                        ? '00:00'
                                                                        : $timings['whiteListWeekDaysTimings'][$i]['closing_time']
                                                                ) !!}"
                                                                {{ empty($timings['whiteListWeekDaysTimings'][$i]) ? '' : 'required=""' }}>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-time"></span>
                                                            </span>

                                                            {!! $errors->first('w_closing_time_day[$i]', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if ($i == 1)
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <div class="checkbox checkbox-info text-left">
                                                            <input type="checkbox" name="w_sametime_for_days"
                                                                class="w_sametime_for_days"
                                                                onclick="return w_sametime_for_all_weekdays(this)">
                                                            <label for="checkbox7"> @lang('location-setting.same_time') </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endfor
                                    <div class="col-sm-12 text-center p-b-10">
                                        <input type="hidden" name="w_weekday_id[0]" value="{!! empty($timings['whiteListWeekDaysTimings'][0]) ? '0' : $timings['whiteListWeekDaysTimings'][0]['id'] !!}" />
                                        <input type="hidden" name="w_weekday_live_id[0]"
                                            value="{!! empty($timings['whiteListWeekDaysTimings'][0]) ? '0' : $timings['whiteListWeekDaysTimings'][0]['live_id'] !!}" />
                                        <div class="col-sm-9">
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <div class="checkbox checkbox-info text-left">
                                                        <input type="checkbox" name="w_weekday_checkbox[0]"
                                                            class="w_weekdays_checkbox w_weekday_checkbox_0"
                                                            data-week_day_num='0'
                                                            onclick="return add_location_whitelist_weekdays_checkboxes(this)"
                                                            {{ empty($timings['whiteListWeekDaysTimings'][0]) ? '' : 'checked' }}>
                                                        <label for="checkbox7"> {{ $dowMap[0] }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <div
                                                    class="form-group {{ $errors->first('w_opening_time_day[0]', 'has-error') }}">
                                                    <div class="input-group clockpicker">
                                                        <input type="text" name="w_opening_time_day[0]"
                                                            class="form-control w_opening_time_day w_opening_time_day0"
                                                            value="{!! old(
                                                                'w_opening_time_day[0]',
                                                                empty($timings['whiteListWeekDaysTimings'][0]) ? '00:00' : $timings['whiteListWeekDaysTimings'][0]['opening_time']
                                                            ) !!}"
                                                            {{ empty($timings['whiteListWeekDaysTimings'][0]) ? '' : 'required=""' }}>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </span>

                                                        {!! $errors->first('w_opening_time_day[0]', '<span class="help-block">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <div
                                                    class="form-group {{ $errors->first('w_closing_time_day[0]', 'has-error') }}">
                                                    <div class="input-group clockpicker">
                                                        <input type="text" name="w_closing_time_day[0]"
                                                            class="form-control w_closing_time_day w_closing_time_day0"
                                                            value="{!! old(
                                                                'w_closing_time_day[0]',
                                                                empty($timings['whiteListWeekDaysTimings'][0]) ? '00:00' : $timings['whiteListWeekDaysTimings'][0]['closing_time']
                                                            ) !!}"
                                                            {{ empty($timings['whiteListWeekDaysTimings'][0]) ? '' : 'required=""' }}>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </span>

                                                        {!! $errors->first('w_closing_time_day[0]', '<span class="help-block">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        </div>
                                <div class="tab-pane" id="tab7" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="col-sm-12 text-center p-b-10">
                                        <div class="col-sm-8">
                                            <div class="col-sm-2"></div>
                                            <div class="col-sm-5">@lang('location-setting.open_time')</div>
                                            <div class="col-sm-5">@lang('location-setting.close_time')</div>
                                        </div>
                                    </div>
                                    @for ($i = 1; $i <= 6; $i++)
                                        <div class="col-sm-12 text-center p-b-10">
                                            <input type="hidden" name="p_weekday_id[{{ $i }}]"
                                                value="{!! empty($timings['personWeekDaysTimings'][$i]) ? '0' : $timings['personWeekDaysTimings'][$i]['id'] !!}" />
                                            <input type="hidden" name="p_weekday_live_id[{{ $i }}]"
                                                value="{!! empty($timings['personWeekDaysTimings'][$i]) ? '0' : $timings['personWeekDaysTimings'][$i]['live_id'] !!}" />
                                            <div class="col-sm-9">
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <div class="checkbox checkbox-info text-left">
                                                            <input type="checkbox"
                                                                name="p_weekday_checkbox[{{ $i }}]"
                                                                class="p_weekdays_checkbox p_weekday_checkbox_{{ $i }}"
                                                                data-week_day_num='{{ $i }}'
                                                                onclick="return add_location_person_weekdays_checkboxes(this)"
                                                                {{ empty($timings['personWeekDaysTimings'][$i]) ? '' : 'checked' }}>
                                                            <label for="checkbox7"> {{ $dowMap[$i] }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div
                                                        class="form-group {{ $errors->first('p_opening_time_day[' . $i . ']', 'has-error') }}">
                                                        <div class="input-group clockpicker">
                                                            <input type="text"
                                                                name="p_opening_time_day[{{ $i }}]"
                                                                class="form-control p_opening_time_day p_opening_time_day{{ $i }}"
                                                                value="{!! old(
                                                                    'p_opening_time_day[{{ $i }}]',
                                                                    empty($timings['personWeekDaysTimings'][$i]) ? '00:00' : $timings['personWeekDaysTimings'][$i]['opening_time']
                                                                ) !!}"
                                                                {{ empty($timings['personWeekDaysTimings'][$i]) ? '' : 'required=""' }}>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-time"></span>
                                                            </span>

                                                            {!! $errors->first('p_opening_time_day[$i]', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div
                                                        class="form-group {{ $errors->first('p_closing_time_day[' . $i . ']', 'has-error') }}">
                                                        <div class="input-group clockpicker">
                                                            <input type="text"
                                                                name="p_closing_time_day[{{ $i }}]"
                                                                class="form-control p_closing_time_day p_closing_time_day{{ $i }}"
                                                                value="{!! old(
                                                                    'p_closing_time_day[{{ $i }}]',
                                                                    empty($timings['personWeekDaysTimings'][$i]) ? '00:00' : $timings['personWeekDaysTimings'][$i]['closing_time']
                                                                ) !!}"
                                                                {{ empty($timings['personWeekDaysTimings'][$i]) ? '' : 'required=""' }}>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-time"></span>
                                                            </span>

                                                            {!! $errors->first('p_closing_time_day[$i]', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if ($i == 1)
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <div class="checkbox checkbox-info text-left">
                                                            <input type="checkbox" name="p_sametime_for_days"
                                                                class="p_sametime_for_days"
                                                                onclick="return p_sametime_for_all_weekdays(this)">
                                                            <label for="checkbox7"> @lang('location-setting.same_time') </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endfor
                                    <div class="col-sm-12 text-center p-b-10">
                                        <input type="hidden" name="p_weekday_id[0]" value="{!! empty($timings['personWeekDaysTimings'][0]) ? '0' : $timings['personWeekDaysTimings'][0]['id'] !!}" />
                                        <input type="hidden" name="p_weekday_live_id[0]"
                                            value="{!! empty($timings['personWeekDaysTimings'][0]) ? '0' : $timings['personWeekDaysTimings'][0]['live_id'] !!}" />
                                        <div class="col-sm-9">
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <div class="checkbox checkbox-info text-left">
                                                        <input type="checkbox" name="p_weekday_checkbox[0]"
                                                            class="p_weekdays_checkbox p_weekday_checkbox_0"
                                                            data-week_day_num='0'
                                                            onclick="return add_location_person_weekdays_checkboxes(this)"
                                                            {{ empty($timings['personWeekDaysTimings'][0]) ? '' : 'checked' }}>
                                                        <label for="checkbox7"> {{ $dowMap[0] }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <div
                                                    class="form-group {{ $errors->first('p_opening_time_day[0]', 'has-error') }}">
                                                    <div class="input-group clockpicker">
                                                        <input type="text" name="p_opening_time_day[0]"
                                                            class="form-control p_opening_time_day p_opening_time_day0"
                                                            value="{!! old(
                                                                'p_opening_time_day[0]',
                                                                empty($timings['personWeekDaysTimings'][0]) ? '00:00' : $timings['personWeekDaysTimings'][0]['opening_time']
                                                            ) !!}"
                                                            {{ empty($timings['personWeekDaysTimings'][0]) ? '' : 'required=""' }}>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </span>

                                                        {!! $errors->first('p_opening_time_day[0]', '<span class="help-block">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <div
                                                    class="form-group {{ $errors->first('p_closing_time_day[0]', 'has-error') }}">
                                                    <div class="input-group clockpicker">
                                                        <input type="text" name="p_closing_time_day[0]"
                                                            class="form-control p_closing_time_day p_closing_time_day0"
                                                            value="{!! old(
                                                                'p_closing_time_day[0]',
                                                                empty($timings['personWeekDaysTimings'][0]) ? '00:00' : $timings['personWeekDaysTimings'][0]['closing_time']
                                                            ) !!}"
                                                            {{ empty($timings['personWeekDaysTimings'][0]) ? '' : 'required=""' }}>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </span>

                                                        {!! $errors->first('p_closing_time_day[0]', '<span class="help-block">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<div class="tab-pane" id="tab8" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="col-sm-12 text-center p-b-10">
                                        <div class="col-sm-8">
                                            <div class="col-sm-2"></div>
                                            <div class="col-sm-5">@lang('location-setting.open_time')</div>
                                            <div class="col-sm-5">@lang('location-setting.close_time')</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 text-center p-b-10">
                                        <div class="col-sm-8">
                                            <div class="col-sm-2">
                                                <div class="checkbox checkbox-info text-left">
                                                    <input type="checkbox" name="today_hours" class="today_hours" {{ empty($today_hours) ? '' : 'checked' }}>
                                                    <label for="checkbox8">@lang('location-setting.today')</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <div
                                                    class="form-group {{ $errors->first('today_opening_time', 'has-error') }}">
                                                    <div class="input-group clockpicker">
                                                        <input type="text" name="today_opening_time"
                                                            class="form-control today_opening_time"
                                                            value="{{ $today_hours->opening_time ?? '00:00' }}">
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </span>

                                                        {!! $errors->first('today_opening_time', '<span class="help-block">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <div
                                                    class="form-group {{ $errors->first('today_closing_time', 'has-error') }}">
                                                    <div class="input-group clockpicker">
                                                        <input type="text" name="today_closing_time"
                                                            class="form-control today_closing_time"
                                                            value="{{ $today_hours->closing_time ?? ""}}">
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </span>

                                                        {!! $errors->first('today_closing_time', '<span class="help-block">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab5" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group {{ $errors->first('price_per_hour', 'has-error') }}">
                                        <label for="price_per_hour" class="col-sm-2 control-label">@lang('location-setting.price_per_hr')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="price_per_hour" name="price_per_hour" type="text"
                                                placeholder="@lang('location-setting.price_per_hr')" class="form-control price_per_hour"
                                                value="{!! old('price_per_hour', $location->price_per_hour) !!}" required="" />

                                            {!! $errors->first('price_per_hour', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('price_per_day', 'has-error') }}">
                                        <label for="email" class="col-sm-2 control-label">@lang('location-setting.price_per_day') *</label>
                                        <div class="col-sm-10">
                                            <input id="price_per_day" name="price_per_day"
                                                placeholder="@lang('location-setting.price_per_day')" type="text"
                                                class="form-control price_per_day" value="{!! old('price_per_day', $location->price_per_day) !!}"
                                                required="" />

                                            {!! $errors->first('price_per_day', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    {{-- <div class="form-group {{ $errors->first('is_doors', 'has-error') }}">
                                        <label for="is_doors" class="col-sm-2 control-label">@lang('location-setting.doors_available') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control is_doors" name="is_doors" required="">

                                                <option value='0'
                                                    @if (old('is_doors') == 0 || $location->is_doors == 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('is_doors') == 1 || $location->is_doors == 1) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('is_doors', ':message') }}</span>
                                    </div>

                                    <div
                                        class="form-group door_selector_con {{ $location->is_doors == 0 ? 'hidden' : '' }} {{ $errors->first('door_selector', 'has-error') }}">
                                        <label for="door_selector" class="col-sm-2 control-label">@lang('location-setting.doors_range')
                                        </label>
                                        <input type="hidden" name="door_selector_from" class="selector-from"
                                            value="{!! old('door_selector_from', $location->door_range_start) !!}" />
                                        <input type="hidden" name="door_selector_to" class="selector-to"
                                            value="{!! old('door_selector_to', $location->door_range_end) !!}" />
                                        <div class="col-sm-10">
                                            <input id="door_selector" name="door_selector" type="text"
                                                class="form-control door_selector input_slider" data-type="double"
                                                data-grid="true" data-min="0" data-max="100"
                                                data-from="{!! old('door_selector_from', $location->door_range_start) !!}" data-to="{!! old('door_selector_to', $location->door_range_end) !!}"
                                                data-step="1" />

                                            {!! $errors->first('door_selector', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('is_bikes', 'has-error') }}">
                                        <label for="is_bikes" class="col-sm-2 control-label">@lang('location-setting.bikes_available') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control is_bikes" name="is_bikes" required="">

                                                <option value='0'
                                                    @if (old('is_bikes') == 0 || $location->is_bikes == 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('is_bikes') == 1 || $location->is_bikes == 1) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span class="help-block">{{ $errors->first('is_bikes', ':message') }}</span>
                                    </div>

                                    <div
                                        class="form-group bike_selector_con {{ $location->is_bikes == 0 ? 'hidden' : '' }} {{ $errors->first('bike_selector', 'has-error') }}">
                                        <label for="bike_selector" class="col-sm-2 control-label">Bikes Range </label>
                                        <input type="hidden" name="bike_selector_from" class="from"
                                            value="{!! old('bike_selector_from', $location->bike_range_start) !!}" />
                                        <input type="hidden" name="bike_selector_to" class="to"
                                            value="{!! old('bike_selector_to', $location->bike_range_end) !!}" />
                                        <div class="col-sm-10">
                                            <input id="bike_selector" name="bike_selector" type="text"
                                                class="form-control bike_selector input_slider" data-type="double"
                                                data-grid="true" data-min="0" data-max="100"
                                                data-from="{!! old('bike_selector_from', $location->bike_range_start) !!}" data-to="{!! old('bike_selector_to', $location->bike_range_end) !!}"
                                                data-step="1" />

                                            {!! $errors->first('bike_selector', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('is_ev_charger_available', 'has-error') }}">
                                        <label for="is_ev_charger_available"
                                            class="col-sm-2 control-label">@lang('location-setting.ev_charger_available') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control is_ev_charger_available"
                                                name="is_ev_charger_available" required="">

                                                <option value='0'
                                                    @if (old('is_ev_charger_available') == 0 || $location->ev_charger == 0) selected="selected" @endif>
                                                    @lang('location-setting.no')</option>
                                                <option value='1'
                                                    @if (old('is_ev_charger_available') == 1 || $location->ev_charger == 1) selected="selected" @endif>
                                                    @lang('location-setting.yes')</option>
                                            </select>
                                        </div>
                                        <span
                                            class="help-block">{{ $errors->first('is_ev_charger_available', ':message') }}</span>
                                    </div>

                                    <div
                                        class="form-group ev_charger_range_con {{ $location->ev_charger == 0 ? 'hidden' : '' }} {{ $errors->first('ev_charger_range', 'has-error') }}">
                                        <label for="ev_charger_range" class="col-sm-2 control-label">@lang('location-setting.ev_charger')
                                            *</label>
                                        <input type="hidden" name="ev_charger_range_from" class="from"
                                            value="{!! old('ev_charger_range_from', $location->ev_charger_range_start) !!}" />
                                        <input type="hidden" name="ev_charger_range_to" class="to"
                                            value="{!! old('ev_charger_range_to', $location->ev_charger_range_end) !!}" />
                                        <div class="col-sm-10">
                                            <input id="ev_charger_range" name="ev_charger_range" type="text"
                                                class="form-control ev_charger_range input_slider" data-type="double"
                                                data-grid="true" data-min="0" data-max="100"
                                                data-from="{!! old('ev_charger_range_from', $location->ev_charger_range_start) !!}" data-to="{!! old('ev_charger_range_to', $location->ev_charger_range_end) !!}"
                                                data-step="1" />

                                            {!! $errors->first('ev_charger_range', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div
                                        class="form-group ev_charger_energy_con {{ $location->ev_charger == 0 ? 'hidden' : '' }} {{ $errors->first('ev_charger_energy', 'has-error') }}">
                                        <label for="ev_charger_energy" class="col-sm-2 control-label">@lang('location-setting.ev_charger_energy')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="ev_charger_energy" name="ev_charger_energy" type="number"
                                                placeholder="lang('location-setting.ev_charger_energy')"
                                                class="form-control ev_charger_energy" value="{!! old('ev_charger_energy', $location->ev_charger_energy) !!}"
                                                {{ $location->ev_charger != 0 ? 'required=""' : '' }} />

                                            {!! $errors->first('ev_charger_energy', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div> --}}
                                </div>

                                <div class="tab-pane" id="tab6" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group">
                                        <label for="extra_features" class="col-sm-2 control-label">Extra Features
                                        </label>
                                        <div class="col-sm-10">
                                            <textarea name="extra_features" id="extra_features" class="form-control resize_vertical extra_features"
                                                rows="6">{!! old('extra_features', $location->extra_features) !!}</textarea>
                                            <small>@lang('location-setting.one_per_line')</small>
                                        </div>

                                        {!! $errors->first('extra_features', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab9" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>

                                    <div class="form-group {{ $errors->first('reply_to_name', 'has-error') }}">
                                        <label for="reply_to_name" class="col-sm-2 control-label">@lang('location-setting.reply_to_name')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="reply_to_name" name="reply_to_name" type="text"
                                                placeholder="@lang('location-setting.reply_to_name')" class="form-control reply_to_name"
                                                value="{!! old('reply_to_name', $location->reply_to_name) !!}" required="" />

                                            {!! $errors->first('reply_to_name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('reply_to_email', 'has-error') }}">
                                        <label for="reply_to_email" class="col-sm-2 control-label">@lang('location-setting.reply_to_email')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="reply_to_email" name="reply_to_email" type="email"
                                                placeholder="@lang('location-setting.reply_to_email')" class="form-control reply_to_email"
                                                value="{!! old('reply_to_email', $location->reply_to_email) !!}" required="" />

                                            {!! $errors->first('reply_to_email', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('location_phone_num', 'has-error') }}">
                                        <label for="location_phone_num" class="col-sm-2 control-label">@lang('location-setting.loc_ph_no')
                                            *</label>
                                        <div class="col-sm-10">
                                            <input id="location_phone_num" name="location_phone_num" type="text"
                                                placeholder="@lang('location-setting.loc_ph_no')" class="form-control"
                                                value="{!! old('location_phone_num', $location->location_phone_num) !!}" required="" />

                                            {!! $errors->first('location_phone_num', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('logo_file', 'has-error') }}">
                                        <label for="logo_file" class="col-sm-2 control-label">@lang('location-setting.logo_pic')</label>
                                        <div class="col-sm-10">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail" style="width: 200px; height: 200px;">
                                                    @if ($location->logo_path != null)
                                                        <img src="{{ asset($location->logo_path) }}" alt="logo pic">
                                                    @else
                                                        <img src="{{ asset('/uploads/users/default_user.png') }}"
                                                            alt="logo pic">
                                                    @endif
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail"
                                                    style="max-width: 200px; max-height: 200px;"></div>
                                                <div>
                                                    <span class="btn btn-default btn-file">
                                                        <span class="fileinput-new">@lang('account-setting.select_img')</span>
                                                        <span class="fileinput-exists">@lang('account-setting.change')</span>
                                                        <input id="pic" name="logo_file" type="file"
                                                            class="form-control" />
                                                    </span>
                                                    <a href="#" class="btn btn-danger fileinput-exists"
                                                        data-dismiss="fileinput">@lang('account-setting.remove')</a>
                                                </div>
                                            </div>
                                            <span class="help-block">{{ $errors->first('logo_file', ':message') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('loc_file', 'has-error') }}">
                                        <label for="loc_file" class="col-sm-2 control-label">@lang('location-setting.location_pic')</label>
                                        <div class="col-sm-10">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail" style="width: 200px; height: 200px;">
                                                    @if ($location->loc_path != null)
                                                        <img src="{{ asset($location->loc_path) }}" alt="location pic">
                                                    @else
                                                        <img src="{{ asset('/uploads/users/default_user.png') }}"
                                                            alt="location pic">
                                                    @endif
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail"
                                                    style="max-width: 200px; max-height: 200px;"></div>
                                                <div>
                                                    <span class="btn btn-default btn-file">
                                                        <span class="fileinput-new">@lang('account-setting.select_img')</span>
                                                        <span class="fileinput-exists">@lang('account-setting.change')</span>
                                                        <input id="pic" name="loc_file" type="file"
                                                            class="form-control" />
                                                    </span>
                                                    <a href="#" class="btn btn-danger fileinput-exists"
                                                        data-dismiss="fileinput">@lang('account-setting.remove')</a>
                                                </div>
                                            </div>
                                            <span class="help-block">{{ $errors->first('loc_file', ':message') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('bg_path', 'has-error') }}">
                                        <label for="bg_path" class="col-sm-2 control-label">@lang('location-setting.location_bg')</label>
                                        <div class="col-sm-10">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail" style="width: 200px; height: 200px;">
                                                    @if ($location->bg_path != null)
                                                        <img src="{{ asset($location->bg_path) }}" alt="location pic">
                                                    @else
                                                        <img src="{{ asset('/uploads/users/default_user.png') }}"
                                                            alt="location pic">
                                                    @endif
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail"
                                                    style="max-width: 200px; max-height: 200px;"></div>
                                                <div>
                                                    <span class="btn btn-default btn-file">
                                                        <span class="fileinput-new">@lang('account-setting.select_img')</span>
                                                        <span class="fileinput-exists">@lang('account-setting.change')</span>
                                                        <input id="bg_path" name="bg_path" type="file"
                                                            class="form-control" />
                                                    </span>
                                                    <a href="#" class="btn btn-danger fileinput-exists"
                                                        data-dismiss="fileinput">@lang('account-setting.remove')</a>
                                                </div>
                                            </div>
                                            <span class="help-block">{{ $errors->first('bg_path', ':message') }}</span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                                <ul class="pager wizard">
                                    <li class="previous"><a href="#">@lang('location-setting.prev')</a></li>
                                    <li class="next"><a href="#">@lang('location-setting.nxt')</a></li>
                                    <li class="next finish" style="display:none;"><a
                                            href="javascript:;">@lang('location-setting.finish')</a></li>
                                </ul>
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
    <script src="{{ asset('plugins/components/dropzone-master/dist/dropzone.js') }}"></script>
    <!-- Clock Plugin JavaScript -->
    <script src="{{ asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.js') }}"></script>
    <!-- Color Picker Plugin JavaScript -->
    <script src="{{ asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asColor.js') }}"></script>
    <script src="{{ asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asGradient.js') }}"></script>
    <script src="{{ asset('plugins/components/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js') }}"></script>
    <script src="{{ asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider-init.js') }}"></script>
    <script src="{{ asset('/js/jquery.mask.js') }}"></script>
    <script src="{{ asset('/js/location.js') }}"></script>

    <script>
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
    </script>
    <script>
        // Clock pickers
        $('#single-input').clockpicker({
            placement: 'bottom',
            align: 'left',
            autoclose: true,
            'default': 'now'
        });
        $('.clockpicker').clockpicker({
            donetext: 'Done',
        }).find('input').change(function() {
            console.log(this.value);
        });
        $('#check-minutes').click(function(e) {
            // Have to stop propagation here
            e.stopPropagation();
            input.clockpicker('show').clockpicker('toggleView', 'minutes');
        });
        if (/mobile/i.test(navigator.userAgent)) {
            $('input').prop('readOnly', true);
        }
        // Colorpicker
        $(".colorpicker").asColorPicker();
        $(".complex-colorpicker").asColorPicker({
            mode: 'complex'
        });
        $(".gradient-colorpicker").asColorPicker({
            mode: 'gradient'
        });
        // Date Picker
        jQuery('.mydatepicker, #datepicker').datepicker();
        jQuery('#datepicker-autoclose').datepicker({
            autoclose: true,
            todayHighlight: true
        });
        jQuery('#date-range').datepicker({
            toggleActive: true
        });
        jQuery('#datepicker-inline').datepicker({
            todayHighlight: true
        });
        // Daterange picker
        $('.input-daterange-datepicker').daterangepicker({
            buttonClasses: ['btn', 'btn-sm'],
            applyClass: 'btn-danger',
            cancelClass: 'btn-inverse'
        });
        $('.input-daterange-timepicker').daterangepicker({
            timePicker: true,
            format: 'MM/DD/YYYY h:mm A',
            timePickerIncrement: 30,
            timePicker12Hour: true,
            timePickerSeconds: false,
            buttonClasses: ['btn', 'btn-sm'],
            applyClass: 'btn-danger',
            cancelClass: 'btn-inverse'
        });
        $('.input-limit-datepicker').daterangepicker({
            format: 'MM/DD/YYYY',
            minDate: '06/01/2015',
            maxDate: '06/30/2015',
            buttonClasses: ['btn', 'btn-sm'],
            applyClass: 'btn-danger',
            cancelClass: 'btn-inverse',
            dateLimit: {
                days: 6
            }
        });
    </script>
@endpush
