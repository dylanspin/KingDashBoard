@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
<link href="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.css')}}" rel="stylesheet">
<style>
    .color-star-fill {
        color: #fde16d;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- .row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('payments.payments')</h3>
                <p class="pull-right">
                    @lang('payments.today_amount') {{number_format($todayTotalAmount, 2, ',', '.')}} &euro;
                    <br>
                    @lang('payments.today_bookings') {{$todayBookingPayments->count()}}
                    <br>
                    @lang('payments.t_amount') {{number_format($totalAmount, 2, ',', '.')}} &euro;
                    <br>
                    @lang('payments.t_bookings') {{$totalBookings}}
                </p>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <form method="post" action="{{url('/location/payments')}}" class="col-md-12 custom-search-form">
                        <div class="col-md-6">
                            
                        </div>
                        <div class="col-md-6 text-left">
                            @csrf
                            <div class="form-group col-md-6">
                                <select class="form-control" name="search_type">
                                    <option value="" {{ $search_type == '' ? 'selected' :  ''}}>Search In</option>
                                    <option value="first_name" {{ $search_type == 'first_name' ? 'selected' :  ''}}>Name</option>
                                    <option value="vehicle" {{ $search_type == 'vehicle' ? 'selected' :  ''}}>Vehicle</option>
                                    <option value="email" {{ $search_type == 'email' ? 'selected' :  ''}}>Email</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <input type="text" name="search_val" value="{{$search_val}}" class="form-control" placeholder="Search">
                            </div>
                        </div>
                        <div class="col-md-12 text-left">
                            <div class="form-group col-md-2">
                                <select class="form-control" name="filter_booking_online">
                                    <option value="all" {{ $filter_booking_online == 'all' ? 'selected' :  ''}}>All</option>
                                    <option value="0" {{ $filter_booking_online == '0' ? 'selected' :  ''}}>Local</option>
                                    <option value="1" {{ $filter_booking_online == '1' ? 'selected' :  ''}}>Online</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <select class="form-control" name="filter_booking_type">
                                    <option value="all" {{ $filter_booking_type == 'all' ? 'selected' :  ''}}>All</option>
                                    <option value="person" {{ $filter_booking_type == 'person' ? 'selected' :  ''}}>Person</option>
                                    <option value="parking" {{ $filter_booking_type == 'parking' ? 'selected' :  ''}}>Parking</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <select class="form-control" name="filter_ticket_type">
                                    <option value="all" {{ $filter_ticket_type == 'all' ? 'selected' :  ''}}>All</option>
                                    <option value="day" {{ $filter_ticket_type == 'day' ? 'selected' :  ''}}>Day</option>
                                    <option value="seasonal" {{ $filter_ticket_type == 'seasonal' ? 'selected' :  ''}}>Seasonal</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <input 
                                    id="filter_valid_dates" 
                                    name="filter_valid_dates" 
                                    placeholder="@lang('promo.valid_dates')" 
                                    type="text" 
                                    class="form-control filter_valid_dates input-daterange-datepicker" 
                                    value="{!! $filter_valid_dates !!}"/>
                            </div>
                            <div class="form-group col-md-3">
                                <input type="submit" name="search_btn" class="btn btn-primary btn-sm" value="Search">
                                <a href="{{url('/location/payments')}}" class="btn btn-danger btn-sm">Reset</a>
                                <input type="submit" name="export_btn" class="btn btn-primary btn-sm" value="Export to Excel">
                            </div>
                        </div>
                    </form>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <!--<th>@lang('payments.no')</th>-->
                                        <th>@sortablelink('first_name',trans('payments.name'))</th>
                                        <th>@sortablelink('vehicle_num',trans('payments.vehicle'))</th>
                                        <th>@sortablelink('amount',trans('payments.amount'))</th>
                                        <th>@sortablelink('check_in',trans('payments.arrival'))</th>
                                        <th>@sortablelink('check_out',trans('payments.departure'))</th>
                                        <th>@lang('payments.type')</th>
                                        <th>@sortablelink('check_out',trans('payments.purchase_time'))</th>
                                        <th>@sortablelink('is_online',trans('payments.status'))</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookingPayments as $key=>$bookingPayment)
                                    @php
                                    if($bookingPayment->type == 0){
                                        $type = 'N/A';
                                    }else if($bookingPayment->type == 1){
                                        $type = 'Send Ticket';
                                    }else if($bookingPayment->type == 2){
                                        $type = 'White List';
                                    }else if($bookingPayment->type == 3){
                                        $type = 'User List';
                                    }else if($bookingPayment->type == 4){
                                        if(date('d/m/Y H:i', strtotime($bookingPayment->check_out)) == date('31/12/Y 23:59')){
                                            $type = 'Annual parking subscription';
                                        }else{
                                            $type = 'Day ticket parking';
                                        }
                                    }else if($bookingPayment->type == 5){
                                        $type = 'BarCode';
                                    }else if($bookingPayment->type == 6 || $bookingPayment->type == 7){
                                        if(date('d/m/Y H:i', strtotime($bookingPayment->check_out)) == date('31/12/Y 23:59')){
                                            $type = 'Seasonal subscription person';
                                        }else{
                                            $type = 'Day ticket person';
                                        }
                                    }else{
                                        $type = 'N/A';
                                    }
                                    @endphp
                                    <tr>
                                        <!--<td>{{$key+1}}</td>-->
                                        <td>{{!empty($bookingPayment->first_name) ? $bookingPayment->first_name : $bookingPayment->email}}</td>
                                        <td>{{$bookingPayment->vehicle_num ? $bookingPayment->vehicle_num : 'N/A'}}</td>
                                        <td>{{number_format($bookingPayment->amount, 2, ',', '.')}} &euro;</td>
                                        <td>{{date('d/m/Y H:i', strtotime($bookingPayment->check_in))}}</td>
                                        <td>{{date('d/m/Y H:i', strtotime($bookingPayment->check_out))}}</td>
                                        <td>{{$type}}</td>
                                        <td>{{date('d/m/Y H:i', strtotime($bookingPayment->created_at))}}</td>
                                        <td>
                                            @if($bookingPayment->is_online)
                                            @lang('payments.online_payment')
                                            @else
                                            @lang('payments.payment_terminal')
                                            @endif
                                        </td>
                                    </tr>

                                    @endforeach
                                    <tr>
                                        <td colspan="7" class="text-right">
                                            <br>
                                            @lang('payments.t_amount') {{number_format($currentPageTotalAmount, 2, ',', '.')}} &euro;
                                            <br>
                                            @lang('payments.t_bookings') {{$currentPageTotalCount}}
                                            <br>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            {!! $bookingPayments->appends(\Request::except('page'))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.right-sidebar')
</div>
@endsection

@push('js')
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
<script src="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
$(function () {
    $('.input-daterange-datepicker').daterangepicker({
        autoUpdateInput: false,
        buttonClasses: ['btn', 'btn-xs'],
        applyClass: 'btn-danger',
        cancelClass: 'btn-inverse'
    });
    $('.input-daterange-datepicker').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY')+' - '+picker.endDate.format('MM/DD/YYYY'));
    });
    $('.input-daterange-datepicker').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
@endpush