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
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">PROMO BOOKING DETAILS</h3>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table 
                                class="table" 
                                data-sorting="true" 
                                data-filtering="true" 
                                data-toggle-column="first"
                                data-paging="true" 
                                data-paging-size="25">
                                <thead>
                                    <tr>
                                        <th data-sortable="false"></th>
                                        <th>@lang('promo.promo_code')</th>
                                        <th>@lang('promo.type')</th>
                                        <th>@lang('promo.validity')</th>
                                        <th data-breakpoints="all"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groups as $key => $promo)
                                    <tr>
                                        <td></td>
                                        <td>{{$promo->code ? $promo->code : 'N/A'}}</td>
                                        <td>{{$promo->promo_type->title}}</td>
                                        <td>
                                        <?php
                                        if ($promo->end_date != Null && $promo->coupon_number_limit == Null) {
                                            echo date('d M Y', strtotime($promo->start_date)) . ' - ' . date('d M Y', strtotime($promo->end_date));
                                        } else if ($promo->end_date == Null && $promo->coupon_number_limit != Null) {
                                            echo $promo->coupon_number_limit - $promo->coupon_used . " Coupons Remaining";
                                        } else {
                                            echo 'Unlimited';
                                        }
                                        ?>
                                        </td>
                                        <td>
                                            <table class="table table-responsive" style="width:100%;"> 
                                                <tr>
                                                    <th>@lang('at_location.name')</th>
                                                    <th>@lang('at_location.contact_details')</th>
                                                    <th data-type="date" data-sorted="true" data-direction="DESC">@lang('at_location.vehicle')</th>
                                                    <th>@lang('at_location.type')</th>
                                                    <th>@lang('at_location.check_in')</th>
                                                    <th>@lang('at_location.check_out')</th>
                                                </tr>
                                                @if(count($promo->bookings) > 0)
                                                @foreach($promo->bookings as $key => $bookings)
                                                <tr>
                                                    <td>
                                                        <?php
                                                        if($bookings->first_name != NULL && $bookings->last_name != NULL){
                                                            echo $bookings->first_name.' '.$bookings->last_name;
                                                        }
                                                        else if($bookings->first_name != NULL && $bookings->last_name == NULL){
                                                            echo $bookings->first_name;
                                                        }
                                                        else if($bookings->first_name == NULL && $bookings->last_name != NULL){
                                                            echo $bookings->last_name;
                                                        }
                                                        else{
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if($bookings->email != NULL && $bookings->phone_number != NULL){
                                                            echo $bookings->email.'('.$bookings->phone_number.')';
                                                        }
                                                        else if($bookings->email == NULL && $bookings->phone_number != NULL){
                                                            echo $bookings->phone_number;
                                                        }
                                                        else if($bookings->email != NULL && $bookings->phone_number == NULL){
                                                            echo $bookings->email;
                                                        }
                                                        else{
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>{{$bookings->vehicle_num ? $bookings->vehicle_num : 'N/A'}}</td>
                                                    <td>
                                                        <?php
                                                        if ($bookings->type == 0) {
                                                            echo 'N/A';
                                                        } else if ($bookings->type == 1) {
                                                            echo 'Send Ticket';
                                                        } else if ($bookings->type == 2) {
                                                            echo 'White List';
                                                        } else if ($bookings->type == 3) {
                                                            echo 'User List';
                                                        } else if ($bookings->type == 4) {
                                                            echo 'Customer';
                                                        } else if ($bookings->type == 5) {
                                                            echo 'BarCode';
                                                        } else if ($bookings->type == 6 || $bookings->type == 7) {
                                                            echo 'Tommy Reservation';
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>{{$bookings->checkin_time != NULL ? date('d/m/Y H:i', strtotime($bookings->checkin_time)) : '--'}}</td>
                                                    <td>{{$bookings->checkout_time != NULL ? date('d/m/Y H:i', strtotime($bookings->checkout_time)) : '--'}}</td>
                                                </tr>
                                                @endforeach
                                                @else
                                                <tr>Record Not Found</tr>
                                                @endif
                                            </table>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('plugins/components/footable/js/footable.min.js') }}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>

<script src="{{ asset('plugins/components/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
<script src="{{asset('plugins/components/icheck/icheck.min.js')}}"></script>
<script src="{{asset('plugins/components/icheck/icheck.init.js')}}"></script>
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
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
    
});

$('.table').footable();

</script>
@endpush