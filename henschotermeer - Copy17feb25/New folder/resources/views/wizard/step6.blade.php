@extends('layouts.app')

@section('content')
<section id="wrapper" class="login-register">
    <div class="login-box">
        <div class="white-box col-md-12">
            <form class="form-horizontal form-material" method="POST"  action="{{ route('wizard_step_6_submit') }}">
                @csrf
                <div class="form-group ">
                    <div class="col-xs-12">
                        <h3 class="text-center">{{ __('Welcome to Parkingshop') }}</h3>
                        <p class="text-muted text-center">@lang('wizard.config_plate_reader')</p>
                    </div>
                </div>
                <div class="flash-message playfairRegular">
                    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if(Session::has('alert-' . $msg))
                    <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}<a type="button" class="close" data-dismiss="alert" aria-hidden="true">Ã—</a></p>
                    @endif
                    @endforeach
                </div>
                @for ($i = 0; $i < $plate_reader; $i++)
                <div class="col-md-4">
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input id="device_name" placeholder="@lang('wizard.name_plate_reader') {{ $i + 1 }}" type="text" class=" form-control device_name" name="device_name[]" value="" >
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <select class="form-control" name="device_direction[]" >
                                <option value="">@lang('wizard.device_direction')</option>
                                <option value="bi-directional">@lang('wizard.bi_directional')</option>
                                <option value="in">@lang('wizard.in')</option>
                                <option value="out">@lang('wizard.out')</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input id="ip" placeholder="@lang('wizard.ip_plate_reader') {{ $i + 1 }}" type="text" class=" form-control ip" name="ip[]" value="" >
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input id="port" placeholder="@lang('wizard.port_plate_reader') {{ $i + 1 }}" type="text" class=" form-control port" name="port[]" value="" >
                        </div>
                    </div>
                     <div class="form-group">
                        <div class="col-sm-12">
                            <div class="">
                                <input  type="checkbox" name="import_settings[]" class="import_settings" value="1">
                                <input type="hidden" value="0" class="import_settings_hidden" name="import_settings_hidden[]">
                                <label>@lang('wizard.import')</label>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>

                @endfor
                <div class="col-md-12">
                    <div class="form-group text-center m-t-20 col-md-2 pull-right">
                        <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">@lang('wizard.nxt')</button>
                        <div class="form-group text-center m-t-5">
                            <a href="{{url('/wizard-step-7')}}" class="text-muted">@lang('wizard.skip')</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<style>
    .login-box{
        width: 1024px;
    }
    .login-register {
        height: auto !important;
        position: absolute!important;
        background-size: cover;
    }
</style>
@endsection
