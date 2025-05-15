@extends('layouts.master')
@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<!--{{--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">--}}-->
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
            <div class="panel panel-info">
                <div class="panel-heading">@lang('user-list.edit_userlist')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <form 
                            id="commentForm" 
                            class="userListWithEmailForm"
                            action="{{url('user-list/edit/'.$userListUser->id)}}" 
                            method="POST" 
                            enctype="multipart/form-data">
                            <!-- CSRF Token -->
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" class="use_profile_name" name="use_profile_name" value="">
                            <input type="hidden" 
                                   class="use_profile_name_val" 
                                   name="use_profile_name_val" 
                                   value="">
                            <div class="form-body">
                                <h3 class="box-title">@lang('user-list.person_info')</h3>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->first('name', 'has-error') }}">
                                            <label class="control-label">@lang('user-list.name')</label>
                                            <input 
                                                type="text" 
                                                id="name"
                                                name="name"
                                                class="form-control" 
                                                value="{!! old('name', $userListUser->user_name) !!}"
                                                placeholder="@lang('user-list.name')"> 
                                            {!! $errors->first('name', '<span class="help-block">:message</span>') !!} 
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group  {{ $errors->first('name', 'has-error') }}">
                                            <label class="control-label">@lang('user-list.email')</label>
                                            <input 
                                                type="email" 
                                                id="lastName" 
                                                name="email" 
                                                class="form-control" 
                                                value="{!! old('email', $userListUser->email) !!}"
                                                placeholder="@lang('user-list.email')">
                                            {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                </div>
                                <!--/row-->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('user-list.phone')</label>
                                            <input type="text" 
                                                   class="form-control phone_mask"
                                                   name="phone"
                                                   value="{!! old('phone', $userListUser->user_phone ? $userListUser->user_phone : '') !!}"
                                                   maxlength="12"
                                                   placeholder="@lang('user-list.phone')"
                                                   > 
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->first('group', 'has-error') }}">
                                            <label for="group" class="control-label">@lang('user-list.select_group') </label>
                                            <select class="form-control" title="@lang('user-list.group_title')" name="group">
                                                <option value="">@lang('user-list.select')</option>
                                                @foreach ($groups as $group)
                                                <option 
                                                    value="{{$group->id}}"
                                                    @if(old('group',$userListUser->group_id) == $group->id) selected="selected" @endif >{{$group->name}}
                                            </option>
                                            @endforeach
                                        </select>
                                        <span class="help-block">{{ $errors->first('group', ':message') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->first('access_rights', 'has-error') }}">
                                        <label for="access_rights" class="control-label">@lang('user-list.select_rights') </label>
                                        <select class="form-control" title="@lang('user-list.access_right_title')" name="access_rights">
                                            <option value="">@lang('user-list.select')</option>
                                            @foreach ($rights as $right)
                                            <option 
                                                value="{{$right->id}}"
                                                @if(old('access_rights',$userListUser->group_access_id) == $right->id) selected="selected" @endif >{{$right->name}}
                                        </option>
                                        @endforeach
                                    </select>
                                    <span class="help-block">{{ $errors->first('access_rights', ':message') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->first('language', 'has-error') }}">
                                    <label for="language" class=" control-label">@lang('user-list.lang') *</label>
                                    <select class="form-control" title="@lang('user-list.select_title')" name="language">
                                        <option value="">@lang('user-list.select')</option>
                                        @foreach ($languages as $language)
                                        <option 
                                            value="{{$language->id}}"
                                            @if(old('language') === $language->id || $userListUser->language_id === $language->id) selected="selected" @endif >{{$language->code . ' ' . $language->name . ',' . $language->country}}
                                    </option>
                                    @endforeach
                                </select>
                                <span class="help-block">{{ $errors->first('language', ':message') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->first('pic_file', 'has-error') }}">
                                <label for="pic" class="control-label">@lang('user-list.profile_pic')</label>
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

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="note" class="control-label">@lang('user-list.note')
                                    <small>(@lang('user-list.brief_intro')) </small>
                                </label>
                                <textarea 
                                    name="note" 
                                    id="note" 
                                    class="form-control resize_vertical" 
                                    rows="2" 
                                    maxlength="100">{!! old('note',$userListUser->notation) !!}</textarea>
                                <small>@lang('user-list.max_length')</small>
                            </div>

                            {!! $errors->first('note', '<span class="help-block">:message</span>') !!}
                        </div>
                    </div>
                    <!--/row-->
                    <div class="row hidden">

                        <div class="form-group door_selector_con {{ $errors->first('door_selector', 'has-error') }}">
                            <label for="door_selector" class="col-sm-2 control-label">@lang('user-list.doors_range') </label>
                            <input 
                                type="hidden" 
                                name="door_selector_from" 
                                class="selector-from" 
                                value="{!! old('door_selector_from', 0) !!}" />
                            <input 
                                type="hidden" 
                                name="door_selector_to" 
                                class="selector-to" 
                                value="{!! old('door_selector_to', 0) !!}" />
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
                                    data-from="{!! old('door_selector_from', 0) !!}" 
                                    data-to="{!! old('door_selector_to', 0) !!}" 
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
                                value="{!! old('bike_selector_from', 0) !!}" />
                            <input 
                                type="hidden" 
                                name="bike_selector_to" 
                                class="to" 
                                value="{!! old('bike_selector_to', 0) !!}" />
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
                                    data-from="{!! old('bike_selector_from', 0) !!}" 
                                    data-to="{!! old('bike_selector_to', 0) !!}" 
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
                                value="{!! old('ev_charger_selector_from', 0) !!}" />
                            <input 
                                type="hidden" 
                                name="ev_charger_selector_to" 
                                class="to" 
                                value="{!! old('ev_charger_selector_to', 0) !!}" />
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
                                    data-from="{!! old('ev_charger_selector_from', 0) !!}" 
                                    data-to="{!! old('ev_charger_selector_to', 0) !!}" 
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
                                    value="{!! old('energy_limit', 0) !!}"/>

                                {!! $errors->first('energy_limit', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>


                    </div>
                    <h3 class="box-title m-t-40">@lang('user-list.vehicle')</h3>
                    <hr>

                    <input type="hidden" name="removed" value='' id="remove_old">
                    <input type="hidden" name="total_plates" value='<?= count($user_vehicles_info) ?>' id="total_plates">
                    <div class="row add_new_vehicles">
                        @if($user_vehicles_info)
                        @foreach($user_vehicles_info as $key=>$plate)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('customer.vehicle') {{$key+1}} </label>
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control old_val"
                                               value="{{$plate->num_plate}}"
                                               placeholder="@lang('customer.plate_number')"
                                               disabled=''>
                                    </div>
                                    <div class="col-md-4">
                                        <button  type="button" class="btn btn-danger remove_btn" onclick="remove_vehicle(this)">@lang('user-list.remove')</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                    <button type="button"  class="btn btn-success add_more_btn">@lang('customer.add_more')</button>
                    <hr>
                    <h3 class="box-title m-t-40">@lang('customer.confirmation')</h3>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="checkbox checkbox-primary pull-left p-t-0">
                                <input 
                                    type="checkbox" 
                                    id="user_arrival_notification" 
                                    class="form-control user_arrival_notification" 
                                    name="user_arrival_notification"
                                    {{old('user_arrival_notification', $userListUser->user_arrival_notification) ? 'checked' : ''}}> 
                                <label for="user_arrival_notification"> @lang('customer.user_arrival_notification') </label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group notify_email{{old('user_arrival_notification', $userListUser->user_arrival_notification) ? '' : ' hidden'}} {{ $errors->first('notify_emails', 'has-error') }}">
                                <label for="notify_email" class="control-label">@lang('customer.notify_email') *</label>
                                <input 
                                    id="notify_email" 
                                    name="notify_email" 
                                    type="text" 
                                    placeholder="@lang('customer.notify_email')" 
                                    class="form-control required" 
                                    value="{!! old('notify_email', $userListUser->notify_emails) !!}" />
                                <span 
                                    class="note-color-row" 
                                    style="display: block;margin-top: 5px;margin-bottom: 10px;color: #737373;">@lang('customer.notify_email_note')</span>

                                {!! $errors->first('notify_email', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success finish"> 
                            <i class="fa fa-check"></i> @lang('user-list.save')
                        </button>
                        <a href="{{url('/user-list')}}" class="btn btn-default">@lang('user-list.cancel')</a>
                    </div>
            </form>
        </div>
    </div>
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
<!--{{--<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>--}}-->
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
    
    
    $(document).ready(function () {
        $('#user_arrival_notification').click(function () {
            if ($(this).is(":checked")) {
                $('div.notify_email').removeClass('hidden');
            } else {
                $('div.notify_email').addClass('hidden');
                $('#notify_email').val('');
            }
        });
    });
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
   // $('input').prop('readOnly', true);
    }
    //            Colorpicker
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
    function remove_vehicle(handle) {
    var plate_value = $(handle).parent().parent().find(".old_val").val();
    if (plate_value === undefined){
    $(handle).parent().parent().parent().parent().remove();
    } else{
    var current_removal_ready = $('#remove_old').val();
    plate_value = current_removal_ready === '' ? plate_value : current_removal_ready + ',' + plate_value;
    $('#remove_old').val(plate_value);
    }
    var vehiclesCount = $('#total_plates').val() - 1;
    $('#total_plates').val(vehiclesCount);
    $(handle).parent().parent().parent().parent().remove();
    }

</script>
@endpush
