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
                    <h3 class="box-title pull-left">@lang('products.person_ticket')</h3>
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
                        id="personTicketForm" 
                        action="{{url('products/person_ticket')}}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="form-horizontal personTicketForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab1" data-toggle="tab">@lang('barcode.details')</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group {{ $errors->first('title', 'has-error') }}">
                                        <label for="title" class="col-sm-2 control-label">@lang('products.title') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="title" 
                                                name="title" 
                                                type="text" 
                                                placeholder="@lang('products.title')" 
                                                class="form-control required" 
                                                value="{!! old('title',$title) !!}"/>

                                            {!! $errors->first('title', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('title_nl', 'has-error') }}">
                                        <label for="title_nl" class="col-sm-2 control-label">@lang('products.title_nl') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="title_nl" 
                                                name="title_nl" 
                                                type="text" 
                                                placeholder="@lang('products.title_nl')" 
                                                class="form-control required" 
                                                value="{!! old('title_nl',$title_nl) !!}"/>

                                            {!! $errors->first('title_nl', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('price', 'has-error') }}">
                                        <label for="price" class="col-sm-2 control-label">@lang('products.price') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="price" 
                                                name="price" 
                                                type="text" 
                                                placeholder="@lang('products.price')" 
                                                class="form-control required" 
                                                value="{!! old('price',$price) !!}"/>

                                            {!! $errors->first('price', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                </div>
                                <ul class="pager wizard">
                                    <li class="next finish" ><a href="javascript:;">@lang('products.submit')</a></li>
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
    <script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
    <script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
        <script src="{{ asset('/js/person_ticket.js') }}"></script>

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