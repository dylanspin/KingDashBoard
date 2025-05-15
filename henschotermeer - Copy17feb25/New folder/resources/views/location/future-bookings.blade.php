@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css" />
<style>
    .color-star-fill {
        color: #fde16d;
    }
    .overlay_increase_stops{
        position: absolute;
        top:0px;
        left:0px;
        bottom: 0px;
        right: 0px;
        z-index: 1;
    }
    .overlay_increase_stops .fa{
        font-size: 50px;
        margin-left: 45%;
        margin-top: 16%;
        color: #456bb3;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- .row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('sidebar.future_bookings')</h3>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <form 
                        method="post" 
                        action="{{url('/location/future/bookings')}}" 
                        class="col-md-12 custom-search-form">
                        @csrf
                        <div class="col-md-12 text-left">
                            <div class="form-group col-md-4">
                                <select class="form-control" name="filter_booking_type">
                                    <option value="all" {{ $filter_booking_type == 'all' ? 'selected' :  ''}}>All</option>
                                    <option value="person" {{ $filter_booking_type == 'person' ? 'selected' :  ''}}>@lang('booking.person')</option>
                                    <option value="parking" {{ $filter_booking_type == 'parking' ? 'selected' :  ''}}>@lang('booking.parking')</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <input 
                                    id="filter_valid_dates" 
                                    name="filter_valid_dates" 
                                    placeholder="@lang('promo.posting_date')" 
                                    type="text" 
                                    autocomplete="off"
                                    class="form-control filter_valid_dates valid-dates-datepicker" 
                                    value="{!! $filter_valid_dates !!}"/>
                            </div>
                            <div class="form-group col-md-4">
                                <input type="submit" name="search_btn" class="btn btn-primary btn-sm" value="Search">
                                <a href="{{url('/location/future/bookings')}}" class="btn btn-danger btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>
                    <div class="col-md-12">
                        @if($filter_valid_dates != '')
                        <div class="col-md-12">
                            @if($filter_booking_type != 'parking')
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <div class="white-box bg-primary color-box color-white col-md-12 col-xs-12">
                                    <a href="#">
                                        <div class="col-md-9 col-xs-9">
                                            <h4 class="color-white">@lang('booking.person')</h4>
                                        </div>
                                        <div class="col-md-3 col-xs-3 text-right widget1_con">
                                            <h4 class="color-white">{{$totalPersonBookings}}</h4>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            @endif
                            @if($filter_booking_type != 'person')
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <div class="white-box bg-success color-box color-white col-md-12 col-xs-12">
                                    <a href="#">
                                        <div class="col-md-7 col-xs-7">
                                            <h4 class="color-white">@lang('booking.parking')</h4>
                                        </div>
                                        <div class="col-md-5 col-xs-5 text-right widget2_con">
                                            <h4 class="color-white">{{$totalBookings}}</h4>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.right-sidebar')
</div>
@endsection

@push('js')
<script src="{{ asset('plugins/components/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/components/moment/moment.js') }}"></script>
<script src="{{ asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/components/toast-master/js/jquery.toast.js') }}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
    $(function () {
        $('.valid-dates-datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            startDate: '0d',
            todayHighlight: true
        });
    });
</script>
@endpush