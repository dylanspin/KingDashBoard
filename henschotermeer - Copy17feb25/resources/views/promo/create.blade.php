@extends('layouts.master')

@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
<link href="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.css')}}" rel="stylesheet">
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
                <h3 class="box-title pull-left">@lang('promo.create_promo')</h3>
                <a  class="btn btn-success pull-right" href="{{url('promo/')}}">
                    <i class="icon-list"></i> @lang('promo.view_promos')
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
                    action="{{url('promo/create')}}" 
                    method="POST" 
                    enctype="multipart/form-data" 
                    class="form-horizontal promoForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                    <div id="rootwizard">
                        <ul class="nav nav-tabs">
                            <!--<li class="active">
                                <a href="#tab1" 
                                   data-toggle="tab" 
                                   style="cursor:pointer;">Promo Info</a>
                            </li>-->
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab1">
                                <h2 class="hidden">&nbsp;</h2>
                                <div class="form-group {{ $errors->first('code', 'has-error') }}">
                                    <label for="code" class="col-sm-2 control-label">@lang('promo.promo_code') *</label>
                                    <div class="col-sm-10">
                                        <input 
                                            id="code" 
                                            name="code" 
                                            placeholder="@lang('promo.promo_code')" 
                                            type="text" 
                                            class="form-control code" 
                                            value="{!! old('code', str_random(10)) !!}"/>
                                        <input type="button" value="Change Code" onclick="randomStringToInput(this)">

                                        {!! $errors->first('code', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group hidden {{ $errors->first('discount_type', 'has-error') }}">
                                    <label for="discount_type" class="col-sm-2 control-label">@lang('promo.discount_type') *</label>
                                    <div class="col-sm-10">
                                        <select 
                                            class="form-control" 
                                            title="@lang('promo.discount_type')" 
                                            name="discount_type" 
                                            required="">
                                            <option value="" selected="">@lang('promo.select')</option>
                                            <option value="price">@lang('promo.price')</option>
                                            <option value="percent">@lang('promo.percentage')</option>
                                        </select>

                                        {!! $errors->first('discount_type', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group discount_price_wrapper hidden {{ $errors->first('price', 'has-error') }}">
                                    <label for="price" class="col-sm-2 control-label">@lang('promo.price') *</label>
                                    <div class="col-sm-10">
                                        <input 
                                            id="price" 
                                            name="price" 
                                            placeholder="@lang('promo.price')" 
                                            type="text" 
                                            class="form-control price" 
                                            value="{!! old('price') !!}"/>

                                        {!! $errors->first('price', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group discount_percent_wrapper hidden {{ $errors->first('percentage', 'has-error') }}">
                                    <label for="percentage" class="col-sm-2 control-label">@lang('promo.percentage') *</label>
                                    <div class="col-sm-10">
                                        <input 
                                            id="percentage" 
                                            name="percentage" 
                                            placeholder="@lang('promo.percentage')" 
                                            type="text" 
                                            class="form-control percentage" 
                                            value="{!! old('percent') !!}"/>

                                        {!! $errors->first('percentage', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group hidden {{ $errors->first('valid_number_of_times', 'has-error') }}">
                                    <label for="valid_number_of_times" class="col-sm-2 control-label">@lang('promo.valid_number_of_times') *</label>
                                    <div class="col-sm-10">
                                        <input 
                                            id="valid_number_of_times" 
                                            name="valid_number_of_times" 
                                            placeholder="@lang('promo.valid_number_of_times')" 
                                            type="number" 
                                            class="form-control valid_number_of_times" 
                                            value="{!! old('valid_number_of_times') !!}"/>

                                        {!! $errors->first('valid_number_of_times', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->first('valid_dates', 'has-error') }}">
                                    <label for="valid_dates" class="col-sm-2 control-label">
                                    @lang('promo.valid_dates') *</label>
                                    <div class="col-sm-10">
                                        <input 
                                            id="valid_dates" 
                                            name="valid_dates" 
                                            placeholder="@lang('promo.valid_dates')" 
                                            type="text" 
                                            class="form-control valid_dates input-daterange-datepicker-promo" 
                                            value="{!! old('valid_dates') !!}" 
                                            required=""/>

                                        {!! $errors->first('valid_dates', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>


<div class="form-group {{ $errors->first('valid_group_name', 'has-error') }}">
                                    <label for="valid_group_name" class="col-sm-2 control-label">@lang('promo.valid_group_name') *</label>
                                    <div class="col-sm-10">
                                        <input 
                                            id="valid_group_name" 
                                            name="valid_group_name" 
                                            placeholder="@lang('promo.valid_group_name')" 
                                            type="text" 
                                            class="form-control valid_group_no " 
                                            value="{!! old('valid_group_name') !!}" 
                                            required=""/>

                                        {!! $errors->first('valid_group_name', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>


<div class="form-group {{ $errors->first('valid_group_no', 'has-error') }}">
                                    <label for="valid_group_no" class="col-sm-2 control-label">@lang('promo.valid_group_no') *</label>
                                    <div class="col-sm-10">
                                        <input 
                                            id="valid_group_no" 
                                            name="valid_group_no" 
                                            placeholder="@lang('promo.valid_group_no')" 
                                            type="number" 
                                            class="form-control valid_group_no " 
                                            value="{!! old('valid_group_no') !!}" 
                                            required=""/>

                                        {!! $errors->first('valid_group_no', '<span class="help-block">:message</span>') !!}
                                    </div>
                                </div>



                            </div>
                            <ul class="pager wizard">
                                <!--<li class="previous"><a href="#">Previous</a></li>-->
                                <!--<li class="next"><a href="#">Next</a></li>-->
                                <li class="next finish" style="display:none;"><a href="javascript:;">@lang('promo.submit')</a></li>
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
<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"
type="text/javascript"></script>
<script src="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{ asset('/js/jquery.mask.js') }}"></script>
<script src="{{ asset('/js/promo.js') }}"></script>

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
$('.input-daterange-datepicker-promo').daterangepicker({
                buttonClasses: ['btn', 'btn-sm'],
                applyClass: 'btn-danger',
                cancelClass: 'btn-inverse',
                startDate: '{{date("d-m-Y")}}',
                endDate: '{{date("d-m-Y")}}',
                drops: 'up',
                locale: {
                    format: 'DD-MM-YYYY'
                }
            });
</script>
@endpush