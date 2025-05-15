@extends('layouts.master')
@push('css')
<link href="{{asset('plugins/components/owl.carousel/owl.carousel.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('plugins/components/owl.carousel/owl.theme.default.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="row " style="display: flex">
        <div class="white-box col-md-12">
            <div class="col-md-4 col-sm-12 " >
                <div class="profile-widget">
                    <div class="profile-img">
                        @if(!empty($bookingInfo->customer->profile) && $bookingInfo->customer->profile->pic != NULL)
                        <img src="{{asset('/uploads/users/default_user.png')}}" 
                             alt="user-img"
                             class="img-circle w-127">
                        @else
                        <h1 style="margin:25px 0;">
                            @lang('dashboard.paid_person')
                        </h1>
                        @endif
                        <!--<img src="/plugins/images/users/jeffery.jpg" alt="user-img" class="img-circle">-->
                        <p class="m-t-10 m-b-5"><a href="javascript:void(0);" class="profile-text font-22 font-semibold">
                                @if(!empty($bookingInfo->customer_vehicle_info->name))
                                {{$bookingInfo->customer_vehicle_info->name}}
                                @elseif(!empty($bookingInfo->vehicle_num))
                                {{$bookingInfo->vehicle_num}}
                                @endif
                            </a>
                        </p>
                        <span class="font-16">
                            @if(!empty($bookingInfo->customer_vehicle_info->num_plate))
                            {{$bookingInfo->customer_vehicle_info->num_plate}}
                            @endif
                        </span>
                    </div>
                    <div class="profile-info">
                        <div class="col-xs-12 col-md-12">
                            <h1 class="text-primary">{{$userTotalBookings}} </h1>
                            <span class="font-16">@lang('vehicle.t_bookings')</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-sm-12 ">
                <div class="panel panel-default">
                    <div class="panel-heading h1 text-center">@lang('vehicle.p_bookings')</div>
                    <div class="panel-wrapper collapse in">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>@lang('vehicle.user_name')</th>
                                        <th>@lang('vehicle.contact_details')</th>
                                        <th>@lang('vehicle.check_in')</th>
                                        <th>@lang('vehicle.check_out')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookingDetails as $bookings)
                                    <tr>
                                        <td>{{$bookings->first_name.' '.$bookings->last_name}}</td>
                                        <td>
                                            <?php
                                            if ($bookings->email != NULL && $bookings->phone_number != NULL) {
                                                echo $bookings->email . '(' . $bookings->phone_number . ')';
                                            } else if ($bookings->email == NULL && $bookings->phone_number != NULL) {
                                                echo $bookings->phone_number;
                                            } else if ($bookings->email != NULL && $bookings->phone_number == NULL) {
                                                echo $bookings->email;
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                <i class="fa fa-clock-o"></i> {{date('d/m/Y H:i', strtotime($bookings->checkin_time))}}
                                            </span> 
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                <i class="fa fa-clock-o"></i> {{$bookings->checkout_time != Null ? date('d/m/Y H:i', strtotime($bookings->checkout_time)) : '--'}}
                                            </span> 
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
    
    <div class="row " >

        <div class="col-md-12 col-sm-12 white-box">
            <div class="">
                <div class="profile-widget">
                    <div class="panel-heading">@lang('vehicle.transaction_imgs')</div>
                    <div class="panel-wrapper p-b-10 collapse in">
                        <div id="owl-demo2" class="owl-carousel owl-theme">
                            @foreach($bookingDetails as $bookings)
                            @if(isset($bookings->attendants->attendant_transactions))
                            @foreach($bookings->attendants->attendant_transactions as $bookingTransaction)
                            @foreach($bookingTransaction->transaction_images as $transactionImage)
                            <div class="item">
                                <img 
                                    src="{{$transactionImage->image_path}}" 
                                    alt="Transaction Image">
                            </div>
                            @endforeach
                            @endforeach
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box">
                <h3 class="box-title m-b-0">@lang('vehicle.transactions') </h3>
                <div class="table-responsive">
                    <table id="transaction_table" class="table">
                        <thead>
                            <tr>
                                <th>@lang('vehicle.attendant_name')</th>
                                <!--<th>@lang('vehicle.v_number')</th>-->
                                <th>@lang('vehicle.check_in')</th>
                                <th>@lang('vehicle.check_out')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookingDetails as $bookings)
                            @if(isset($bookings->attendants->attendant_transactions))
                            @foreach($bookings->attendants->attendant_transactions as $bookingTransaction)
                            <tr>
                                <td>{{$bookings->first_name.' '.$bookings->last_name}}</td>
                                <!--<td>{{$bookings->vehicle_num}}</td>-->
                                <td>
                                    <span class="text-muted">
                                        <i class="fa fa-clock-o"></i> {{date('d/m/Y H:i', strtotime($bookingTransaction->check_in))}}
                                    </span> 
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <i class="fa fa-clock-o"></i> {{$bookingTransaction->check_out != Null ? date('d/m/Y H:i', strtotime($bookingTransaction->check_out)) : '--'}}
                                    </span> 
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
@push('js')
<script src="{{asset('plugins/components/owl.carousel/owl.carousel.min.js')}}"></script>
<script src="{{asset('plugins/components/owl.carousel/owl.custom.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
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
    $('#transaction_table').DataTable({
        dom: 'Bfrtip',
        columnDefs: [
            {type: 'date-euro', targets: 1},
            {type: 'date-euro', targets: 2}
        ],
        "pageLength": 25,
        "order": [[1, "desc"]]
    });
}); 
</script>
@endpush
