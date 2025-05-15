@extends('layouts.master')

@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<!--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">-->
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
<link href="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.skinModern.css')}}" rel="stylesheet">
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
                @if($userListUser->has_email == 1)
                <div class="white-box">
                    <h3 class="box-title pull-left">@lang('user-list.edit_userlist')</h3>
                    <a  class="btn btn-success pull-right" href="{{url('user-list/')}}">
                        <i class="icon-list"></i> @lang('user-list.view_users')
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
                        id="commentForm1" 
                        action="{{url('user-list/edit/'.$userListUser->id)}}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="form-horizontal userListWithEmailForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                        <input type="hidden" 
                               class="use_profile_name" 
                               name="use_profile_name" 
                               value="">
                        <input type="hidden" 
                               class="use_profile_name_val" 
                               name="use_profile_name_val" 
                               value="">

                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab1" 
                                       data-toggle="tab" 
                                       style="cursor:pointer;">@lang('user-list.personal')</a>
                                </li>
                                <li>
                                    <a href="#tab2" 
                                       data-toggle="tab" 
                                       style="cursor:pointer;">@lang('user-list.vehicle')</a>
                                </li>
                                <li>
                                    <a href="#tab3" 
                                       data-toggle="tab" 
                                       style="cursor:pointer;">@lang('user-list.loc')</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <input 
                                        type="hidden" 
                                        name="user_live_id" 
                                        value="{{$userListUser->customer->live_id}}"/>
                                    <div class="form-group {{ $errors->first('name', 'has-error') }}">
                                        <label for="name" class="col-sm-2 control-label">@lang('user-list.name') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="name" 
                                                name="name" 
                                                type="text" 
                                                placeholder="@lang('user-list.name')" 
                                                class="form-control required" 
                                                value="{!! old('name', $userListUser->user_name) !!}"/>

                                            {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('email', 'has-error') }}">
                                        <label for="email" class="col-sm-2 control-label">@lang('user-list.email') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="email" 
                                                name="email" 
                                                placeholder="@lang('user-list.email')" 
                                                type="text" 
                                                class="form-control required email" 
                                                value="{!! old('email', $userListUser->email) !!}"/>

                                            {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('phone', 'has-error') }}">
                                        <label for="phone" class="col-sm-2 control-label">@lang('user-list.phone') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="phone" 
                                                name="phone" 
                                                type="text" 
                                                placeholder="@lang('user-list.phone')" 
                                                class="form-control phone_mask required" 
                                                value="{!! old('phone', $userListUser->user_phone ? $userListUser->user_phone : 0) !!}"/>

                                            {!! $errors->first('phone', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('group', 'has-error') }}">
                                        <label for="group" class="col-sm-2 control-label">@lang('user-list.select_group') </label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="@lang('user-list.group_title')" name="group">
                                                <option value="">Select</option>
                                                @foreach ($groups as $group)
                                                <option 
                                                    value="{{$group->id}}"
                                                    @if(old('group') == $group->id || $userListUser->group_id == $group->id) selected="selected" @endif >{{$group->name}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="help-block">{{ $errors->first('group', ':message') }}</span>
                                </div>

                                <div class="form-group {{ $errors->first('pic_file', 'has-error') }}">
                                    <label for="pic" class="col-sm-2 control-label">@lang('user-list.profile_pic')</label>
                                    <div class="col-sm-10">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-new thumbnail"
                                                 style="width: 50px; height: 50px;">
                                                @if($userListUser->profile_image != null)
                                                <img src="{{asset('/uploads/users/'.$userListUser->profile_image)}}" alt="@lang('user-list.profile_pic')">
                                                @else
                                                <img src="{{asset('/uploads/users/default_user.png')}}" alt="@lang('user-list.profile_pic')">
                                                @endif
                                            </div>
                                            <div class="fileinput-preview fileinput-exists thumbnail"
                                                 style="max-width: 50px; max-height: 50px;"></div>
                                            <div>
                                                <span class="btn btn-default btn-file">
                                                    <span class="fileinput-new">@lang('user-list.select_img')</span>
                                                    <span class="fileinput-exists">@lang('user-list.change')</span>
                                                    <input id="pic" name="pic_file" type="file" class="form-control"/>
                                                </span>
                                                <a href="#" class="btn btn-danger fileinput-exists"
                                                   data-dismiss="fileinput">@lang('user-list.remove')</a>
                                            </div>
                                        </div>
                                        <span class="help-block">{{ $errors->first('pic_file', ':message') }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="note" class="col-sm-2 control-label">@lang('user-list.note')
                                        <small>(@lang('user-list.brief_intro')) </small>
                                    </label>
                                    <div class="col-sm-10">
                                        <textarea 
                                            name="note" 
                                            id="note" 
                                            class="form-control resize_vertical" 
                                            rows="6" 
                                            maxlength="100">{!! old('note', $userListUser->notation) !!}</textarea>
                                        <small>@lang('user-list.max_length')</small>
                                    </div>

                                    {!! $errors->first('note', '<span class="help-block">:message</span>') !!}
                                </div>
                            </div>
                                <div class="tab-pane" id="tab2" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>

                                    <input 
                                        type="hidden" 
                                        name="uservehicle_live_id" 
                                        value="{{!empty($userListUser->customer_vehicle_info) ? $userListUser->customer_vehicle_info->live_id : ''}}"/>
                                    <div class=" vehicles_drop_down form-group {{ $errors->first('vehicle_name', 'has-error') }}">
                                        <label for="vehicle_name" class="col-sm-2 control-label">@lang('user-list.vehicle_name') *</label>
                                        <div class="col-sm-10">
                                            <select name="vehicle_id" class="form-control vehicle_id" id="vehicle_id" >
                                                <option value="">Select Vehicle</option>
                                                @if($user_vehicles_info->count() >  0)
                                                @foreach($user_vehicles_info as $user_vehicle)
                                                <option value="{{$user_vehicle->id}}"
                                                        @if($user_vehicle->id == $userListUser->user_vehicle) selected='' @endif       
                                                        >{{$user_vehicle->name.' '.$user_vehicle->num_plate}}</option>
                                                @endforeach
                                                @endif
                                                <option value="add_new">Add New</option>
                                            </select>
                                            {!! $errors->first('vehicle_name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="add_vehicle_form" style="display:none;">
                                        <div class="form-group {{ $errors->first('vehicle_name', 'has-error') }}">
                                            <label for="vehicle_name" class="col-sm-2 control-label">@lang('user-list.vehicle_name') *</label>
                                            <div class="col-sm-10">
                                                <input 
                                                    id="vehicle_name" 
                                                    name="vehicle_name" 
                                                    type="text" 
                                                    placeholder="@lang('user-list.vehicle_name')" 
                                                    class="form-control required" 
                                                    value="{!! old('vehicle_name') !!}"/>

                                                {!! $errors->first('vehicle_name', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->first('vehicle_no', 'has-error') }}">
                                            <label for="vehicle_no" class="col-sm-2 control-label">@lang('user-list.vehicle_no') *</label>
                                            <div class="col-sm-10">
                                                <input 
                                                    id="vehicle_no" 
                                                    name="vehicle_no" 
                                                    type="text" 
                                                    placeholder="@lang('user-list.vehicle_no')" 
                                                    class="form-control required" 
                                                    value="{!! old('vehicle_no') !!}"/>
                                                {!! $errors->first('vehicle_no', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="tab-pane" id="tab3" disabled="disabled">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <input 
                                        type="hidden" 
                                        name="userlist_live_id" 
                                        value="{{$userListUser->live_id}}"/>

                                    <div class="form-group door_selector_con {{ $errors->first('door_selector', 'has-error') }}">
                                        <label for="door_selector" class="col-sm-2 control-label">@lang('user-list.doors_range') </label>
                                        <input 
                                            type="hidden" 
                                            name="door_selector_from" 
                                            class="selector-from" 
                                            value="{!! old('door_selector_from', $userListUser->door_range_start) !!}" />
                                        <input 
                                            type="hidden" 
                                            name="door_selector_to" 
                                            class="selector-to" 
                                            value="{!! old('door_selector_to', $userListUser->door_range_end) !!}" />
                                        <div class="col-sm-10">
                                            <input 
                                                id="door_selector" 
                                                name="door_selector" 
                                                type="text" 
                                                class="form-control input_slider" 
                                                data-type="double" 
                                                data-grid="true"
                                                data-min="0" 
                                                data-max="100" 
                                                data-from="{!! old('door_selector_from', $userListUser->door_range_start) !!}" 
                                                data-to="{!! old('door_selector_to', $userListUser->door_range_end) !!}" 
                                                data-step="1" />

                                            {!! $errors->first('door_selector', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group bike_selector_con {{ $errors->first('bike_selector', 'has-error') }}">
                                        <label for="bike_selector" class="col-sm-2 control-label">@lang('user-list.bikes_range') </label>
                                        <input 
                                            type="hidden" 
                                            name="bike_selector_from" 
                                            class="from" 
                                            value="{!! old('bike_selector_from', $userListUser->bike_range_start) !!}" />
                                        <input 
                                            type="hidden" 
                                            name="bike_selector_to" 
                                            class="to" 
                                            value="{!! old('bike_selector_to', $userListUser->bike_range_end) !!}" />
                                        <div class="col-sm-10">
                                            <input 
                                                id="bike_selector" 
                                                name="bike_selector" 
                                                type="text" 
                                                class="form-control input_slider" 
                                                data-type="double" 
                                                data-grid="true"
                                                data-min="0" 
                                                data-max="100" 
                                                data-from="{!! old('bike_selector_from', $userListUser->bike_range_start) !!}" 
                                                data-to="{!! old('bike_selector_to', $userListUser->bike_range_end) !!}" 
                                                data-step="1" />

                                            {!! $errors->first('bike_selector', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group ev_charger_selector_con {{ $errors->first('ev_charger_selector', 'has-error') }}">
                                        <label for="ev_charger_selector" class="col-sm-2 control-label">@lang('user-list.ev_charger_range') </label>
                                        <input 
                                            type="hidden" 
                                            name="ev_charger_selector_from" 
                                            class="from" 
                                            value="{!! old('ev_charger_selector_from', $userListUser->ev_charger_range_start) !!}" />
                                        <input 
                                            type="hidden" 
                                            name="ev_charger_selector_to" 
                                            class="to" 
                                            value="{!! old('ev_charger_selector_to', $userListUser->ev_charger_range_end) !!}" />
                                        <div class="col-sm-10">
                                            <input 
                                                id="bike_selector" 
                                                name="ev_charger_selector" 
                                                type="text" 
                                                class="form-control input_slider" 
                                                data-type="double" 
                                                data-grid="true"
                                                data-min="0" 
                                                data-max="100" 
                                                data-from="{!! old('ev_charger_selector_from', $userListUser->ev_charger_range_start) !!}" 
                                                data-to="{!! old('ev_charger_selector_to', $userListUser->ev_charger_range_end) !!}" 
                                                data-step="1" />

                                            {!! $errors->first('ev_charger_selector', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('energy_limit', 'has-error') }}">
                                        <label for="energy_limit" class="col-sm-2 control-label">@lang('user-list.energy_limit') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="energy_limit" 
                                                name="energy_limit" 
                                                type="text" 
                                                placeholder="@lang('user-list.energy_limit')" 
                                                class="form-control required" 
                                                value="{!! old('energy_limit', $userListUser->energy_limit ? $userListUser->energy_limit : 0) !!}"/>

                                            {!! $errors->first('energy_limit', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('language', 'has-error') }}">
                                        <label for="language" class="col-sm-2 control-label">@lang('user-list.lang') *</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="@lang('user-list.select_title')" name="language">
                                                <option value="">@lang('user-list.select')</option>
                                                @foreach ($languages as $language)
                                                <option 
                                                    value="{{$language->id}}"
                                                    @if(old('language') === $language->id || $userListUser->language_id === $language->id) selected="selected" @endif >{{$language->code . ' ' . $language->name . ',' . $language->country}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="help-block">{{ $errors->first('language', ':message') }}</span>
                                </div>
                            </div>
                                <ul class="pager wizard">
                                    <li class="previous"><a href="#">@lang('user-list.prev')</a></li>
                                    <li class="next btn-nxt"><a href="#">@lang('user-list.nxt')</a></li>
                                    <li class="next finish" style="display:none;"><a href="javascript:;">@lang('user-list.finish')</a></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
                @else
                <div class="white-box">
                    <h3 class="box-title pull-left">@lang('user-list.create_userlist')</h3>
                    <a  class="btn btn-success pull-right" href="{{url('user-list/')}}">
                        <i class="icon-list"></i> @lang('user-list.view_users')
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
                        id="commentForm2" 
                        action="{{url('user-list/edit-without-email/'.$userListUser->id)}}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="form-horizontal userListWithOutEmailForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab1" 
                                       data-toggle="tab" 
                                       style="cursor:pointer;">@lang('user-list.personal')</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <input 
                                    type="hidden" 
                                    name="uservehicle_live_id" 
                                    value="{{!empty($userListUser->customer_vehicle_info) ? $userListUser->customer_vehicle_info->live_id : ''}}"/>
                                    <div class="form-group {{ $errors->first('name', 'has-error') }}">
                                        <label for="name" class="col-sm-2 control-label">@lang('user-list.name') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="name" 
                                                name="name" 
                                                type="text" 
                                                placeholder="@lang('user-list.name')" 
                                                class="form-control name required" 
                                                value="{!! old('name', $userListUser->user_name) !!}"/>

                                            {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('vehicle_no', 'has-error') }}">
                                        <label for="vehicle_no" class="col-sm-2 control-label">@lang('user-list.vehicle_no') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="vehicle_no" 
                                                name="vehicle_no" 
                                                type="text" 
                                                placeholder="@lang('user-list.vehicle_no')" 
                                                class="form-control vehicle_no required" 
                                                value="{{ old('vehicle_no', !empty($userListUser->customer_vehicle_info) ? $userListUser->customer_vehicle_info->num_plate : '') }}"/>
                                            {!! $errors->first('vehicle_no', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->first('group', 'has-error') }}">
                                        <label for="group" class="col-sm-2 control-label">@lang('user-list.select_group') </label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="@lang('user-list.group_title')" name="group">
                                                <option value="">Select</option>
                                                @foreach ($groups as $group)
                                                <option 
                                                    value="{{$group->id}}"
                                                    @if(old('group') == $group->id || $userListUser->group_id == $group->id) selected="selected" @endif >{{$group->name}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="help-block">{{ $errors->first('group', ':message') }}</span>
                                </div>
                                <ul class="pager wizard">
                                    <li class="previous"><a href="#">@lang('user-list.prev')</a></li>
                                    <li class="next btn-nxt"><a href="#">@lang('user-list.nxt')</a></li>
                                    <li class="next finish" style="display:none;"><a href="javascript:;">@lang('user-list.finish')</a></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <div id="view_userlist_name_change_form" 
             class="modal fade view_userlist_name_change_form" 
             tabindex="-1" 
             role="dialog" 
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <!--<button type="button" class="close" aria-hidden="true" data-dismiss="modal">×</button>-->
                        <h4 class="modal-title">User Information</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info user-info text-left">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default userlist_name_btn" onclick="return saveChangeFormSettings(0);">OK</button>
                        <button type="button" class="btn btn-default profile_name_btn" onclick="return saveChangeFormSettings(1);">OK</button>
                    </div>
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
<!--<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>-->
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"
type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<!-- Clock Plugin JavaScript -->
<script src="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.js')}}"></script>
<!-- Color Picker Plugin JavaScript -->
<script src="{{asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asColor.js')}}"></script>
<script src="{{asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asGradient.js')}}"></script>
<script src="{{asset('plugins/components/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js')}}"></script>
<script src="{{asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider.min.js')}}"></script>
<script src="{{asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider-init.js')}}"></script>
<script src="{{ asset('/js/jquery.mask.js') }}"></script>
<script src="{{ asset('/js/userlist.js') }}"></script>

<script>
                    @if (\Session::has('message'))
                            $.toast({
                            heading: '{{session()->get('heading')}}',
                                    position: 'top-center',
                                    text: '{{session()->get('message')}}',
                                    loaderBg: '#ff6849',
                                    icon: '{{session()->get('icon')}}',
                                    hideAfter: 3000,
                                    stack: 6
                            });
                    @endif
</script>
@endpush