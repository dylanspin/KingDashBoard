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
                    <h3 class="box-title pull-left">@lang('unwanted_character.edit_character')</h3>
                    <a  class="btn btn-success pull-right" href="{{url('unwanted-character')}}">
                        <i class="icon-list"></i> @lang('unwanted_character.view_character')
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
                        id="parkingRule" 
                        action="{{url('unwanted-character/update')}}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="form-horizontal manageRule">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab1" data-toggle="tab">@lang('unwanted_character.update_character')</a></li>
                            </ul>
                            <input type="hidden" name="character_id" value="{{ $unwanted_character->id }}">
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group {{ $errors->first('unwanted_character', 'has-error') }}">
                                        <label for="unwanted_character" class="col-sm-2 control-label">@lang('unwanted_character.character_unwanted')</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="unwanted_character" 
                                                name="unwanted_character" 
                                                type="text" 
                                                placeholder="@lang('unwanted_character.character_unwanted')" 
                                                class="form-control required" 
                                                value="{!! old('unwanted_character',$unwanted_character->unwanted_character) !!}"/>

                                            {!! $errors->first('unwanted_character', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('valid_character', 'has-error') }}">
                                        <label for="valid_character" class="col-sm-2 control-label">@lang('unwanted_character.valid_character')</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="valid_character" 
                                                name="valid_character" 
                                                type="text" 
                                                placeholder="@lang('unwanted_character.valid_character')" 
                                                class="form-control required" 
                                                value="{!! old('valid_character',$unwanted_character->valid_character) !!}"/>

                                            {!! $errors->first('valid_character', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                </div>
                                <ul class="pager wizard">
                                    <li class="next finish" ><a href="javascript:;">@lang('barcode.submit')</a></li>
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
<script src="{{ asset('/js/manage-rules.js') }}"></script>

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