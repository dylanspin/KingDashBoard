@extends('layouts.master')
@push('css')
<link href="{{asset('plugins/components/owl.carousel/owl.carousel.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('plugins/components/owl.carousel/owl.theme.default.css')}}" rel="stylesheet" type="text/css"/>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row " style="display: flex">
        <div class="col-md-12 col-sm-12 white-box">
            <div class="panel panel-default" style="box-shadow: none;">
                <div class="panel-heading h1 text-center ml-15">Vehicle Details</div>
                <div class="panel-wrapper" style="display: flex;">
                    <div class="col-md-8 col-sm-8 text-center " >
                        <!--                        <div class="white-box">
                                                    <div class="profile-widget">-->
                        <div class="profile-img">
                            @if(!empty($data->file_path))
                            <img src="{{asset($data->file_path)}}" 
                                 alt="user-img"
                                 class="w-720">

                            @else
                            <img src="{{asset('/plugins/images/icons/people_car.png')}}" 
                                 alt="user-img"
                                 class="img-circle w-400">
                            @endif
                        </div>
                    </div>
                    <div class="table-responsive col-md-4 text-center">
                        <div class="white-box mt-20" style="padding-bottom:0px;">
                            <div class="profile-widget">
                                <table class="table">
                                    <form id="updated_device_vehicle_num_form" 
                                        class="hidden updated_device_vehicle_num_form" 
                                        action="<?php echo url('dashboard/update_device_vehicle') ?>" 
                                        method="POST">
                                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                                            <input 
                                                type="hidden" 
                                                name="device_booking_id" 
                                                class="device_booking_id" 
                                                value="{{$data->id}}">
                                    <thead>
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>
                                                <input 
                                                    type="text" 
                                                    name="updated_device_vehicle_num" 
                                                    class="updated_device_vehicle_num" 
                                                    value="{{$data->vehicle_num}}">

                                            </th>
                                        </tr>
                                        <tr>
                                            <th>Confidence</th>
                                            <th>{{$data->confidence}}</th>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <button  
                                                    class="btn col-md-12 btn-primary btn-plate-reader" 
                                                    onclick="event.preventDefault(); document.getElementById('updated_device_vehicle_num_form').submit();">Update</button>
                                            </td>
                                        </tr>
                                    </thead>
                                    </form>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
</div>
@endsection
@push('js')
<script src="{{asset('plugins/components/owl.carousel/owl.carousel.min.js')}}"></script>
<script src="{{asset('plugins/components/owl.carousel/owl.custom.js')}}"></script>
@endpush
