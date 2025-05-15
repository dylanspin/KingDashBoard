@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/footable/css/footable.bootstrap.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
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
                <ul class="nav customtab2 nav-tabs" role="tablist">
                    <li 
                        role="presentation" 
                        class="active">
                        <a 
                            href="#allTransactions" 
                            aria-controls="allTransactions" 
                            role="tab"                   
                            data-toggle="tab" 
                            aria-expanded="false"> @lang('payments.all_transactions')</a>
                    </li>
                    <li 
                        role="presentation" 
                        class="">
                        <a 
                            href="#personPaymentTransactions" 
                            aria-controls="personPaymentTransactions" 
                            role="tab"                   
                            data-toggle="tab" 
                            aria-expanded="false"> @lang('payments.person_payment_transactions')</a>
                    </li>
                    <li 
                        role="presentation" 
                        class="">
                        <a 
                            href="#vehiclePaymentTransactions" 
                            aria-controls="vehiclePaymentTransactions" 
                            role="tab"                   
                            data-toggle="tab" 
                            aria-expanded="false"> @lang('payments.vehicle_payment_transactions')</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade active in" id="allTransactions">
                        <h3 class="box-title pull-left">@lang('payments.payments')</h3>
                        <p class="pull-right">
                            @lang('payments.t_amount') {{number_format($totalAmount, 2, ',', '.')}} &euro;
                            <br>
                            @lang('payments.t_bookings') {{$bookingPayments->count()}}
                        </p>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="listingDataTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <!--<th>@lang('payments.no')</th>-->
                                                <th>@lang('payments.name')</th>
                                                <th>@lang('payments.vehicle')</th>
                                                <!--<th>@lang('payments.amount')</th>-->
                                                <th>@lang('payments.arrival')</th>
                                                <th>@lang('payments.departure')</th>
                                                <th>@lang('payments.type')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bookingPayments as $key=>$bookingPayment)
                                            @if($bookingPayment->booking)
                                            @php
                                            if($bookingPayment->booking->type == 0){
                                            $type = 'N/A';
                                            }else if($bookingPayment->booking->type == 1){
                                            $type = 'Send Ticket';
                                            }else if($bookingPayment->booking->type == 2){
                                            $type = 'White List';
                                            }else if($bookingPayment->booking->type == 3){
                                            $type = 'User List';
                                            }else if($bookingPayment->booking->type == 4){
                                            $type = 'Customer';
                                            }else if($bookingPayment->booking->type == 5){
                                            $type = 'BarCode';
                                            }else if($bookingPayment->booking->type == 6 || $bookingPayment->booking->type == 7){
                                            $type = 'Tommy Reservation';
                                            }else{
                                            $type = 'N/A';
                                            }
                                            @endphp
                                            <tr>
                                                <!--<td>{{$key+1}}</td>-->
                                                <td>{{$bookingPayment->booking->first_name.' '.$bookingPayment->booking->last_name}}</td>
                                                <td>{{$bookingPayment->booking->vehicle_num}}</td>
                                                <!--<td>{{$bookingPayment->amount}} &euro;</td>-->
                                                <td>{{date('d/m/Y H:i', strtotime($bookingPayment->checkin_time))}}</td>
                                                <td>{{date('d/m/Y H:i', strtotime($bookingPayment->checkout_time))}}</td>
                                                <td>{{$type}}</td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="personPaymentTransactions">
                        <h3 class="box-title pull-left">@lang('payments.person_payment_transactions')</h3>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table 
                                        class="table foo-table" 
                                        data-sorting="true" 
                                        data-filtering="true" 
                                        data-toggle-column="first"
                                        data-paging="true" 
                                        data-paging-size="25">
                                        <thead>
                                            <tr>
                                                <th data-sortable="false"></th>
                                                <th data-sorted="true" data-direction="DESC">@lang('payments.device')</th>
                                                <th>@lang('payments.quantity')</th>
                                                <th>@lang('payments.amount')</th>
                                                <th data-breakpoints="all" data-sortable="false" style="w-200">@lang('payments.transaction_details')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transactionPaymentPersons as $key=>$data)
                                            @if($data->location_devices)
                                            <tr>
                                                <td></td>
                                                <td>{{$data->location_devices->device_name != NULL ? $data->location_devices->device_name : 'N/A'}}</td>
                                                <td>{{$data->quantity != NULL ? $data->quantity : 'N/A'}}</td>
                                                <td>{{$data->amount == NULL ? number_format($data->quantity*$personTicket->price, 2, ',', '.') : number_format($data->amount, 2, ',', '.')}} &euro;</td>
                                                <td>
                                                    @if(!empty($data->e_general))
                                                    <textarea readonly="" rows="10" cols="70" style="border:none;">{{ $data->e_general }}</textarea>
                                                    @else
                                                    <textarea readonly="" rows="1" cols="70" style="border:none;">N/A</textarea>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="vehiclePaymentTransactions">
                        <h3 class="box-title pull-left">@lang('payments.vehicle_payment_transactions')</h3>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table 
                                        class="table foo-table" 
                                        data-sorting="true" 
                                        data-filtering="true" 
                                        data-toggle-column="first"
                                        data-paging="true" 
                                        data-paging-size="25">
                                        <thead>
                                            <tr>
                                                <th data-sortable="false"></th>
                                                <th>@lang('payments.customer')</th>
                                                <th>@lang('payments.device')</th>
                                                <th>@lang('payments.vehicle')</th>
                                                <th>@lang('payments.amount')</th>
                                                <th data-breakpoints="all" data-sortable="false" class="w-200">@lang('payments.transaction_details')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transactionPaymentVehicles as $key=>$data)
                                            @if($data->location_devices)
                                            <tr>
                                                <td></td>
                                                <td>{{isset($data->bookings->customer) && $data->bookings->customer->name != NULL ? $data->bookings->customer->name : 'N/A'}}</td>
                                                <td>{{$data->location_devices->device_name != NULL ? $data->location_devices->device_name : 'N/A'}}</td>
                                                <td>{{isset($data->bookings->customer_vehicle_info) && $data->bookings->customer_vehicle_info->num_plate != NULL ? $data->bookings->customer_vehicle_info->num_plate : 'N/A'}}</td>
                                                <td>{{$data->amount != NULL ? number_format($data->amount, 2, ',', '.') : 0,00}} &euro;</td>
                                                <td>
                                                    @if(!empty($data->e_general))
                                                    <textarea readonly="" rows="10" cols="70" style="border:none;">{{ $data->e_general }}</textarea>
                                                    @else
                                                    <textarea readonly="" rows="1" cols="70" style="border:none;">N/A</textarea>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
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
<script src="{{ asset('plugins/components/footable/js/footable.min.js') }}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
$(function () {
    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
        "date-euro-pre": function (a) {
        var x;
        if ($.trim(a) !== '') {
            var frDatea = $.trim(a).split(' ');
            var frTimea = (undefined != frDatea[1]) ? frDatea[1].split(':') : [00, 00, 00];
            var frDatea2 = frDatea[0].split('/');
            x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + ((undefined != frTimea[2]) ? frTimea[2] : 0)) * 1;
        } 
        else {
            x = Infinity;
        }
        return x;
        },
        "date-euro-asc": function (a, b) {
            return a - b;
        },
        "date-euro-desc": function (a, b) {
            return b - a;
        }
    });
    $('#listingDataTable').DataTable({
        columnDefs: [
            {type: 'date-euro', targets: 2},
            {type: 'date-euro', targets: 3}
        ],
        "pageLength": 25,
        "order": [[2, "desc"]]
    });
});
</script>
<script>
$('.foo-table').footable();
</script>
@endpush