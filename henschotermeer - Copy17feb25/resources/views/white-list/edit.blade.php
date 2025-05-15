@extends('layouts.master')

@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
{{--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">--}}
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
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
                <div class="white-box">
                    <h3 class="box-title pull-left">@lang('white-list.create_white_list')</h3>
                    <a  class="btn btn-success pull-right" href="{{url('white-list/')}}">
                        <i class="icon-list"></i> @lang('white-list.view_users')
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
                        action="{{url('white-list/edit/'.$whiteListUser->id)}}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="form-horizontal whiteListForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <!--<li class="active">
                                    <a href="#tab1" 
                                       data-toggle="tab" 
                                       style="cursor:pointer;">User Profile</a>
                                </li>-->
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <input 
                                        type="hidden" 
                                        name="whitelist_live_id" 
                                        value="{{$whiteListUser->live_id}}"/>


                                    <div class="form-group {{ $errors->first('email', 'has-error') }}">
                                        <label for="email" class="col-sm-2 control-label">@lang('white-list.email') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="email" 
                                                name="email" 
                                                placeholder="@lang('white-list.email')" 
                                                type="text" 
                                                class="form-control required email" 
                                                value="{!! old('email', $whiteListUser->email) !!}"
                                                readonly=""/>

                                            {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>


                                    <div class="form-group {{ $errors->first('group', 'has-error') }}">
                                        <label for="group" class="col-sm-2 control-label">@lang('white-list.select_group') </label>
                                        <div class="col-sm-10">
                                            <select class="form-control" title="Select Group..." name="group">
                                                <option value="">@lang('white-list.select')</option>
                                                @foreach ($groups as $group)
                                                <option 
                                                    value="{{$group->id}}"
                                                    @if(old('group') == $group->id || $whiteListUser->group_id == $group->id) selected="selected" @endif >{{$group->name}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="help-block">{{ $errors->first('group', ':message') }}</span>
                                </div>
                            </div>
                            <ul class="pager wizard">
                                <li class="next finish" style="display:none;"><a href="javascript:;">@lang('white-list.submit')</a></li>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"
type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{ asset('/js/jquery.mask.js') }}"></script>
<script src="{{ asset('/js/whitelist.js') }}"></script>

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