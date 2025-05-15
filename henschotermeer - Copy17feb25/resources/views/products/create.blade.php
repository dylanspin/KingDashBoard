@extends('layouts.master')

@push('css')
    <link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/icheck/skins/all.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/components/jqueryui/jquery-ui.min.css') }}" rel="stylesheet">
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

        .nav-pills>li>a {
            cursor: default;
            ;
            background-color: inherit;
        }

        .nav-pills>li.active>a,
        .nav-pills>li.active>a:focus,
        .nav-pills>li.active>a:hover {
            background: #0283cc !important;
            color: #fff !important;
        }

        .nav-pills>li>a:focus,
        .nav-tabs>li>a:focus,
        .nav-pills>li>a:hover,
        .nav-tabs>li>a:hover {
            border: 1px solid transparent !important;
            background-color: inherit !important;
        }

        .has-error .help-block {
            color: #EF6F6C;
        }

        .select2 {
            width: 100% !important;
        }

        .error-block {
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
                    <h3 class="box-title pull-left">@lang('products.add_product')</h3>
                    <div class="clearfix"></div>
                    <a class="btn btn-success pull-right" href="{{ url('products') }}"><i class="icon-list"></i>
                        @lang('sidebar.manage_products')</a>
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="createProduct" action="{{ url('products/store') }}" method="POST"
                        enctype="multipart/form-data" class="form-horizontal createProduct">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

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
                                            <input id="title" name="title" type="text"
                                                placeholder="@lang('products.title')" class="form-control required" />

                                            {!! $errors->first('title', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('title_nl', 'has-error') }}">
                                        <label for="title_nl" class="col-sm-2 control-label">@lang('products.title_nl') *</label>
                                        <div class="col-sm-10">
                                            <input id="title_nl" name="title_nl" type="text"
                                                placeholder="@lang('products.title_nl')" class="form-control required" />

                                            {!! $errors->first('title_nl', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('products', 'has-error') }}">
                                        <label for="products" class="col-sm-2 control-label">@lang('products.products') *</label>
                                        <div class="col-sm-10">
                                            <select name="type" id="type" class="form-control">
                                                <option value="day_ticket">@lang('products.day_ticket')</option>
                                                <option value="person_ticket">@lang('products.person_ticket')</option>
                                                <option value="day_ticket_twenty_four_hours">@lang('products.day_ticket_twenty_four')</option>
                                                <option value="year_ticket_person">@lang('products.yearly_person')</option>
                                                <option value="year_ticket_vehicle">@lang('products.yearly_parking')</option>
                                            </select>
                                            {!! $errors->first('products', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->first('price', 'has-error') }}">
                                        <label for="price" class="col-sm-2 control-label">@lang('products.price') *</label>
                                        <div class="col-sm-10">
                                            <input id="price" name="price" type="text"
                                                placeholder="@lang('products.price')" class="form-control required" />

                                            {!! $errors->first('price', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="no_of_times">
                                        <div class="form-group {{ $errors->first('ticket_count', 'has-error') }} mt-3">
                                            <label for="ticket_count" class="col-sm-2 control-label">@lang('products.count_time')
                                            </label>
                                            <div class="col-sm-9 mt-3">
                                                <input type="checkbox" name="ticket_count" id="ticket_count">
                                            </div>
                                        </div>
                                        {{-- <div class="form-group {{ $errors->first('no_of_time', 'has-error') }} hidden"
                                            id="num_of_time">
                                            <label for="no_of_time" class="col-sm-2 control-label"> </label>
                                            <div class="col-sm-10">
                                                <input id="no_of_time" name="no_of_time" type="number"
                                                    placeholder="@lang('products.no_of_time')" class="form-control required" />

                                                {!! $errors->first('no_of_time', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div> --}}
                                    </div>
                                    {{-- <div id="no_of_vehicles">
                                        <div class="form-group {{ $errors->first('vehicle_count', 'has-error') }} mt-3">
                                            <label for="vehicle_count" class="col-sm-2 control-label">@lang('products.vehicle_allowed')
                                            </label>
                                            <div class="col-sm-9 mt-3">
                                                <input type="checkbox" name="vehicle_count" id="vehicle_count">
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->first('no_of_vehicle', 'has-error') }} hidden"
                                            id="nums_of_vehicle">
                                            <label for="no_of_vehicle" class="col-sm-2 control-label "></label>
                                            <div class="col-sm-10">
                                                <input id="no_of_vehicle" name="no_of_vehicle" type="number"
                                                    placeholder="@lang('products.no_of_vehicle')" class="form-control required" />

                                                {!! $errors->first('no_of_vehicle', '<span class="help-block">:message</span>') !!}
                                            </div>
                                        </div>
                                    </div> --}}

                                </div>
                                <ul class="pager wizard">
                                    <li class="next finish"><a href="javascript:;">@lang('products.submit')</a></li>
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
    <script src="{{ asset('plugins/components/jqueryui/jquery-ui.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js">
    </script>
    <script src="{{ asset('plugins/components/toast-master/js/jquery.toast.js') }}"></script>
    <script src="{{ asset('/js/create-products.js') }}"></script>

    <script>
        @if (\Session::has('message'))
            $.toast({
                heading: '{{ session()->get('heading') }}',
                position: 'top-center',
                text: '{{ session()->get('message') }}',
                loaderBg: '#ff6849',
                icon: '{{ session()->get('icon') }}',
                hideAfter: 5000,
                stack: 6
            });
        @endif
    </script>
@endpush
