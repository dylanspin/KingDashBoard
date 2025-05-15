@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.min.css">

@endpush

@section('content')
<div class="container-fluid">
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row ">


  <div class=" reservtiontext-center ml-5">
      <h class="text-center"><b>@lang('sidebar.manual_reservation_person')</b></h>
  </div>
    <button type="button" class="btn btn-success " data-toggle="modal" data-target="#exampleModal" id="manualreservation-button">
		@lang('reservations.add_reservation_button')
    </button>
  <form method="post" id='reservation_manual_form'>
    @csrf
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
            <h5 class="modal-title" id="exampleModalLabel">@lang('reservations.add_person_reservation')</h5>
            
          </div>
  
  <div class="modal-body">
    <input type="hidden" value="0" id="booking_number" name="booking_id">
          <div class="form-group">
               <label class="control-label">@lang('reservations.name')</label>
               <input type="text" class="form-control" id="firstName"  name="firstName">
               {{-- <p class="help is-danger">{{ $errors->first('firstName') }}</p> --}}
         </div>
          <div class="form-group onedate">
             <label class="control-label">@lang('reservations.check_in') <span style="color:#e74a25">*</span></label>
           <div class='input-group date  ' id='datetimepicker1' > 
               
                 <input type='text' class="form-control check_in" id="datetime1" name="check_in" />
                 <span class="input-group-addon">
                 <span class="glyphicon glyphicon-calendar"></span>
                 </span>
            </div>
  
          </div>
           <div class="form-group onedate">
            <label class="control-label">@lang('reservations.check_out') <span style="color:#e74a25">*</span></label>
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
          <button type="button" class="btn btn-primary PersonReservationButton">@lang('reservations.save')</button>
        </div>
  </div>
  </div>
  </form>
</div>

<div class="col-sm-12">
 <div class="clearfix"></div>
 
<hr>
<div class="col-md-12 text-right">
	<form method="post" action="{{url('/person-manual-reservation/')}}" class="col-md-12 text-right custom-search-form">
		@csrf
		<div class="form-group col-md-5">
		</div>
		<div class="form-group col-md-2">
			<select class="form-control" name="search_type">
				<option value="" {{ $search_type == '' ? 'selected' :  ''}}>@lang('bounces_email.search_in')</option>
				 <option value="name" {{ $search_type == 'name' ? 'selected' :  ''}}>@lang('bounces_email.name')</option>
			</select>
		</div>
		<div class="form-group col-md-2">
			<input type="text" name="search_val" value="{{$search_val}}" class="form-control" placeholder="@lang('bounces_email.search')">
		</div>
		<div class="form-group col-md-3">
			<input type="submit" name="search_btn" class="btn btn-primary" value="@lang('bounces_email.search')">
			<input type="submit" name="reset_btn" class="btn btn-info" value="@lang('bounces_email.reset')">
		</div>	
    </form>
</div>
<div class="col-md-12">
<div class="table-responsive">
<table id="listingDataTableee" class="table table-striped tommy_reservation_listinggg">
	<thead>
		<tr>
			<th>
				@sortablelink('first_name',trans('reservations.name'))
			</th>
			<th style="display: none">
				@sortablelink('vehicle_num',trans('reservations.plate'))</th>
			<th>
				@sortablelink('checkin_time', trans('reservations.check_in'))</th>
			<th>
				@sortablelink('checkout_time', trans('reservations.check_out'))
			 </th>
			<th>
				@lang('reservations.action')
			</th>
		  
		</tr>
	</thead>
@foreach($bookingDetails as $booking)
<tr>

	<td>
		{{$booking->first_name}}
	</td>
    <td>
		{{date('d-m-Y',strtotime($booking->checkin_time))}}
	</td> 
	<td>
		{{date('d-m-Y',strtotime($booking->checkout_time))}}
	</td> 
    
  <td>
  <a 
  target="_blank"
  class="btn btn-info btn-sm" 
  href="{{url('download-ticket/'.$booking->id.'/en')}}">
  <i class="fa fa-print" aria-hidden="true"></i>
  @lang('barcode.download_en')
</a>
<a 
  target="_blank"
  class="btn btn-info btn-sm" 
  href="{{url('download-ticket/'.$booking->id.'/nl')}}">
  <i class="fa fa-print" aria-hidden="true"></i>
  @lang('barcode.download_nl')
</a>                                       
  <button type="button" class="btn btn-primary PersonEditButton" title="Edit" data-id="{{ $booking->id }}"  data-toggle="modal" data-target="#exampleModal" id="manualreservation-button_edit"><i class="fa fa-edit"></i></button>
  <button type="button" title="Delete" class="btn btn-danger Deletebutton" id="Delbutton" data-toggle="modal" data-target="#myModal"><i class="fa fa-trash-o"></i></button>
   
  </td>      
  

  <div class="text-center">
  <!-- Button HTML (to Trigger Modal) -->
 
</div>

<!-- Modal HTML -->
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
       <a href = 'reserve/delete/{{ $booking->id }}'> <button type="button" class="btn btn-danger">@lang('reservations.delete')</button></a>
      </div>
    </div>
  </div>
</div>   
</tr>

@endforeach

</table>
{!! $bookingDetails->render() !!}
{{-- </td> --}}


</form>
</div>
</div>
</div>

@include('layouts.partials.right-sidebar')
</div>




@endsection


@push('js')
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script>
  @if (\Session::has('message'))
        $.toast({
            heading: 'Success!',
            position: 'top-center',
            text: '{{session()->get('message')}}',
            loaderBg: '#ff6849',
            icon: 'success',
            hideAfter: 3000,
            stack: 6
        });
    @endif
$(document).ready(function(){
       
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(".PersonReservationButton").click(function(event){
            var id = $("#invisible").val();
            var name = $("#firstName").val();
            var check_in = $("#datetime1").val();
            var check_out = $("#datetime2").val();
            var booking_number=$('#booking_number').val();
            if(booking_number !=null){
              booking_number=booking_number;
            }
            else{
              booking_number=null;
            }
            // alert(data);
            $.ajax({
                /* the route pointing to the post function */
                url: 'store-person-reservation',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data:{id:id, name:name,check_in:check_in,check_out:check_out,booking_number:booking_number},
                /* remind that 'data' is the response of the AjaxController */
                success: function(data) {
                  console.log(data);
                    if(!data.errors){
                        $('.sucess').removeClass('hidden');
                         $('.sucess').append(data.success);
                         location.reload();
                    }else{
                      event.preventDefault();
                        printErrorMsg(data.errors);
                    }
                }   
            }); 
            $(".alert-success").fadeTo(2000, 500).slideUp(500, function(){
                $(".alert-success").slideUp(500);
            });
            
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
           $(".PersonEditButton").click(function(){
            var booking_id = $(this).data('id');
               
               $.ajax({
                   /* the route pointing to the post function */
                   url: '{{ url('edit-person-reservation')}}/'+booking_id,
                   type: 'GET',
                   /* send the csrf-token and the input to the controller */
                   /* remind that 'data' is the response of the AjaxController */
                   success: function (data) {
                       console.log(data);
                       $('#booking_number').val(data.id);
                       $('#firstName').val(data.first_name);
					    var formattedDate = new Date(data.checkin_time);
						var d = formattedDate.getDate();
						var m =  formattedDate.getMonth();
						m += 1;  // JavaScript months are 0-11
						var Y = formattedDate.getFullYear();
						
                       $('#datetime1').val(d + "-" + m + "-" + Y);
					   var formattedDatee = new Date(data.checkout_time);
						var d1 = formattedDatee.getDate();
						var m1 =  formattedDatee.getMonth();
						m1 += 1;  // JavaScript months are 0-11
						var Y1 = formattedDate.getFullYear();
					    
                       $('#datetime2').val(d1 + "-" + m1 + "-" + Y1);
                    //    $('.sucess').removeClass("hidden"); 
                    //    $('.sucess').html(data.success);  
                   }
               }); 
			   
           }); 




</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
@endpush
