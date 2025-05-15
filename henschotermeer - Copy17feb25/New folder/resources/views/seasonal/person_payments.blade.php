@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<style>
    .color-star-fill {
        color: #fde16d;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- .row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('sidebar.seasonal') @lang('sidebar.seasonal_person')</h3>
<!--                <p class="pull-right">
                    @lang('payments.t_amount') {{number_format($totalAmount, 2, ',', '.')}} &euro;
                    <br>
                    @lang('payments.t_bookings') {{$bookingPayments->count()}}
                </p>-->
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <form method="post" action="{{url('/seasonal/person')}}" class="col-md-6 text-right custom-search-form">
                            @csrf
                            <div class="form-group col-md-4">
                                <select class="form-control" name="search_type">
                                    <option value="" {{ $search_type == '' ? 'selected' :  ''}}>Search In</option>
                                    <option value="first_name" {{ $search_type == 'first_name' ? 'selected' :  ''}}>Name</option>
                                    <option value="email" {{ $search_type == 'email' ? 'selected' :  ''}}>Email</option>
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
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <!--<th>@lang('payments.no')</th>-->
                                        <th>@sortablelink('first_name',trans('payments.name'))</th>
                                        <th>@sortablelink('email',trans('payments.email'))</th>
                                        <th>@sortablelink('amount',trans('payments.amount'))</th>
                                        <th>@sortablelink('check_in',trans('payments.arrival'))</th>
                                        <th>@sortablelink('check_out',trans('payments.departure'))</th>
                                        <th>@lang('payments.dob')</th>
                                        <!--<th>@lang('payments.type')</th>-->
                                        <th>@sortablelink('is_online',trans('payments.status'))</th>
                                        <th>@lang('barcode.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookingPayments as $key=>$bookingPayment)
                                   
                                    <tr>
                                        <!--<td>{{$key+1}}</td>-->
                                        <td>{{!empty($bookingPayment->first_name) ? $bookingPayment->first_name : $bookingPayment->email}}</td>
                                        <td>{{!empty($bookingPayment->email) ? $bookingPayment->email : 'N/A'}}</td>
                                        <td>{{number_format($bookingPayment->amount, 2, ',', '.')}} &euro;</td>
                                        <td>{{date('d/m/Y H:i', strtotime($bookingPayment->check_in))}}</td>
                                        <td>{{date('d/m/Y H:i', strtotime($bookingPayment->check_out))}}</td>
                                        <td>{{ !empty($bookingPayment->dob) ? date('d/m/Y', strtotime($bookingPayment->dob)) : 'N/A'}}</td>
                                        <!--<td>Seasonal subscription person</td>-->
                                        <td>
                                              @lang('payments.online_payment')
                                           
                                        </td>
                                        <td>
                                            <a 
                                                class="btn btn-info btn-sm" 
                                                href="{{url('seasonal/download/'.$bookingPayment->booking_id)}}" 
                                                title="Download PDF">
                                                <i class="fa fa-download" aria-hidden="true"></i>
                                            </a>
                                            <a 
                                                class="btn btn-info btn-sm" 
                                                href="{{url('seasonal/resend/'.$bookingPayment->booking_id)}}" 
                                                title="Resend">
                                                <i class="fa fa-send" aria-hidden="true"></i>
                                            </a>
                                            
                                    <button type="button" class="btn btn-primary seasonalPersonEdit" data-id="{{ $bookingPayment->booking_id}}"  data-toggle="modal" data-target="#exampleModal" id="edit-subscription"><i class="fa fa-edit"></i></button>
                                    <button type="button" class="btn btn-danger Deletebutton" 
                                            id="Delbutton" 
                                              data-toggle="modal" data-target="#myModal"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                        </td>
                                        <div id="myModal" class="modal fade">
                                            <div class="modal-dialog modal-confirm">
                                              <div class="modal-content">
                                                <div class="modal-header flex-column">
                                                  <div class="icon-box">
                                                    <i class="fa fa-exclamation"></i>
                                                  </div>            
                                                  <h4 class="text">@lang('reservations.sure')</h4>  
                                                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                  <p>@lang('reservations.really') </p>
                                                </div>
                                                <div class="modal-footer justify-content-center">
                                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('reservations.cancel')</button>
                                                 <a href = "{{url('seasonal/delete/'.$bookingPayment->booking_id) }}"> <button type="button" class="btn btn-danger">@lang('reservations.delete')</button></a>
                                                </div>
                                              </div>
                                            </div>
                                          </div>  
                                    </tr>
                                   
                                    @endforeach
                                </tbody>
                            </table>
                             {!! $bookingPayments->appends(\Request::except('page'))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
                  <div class="modal-content">
                      <div class="alert alert-success sucess hidden" role="alert"></div>
                      <div class="alert alert-danger print-error-msg" style="display:none">
                       <ul></ul>
                      </div>
                    </div>
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
              </button>
              <h5 class="modal-title" id="exampleModalLabel">@lang('Edit')</h5>
              
            </div>
    
            <form method="POST" action="{{url('seasonal/update')}}">
                @csrf
                <input type="hidden" name="booking_id" id="booking_number">
    <div class="modal-body">
            <div class="form-group">
                <label class="control-label">@lang('payments.name')</label>
               <input type="text" class="form-control" name="first_name" id="first_name">
            </div>
            <div class="form-group">
                <label class="control-label">@lang('payments.email')</label>
               <input type="text" class="form-control" name="email" id="email">
            </div>
            <div class="form-group onedate">
                <label class="control-label">@lang('payments.arrival')</label>
              <div class='input-group date  ' id='datetimepicker1' > 
                  
                    <input type='text' class="form-control check_in" id="datetime1" name="arrival_time" />
                    <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                    </span>
               </div>
     
             </div>
             <div class="form-group onedate">
              <label class="control-label">@lang('payments.departure')</label>
             <div class='input-group date ' id='datetimepicker2'> 
                  
                   <input type='text' class="form-control check_out" id="datetime2"  name="departure_time"/>
                   <span class="input-group-addon">
                   <span class="glyphicon glyphicon-calendar"></span>
                   </span>
              </div>
    
            </div>
            </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('reservations.close')</button>
            <input type="submit" class="btn btn-primary" value="@lang('reservations.save')">
          </div>
        </form>
    </div>
    </div>

    @include('layouts.partials.right-sidebar')
</div>
@endsection

@push('js')
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
		$(function () {
			$('#datetime2').datetimepicker({
			format: 'DD-MM-YYYY HH:MM:ss',
		});
		$('#datetime1').datetimepicker({
			format: 'DD-MM-YYYY HH:MM:ss',
		});
//    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
//        "date-euro-pre": function (a) {
//        var x;
//        if ($.trim(a) !== '') {
//            var frDatea = $.trim(a).split(' ');
//            var frTimea = (undefined != frDatea[1]) ? frDatea[1].split(':') : [00, 00, 00];
//            var frDatea2 = frDatea[0].split('/');
//            x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + ((undefined != frTimea[2]) ? frTimea[2] : 0)) * 1;
//        } 
//        else {
//            x = Infinity;
//        }
//        return x;
//        },
//        "date-euro-asc": function (a, b) {
//            return a - b;
//        },
//        "date-euro-desc": function (a, b) {
//            return b - a;
//        }
//    });
//    $('#listingDataTable').DataTable({
//        columnDefs: [
//            {type: 'date-euro', targets: 3},
//            {type: 'date-euro', targets: 4}
//        ],
//        "pageLength": 25,
//        "order": [[3, "desc"]]
//    });

        @if(\Session::has('message'))
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
        function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }
    });
    $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          $(".seasonalPersonEdit").click(function(){
           var booking_id = $(this).data('id');
              $.ajax({
                  /* the route pointing to the post function */
                  url: '{{ url('seasonal/edit-person-ticket')}}/'+booking_id,
                  type: 'GET',
                  /* send the csrf-token and the input to the controller */
                  /* remind that 'data' is the response of the AjaxController */
                  success: function (data) {
                      $('#booking_number').val(data.id);
                      $('#first_name').val(data.first_name);
                      data.email ? $("#email").val(data.email) : $("#email").val("N/A");
                       var formattedDate = new Date(data.checkin_time);
                       var d = formattedDate.getDate();
                       var m =  formattedDate.getMonth();
                       m += 1;  // JavaScript months are 0-11
                       var Y = formattedDate.getFullYear();
                       var seconds = formattedDate.getSeconds();
                       var minutes = formattedDate.getMinutes();
                       var hour = formattedDate.getHours();
                      $('#datetime1').val(d + "-" + m + "-" + Y+ " "+ hour + ":" + minutes + ":" + seconds);
                      var formattedDatee = new Date(data.checkout_time);
                       var d1 = formattedDatee.getDate();
                       var m1 =  formattedDatee.getMonth();
                       m1 += 1;  // JavaScript months are 0-11
                       var Y1 = formattedDate.getFullYear();
                       
                       var seconds_checkout = formattedDatee.getSeconds();
                       var minutes_checkout = formattedDatee.getMinutes();
                       var hour_checkout = formattedDatee.getHours();
                       
                       $('#datetime2').val(d1 + "-" + m1 + "-" + Y1+ " "+ hour_checkout + ":" + minutes_checkout + ":" + seconds_checkout);
                   //    $('.sucess').removeClass("hidden"); 
                   //    $('.sucess').html(data.success);  
                  }
              });
});
$(document).on('click', '.delete', function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Destroy Subscription?",
            message: "Are you sure want to Subscription?",
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
                    window.location.href = "{{url('/group/delete-promo-member')}}/" + id;
                }
            }
        });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
@endpush