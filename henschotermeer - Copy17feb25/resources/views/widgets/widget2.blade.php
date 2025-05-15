@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<!--<link href="{{asset('plugins/components/footable/css/footable.bootstrap.css')}}" rel="stylesheet" type="text/css" />-->
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
                            href="#expectedBookings" 
                            aria-controls="expectedBookings" 
                            role="tab"                   
                            data-toggle="tab" 
                            aria-expanded="false"> @lang('booking.expected_bookings')</a>
                    </li>
                    <li 
                        role="presentation" 
                        class="">
                        <a 
                            href="#onLocation" 
                            aria-controls="onLocation" 
                            role="tab"                   
                            data-toggle="tab" 
                            aria-expanded="false"> @lang('booking.on_location')</a>
                    </li>
                    <li 
                        role="presentation" 
                        class="">
                        <a 
                            href="#left" 
                            aria-controls="left" 
                            role="tab"                   
                            data-toggle="tab" 
                            aria-expanded="false"> @lang('booking.left')</a>
                    </li>
                </ul>
                <div class="tab-content" style="margin-top:10px;">
                    <div role="tabpanel" class="tab-pane fade active in" id="expectedBookings">
                        <!--<h3 class="box-title pull-left">@lang('booking.group_bookings')</h3>-->
                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="expectedListingDataTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>@lang('at_location.name')</th>
                                                <th>@lang('at_location.contact_details')</th>
                                                <th>@lang('at_location.vehicle')</th>
                                                <th>@lang('at_location.type')</th>
                                                <th>@lang('at_location.check_in')</th>
                                                <th>@lang('at_location.check_out')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($expected_bookings as $key => $bookings)
                                            <tr>
                                                <td>
                                                    <?php
                                                    if($bookings->first_name != NULL && $bookings->last_name != NULL){
                                                        echo $bookings->first_name.' '.$bookings->last_name;
                                                    }
                                                    else if($bookings->first_name != NULL && $bookings->last_name == NULL){
                                                        echo $bookings->first_name;
                                                    }
                                                    else if($bookings->first_name == NULL && $bookings->last_name != NULL){
                                                        echo $bookings->last_name;
                                                    }
                                                    else{
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if($bookings->email != NULL && $bookings->phone_number != NULL){
                                                        echo $bookings->email.'('.$bookings->phone_number.')';
                                                    }
                                                    else if($bookings->email == NULL && $bookings->phone_number != NULL){
                                                        echo $bookings->phone_number;
                                                    }
                                                    else if($bookings->email != NULL && $bookings->phone_number == NULL){
                                                        echo $bookings->email;
                                                    }
                                                    else{
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>{{$bookings->vehicle_num ? $bookings->vehicle_num : 'N/A'}}</td>
                                                <td>
                                                    <?php
                                                    if ($bookings->type == 0) {
                                                        echo 'N/A';
                                                    } else if ($bookings->type == 1) {
                                                        echo 'Send Ticket';
                                                    } else if ($bookings->type == 2) {
                                                        echo 'White List';
                                                    } else if ($bookings->type == 3) {
                                                        echo 'User List';
                                                    } else if ($bookings->type == 4) {
                                                        echo 'Customer';
                                                    } else if ($bookings->type == 5) {
                                                        echo 'BarCode';
                                                    } else if ($bookings->type == 6 || $bookings->type == 7) {
                                                        echo 'Tommy Reservation';
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>{{$bookings->checkin_time != NULL ? date('d/m/Y H:i', strtotime($bookings->checkin_time)) : '--'}}</td>
                                                <td>{{$bookings->checkout_time != NULL ? date('d/m/Y H:i', strtotime($bookings->checkout_time)) : '--'}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="onLocation">
                        <!--<h3 class="box-title pull-left">@lang('booking.expected_bookings')</h3>-->
                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="onLocationListingDataTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>@lang('at_location.name')</th>
                                                <th>@lang('at_location.contact_details')</th>
                                                <th>@lang('at_location.vehicle')</th>
                                                <th>@lang('at_location.type')</th>
                                                <th>@lang('at_location.check_in')</th>
                                                <th>@lang('at_location.check_out')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($on_location as $key => $bookings)
                                            <tr>
                                                <td>
                                                    <?php
                                                    if($bookings->first_name != NULL && $bookings->last_name != NULL){
                                                        echo $bookings->first_name.' '.$bookings->last_name;
                                                    }
                                                    else if($bookings->first_name != NULL && $bookings->last_name == NULL){
                                                        echo $bookings->first_name;
                                                    }
                                                    else if($bookings->first_name == NULL && $bookings->last_name != NULL){
                                                        echo $bookings->last_name;
                                                    }
                                                    else{
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if($bookings->email != NULL && $bookings->phone_number != NULL){
                                                        echo $bookings->email.'('.$bookings->phone_number.')';
                                                    }
                                                    else if($bookings->email == NULL && $bookings->phone_number != NULL){
                                                        echo $bookings->phone_number;
                                                    }
                                                    else if($bookings->email != NULL && $bookings->phone_number == NULL){
                                                        echo $bookings->email;
                                                    }
                                                    else{
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>{{$bookings->vehicle_num ? $bookings->vehicle_num : 'N/A'}}</td>
                                                <td>
                                                    <?php
                                                    if ($bookings->type == 0) {
                                                        echo 'N/A';
                                                    } else if ($bookings->type == 1) {
                                                        echo 'Send Ticket';
                                                    } else if ($bookings->type == 2) {
                                                        echo 'White List';
                                                    } else if ($bookings->type == 3) {
                                                        echo 'User List';
                                                    } else if ($bookings->type == 4) {
                                                        echo 'Customer';
                                                    } else if ($bookings->type == 5) {
                                                        echo 'BarCode';
                                                    } else if ($bookings->type == 6 || $bookings->type == 7) {
                                                        echo 'Tommy Reservation';
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>{{$bookings->checkin_time != NULL ? date('d/m/Y H:i', strtotime($bookings->checkin_time)) : '--'}}</td>
                                                <td>{{$bookings->checkout_time != NULL ? date('d/m/Y H:i', strtotime($bookings->checkout_time)) : '--'}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="left">
                        <!--<h3 class="box-title pull-left">@lang('booking.unexpected_bookings')</h3>-->
                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="leftListingDataTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>@lang('at_location.name')</th>
                                                <th>@lang('at_location.contact_details')</th>
                                                <th>@lang('at_location.vehicle')</th>
                                                <th>@lang('at_location.type')</th>
                                                <th>@lang('at_location.check_in')</th>
                                                <th>@lang('at_location.check_out')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($arrivals as $key => $bookings)
                                            <tr>
                                                <td>
                                                    <?php
                                                    if($bookings->first_name != NULL && $bookings->last_name != NULL){
                                                        echo $bookings->first_name.' '.$bookings->last_name;
                                                    }
                                                    else if($bookings->first_name != NULL && $bookings->last_name == NULL){
                                                        echo $bookings->first_name;
                                                    }
                                                    else if($bookings->first_name == NULL && $bookings->last_name != NULL){
                                                        echo $bookings->last_name;
                                                    }
                                                    else{
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if($bookings->email != NULL && $bookings->phone_number != NULL){
                                                        echo $bookings->email.'('.$bookings->phone_number.')';
                                                    }
                                                    else if($bookings->email == NULL && $bookings->phone_number != NULL){
                                                        echo $bookings->phone_number;
                                                    }
                                                    else if($bookings->email != NULL && $bookings->phone_number == NULL){
                                                        echo $bookings->email;
                                                    }
                                                    else{
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>{{$bookings->vehicle_num ? $bookings->vehicle_num : 'N/A'}}</td>
                                                <td>
                                                    <?php
                                                    if ($bookings->type == 0) {
                                                        echo 'N/A';
                                                    } else if ($bookings->type == 1) {
                                                        echo 'Send Ticket';
                                                    } else if ($bookings->type == 2) {
                                                        echo 'White List';
                                                    } else if ($bookings->type == 3) {
                                                        echo 'User List';
                                                    } else if ($bookings->type == 4) {
                                                        echo 'Customer';
                                                    } else if ($bookings->type == 5) {
                                                        echo 'BarCode';
                                                    } else if ($bookings->type == 6 || $bookings->type == 7) {
                                                        echo 'Tommy Reservation';
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td>{{$bookings->checkin_time != NULL ? date('d/m/Y H:i', strtotime($bookings->checkin_time)) : '--'}}</td>
                                                <td>{{$bookings->checkout_time != NULL ? date('d/m/Y H:i', strtotime($bookings->checkout_time)) : '--'}}</td>
                                            </tr>
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
<!--<script src="{{ asset('plugins/components/footable/js/footable.min.js') }}"></script>-->
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
    $('#expectedListingDataTable').DataTable({
        columnDefs: [
            {type: 'date-euro', targets: 4},
            {type: 'date-euro', targets: 5}
        ],
        "pageLength": 25,
        "order": [[4, "desc"]]
    });
    $('#onLocationListingDataTable').DataTable({
        columnDefs: [
            {type: 'date-euro', targets: 4},
            {type: 'date-euro', targets: 5}
        ],
        "pageLength": 25,
        "order": [[4, "desc"]]
    });
    $('#leftListingDataTable').DataTable({
        columnDefs: [
            {type: 'date-euro', targets: 4},
            {type: 'date-euro', targets: 5}
        ],
        "pageLength": 25,
        "order": [[4, "desc"]]
    });
});
</script>
<!--<script>
$('.foo-table').footable();
</script>-->
@endpush