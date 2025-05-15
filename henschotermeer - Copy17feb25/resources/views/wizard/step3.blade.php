@extends('layouts.app')

@section('content')
<section id="wrapper" class="login-register">
    <div class="login-box">
        <div class="white-box">
            <form class="form-horizontal form-material" method="POST"  action="{{ route('wizard_step_3_submit') }}">
                @csrf
                <div class="form-group ">
                    <div class="col-xs-12">
                        <h3 class="text-center">{{ __('Welcome to Parkingshop') }}</h3>
                        <p class="text-muted text-center">@lang('wizard.ticket_reader_no')</p>
                    </div>
                </div>
                <div class="flash-message">
                    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if(Session::has('alert-' . $msg))
                    <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}<a type="button" class="close" data-dismiss="alert" aria-hidden="true">Ã—</a></p>
                    @endif
                    @endforeach
                </div>
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input id="ticket_readers" placeholder="@lang('wizard.ticket_readers')" type="text" class="form-control ticket_readers" name="ticket_readers" value="" required>
                    </div>
                </div>
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input id="person_ticket_readers" placeholder="@lang('wizard.person_ticket_readers')" type="text" class="form-control person_ticket_readers" name="person_ticket_readers" value="" required>
                    </div>
                </div>
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input id="plate_reader" placeholder="@lang('wizard.plate_readers')" type="text" class="form-control plate_reader" name="plate_reader" value="" required>
                    </div>
                </div>
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input id="outdoor_display" placeholder="@lang('wizard.outdoor_display')" type="text" class="form-control outdoor_display" name="outdoor_display" value="" required>
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
