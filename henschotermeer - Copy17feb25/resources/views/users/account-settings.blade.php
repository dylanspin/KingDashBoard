@extends('layouts.master')

@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css"><link href="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/jquery-asColorPicker-master/css/asColorPicker.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/timepicker/bootstrap-timepicker.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.css')}}" rel="stylesheet">
<style>
    #rootwizard .nav.nav-pills {
        margin-bottom: 25px;
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

    .help-block {
        display: block;
        margin-top: 5px;
        margin-bottom: 10px;
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
                <h3 class="box-title pull-left">@lang('account-setting.account_settings')</h3>
                <div class="clearfix"></div>
                <form id="commentForm" action="{{url('account-settings')}}"
                      method="POST" enctype="multipart/form-data" class="form-horizontal">
                    <!-- CSRF Token -->
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                    <div id="rootwizard">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#tab1" 
                                   data-toggle="tab" 
                                   style="cursor:pointer;">@lang('account-setting.user_profile')</a>
                            </li>
                            <li>
                                <a href="#tab2" 
                                   data-toggle="tab" 
                                   style="cursor:pointer;">@lang('account-setting.bio')</a>
                            </li>
                            <li>
                                <a href="#tab3" 
                                   data-toggle="tab" 
                                   style="cursor:pointer;">@lang('account-setting.address')</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab1">
                                <h2 class="hidden">&nbsp;</h2>
                                <div class="form-group {{ $errors->first('name', 'has-error') }}">
                                    <label for="name" class="col-sm-2 control-label">@lang('account-setting.name') *</label>
                                    <div class="col-sm-10">
                                        <input id="name" name="name" type="text"
                                               placeholder="@lang('account-setting.name')" class="form-control required"
                                               value="{{$user->name}}"/>

                                        {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->first('email', 'has-error') }}">
                                    <label for="email" class="col-sm-2 control-label">@lang('account-setting.email') *</label>
                                    <div class="col-sm-10">
                                        <input id="email" name="email" placeholder="@lang('account-setting.email')" type="text"
                                               class="form-control required email" value="{{$user->email}}"/>
                                        {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <h6 class="col-sm-offset-2 pl-15"><b>@lang('account-setting.password_msg')</b></h6>
                                </div>
                                <div class="form-group {{ $errors->first('password', 'has-error') }}">
                                    <label for="password" class="col-sm-2 control-label">@lang('account-setting.pass') *</label>
                                    <div class="col-sm-10">
                                        <input id="password" name="password" type="password" placeholder="@lang('account-setting.pass')"
                                               class="form-control required" value="{!! old('password') !!}"/>
                                        {!! $errors->first('password', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->first('password_confirmation', 'has-error') }}">
                                    <label for="password_confirm" class="col-sm-2 control-label">@lang('account-setting.confirm_pass')
                                        *</label>
                                    <div class="col-sm-10">
                                        <input id="password_confirmation" name="password_confirmation"
                                               type="password"
                                               placeholder="@lang('account-setting.confirm_pass') " class="form-control required"/>
                                        {!! $errors->first('password_confirmation', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab2" disabled="disabled">
                                <h2 class="hidden">&nbsp;</h2>
                                <div class="form-group  {{ $errors->first('dob', 'has-error') }}">
                                    <label for="dob" class="col-sm-2 control-label">@lang('account-setting.dob')</label>
                                    <div class="col-sm-10">
                                        <input 
                                            autocomplete="off" 
                                            value="{{$user->profile->dob ? date('d-m-Y', strtotime($user->profile->dob)) : null}}" 
                                            id="dob" 
                                            name="dob" 
                                            type="text" 
                                            class="form-control dob"
                                            data-date-format="dd-mm-yyyy"
                                            placeholder="dd-mm-yyyy"/>
                                        <span class="help-block">{{ $errors->first('dob', ':message') }}</span>

                                    </div>
                                </div>


                                <div class="form-group {{ $errors->first('pic_file', 'has-error') }}">
                                    <label for="pic" class="col-sm-2 control-label">@lang('account-setting.profile_pic')</label>
                                    <div class="col-sm-10">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-new thumbnail"
                                                 style="width: 200px; height: 200px;">
                                                @if($user->profile->pic != null)
                                                <img src="{{asset('/uploads/users/'.$user->profile->pic)}}" alt="profile pic">
                                                @else
                                                <img src="{{asset('/uploads/users/default_user.png')}}" alt="profile pic">
                                                @endif
                                            </div>
                                            <div class="fileinput-preview fileinput-exists thumbnail"
                                                 style="max-width: 200px; max-height: 200px;"></div>
                                            <div>
                                                <span class="btn btn-default btn-file">
                                                    <span class="fileinput-new">@lang('account-setting.select_img')</span>
                                                    <span class="fileinput-exists">@lang('account-setting.change')</span>
                                                    <input id="pic" name="pic_file" type="file" class="form-control"/>
                                                </span>
                                                <a href="#" class="btn btn-danger fileinput-exists"
                                                   data-dismiss="fileinput">@lang('account-setting.remove')</a>
                                            </div>
                                        </div>
                                        <span class="help-block">{{ $errors->first('pic_file', ':message') }}</span>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label for="bio" class="col-sm-2 control-label">@lang('account-setting.bio')
                                        <small>(@lang('account-setting.brief_intro')) </small>
                                    </label>
                                    <div class="col-sm-10">
                                        <textarea name="bio" id="bio" class="form-control resize_vertical"
                                                  rows="4">{{$user->profile->bio}}</textarea>
                                    </div>
                                    {!! $errors->first('bio', '<span class="help-block">:message</span>') !!}
                                </div>
                            </div>
                            <div class="tab-pane" id="tab3" disabled="disabled">
                                <div class="form-group {{ $errors->first('gender', 'has-error') }}">
                                    <label for="email" class="col-sm-2 control-label">@lang('account-setting.gender') *</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" title="@lang('account-setting.select_gender')" name="gender">
                                            <option value="">Select</option>
                                            <option value="male"
                                                    @if($user->profile->gender === 'male') selected="selected" @endif >@lang('account-setting.male')
                                        </option>
                                        <option value="female"
                                                @if($user->profile->gender === 'female') selected="selected" @endif >
                                                @lang('account-setting.female')
                                    </option>
                                    <option value="other"
                                            @if($user->profile->gender === 'other') selected="selected" @endif >@lang('account-setting.other')
                                </option>

                            </select>
                            <span class="help-block">{{ $errors->first('gender', ':message') }}</span>
                        </div>

                    </div>

                    <div class="form-group {{ $errors->first('country', 'has-error') }}">
                        <label for="country" class="col-sm-2 control-label">@lang('account-setting.country')</label>
                        <div class="col-sm-10">
                            <input id="countries" name="country" type="text"
                                   class="form-control"
                                   value="{{$user->profile->country}}"/>
                            <span class="help-block">{{ $errors->first('country', ':message') }}</span>

                        </div>
                    </div>

                    <div class="form-group {{ $errors->first('state', 'has-error') }}">
                        <label for="state" class="col-sm-2 control-label">@lang('account-setting.state')</label>
                        <div class="col-sm-10">
                            <input id="state" name="state" type="text"
                                   class="form-control"
                                   value="{{$user->profile->state}}"/>
                            <span class="help-block">{{ $errors->first('state', ':message') }}</span>
                        </div>
                    </div>

                    <div class="form-group {{ $errors->first('city', 'has-error') }}">
                        <label for="city" class="col-sm-2 control-label">@lang('account-setting.city')</label>
                        <div class="col-sm-10">
                            <input id="city" name="city" type="text" class="form-control"
                                   value="{{$user->profile->city}}"/>
                            <span class="help-block">{{ $errors->first('city', ':message') }}</span>

                        </div>
                    </div>

                    <div class="form-group {{ $errors->first('address', 'has-error') }}">
                        <label for="address" class="col-sm-2 control-label">@lang('account-setting.address')</label>
                        <div class="col-sm-10">
                            <input id="address" name="address" type="text" class="form-control"
                                   value="{{$user->profile->address}}"/>
                            <span class="help-block">{{ $errors->first('address', ':message') }}</span>

                        </div>
                    </div>

                    <div class="form-group {{ $errors->first('postal', 'has-error') }}">
                        <label for="postal" class="col-sm-2 control-label">@lang('account-setting.postal')</label>
                        <div class="col-sm-10">
                            <input id="postal" name="postal" type="text" class="form-control"
                                   value="{{$user->profile->postal}}"/>
                            <span class="help-block">{{ $errors->first('postal', ':message') }}</span>

                        </div>
                    </div>
                </div>

                <ul class="pager wizard">
                    <li class="previous"><a href="#">@lang('account-setting.prev')</a></li>
                    <li class="next"><a href="#">@lang('account-setting.nxt')</a></li>
                    <li class="next finish" style="display:none;"><a href="javascript:;">@lang('account-setting.finish')</a></li>
                </ul>
            </div>
        </div>
    </form>


    @if(count($errors) > 0)
    <div class="alert alert-danger">@lang('account-setting.error_msg')</div>
    @endif

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
<!-- Date Picker Plugin JavaScript -->
<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
<!-- Date range Plugin JavaScript -->
<script src="{{asset('plugins/components/timepicker/bootstrap-timepicker.min.js')}}"></script>
<script src="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>

<script src="{{ asset('js/edituser.js') }}"></script>

<script>
@if (\Session::has('message'))
        $.toast({
        heading: 'Success!',
                position: 'top-center',
                text: '{{session()->get('message')}}',
                loaderBg: '#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 6
        });
@endif
</script>
<script>
            jQuery('.dob').datepicker({
    endDate: moment().add(1, 'h').toDate()
    });
</script>
@endpush