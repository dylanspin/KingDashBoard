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
                @if (session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                <hr>
                <style>
                    .footable-filtering{
                        display:none;
                    }
                </style>
                <div class="col-md-12 text-right">
                    <form method="post" action="{{url('/user-list')}}" class="col-md-6 text-right custom-search-form">
                        @csrf
                        <div class="form-group col-md-4">
                            <select class="form-control" name="search_type">
                                <option value="plate" {{ $search_type == 'plate' ? 'selected' :  ''}}>Plate Number</option>
                                <option value="email" {{ $search_type == 'email' ? 'selected' :  ''}}>Email</option>
                                <option value="name" {{ $search_type == 'name' ? 'selected' :  ''}}>Name</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <input type="text" name="search_val" value="{{$search_val}}" class="form-control" placeholder="Search">
                        </div>
                        <div class="form-group col-md-4">
                            <input type="submit" name="search_btn" class="btn btn-primary" value="Search">
                            <input type="submit" name="reset_btn" class="btn btn-danger" value="Reset">
                        </div>
                    </form>
                </div>
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
                                        <th>@lang('user-list.select_rights')</th>
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
                                        <td class="">{{$userList->group_access_id ? ($userList->group_access->name) ?: 'N/A' : 'N/A'}}</td>
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
                                            @if($userList->email)
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
                                                title="@lang('tommy-reservation.view_plates')" 
                                                class="view_plates btn btn-success btn-sm mt-10"
                                                data-id="{{$userList->id}}" 
                                                style="cursor:pointer;"><i class="icon-list"></i>
                                            </a>
                                            <a 
                                                title="@lang('user-list.edit')" 
                                                class="btn btn-info btn-sm mt-10" 
                                                href="{{url('user-list/edit/'.$userList->id)}}">
                                                <i class="fa fa-pencil-square-o"></i>
                                            </a>
                                            <a 
                                                title="@lang('user-list.delete')" 
                                                class="delete btn btn-danger btn-sm mt-10"
                                                data-id="{{$userList->id}}" 
                                                style="cursor:pointer;">
                                                <i class="fa fa-trash-o"></i>
                                            </a>
                                        <td>
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
                                            <div class="form-group col-sm-6 {{ $errors->first('access_rights', 'has-error') }}">
                                                <label for="access_rights" class="col-sm-4 control-label">@lang('user-list.select_rights') </label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" title="@lang('user-list.select_rights')" name="access_rights">
                                                        <option value="">@lang('user-list.select')</option>
                                                        @foreach ($rights as $right)
                                                        <option 
                                                            value="{{$right->id}}"
                                                            @if(old('access_rights',$userList->group_access_id) === $right->id || $userList->group_access_id === $right->id) selected="selected" @endif >{{$right->name}}
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

<div id="view_number_plates" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Number Plates</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>
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
    $(document).on('click', '.view_plates', function (e) {
var id = $(this).data('id');
$.get("/user-list/view-plates",
{
"id": id
},
        function (data) {
        $("#view_number_plates .modal-body").empty();
        $("#view_number_plates .modal-body").append(data);
        $("#view_number_plates").modal("show");
        });
});
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
        if (result){
        window.location.href = "{{url('user-list/delete')}}/" + id;
        }
        }
});
});
$(document).on('click', '.send-instructions', function (e) {
var id = $(this).data('id');
bootbox.confirm({
title: "<?= __('user-list.send_instructions_title') ?>",
        message: "<?= __('user-list.send_instructions_message') ?>",
        buttons: {
        cancel: {
        label: '<i class="fa fa-times"></i> <?= __('user-list.send_instructions_cancel') ?>',
                className: 'btn-danger'
        },
                confirm: {
                label: '<i class="fa fa-check"></i> <?= __('user-list.send_instructions_confirm') ?>',
                        className: 'btn-success'
                }
        },
        callback: function (result) {
        if (result){
        window.location.href = "{{url('user-list/send-instructions')}}/" + id;
        }
        }
});
});
$(document).on('click', '.block', function (e) {
var id = $(this).data('id');
var blockText = $(this).text();
bootbox.confirm({
title: blockText + " User?",
        message: "Are you sure want to " + blockText + " user?",
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
        if (result){
        window.location.href = "{{url('user-list/block-or-unbolock')}}/" + id;
        }
        }
});
});
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
        });
$('.table').footable();

</script>
@endpush