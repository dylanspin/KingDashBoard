@extends('layouts.master')
@push('css')
<link href="{{asset('plugins/components/owl.carousel/owl.carousel.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('plugins/components/owl.carousel/owl.theme.default.css')}}" rel="stylesheet" type="text/css"/>
@endpush

@section('content')
<div class="container-fluid">
    @if($type == 1)
    <div class="row " style="display: flex">
        <div class="col-md-12 col-sm-12 white-box">
            <div class="panel panel-default" style="box-shadow: none;">
                <div class="panel-heading h1 text-center ml-15">Transaction Details</div>
                <div class="panel-wrapper" style="display: flex;">
                    <div class="col-md-6 col-sm-6 text-center " >
                        <!--                        <div class="white-box">
                                                    <div class="profile-widget">-->
                        <div class="profile-img">
                            @if(!empty($data->image_path))
                            <img src="{{asset($data->image_path)}}" 
                                 alt="user-img"
                                 class=" w-400">

                            @else
                            <img src="{{asset('/plugins/images/icons/people_car.png')}}" 
                                 alt="user-img"
                                 class="img-circle w-400">
                            @endif
                        </div>
                        <!--                            </div>
                                                </div>-->
                    </div>
                    <div class="table-responsive col-md-6 text-center">
                        <div class="white-box mt-20">
                            <div class="profile-widget">


                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>{{$data->vehicle}}</th>
                                        </tr>
                                        <tr>
                                            <th>Time</th>
                                            <th>{{$data->time}}</th>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <th class="text-capitalize">{{$data->type}}</th>
                                        </tr>
                                        @if($data->manual_transaction_details)
                                        <tr>
                                            <th>User</th>
                                            <th class="text-capitalize">{{$data->manual_transaction_details->name}}</th>
                                        </tr>
                                        <tr>
                                            <th>Reason</th>
                                            <th class="text-capitalize">{{$data->manual_transaction_details->reason}}</th>
                                        </tr>
                                        @endif
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
    @elseif($type == 2)
    <div class="row " style="display: flex">

        <div class="col-md-12 col-sm-12 white-box">
            <div class="panel panel-default" style="box-shadow: none;">
                <div class="panel-heading h1 text-center">Transaction Details</div>
                <div class="panel-wrapper ">
                    <div class="col-md-12 col-sm-12 " >
                        <div class="white-box">
                            <div class="profile-widget">
                                <div class="profile-img">
                                    <img src="{{asset('/plugins/images/icons/people_car.png')}}" 
                                         alt="user-img"
                                         class="img-circle w-200">
                                    <p class="m-t-10 m-b-5">
                                        <a href="javascript:void(0);" class="profile-text font-22 font-semibold">
                                            {{$data->name}}
                                        </a>
                                    </p>
                                    <span class="font-16">
                                        {{$data->content}}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>{{$data->vehicle}}</th>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <th>{{$data->amount}}&euro;</th>
                                </tr>
                                <tr>
                                    <th>Time</th>
                                    <th>{{$data->time}}</th>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <th>{{$data->type}}</th>
                                </tr>
                                <tr>
                                    <th>E-Journal</th>
                                    <th>
                                        @if($data->e_journal != 'N/A')
                                        <textarea readonly="" rows="10" cols="50" style="border:none;">{{ $data->e_journal }}</textarea>
                                        @else
                                        N/A
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div> 
    </div>
    @elseif($type == 3)
    <div class="row " style="display: flex">

        <div class="col-md-12 col-sm-12 white-box">
            <div class="panel panel-default" style="box-shadow: none;">
                <div class="panel-heading h1 text-center">Transaction Details</div>
                <div class="panel-wrapper ">
                    <div class="col-md-12 col-sm-12 " >
                        <div class="white-box">
                            <div class="profile-widget">
                                <div class="profile-img">
                                    <img src="{{asset('/plugins/images/icons/User_List.png')}}" 
                                         alt="user-img"
                                         class=" w-200">
                                    <p class="m-t-10 m-b-5">
                                        <a href="javascript:void(0);" class="profile-text font-22 font-semibold">
                                            {{$data->content}}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Persons</th>
                                    <th>{{$data->quantity.' persons'}}</th>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <th>{{$data->amount}}&euro;</th>
                                </tr>
                                <tr>
                                    <th>Time</th>
                                    <th>{{$data->time}}</th>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <th>{{$data->type}}</th>
                                </tr>
                                <tr>
                                    <th>E-Journal</th>
                                    <th>
                                        @if($data->e_journal != 'N/A')
                                        <textarea readonly="" rows="10" cols="50" style="border:none;">{{ $data->e_journal }}</textarea>
                                        @else
                                        N/A
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
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
