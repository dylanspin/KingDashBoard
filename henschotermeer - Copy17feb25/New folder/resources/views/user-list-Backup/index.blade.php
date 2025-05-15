@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/footable/css/footable.bootstrap.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<!--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">-->
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
<link href="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.skinModern.css')}}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('user-list.userslist')</h3>
                <a  class="btn btn-success pull-right" href="{{url('user-list/create')}}">
                    <i class="icon-plus"></i> @lang('user-list.add_user')
                </a>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table 
                                class="table" 
                                data-sorting="true" 
                                data-filtering="true" 
                                data-filter-connectors="false"
                                data-toggle-column="first"
                                data-paging="true" 
                                data-paging-size="25">
                                <thead>
                                    <tr>
                                        <th data-sortable="false"></th>
                                        <th>@lang('user-list.user_name')</th>
                                        <th>@lang('user-list.email')</th>
                                        <th>@lang('user-list.vehicle_no')</th>
                                        <th>@lang('user-list.group')</th>
                                        <th data-type="date" data-format-string="DD/MM/YYYY" data-sorted="true" data-direction="DESC">@lang('user-list.added_at')</th>
                                        <th data-sortable="false">@lang('user-list.block')</th>
                                        <th data-sortable="false">@lang('user-list.send_instructions')</th>
                                        <th data-sortable="false">@lang('user-list.actions')</th>
                                        <th data-breakpoints="all"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userListUsers as $key=>$userList)
                                    <tr class="record{{$userList->id}}">
                                        <td></td>
                                        <td class="name">{{$userList->user_name}}</td>
                                        <td class="email">{{$userList->email ? $userList->email : 'N/A'}}</td>
                                        @if($userList->has_email == 1)
                                        @if($userList->customer)
                                        @if($userList->customer->customer_vehicle_info)
                                        <td>{{$userList->customer->customer_vehicle_info->num_plate}}</td>
                                        @else
                                        <td>N/A</td>
                                        @endif
                                        @else
                                        <td>N/A</td>
                                        @endif
                                        @else
                                        <td>{{!empty($userList->customer_vehicle_info) ? $userList->customer_vehicle_info->num_plate : 'N/A'}}</td>
                                        @endif
                                        <td>{{!empty($userList->group) ? $userList->group->name : 'N/A'}}</td>
                                        <td>{{date('d/m/Y', strtotime($userList->created_at))}}</td>
                                        <td>
                                            <a 
                                                class="block btn btn-sm" 
                                                data-id="{{$userList->id}}" 
                                                style="cursor:pointer;" >
                                                <u>{{$userList->is_blocked == 0 ? 'Block' : 'Unblock'}}</u>
                                            </a>
                                        </td>
                                        <td>
                                            @if($userList->has_email == 1)
                                            <a 
                                                class="send-instructions btn btn-sm" 
                                                data-id="{{$userList->id}}" 
                                                style="cursor:pointer;" >
                                                <u>@lang('user-list.send')</u>
                                            </a>
                                            @else
                                            N/A
                                            @endif
                                        </td>
                                        <td>
                                            <a 
                                                class="btn btn-info btn-sm" 
                                                href="{{url('user-list/edit/'.$userList->id)}}">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i> @lang('user-list.edit')
                                            </a>
                                            &nbsp;&nbsp;
                                            <a 
                                                class="delete btn btn-danger btn-sm"
                                                data-id="{{$userList->id}}" 
                                                style="cursor:pointer;">
                                                <i class="fa fa-trash-o"></i> @lang('user-list.delete')
                                            </a>
                                        </td>
                                        <td>
                                            @if($userList->has_email == 1)
                                            <div class="row">
                                                <form 
                                                    id="userListQuickEditForm" 
                                                    class="form-horizontal userListQuickEditForm{{$userList->id}} col-sm-12">
                                                    <!--ERRORS-->
                                                    <div class="col-sm-12">
                                                        <div class="alert alert-success alert-dismissible print-success-msg" style="display:none">
                                                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                            <ul></ul>
                                                        </div>
                                                        <div class="alert alert-danger alert-dismissible print-error-msg" style="display:none">
                                                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                            <ul></ul>
                                                        </div>
                                                    </div>
                                                    <!--ERRORS-->
                                                    <input 
                                                        type="hidden" 
                                                        name="_token" 
                                                        value="{{ csrf_token() }}"/>
                                                    <div class="form-group col-sm-6 {{ $errors->first('name', 'has-error') }}">
                                                        <label for="name" class="col-sm-4 control-label">@lang('user-list.name') *</label>
                                                        <div class="col-sm-8">
                                                            <input 
                                                                id="name" 
                                                                name="name" 
                                                                type="text" 
                                                                placeholder="@lang('user-list.name')" 
                                                                class="form-control name required" 
                                                                value="{!! old('name', $userList->user_name) !!}"/>

                                                            {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-sm-6 {{ $errors->first('email', 'has-error') }}">
                                                        <label for="email" class="col-sm-4 control-label">@lang('user-list.email') *</label>
                                                        <div class="col-sm-8">
                                                            <input 
                                                                id="email" 
                                                                name="email" 
                                                                placeholder="@lang('user-list.email')" 
                                                                type="text" 
                                                                class="form-control required email" 
                                                                value="{!! old('email', $userList->email) !!}"/>

                                                            {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-sm-6 {{ $errors->first('phone', 'has-error') }}">
                                                        <label for="phone" class="col-sm-4 control-label">@lang('user-list.phone') *</label>
                                                        <div class="col-sm-8">
                                                            <input 
                                                                id="phone" 
                                                                name="phone" 
                                                                type="text" 
                                                                placeholder="@lang('user-list.phone')" 
                                                                class="form-control phone_mask required" 
                                                                value="{!! old('phone', $userList->user_phone) !!}"/>

                                                            {!! $errors->first('phone', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-sm-6 {{ $errors->first('group', 'has-error') }}">
                                                        <label for="group" class="col-sm-4 control-label">@lang('user-list.select_group') </label>
                                                        <div class="col-sm-8">
                                                            <select class="form-control" title="@lang('user-list.group_title')" name="group">
                                                                <option value="">Select</option>
                                                                @foreach ($groups as $group)
                                                                <option 
                                                                    value="{{$group->id}}"
                                                                    @if(old('group') == $group->id || $userList->group_id == $group->id) selected="selected" @endif >{{$group->name}}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <span class="help-block">{{ $errors->first('group', ':message') }}</span>
                                                    </div>
                                                    <div class="form-group col-sm-6 {{ $errors->first('energy_limit', 'has-error') }}">
                                                        <label for="energy_limit" class="col-sm-4 control-label">@lang('user-list.energy_limit') *</label>
                                                        <div class="col-sm-8">
                                                            <input 
                                                                id="energy_limit" 
                                                                name="energy_limit" 
                                                                type="text" 
                                                                placeholder="@lang('user-list.energy_limit')" 
                                                                class="form-control required" 
                                                                value="{!! old('energy_limit', $userList->energy_limit) !!}"/>

                                                            {!! $errors->first('energy_limit', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-sm-6 {{ $errors->first('language', 'has-error') }}">
                                                        <label for="language" class="col-sm-4 control-label">@lang('user-list.lang') *</label>
                                                        <div class="col-sm-8">
                                                            <select class="form-control" title="@lang('user-list.select_title')" name="language">
                                                                <option value="">@lang('user-list.select')</option>
                                                                @foreach ($languages as $language)
                                                                <option 
                                                                    value="{{$language->id}}"
                                                                    @if(old('language') === $language->id || $userList->language_id === $language->id) selected="selected" @endif >{{$language->code . ' ' . $language->name . ',' . $language->country}}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <span class="help-block">{{ $errors->first('language', ':message') }}</span>
                                                    </div>
                                                    <div class="form-group col-sm-12">
                                                        <div class="col-sm-12">
                                                            <button 
                                                                class="btn btn-success userListQuickEditBtn"
                                                                onclick="return saveUserListQuickEditFormChanges({{$userList->id}})" 
                                                                style="float:right;">Update</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            @else
                                            <div class="row">
                                                <form 
                                                    id="userListQuickEditWithOutEmailForm" 
                                                    class="form-horizontal userListQuickEditWithOutEmailForm{{$userList->id}} col-sm-12">
                                                    <!--ERRORS-->
                                                    <div class="col-sm-12">
                                                        <div class="alert alert-success alert-dismissible print-success-msg" style="display:none">
                                                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                            <ul></ul>
                                                        </div>
                                                        <div class="alert alert-danger alert-dismissible print-error-msg" style="display:none">
                                                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                            <ul></ul>
                                                        </div>
                                                    </div>
                                                    <!--ERRORS-->
                                                    <!-- CSRF Token -->
                                                    <input 
                                                        type="hidden" 
                                                        name="_token" 
                                                        value="{{ csrf_token() }}"/>
                                                    <div class="form-group col-sm-6 {{ $errors->first('name', 'has-error') }}">
                                                        <label for="name" class="col-sm-4 control-label">@lang('user-list.name') *</label>
                                                        <div class="col-sm-8">
                                                            <input 
                                                                id="name" 
                                                                name="name" 
                                                                type="text" 
                                                                placeholder="@lang('user-list.name')" 
                                                                class="form-control name required" 
                                                                value="{!! old('name', $userList->user_name) !!}"/>

                                                            {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group col-sm-6 {{ $errors->first('vehicle_no', 'has-error') }}">
                                                        <label for="vehicle_no" class="col-sm-4 control-label">@lang('user-list.vehicle_no') *</label>
                                                        <div class="col-sm-8">
                                                            <input 
                                                                id="vehicle_no" 
                                                                name="vehicle_no" 
                                                                type="text" 
                                                                placeholder="@lang('user-list.vehicle_no')" 
                                                                class="form-control vehicle_no required" 
                                                                value="{{ old('vehicle_no', !empty($userList->customer_vehicle_info) ? $userList->customer_vehicle_info->num_plate : '') }}"/>
                                                            {!! $errors->first('vehicle_no', '<span class="help-block">:message</span>') !!}
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group col-sm-6 {{ $errors->first('group', 'has-error') }}">
                                                        <label for="group" class="col-sm-4 control-label">@lang('user-list.select_group') </label>
                                                        <div class="col-sm-8">
                                                            <select class="form-control" title="@lang('user-list.group_title')" name="group">
                                                                <option value="">Select</option>
                                                                @foreach ($groups as $group)
                                                                <option 
                                                                    value="{{$group->id}}"
                                                                    @if(old('group') == $group->id || $userList->group_id == $group->id) selected="selected" @endif >{{$group->name}}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <span class="help-block">{{ $errors->first('group', ':message') }}</span>
                                                    </div>
                                                    
                                                    <div class="form-group col-sm-12">
                                                        <div class="col-sm-12">
                                                            <button 
                                                                class="btn btn-success userListQuickEditWithOutEmailBtn"
                                                                onclick="return saveUserListQuickEditWithOutEmailFormChanges({{$userList->id}})" 
                                                                style="float:right;">Update</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('plugins/components/footable/js/footable.min.js') }}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>

<script src="{{ asset('plugins/components/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
<script src="{{asset('plugins/components/icheck/icheck.min.js')}}"></script>
<script src="{{asset('plugins/components/icheck/icheck.init.js')}}"></script>
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
$(document).ready(function () {
    $(document).on('click', '.delete', function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Destroy User?",
            message: "Are you sure want to delete user from Userlist?",
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
            callback: function (result) {
                if(result){
                    window.location.href = "{{url('user-list/delete')}}/"+id;
                }
            }
        });
    });
            
    $(document).on('click', '.send-instructions', function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Send Instructions?",
            message: "Are you sure want to send Instructions to user?",
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
            callback: function (result) {
                if(result){
                    window.location.href = "{{url('user-list/send-instructions')}}/"+id;
                }
            }
        });
    });
            
    $(document).on('click', '.block', function (e) {
        var id = $(this).data('id');
        var blockText = $(this).text();
        bootbox.confirm({
            title: blockText+" User?",
            message: "Are you sure want to "+blockText+" user?",
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
            callback: function (result) {
                if(result){
                    window.location.href = "{{url('user-list/block-or-unbolock')}}/"+id;
                }
            }
        });
    });
            
    @if(\Session::has('message'))
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
});

$('.table').footable();

</script>
@endpush