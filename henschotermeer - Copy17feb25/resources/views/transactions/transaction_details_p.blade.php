@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/footable/css/footable.bootstrap.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<!--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">-->
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
<link href="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.skinModern.css')}}" rel="stylesheet">

<style>
    /* Bootstrap 4 text input with search icon */

    .has-search .form-control {
        padding-left: 2.375rem;
    }

    .has-search .form-control-feedback {
        position: absolute;
        z-index: 2;
        display: block;
        width: 2.375rem;
        height: 2.375rem;
        line-height: 2.375rem;
        text-align: center;
        pointer-events: none;
        color: #aaa;
    }
    .input-group-append{
        margin-left: -1px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">PERSON TRANSACTION DETAILS</h3>
                <!--                <a  class="btn btn-success pull-right" href="{{url('user-list/create')}}">
                                    <i class="icon-plus"></i> @lang('user-list.add_user')
                                </a>-->
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <form method="post" action="{{url('/transaction_details/person')}}" class="col-md-6 text-right custom-search-form">
                            @csrf
                            <div class="form-group col-md-4">
                                <select class="form-control" name="search_type">
                                    <option value="" {{ $search_type == '' ? 'selected' :  ''}}>Search In</option>
                                    <option value="first_name" {{ $search_type == 'first_name' ? 'selected' :  ''}}>Name</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <input type="text" name="search_val" value="{{$search_val}}" class="form-control" placeholder="Search">
                            </div>
                            <div class="form-group col-md-4">
                                <input type="submit" name="search_btn" class="btn btn-primary" value="Search">
                                <input type="submit" name="reset_btn" class="btn btn-danger" value="Reset">
                            </div>
                        </form>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th ></th>
                                        <th>@sortablelink('first_name',trans('at_location.name'))</th>
                                        <th>@lang('at_location.contact_details')</th>
                                        <th>@sortablelink('type',trans('at_location.type'))</th>
                                        <th>@lang('at_location.entry') / @lang('at_location.exit')</th>
                                        <th>@sortablelink('check_in',trans('at_location.check_in'))</th>
                                        <th>@sortablelink('check_out',trans('at_location.check_out'))</th>
                                                                                <!--<th data-breakpoints="all"></th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($get_last_5_transactions) > 0)
                                    @foreach($get_last_5_transactions as $key => $transaction)
                                    <tr>
                                        <td style="cursor: pointer!important;"><i  class="fa fa-plus f-30 show_details cursor-pointer" data-transaction-id='{{$transaction->id}}'></i></td>
                                        <td><a href="{{ url('/person/'.$transaction->id)}}" class="text-link">{{$transaction->name}}</a></td>
                                        <td>
                                            <?php
                                            if ($transaction->email != 'N/A' && $transaction->phone_number != 'N/A') {
                                                echo $transaction->email . '(' . $transaction->phone_number . ')';
                                            } else if ($transaction->email == 'N/A' && $transaction->phone_number != 'N/A') {
                                                echo $transaction->phone_number;
                                            } else if ($transaction->email != 'N/A' && $transaction->phone_number == 'N/A') {
                                                echo $transaction->email;
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td>{{$transaction->type}}</td>
                                        <td>{{$transaction->entry_device}} / {{$transaction->exit_device}}</td>
                                        <td>{{$transaction->check_in}}</td>
                                        <td>{{$transaction->check_out}}</td>
                                    </tr>
                                    <tr class="extra_details extra_details_{{$transaction->id}} hidden">
                                        <td colspan="9">
                                            @if(count($transaction->vehicle_payment_transactions) > 0)
                                            @foreach($transaction->vehicle_payment_transactions as $key => $vehiclePaymentTransaction)
                                            <div class="col-md-12">
                                                <label class="col-md-2" style="font-weight:bold">@lang('at_location.device')</label>
                                                <div class="col-md-10">
                                                    <input 
                                                        type="text" 
                                                        value="{{!empty($vehiclePaymentTransaction->location_devices) && $vehiclePaymentTransaction->location_devices->device_name != NULL  ? $vehiclePaymentTransaction->location_devices->device_name : 'N/A'}}" 
                                                        style="border:none;"
                                                        readonly="" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="col-md-2" style="font-weight:bold">@lang('at_location.amount')</label>
                                                <div class="col-md-10">
                                                    <input 
                                                        type="text" 
                                                        value="{{number_format($vehiclePaymentTransaction->amount, 2, ',', '.')}} &euro;" 
                                                        style="border:none;"
                                                        readonly="" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="col-md-2" style="font-weight:bold">@lang('at_location.status')</label>
                                                <div class="col-md-10">
                                                    <input 
                                                        type="text" 
                                                        value="{{$vehiclePaymentTransaction->status ? 'SUCCESS' : 'FAILED'}}" 
                                                        style="border:none;"
                                                        readonly="" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="col-md-2" style="font-weight:bold">@lang('payments.type')</label>
                                                @if($transaction->is_online)
                                            @lang('payments.online_payment')
                                             <div class="col-md-10">
                                                    <input 
                                                        type="text" 
                                                        value="@lang('payments.online_payment')" 
                                                        style="border:none;"
                                                        readonly="" />
                                                </div>
                                            @else
                                             <div class="col-md-10">
                                                    <input 
                                                        type="text" 
                                                        value="@lang('payments.payment_terminal')" 
                                                        style="border:none;"
                                                        readonly="" />
                                                </div>
                                            
                                            @endif
                                               
                                            </div>
                                            @endforeach
                                            @else
                                            <div class="col-md-12">
                                                <input 
                                                    type="text" 
                                                    value="N/A" 
                                                    style="border:none;"
                                                    readonly="" />
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            No record found for this search. 
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                            <!--{{ $transactions->links() }}-->
                            {!! $transactions->appends(\Request::except('page'))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.right-sidebar')
</div>
@endsection

@push('js')
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('plugins/components/footable/js/footable.min.js') }}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>

<script src="{{ asset('plugins/components/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
<script src="{{asset('plugins/components/icheck/icheck.min.js')}}"></script>
<script src="{{asset('plugins/components/icheck/icheck.init.js')}}"></script>
<!--<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>-->
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"
type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<!-- Clock Plugin JavaScript -->
<script src="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.js')}}"></script>
<!-- Color Picker Plugin JavaScript -->
<script src="{{asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asColor.js')}}"></script>
<script src="{{asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asGradient.js')}}"></script>
<script src="{{asset('plugins/components/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js')}}"></script>
<script src="{{asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider.min.js')}}"></script>
<script src="{{asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider-init.js')}}"></script>
<script>
$(document).ready(function () {
    jQuery(".show_details").click(function () {
        if (jQuery(this).hasClass('active')) {
            jQuery(this).removeClass('active');
            jQuery('.extra_details').addClass('hidden');
        } else {
            jQuery(this).addClass('active');
            var transaction_id = jQuery(this).attr('data-transaction-id');
            jQuery('.extra_details').addClass('hidden');
            jQuery('.extra_details_' + transaction_id).removeClass('hidden');
        }

    });
});


</script>
@endpush