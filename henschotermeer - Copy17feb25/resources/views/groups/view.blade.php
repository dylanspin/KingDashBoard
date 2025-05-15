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
               

<div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>

                                    <tr>
                                        <th>@lang('promo.valid_group_name')</th>
                                        <th>@lang('promo.person_email')</th>
                                        <th>@lang('promo.person_name')</th>
                                        <th>@lang('promo.vehicle_number')</th>
                                        <th>@lang('promo.person_arrival_time')</th>
                                        <th>@lang('promo.departure')</th>
                                        <th>@lang('promo.actions')</th>
                                        {{-- <th>@lang('promo.total_bookings')</th>
                                        <th>@lang('promo.total_arrivals')</th> --}}
                                      
                                  </tr>
                                </thead>

                            <tbody>
                                @foreach($promos->bookings as $key=>$promo)
                              <tr>  

                                <td>{{$promos->group_name}}</td>
                                <td>{{$promo->email}}</td>
                                <td>{{$promo->first_name ?? ""}} {{$promo->last_name ?? ""}}</td>
                                <td> {{$promo->vehicle_num}} </td> 
                                <td>{{ date('d-m-Y H:i',strtotime($promo->checkin_time))}} </td>
                                <td>{{date('d-m-Y H:i',strtotime($promo->checkout_time))}}</td>
                                <td>
                                    <button type="button" class="btn btn-primary MemberEditButton" data-id="{{ $promo->id}}"  data-toggle="modal" data-target="#exampleModal" id="edit-promo-member-info"><i class="fa fa-edit"></i></button>
                                    <a 
                                                title="@lang('promo.delete')" 
                                                class="delete btn btn-danger btn-sm" 
                                                data-id="{{$promo->id}}" 
                                                style="cursor:pointer;">
                                                <i class="fa fa-trash-o"></i>
                                            </a>

                                   
                                
                                </td>
                                {{-- <td>{{count($promo->bookings) > 0 ? count($promo->bookings) : 0}}</td>
                                <td></td> --}}
                              </tr>

                               @endforeach 
                           </tbody>

                                
                        </table>
                       <a href="{{url('/promo')}}">
                            <button class="btn btn-primary">@lang('promo.back_button')</button></a>
                           


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

        <form method="POST" action="{{url('group/update-promo-member')}}">
            @csrf
            <input type="hidden" name="booking_number" id="booking_number">
<div class="modal-body">
        <div class="form-group">
            <label class="control-label">@lang('reservations.plate')</label>
           <input type="text" class="form-control" name="plate" id="LicensePlate">
        </div>

        <div class="form-group onedate">
           <label class="control-label">@lang('reservations.check_in')</label>
         <div class='input-group date  ' id='datetimepicker1' > 
             
               <input type='text' class="form-control check_in" id="datetime1" name="check_in" />
               <span class="input-group-addon">
               <span class="glyphicon glyphicon-calendar"></span>
               </span>
          </div>

        </div>
         <div class="form-group onedate">
          <label class="control-label">@lang('reservations.check_out')</label>
         <div class='input-group date ' id='datetimepicker2'> 
              
               <input type='text' class="form-control check_out" id="datetime2"  name="check_out"/>
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
          $(".MemberEditButton").click(function(){
           var booking_id = $(this).data('id');
              $.ajax({
                  /* the route pointing to the post function */
                  url: '{{ url('group/edit-promo-member')}}/'+booking_id,
                  type: 'GET',
                  /* send the csrf-token and the input to the controller */
                  /* remind that 'data' is the response of the AjaxController */
                  success: function (data) {
                      $('#booking_number').val(data.id);
                      $('#LicensePlate').val(data.vehicle_num);
                       var formattedDate = new Date(data.checkin_time);
                       var d = formattedDate.getDate();
                       var m =  formattedDate.getMonth();
                       m += 1;  // JavaScript months are 0-11
                       var Y = formattedDate.getFullYear();
                       function checkTime(i) {
							return (i < 10) ? "0" + i : i;
						}
                       var seconds = formattedDate.getSeconds();
                       var minutes = checkTime(formattedDate.getMinutes());
                       var hour = checkTime(formattedDate.getHours());
                      $('#datetime1').val(d + "-" + m + "-" + Y+ " "+ hour + ":" + minutes);
                      var formattedDatee = new Date(data.checkout_time);
                       var d1 = formattedDatee.getDate();
                       var m1 =  formattedDatee.getMonth();
                       m1 += 1;  // JavaScript months are 0-11
                       var Y1 = formattedDate.getFullYear();

                       var seconds_checkout = formattedDatee.getSeconds();
                       var minutes_checkout = checkTime(formattedDatee.getMinutes());
                       var hour_checkout = checkTime(formattedDatee.getHours());
                       
                       $('#datetime2').val(d + "-" + m + "-" + Y+ " "+ hour_checkout + ":" + minutes_checkout);
                   //    $('.sucess').removeClass("hidden"); 
                   //    $('.sucess').html(data.success);  
                  }
              });
});
$('#datetime2').datetimepicker({
    format: 'DD-MM-YYYY HH:mm'
});
$('#datetime1').datetimepicker({
    format: 'DD-MM-YYYY HH:mm',
});
$(document).on('click', '.delete', function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Destroy Member?",
            message: "Are you sure want to delete?",
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
        
        })
@if (\Session::has('danger'))
        $.toast({
        heading: '{{session()->get('heading')}}',
                position: 'top-center',
                text: '{{session()->get('danger')}}',
                loaderBg: '#ff6849',
                backgroundColor:'#e74a25',
                icon: '{{session()->get('icon')}}',
                hideAfter: 3000,
                stack: 6
        });
@endif
@if (\Session::has('success'))
        $.toast({
        heading: '{{session()->get('heading')}}',
                position: 'top-center',
                text: '{{session()->get('success')}}',
                loaderBg: '#ff6849',
                icon: '{{session()->get('icon')}}',
                hideAfter: 3000,
                stack: 6
        });
@endif
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
@endpush