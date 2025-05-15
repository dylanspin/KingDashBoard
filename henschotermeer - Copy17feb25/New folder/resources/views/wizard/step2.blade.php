@extends('layouts.app')

@section('content')
<section id="wrapper" class="login-register">


    @if(count($device_details) > 0)
    <div class="login-box device_found">
        <div class="white-box pull-left col-md-12 ">
            <div class="col-md-6 pt-50  pb-50">
                @foreach($device_details as $device)
                <div class="col-md-12">
                    <label>Device: {{$device['name']}}</label>
                    @if($device['status'] == 1)
                    <i class="fa fa-question-circle ml-20 text-primary f-16" data-toggle="tooltip" title="Incomplete Device"></i>
                    @elseif($device['status'] == 2)
                    <i class="fa fa-times ml-20 text-danger f-16" data-toggle="tooltip"  title="Device unable to connect"></i>
                    @elseif($device['status'] == 3)
                    <i class="fa fa-check ml-20 text-success f-16" data-toggle="tooltip"  title="Device connected"></i>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="col-md-6 right-con pt-50 pb-50">
                <div class="form-group ">
                    <div class="col-xs-12">
                        <h3 class="text-center">{{ __('Welcome to Parkingshop') }}</h3>
                        <p class="text-muted text-center">@lang('wizard.add_device_info')</p>
                    </div>
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-6">
                        <a href="{{route('wizard_step_3')}}" class="btn btn-info  btn-block text-uppercase waves-effect waves-light" >@lang('wizard.yes')</a>
                    </div>
                    <div class="col-xs-6">
                        <a href="{{url('/')}}" class="btn btn-danger  btn-block text-uppercase waves-effect waves-light" >@lang('wizard.no')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="login-box">
        <div class="white-box pull-left col-md-12">
            <div class="form-group ">
                <div class="col-xs-12">
                    <h3 class="text-center">{{ __('Welcome to Parkingshop') }}</h3>
                    <p class="text-muted text-center">@lang('wizard.add_device_info')</p>
                </div>
            </div>
            <div class="form-group text-center m-t-20">
                <div class="col-xs-6">
                    <a href="{{route('wizard_step_3')}}" class="btn btn-info  btn-block text-uppercase waves-effect waves-light" >@lang('wizard.yes')</a>
                </div>
                <div class="col-xs-6">
                    <a href="{{url('/')}}" class="btn btn-danger  btn-block text-uppercase waves-effect waves-light" >@lang('wizard.no')</a>
                </div>
            </div>
            @endif


        </div>
    </div>
</section>
<style>
    .device_found{
        width: 1024px;
    }

    .right-con{
        border-left: 1px solid #eee;
    }
</style>
@endsection
