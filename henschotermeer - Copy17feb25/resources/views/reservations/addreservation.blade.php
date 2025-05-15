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
      <h class="text-center"><b>@lang('reservations.add_reservation')</b></h>
  </div>
    <button type="button" class="btn btn-success " data-toggle="modal" data-target="#exampleModal" id="manualreservation-button">
		@lang('reservations.add_reservation_button')
    </button>
  <form method="post" id='reservation_manual_form' action="/store_reservation_info">
    @csrf
  <input type="hidden" value="0" id="invisible">
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
          <h5 class="modal-title" id="exampleModalLabel">@lang('reservations.add')</h5>
          
        </div>

<div class="modal-body">

        <div class="form-group">
             <label class="control-label">@lang('reservations.name')</label>
             <input type="text" class="form-control" id="firstName"  name="firstName">
             {{-- <p class="help is-danger">{{ $errors->first('firstName') }}</p> --}}
       </div>

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
        <button type="button" class="btn btn-primary ReservationButton" type="submit" >@lang('reservations.save')</button>
      </div>
</div>
</div>
</div>

</form> 
<div class="col-sm-12">
 <div class="clearfix"></div>
 
<hr>
<div class="col-md-12 text-right">
	<form method="post" action="{{url('/manual-reservation/')}}" class="col-md-12 text-right custom-search-form">
		@csrf
		<div class="form-group col-md-5">
		</div>
		<div class="form-group col-md-2">
			<select class="form-control" name="search_type">
				<option value="" {{ $search_type == '' ? 'selected' :  ''}}>@lang('bounces_email.search_in')</option>
				 <option value="name" {{ $search_type == 'name' ? 'selected' :  ''}}>@lang('bounces_email.name')</option>
				<option value="license_plate" {{ $search_type == 'license_plate' ? 'selected' :  ''}}>@lang('tommy-reservation.license_plate')</option>
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
			<th>
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
		{{$booking->vehicle_num }}
	</td>
    <td>
		{{date('d-m-Y H:i:s',strtotime($booking->checkin_time))}}
	</td> 
	<td>
		{{date('d-m-Y H:i:s',strtotime($booking->checkout_time))}}
	</td> 
    
  <td>                                       
  <button type="button" class="btn btn-primary EditButton" data-id="{{ $booking->id }}"  data-toggle="modal" data-target="#exampleModal" id="manualreservation-button_edit">@lang('reservations.edit')</button> 
  </form>
    <button type="button" class="btn btn-danger Deletebutton" 
    id="Delbutton" 
      data-toggle="modal" data-target="#myModal">
     
      
  
  @lang('reservations.delete') </button>

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


</body>





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
</script>

@endpush
