@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="container-fluid">
    <!-- .row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('promo.promo')</h3>
                <a  class="btn btn-success pull-right" href="{{url('promo/create')}}">
                    <i class="icon-plus"></i> @lang('promo.add_promo')
                </a>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('promo.promo_code')</th>
                                        <th>@lang('promo.type')</th>
                                        <!--<th>@lang('promo.discount')</th>-->
                                        <th>@lang('promo.validity')</th>
                                        <th>@lang('promo.total_bookings')</th>
                                        <th>@lang('promo.total_arrivals')</th>
                                        <th>@lang('promo.status')</th>
                                        <th>@lang('promo.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($promos as $key=>$promo)
                                    <tr>
                                        <td>{{$promo->code ? $promo->code : 'N/A'}}</td>
                                        <td>{{$promo->promo_type->title}}</td>
                                        <!--<td>-->
                                            <?php
//                                            if ($promo->price != Null && $promo->percentage == Null) {
//                                                echo $promo->price . " &euro;";
//                                            } else if ($promo->price == Null && $promo->percentage != Null) {
//                                                echo $promo->percentage . " %";
//                                            } else {
//                                                echo 'N/A';
//                                            }
                                            ?>
                                        <!--</td>-->
                                        <td data-sort="{{$promo->start_date}}">
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
                                        <td>{{count($promo->bookings) > 0 ? count($promo->bookings) : 0}}</td>
                                        <td>
                                            <?php
                                                $countArrivals = 0;
                                                foreach($promo->bookings as $booking){
                                                    $attendant = \App\Attendants::where('booking_id', $booking->id)->first();
                                                    if($attendant){
//                                                        $attendantTransaction = \App\AttendantTransactions::where('attendant_id', $attendant->id)->count();
                                                        $attendantTransaction = \App\AttendantTransactions::where('attendant_id', $attendant->id)
                                                                ->first();
                                                        if($attendantTransaction){
                                                            $countArrivals = $countArrivals + 1;
//                                                            $countArrivals = $countArrivals + $attendantTransaction;
                                                        }
                                                    }
                                                }
                                                echo $countArrivals;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($promo->end_date != Null && $promo->coupon_number_limit == Null) {
                                                if (strtotime($promo->end_date) < strtotime(date("Y-m-d h:i:s"))) {
                                                    echo "Expired";
                                                } else if (strtotime($promo->start_date) < strtotime(date("Y-m-d h:i:s")) && strtotime($promo->end_date) > strtotime(date("Y-m-d h:i:s"))) {
                                                    echo "Active";
                                                } else if (strtotime($promo->start_date) > strtotime(date("Y-m-d h:i:s"))) {
                                                    echo "Pending";
                                                }
                                            } else if ($promo->end_date == Null && $promo->coupon_number_limit != Null) {
                                                if ($promo->coupon_number_limit > $promo->coupon_used) {
                                                    echo "Active";
                                                } else {
                                                    echo "Expired";
                                                }
                                            } else if ($promo->end_date == Null && $promo->coupon_number_limit == Null) {
                                                echo "Active";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a 
                                                title="@lang('promo.send')" 
                                                class="send_promo btn btn-success btn-sm"  
                                                data-id="{{$promo->id}}" 
                                                style="cursor:pointer;">
                                                <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                            </a>
                                            &nbsp;&nbsp;
                                            <a 
                                                title="@lang('promo.delete')" 
                                                class="delete btn btn-danger btn-sm" 
                                                data-id="{{$promo->id}}" 
                                                style="cursor:pointer;">
                                                <i class="fa fa-trash-o"></i>
                                            </a>
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
<!-- Modal -->
<div id="sendPromo" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">SEND PROMO INVITATION</h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal sendPromoForm">
                    <!--ERRORS-->
                    <div class="col-sm-12">
                        <div class="alert alert-success alert-dismissible print-success-msg" style="display:none">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <ul></ul>
                        </div>
                        <div class="alert alert-danger alert-dismissible print-error-msg" style="display:none">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <ul></ul>
                        </div>
                    </div>
                    <!--ERRORS-->
                    <!-- CSRF Token -->
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                    <input type="hidden" name="promo_id" class="promo_id" value="0"/>
                    <h2 class="hidden">&nbsp;</h2>
                    <div class="form-group {{ $errors->first('email', 'has-error') }}">
                        <label for="email" class="col-sm-2 control-label">@lang('promo.email') *</label>
                        <div class="col-sm-10">
                            <input 
                                id="email" 
                                name="email" 
                                placeholder="@lang('promo.email')" 
                                type="text" 
                                class="form-control required email" 
                                value="{!! old('email') !!}"/>

                            {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <button 
                            type="button" 
                            class="btn btn-success pull-right mr-20" 
                            name="submit" 
                            onclick="return sendPromoCode();">SEND</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('js')
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>

<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
$(function () {
    $('#listingDataTable').DataTable({
        "columns": [
            null, null, null, null, null, null, {"orderable": false}
        ],
        "order": [[ 2, "desc" ]],
        "pageLength": 25
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
$(document).ready(function () {
    $(document).on('click', '.delete', function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Destroy Promo?",
            message: "Are you sure want to delete promo?",
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancel',
                    className: 'btn-danger'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirm',
                    className: 'btn-success'
                }
            },
            callback: function (result) {
                if (result){
                    window.location.href = "{{url('promo/delete')}}/" + id;
                }
            }
        });
    });
    $(document).on('click', '.send_promo', function (e) {
        var id = $(this).data('id');
        $('#sendPromo .sendPromoForm .promo_id').val(id);
        $('#sendPromo').modal('show');
    });
    @if (\Session::has('message'))
    $.toast({
        heading: '{{session()->get('heading')}}',
        position: 'top-center',
        text: '{{session()->get('message')}}',
        loaderBg: '#ff6849',
        icon: '{{session()->get('icon')}}',
        hideAfter: 3000,
        stack: 6
    });
    @endif
});

function sendPromoCode() {
    event.preventDefault();
    var form_data = new FormData();
    var form_wrapper = "div.sendPromoForm";
    var promo_id = $(form_wrapper+" input[name='promo_id']").val();
    form_data.append("promo_id", promo_id);
    var _token = $(form_wrapper+" input[name='_token']").val();
    form_data.append("_token", _token);
    var email = $(form_wrapper+" input[name='email']").val();
    form_data.append("email", email);
    $.ajax({
        url: '/promo/send/'+promo_id,
        type: 'post',
        data: form_data, // Remember that you need to have your csrf token included
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (_response) {
            if ($.isEmptyObject(_response.error)) {
//                $('tr.record'+id+' td.name').html(name);
//                $('tr.record'+id+' td.email').html(email);
                printSuccessMsg(_response.success, form_wrapper);
            } else {
                printErrorMsg(_response.error, form_wrapper);
            }
        },
        error: function (_response) {
            // Handle error
            printErrorMsg(_response.error, form_wrapper);
        }
    });
}

function printSuccessMsg(msg, wrapper) {
    $(".print-success-msg").css('display', 'none');
    $(".print-error-msg").css('display', 'none');
    $(wrapper+" .print-success-msg").find("ul").html('');
    $(wrapper+" .print-success-msg").css('display', 'block');
    $.each(msg, function (key, value) {
        $(wrapper+" .print-success-msg").find("ul").append('<li style="list-style-type: none;">' + value + '</li>');
    });
}

function printErrorMsg(msg, wrapper) {
    $(".print-success-msg").css('display', 'none');
    $(".print-error-msg").css('display', 'none');
    $(wrapper+" .print-error-msg").find("ul").html('');
    $(wrapper+" .print-error-msg").css('display', 'block');
    $.each(msg, function (key, value) {
        $(wrapper+" .print-error-msg").find("ul").append('<li style="list-style-type: none;">' + value + '</li>');
    });
}
</script>
@endpush