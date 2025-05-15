@extends('layouts.app')

@section('content')
<section id="wrapper" class="login-register">
    <div class="login-box">
        <div class="white-box pull-left">

            <div class="form-group ">
                <div class="col-xs-12">
                    <h3 class="text-center">{{ __('Welcome to Parkingshop') }}</h3>
                    <p class="text-muted text-center">@lang('wizard.thanks')</p>
                </div>
            </div>
            <div class="form-group text-center m-t-20">
                <div class="col-xs-12">
                    <a href="{{url('/')}}" class="btn btn-info  btn-block text-uppercase waves-effect waves-light" >@lang('wizard.finish')</a>
                </div>

            </div>
            </form>
        </div>
    </div>
</section>
@endsection
