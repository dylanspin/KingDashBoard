@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.min.css">

@endpush

@section('content')
<div class="container-fluid">
<!-- .row -->
<div class="row ">
<div class=" reservtiontext-center">
<h class="text-center"><b>Reservations Listings</b></h>
</div>


<!-- Button trigger modal -->

<button type="button" class="btn btn-success " data-toggle="modal" data-target="#editModal" id="manualreservation-button"
>
+ Add Reservations
</button>



<!-- Modal -->

<form method="post" id='reservation_manual_form' action="/store_reservation_info">
@csrf
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="alert alert-success sucess hidden" role="alert">

</div>
<div class="alert alert-danger fail hidden" role="alert">

</div>
<div class="modal-header">
<h5 class="modal-title" id="editModal">Add Reservations</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>

<div class="modal-body">
<table class="table table-bordered w-auto">

<tr>
<th>Name:</th><td><input type="text" class="form-control" name="name">
<!-- @if ($errors->has('name'))
                      <span class="text-danger">{{ $errors->first('name') }}</span>
                  @endif -->
</td>
</tr>

<tr>
<th>Plate:</th>
<td><input type="text" class="form-control" name="plate">

<!-- @if ($errors->has('plate'))
                      <span class="text-danger">{{ $errors->first('plate') }}</span>
                  @endif -->
</td>
</td>
</tr>

<tr>
<th>Check-in
</th>
<td>
<div class='input-group date' id='datetimepicker1'>
  <input type='text' class="form-control" id="datetime1"  name="in" />
  <span class="input-group-addon">
      <span class="glyphicon glyphicon-calendar"></span>
  </span>
</div>
</td>
</tr>


<tr>
<th>Check-out
</th>
<td>
                                        <div class='input-group date' id='datetimepicker2'>
                                          <input type='text' class="form-control" id="datetime1"  name="out" />
                                          <span class="input-group-addon">
                                              <span class="glyphicon glyphicon-calendar"></span>
                                          </span>
                                        </div>
                                        </td>
                                        </tr>





                                                                                  </table>

                                                                                  </div>
                                                                                  <div class="modal-footer">
                                                                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                                  <button type="button" class="btn btn-primary ReservationButton" type="submit" >Save changes</button>
                                                                                  </div>


                                                                                  </div>
                                                                                  </div>
                                                                                  </div>


                                                                                  </form> 










<div class="col-sm-12">

  <hr>
  <div class="col-md-12">
          <div class="table-responsive">
              
              <table id="listingDataTable" class="table table-striped tommy_reservation_listing">
                  <thead>
                      <tr>
                        <th>ID</th>
                          <th>Name</th>
                          <th>Plate</th>
                          
                          <th>Checkin Time</th>
                          <th>Checkout Time</th>
                          <th>Action</th>
                          
                      </tr>
                  </thead>
                  @foreach($bookingDetails as $booking)
                  <tr>
                  <td>{{$booking->id }}</td>
                      <td>{{$booking->first_name}}</td>
                        <td>{{$booking->vehicle_num }}</td>
                        
                        <td>{{date('d-m-Y',strtotime($booking->checkin_time))}}</td> 
                      <td>{{date('d-m-Y',strtotime($booking->checkout_time))}}</td> 
                        
                      <td>
                      <button type="button" class="btn btn-warning"  >Edit</button>
                      <a href = 'reserve/delete/{{ $booking->id }}'>  <button type="button" class="btn btn-danger">
                          
                      
                      Delete</button></a>





                  </tr>

                  @endforeach
                  @if (\Session::has('success'))
<div class="alert alert-success">
<ul>
<li>{!! \Session::get('success') !!}</li>
</ul>
</div>
@endif
</table>
</td>


</form>
</div>
</div>
</div>

@include('layouts.partials.right-sidebar')
</div>




@endsection

@push('js')



@endpush
