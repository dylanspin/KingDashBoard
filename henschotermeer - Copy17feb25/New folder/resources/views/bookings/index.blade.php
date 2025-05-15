@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/owl.carousel/owl.carousel.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('plugins/components/owl.carousel/owl.theme.default.css')}}" rel="stylesheet" type="text/css"/>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row " style="display: flex">

        <div class="col-md-4 col-sm-12 " >
            <div class="white-box">
                <div class="profile-widget">
                    <div class="profile-img">
                        @php
                        if(isset($bookingDetails->customer->profile) && $bookingDetails->customer->profile->pic != null){
                            $imgPath =  $bookingDetails->customer->profile->pic;
                        }
                        else {
                            $imgPath =  'default_user.png';
                        }
                        @endphp
                        <img src="{{asset('/uploads/users/'.$imgPath)}}"
                             alt="user-img" 
                             class="img img-responsive" 
                             style="max-width:58%;margin:0 auto;">
                        <!--<img src="/plugins/images/users/jeffery.jpg" alt="user-img" class="img-circle">-->
                        <p class="m-t-10 m-b-5"><a href="javascript:void(0);" class="profile-text font-22 font-semibold">{{isset($bookingDetails->customer->profile) ? $bookingDetails->customer->profile->first_name.' '.$bookingDetails->customer->profile->last_name : ''}}</a></p>
                        <span class="font-16">{{isset($bookingDetails->customer->profile) ? $bookingDetails->customer->profile->country.', '.$bookingDetails->customer->profile->state : ''}}</span>
                    </div>
                    <div class="profile-info">
                        <div class="col-xs-6 col-md-6 b-r">
                            <h1 class="text-primary">{{$userTotalBookings}} </h1>
                            <span class="font-16">@lang('booking.t_bookings')</span>
                        </div>
                        <div class="col-xs-6 col-md-6">
                            <h1 class="text-primary">{{$totalAmount}} &euro; </h1>
                            <span class="font-16">@lang('booking.t_payments')</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-sm-12 white-box">
            <div class="panel panel-default">
                <div class="panel-heading h1 text-center">@lang('booking.booking_details')</div>
                <div class="panel-wrapper collapse in">

                    <div class="col-md-12 p-30">
                        @php
                        
                        @endphp

                        <div class="col-md-6">
                            <h4>@lang('booking.user_name') : {{isset($bookingDetails->first_name) ? $bookingDetails->first_name : ''}} {{isset($bookingDetails->last_name) ? $bookingDetails->last_name : ''}}</h4>
                        </div>

                        <div class="col-md-6">
                            <h4>@lang('booking.email') : {{isset($bookingDetails->email) ? $bookingDetails->email : $bookingDetails->first_name ? $bookingDetails->first_name : ''}}</h4>
                        </div>

                        <div class="col-md-6">
                            <h4>@lang('booking.vehicle_no') : {{isset($bookingDetails->vehicle_num) ? $bookingDetails->vehicle_num : ''}}</h4>
                        </div>

                        <div class="col-md-6">
                            <h4>@lang('booking.booking_payment') : {{isset($bookingDetails->booking_payments) ? $bookingDetails->booking_payments->amount : ''}} &euro;</h4>
                        </div>

                        <div class="col-md-6">
                            <h4>@lang('booking.check_in') : <span class="text-muted"><i class="fa fa-clock-o"></i> {{date('d/m/Y H:i', strtotime(isset($bookingDetails->checkin_time) ? $bookingDetails->checkin_time : ''))}}</span></h4>
                        </div>

                        <div class="col-md-6">
                            <h4>@lang('booking.check_out') : <span class="text-muted"><i class="fa fa-clock-o"></i> {{date('d/m/Y H:i', strtotime(isset($bookingDetails->checkout_time) ? $bookingDetails->checkout_time : ''))}}</span></h4>
                        </div>

                    </div>
                </div>
            </div>
        </div> 
    </div>
    @if(isset($bookingDetails->attendants->attendant_transactions))
    <div class="row " >

        <div class="col-md-12 col-sm-12 white-box">
            <div class="">
                <div class="profile-widget">
                    <div class="panel-heading">@lang('booking.trans_img')</div>
                    <div class="panel-wrapper p-b-10 collapse in">
                        <div id="owl-demo2" class="owl-carousel owl-theme">
                            @foreach($bookingDetails->attendants->attendant_transactions as $bookingTransaction)
                            @foreach($bookingTransaction->transaction_images as $transactionImage)
                            <div class="item">
                                <img 
                                    src="{{$transactionImage->image_path}}" 
                                    alt="Transaction Image">
                            </div>
                            @endforeach
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
                <h3 class="box-title m-b-0">@lang('booking.booking_transactions') </h3>
                <p class="text-muted m-b-20 hidden">Create responsive tables by wrapping any <code>.table</code> in <code>.table-responsive </code></p>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>@lang('booking.attendant_name')</th>
                                <th>@lang('booking.vehicle_no')</th>
                                <th>@lang('booking.check_in')</th>
                                <th>@lang('booking.check_out')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookingDetails->attendants->attendant_transactions as $bookingTransaction)
                            <tr>
                                <td>{{isset($bookingDetails->first_name) ? $bookingDetails->first_name : ''}} {{isset($bookingDetails->last_name) ? $bookingDetails->last_name : ''}}</td>
                                <td>{{isset($bookingDetails->vehicle_num) ? $bookingDetails->vehicle_num : ''}}</td>
                                <td>
                                    <span class="text-muted">
                                        <i class="fa fa-clock-o"></i> {{date('d/m/Y H:i', strtotime(isset($bookingTransaction->check_in) ? $bookingTransaction->check_in : ''))}}
                                    </span> 
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <i class="fa fa-clock-o"></i> {{$bookingTransaction->check_out != Null ? date('d/m/Y H:i', strtotime(isset($bookingTransaction->check_out) ? $bookingTransaction->check_out : '')) : '-----'}}
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
    @endif

</div>
@endsection
@push('js')
<script src="{{asset('plugins/components/owl.carousel/owl.carousel.min.js')}}"></script>
<script src="{{asset('plugins/components/owl.carousel/owl.custom.js')}}"></script>
@endpush
