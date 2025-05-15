@extends('layouts.app')

@section('content')
<section id="wrapper" class="login-register">
    <div class="login-box">
        <div class="white-box">
            <form class="form-horizontal form-material" method="POST"  action="{{ route('wizard_step_0_submit') }}">
                @csrf
                <div class="form-group ">
                    <div class="col-xs-12">
                        <h3 class="text-center">{{ __('Welcome to Parkingshop') }}</h3>
                        <p class="text-muted text-center">@lang('wizard.conn_details')</p>
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
                        <input id="db_host" placeholder="@lang('wizard.user')" type="text" class="form-control db_host" name="db_host" value="localhost" required>
                    </div>
                    <div class="col-xs-12">
                        <input id="db_port" placeholder="@lang('wizard.port')" type="text" class="form-control db_port" name="db_port" value="3306" required>
                    </div>
                    <div class="col-xs-12">
                        <input id="db_user" placeholder="@lang('wizard.user')" type="text" class="form-control db_user" name="db_user" value="" required>
                    </div>
                    <div class="col-xs-12">
                        <input id="db_pass" placeholder="@lang('wizard.pass')" type="password" class="form-control db_pass" name="db_pass" value="">
                    </div>
                    <div class="col-xs-12">
                        <input id="db_name" placeholder="@lang('wizard.db_name')" type="text" class="form-control db_name" name="db_name" value="">
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
