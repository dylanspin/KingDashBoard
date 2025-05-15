@extends('layouts.master')

@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
{{--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">--}}
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
<link href="{{asset('plugins/components/custom-select/custom-select.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/switchery/dist/switchery.min.css')}}" rel="stylesheet" />
<link href="{{asset('plugins/components/bootstrap-select/bootstrap-select.min.css')}}" rel="stylesheet" />
<link href="{{asset('plugins/components/bootstrap-tagsinput/dist/bootstrap-tagsinput.css')}}" rel="stylesheet" />
<link href="{{asset('plugins/components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
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
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('groups.create_group')</h3>
                <a  class="btn btn-success pull-right" href="{{url('group/')}}">
                    <i class="icon-list"></i> @lang('groups.view_groups')
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
                    id="commentForm" 
                    action="{{url('group/create')}}" 
                    method="POST" 
                    enctype="multipart/form-data" 
                    class="form-horizontal groupForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                    <div id="rootwizard">
                        <ul class="nav nav-tabs">
                            <!--<li class="active"><a href="#tab1" data-toggle="tab"></a></li>-->
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab1">
                                <h2 class="hidden">&nbsp;</h2>
                                <div class="form-group {{ $errors->first('name', 'has-error') }}">
                                    <label for="name" class="col-sm-2 control-label">@lang('groups.name') *</label>
                                    <div class="col-sm-10">
                                        <input 
                                            id="name" 
                                            name="name" 
                                            type="text" 
                                            placeholder="@lang('groups.name')" 
                                            class="form-control required" 
                                            value="{!! old('name') !!}"/>

                                        {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->first('device', 'has-error') }}">
                                    <label for="device" class="col-sm-2 control-label">@lang('groups.devices') *</label>
                                    <div class="col-sm-10">
                                        <select 
                                            class="select2 select2-multiple" 
                                            title="@lang('groups.device_title')" 
                                            name="device[]" 
                                            multiple="multiple" 
                                            data-placeholder="Choose">
                                            @foreach ($devices as $device)
                                            <option 
                                                value="{{$device->id}}">{{$device->device_name}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {!! $errors->first('device', '<span class="help-block">:message</span>') !!}
                                </div>
                                <div class="form-group">
                                    <div class="col-md-10 col-md-offset-2">
                                        <div class="checkbox checkbox-primary pull-left p-t-0">
                                            <input 
                                                type="checkbox" 
                                                id="has_anti_pass_back" 
                                                class="form-control has_anti_pass_back" 
                                                name="has_anti_pass_back" checked> 
                                            <label for="has_anti_pass_back"> @lang('groups.has_anti_pass_back') </label>
                                        </div>
                                    </div>
                                </div>
                                <ul class="pager wizard">
                                    <li class="next finish" ><a href="javascript:;">@lang('groups.submit')</a></li>
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
<script src="{{asset('plugins/components/icheck/icheck.min.js')}}"></script>
<script src="{{asset('plugins/components/icheck/icheck.init.js')}}"></script>
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
{{--<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>--}}
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="{{asset('plugins/components/switchery/dist/switchery.min.js')}}"></script>
<script src="{{asset('plugins/components/custom-select/custom-select.min.js')}}" type="text/javascript"></script>
<script src="{{asset('plugins/components/bootstrap-select/bootstrap-select.min.js')}}" type="text/javascript"></script>
<script src="{{asset('plugins/components/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js')}}"></script>
<script src="{{asset('plugins/components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js')}}" type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{ asset('/js/group.js') }}"></script>

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