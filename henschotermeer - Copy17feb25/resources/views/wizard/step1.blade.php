@extends('layouts.app')

@section('content')
<section id="wrapper" class="login-register">
    <div class="login-box">
        <div class="white-box">
            <form class="form-horizontal form-material" method="POST"  action="{{ route('wizard_step_1_submit') }}">
                @csrf
                <div class="form-group ">
                    <div class="col-xs-12">
                        <h3 class="text-center">{{ __('Welcome to Parkingshop') }}</h3>
                        <p class="text-muted text-center">@lang('wizard.activation_key')</p>
                    </div>
                </div>
                <div class="flash-message playfairRegular">
                    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if(Session::has('alert-' . $msg))
                    <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}<a type="button" class="close" data-dismiss="alert" aria-hidden="true">Ã—</a></p>
                    @endif
                    @endforeach
                </div> <!-- end .flash-message -->
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input id="activation_key" placeholder="@lang('wizard.active_key')" type="text" class="form-control activation_key" name="activation_key" value="" required>
                    </div>
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">@lang('wizard.nxt')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
