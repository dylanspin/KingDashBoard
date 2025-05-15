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
                    <h3 class="box-title pull-left">@lang('messages.message')</h3>
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
                        action="{{url('messages/create')}}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="form-horizontal messageForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab1" data-toggle="tab">@lang('messages.details')</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group {{ $errors->first('device_name', 'has-error') }}">
                                        <label for="message_key" class="col-sm-3 control-label">@lang('messages.key') *</label>
                                        <div class="col-sm-9">
                                            <select 
                                                class="form-control message_key select2" 
                                                title="@lang('messages.key')" 
                                                name="message_key">
                                                <option value="">@lang('devices.select')</option>
                                                @foreach ($message_type as $type)
                                                <option 
                                                    value="{{$type->id}}"
                                                    @if(old('message_key') === $type->id) selected="selected" @endif >{{$type->name}}
                                            </option>
                                            @endforeach
                                        </select>
                                        {!! $errors->first('message_key', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                @foreach($langs as $lang)
                                @if($lang->code == 'en')
                                <div class="form-group {{ $errors->first('lang_en', 'has-error') }}">
                                    <label for="lang_en" class="col-sm-3 control-label">@lang('messages.message_in') {{$lang->name}} ({{$lang->country}}) *</label>
                                    <div class="col-sm-9">
                                        <input 
                                            id="lang_en" 
                                            name="lang_en" 
                                            type="text" 
                                            placeholder="@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})" 
                                            class="form-control required" 
                                            value="{!! old('lang_en') !!}"/>

                                        {!! $errors->first('lang_en', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <span class="help-block">{{ $errors->first('lang_en', ':message') }}</span>
                                </div>
                                @elseif($lang->code == 'nl')
                                <div class="form-group {{ $errors->first('lang_nl', 'has-error') }}">
                                    <label for="lang_nl" class="col-sm-3 control-label">@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})</label>
                                    <div class="col-sm-9">
                                        <input 
                                            id="lang_nl" 
                                            name="lang_nl" 
                                            type="text" 
                                            placeholder="@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})" 
                                            class="form-control required" 
                                            value="{!! old('lang_nl') !!}"/>

                                        {!! $errors->first('lang_nl', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <span class="help-block">{{ $errors->first('lang_nl', ':message') }}</span>
                                </div>
                                @elseif($lang->code == 'fr')
                                <div class="form-group {{ $errors->first('lang_fr', 'has-error') }}">
                                    <label for="lang_fr" class="col-sm-3 control-label">@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})</label>
                                    <div class="col-sm-9">
                                        <input 
                                            id="lang_fr" 
                                            name="lang_fr" 
                                            type="text" 
                                            placeholder="@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})" 
                                            class="form-control required" 
                                            value="{!! old('lang_fr') !!}"/>

                                        {!! $errors->first('lang_fr', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <span class="help-block">{{ $errors->first('lang_fr', ':message') }}</span>
                                </div>
                                @elseif($lang->code == 'es')
                                <div class="form-group {{ $errors->first('lang_es', 'has-error') }}">
                                    <label for="lang_es" class="col-sm-3 control-label">@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})</label>
                                    <div class="col-sm-9">
                                        <input 
                                            id="lang_es" 
                                            name="lang_es" 
                                            type="text" 
                                            placeholder="@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})" 
                                            class="form-control required" 
                                            value="{!! old('lang_es') !!}"/>

                                        {!! $errors->first('lang_es', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <span class="help-block">{{ $errors->first('lang_es', ':message') }}</span>
                                </div>
                                @elseif($lang->code == 'no')
                                <div class="form-group {{ $errors->first('lang_no', 'has-error') }}">
                                    <label for="lang_no" class="col-sm-3 control-label">@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})</label>
                                    <div class="col-sm-9">
                                        <input 
                                            id="lang_no" 
                                            name="lang_no" 
                                            type="text" 
                                            placeholder="@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})" 
                                            class="form-control required" 
                                            value="{!! old('lang_no') !!}"/>

                                        {!! $errors->first('lang_no', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <span class="help-block">{{ $errors->first('lang_no', ':message') }}</span>
                                </div>
                                @elseif($lang->code == 'gr')
                                <div class="form-group {{ $errors->first('lang_gr', 'has-error') }}">
                                    <label for="lang_gr" class="col-sm-3 control-label">@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})</label>
                                    <div class="col-sm-9">
                                        <input 
                                            id="lang_gr" 
                                            name="lang_gr" 
                                            type="text" 
                                            placeholder="@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})" 
                                            class="form-control required" 
                                            value="{!! old('lang_gr') !!}"/>

                                        {!! $errors->first('lang_gr', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <span class="help-block">{{ $errors->first('lang_gr', ':message') }}</span>
                                </div>
                                @elseif($lang->code == 'de')
                                <div class="form-group {{ $errors->first('lang_de', 'has-error') }}">
                                    <label for="lang_de" class="col-sm-3 control-label">@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})</label>
                                    <div class="col-sm-9">
                                        <input 
                                            id="lang_de" 
                                            name="lang_de" 
                                            type="text" 
                                            placeholder="@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})" 
                                            class="form-control required" 
                                            value="{!! old('lang_gr') !!}"/>

                                        {!! $errors->first('lang_de', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <span class="help-block">{{ $errors->first('lang_de', ':message') }}</span>
                                </div>
                                @elseif($lang->code == 'fy')
                                <div class="form-group {{ $errors->first('lang_fy', 'has-error') }}">
                                    <label for="lang_fy" class="col-sm-3 control-label">@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})</label>
                                    <div class="col-sm-9">
                                        <input 
                                            id="lang_fy" 
                                            name="lang_fy" 
                                            type="text" 
                                            placeholder="@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})" 
                                            class="form-control required" 
                                            value="{!! old('lang_fy') !!}"/>

                                        {!! $errors->first('lang_fy', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <span class="help-block">{{ $errors->first('lang_fy', ':message') }}</span>
                                </div>
                                @elseif($lang->code == 'dr')
                                <div class="form-group {{ $errors->first('lang_dr', 'has-error') }}">
                                    <label for="lang_dr" class="col-sm-3 control-label">@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})</label>
                                    <div class="col-sm-9">
                                        <input 
                                            id="lang_dr" 
                                            name="lang_dr" 
                                            type="text" 
                                            placeholder="@lang('messages.message_in') {{$lang->name}} ({{$lang->country}})" 
                                            class="form-control required" 
                                            value="{!! old('lang_dr') !!}"/>

                                        {!! $errors->first('lang_gr', '<span class="help-block">:message</span>') !!}
                                    </div>
                                    <span class="help-block">{{ $errors->first('lang_gr', ':message') }}</span>
                                </div>

                                @endif
                                @endforeach

                            </div>
                            <ul class="pager wizard">
                                <li class="next finish" ><a href="javascript:;">@lang('messages.finish')</a></li>
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
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"
type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{ asset('/js/jquery.mask.js') }}"></script>
<script src="{{ asset('/js/messages.js') }}"></script>

<script>


@if (\Session::has('message'))
        $.toast({
        heading: '{{session()->get('heading')}}',
                position: 'top-center',
                text: '{{session()->get('message')}}',
                loaderBg: '#ff6849',
                icon: '{{session()->get('icon')}}',
                hideAfter: 5000,
                stack: 6
        });
@endif

</script>
@endpush