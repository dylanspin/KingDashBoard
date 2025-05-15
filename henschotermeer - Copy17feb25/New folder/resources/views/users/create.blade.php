@extends('layouts.master')

@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<!--{{--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">--}}-->
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
<style>

    #rootwizard .nav.nav-pills {
        margin-bottom: 25px;
    }
    .nav-pills>li>a{
        cursor: default;;
        background-color: inherit;
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
                <h3 class="box-title pull-left">Create @lang('sidebar.employees')</h3>
                <div class="clearfix"></div>
                <form id="userForm" action="{{url('user/create')}}"
                      method="POST" enctype="multipart/form-data" class="form-horizontal userForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                    <div id="rootwizard">

                        <div class="form-group {{ $errors->first('name', 'has-error') }}">
                            <label for="name" class="col-sm-2 control-label">Name *</label>
                            <div class="col-sm-10">
                                <input id="name" name="name" type="text"
                                       placeholder="Name" class="form-control required"
                                       value="{!! old('name') !!}"/>

                                {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group {{ $errors->first('email', 'has-error') }}">
                            <label for="email" class="col-sm-2 control-label">Email *</label>
                            <div class="col-sm-10">
                                <input id="email" name="email" placeholder="E-mail" type="text"
                                       class="form-control required email" value="{!! old('email') !!}"/>
                                {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group {{ $errors->first('password', 'has-error') }}">
                            <label for="password" class="col-sm-2 control-label">Password *</label>
                            <div class="col-sm-10">
                                <input id="password" name="password" type="password" placeholder="Password"
                                       class="form-control required" value="{!! old('password') !!}"/>
                                {!! $errors->first('password', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group {{ $errors->first('password_confirmation', 'has-error') }}">
                            <label for="password_confirm" class="col-sm-2 control-label">Confirm Password
                                *</label>
                            <div class="col-sm-10">
                                <input id="password_confirmation" name="password_confirmation"
                                       type="password"
                                       placeholder="Confirm Password " class="form-control required"/>
                                {!! $errors->first('password_confirmation', '<span class="help-block">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group required {{ $errors->first('role', 'has-error') }}">
                            <label for="group" class="col-sm-2 control-label">Role *</label>
                            <div class="col-sm-10">
                                <select class="form-control required text-capitalize" title="Select role..." name="role"
                                        id="role">
                                    <option value="">Select</option>
                                    @foreach($roles as $role)
                                    @if($role->name == 'admin'  || $role->name == 'service')
                                    @continue
                                    @endif
                                    <option value="{{ $role->id }}"
                                            @if($role->id == old('role')) selected="selected" @endif >{{ $role->name}}</option>
                                    @endforeach
                                </select>
                                <span class="help-block">{{ $errors->first('role', ':message') }}</span>
                            </div>
                        </div>
                        <ul class="pager wizard">
                            <li class="next finish" ><a href="javascript:;">Add</a></li>
                        </ul>
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
<!--{{--<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>--}}-->
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"
type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{ asset('/js/users.js') }}"></script>

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
@endpush