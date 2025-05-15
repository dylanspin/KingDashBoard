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
                    <h3 class="box-title pull-left">@lang('tommy-reservation.mem_of') {{strtoupper($familyHead)}}</h3>
                    <a  class="btn btn-success pull-right" href="{{url('tommy-reservations/bookings/')}}">
                        <i class="icon-list"></i> @lang('tommy-reservation.view_reservations')
                    </a>
                    <div class="clearfix"></div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="listingDataTable" class="table table-striped tommy_reservation_listing">
                                    <thead>
                                    <tr>
                                        <th>@lang('white-list.first_name')</th>
                                        <th>@lang('white-list.last_name')</th>
                                        <th>@lang('tommy-reservation.family_status')</th>
                                        <th>@lang('tommy-reservation.dob')</th>
                                        <th>@lang('tommy-reservation.actions')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($onLineMembers as $tommyReservation)
                                        <tr>
                                            <td>{{$tommyReservation->first_name ? $tommyReservation->first_name : 'N/A'}}</td>
                                            <td>{{$tommyReservation->last_name ? $tommyReservation->last_name : 'N/A'}}</td>
                                            <td>{{$tommyReservation->family_status}}</td>
                                            <td>{{$tommyReservation->dob || $tommyReservation->dob != date('Y-m-d', strtotime('1970-01-01')) ? date('d/m/Y', strtotime($tommyReservation->dob)) : 'N/A'}}</td>
                                            <td>  
                                                <a 
                                                    target="_blank"
                                                    title="@lang('tommy-reservation.print')" 
                                                    class="btn btn-info btn-sm mt-10" 
                                                    href="{{url('tommy-reservations/bookings/print-ticket/'.$tommyReservation->id)}}">
                                                    <i class="fa fa-print"></i>
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
@endsection

@push('js')
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
$(function () {
    $('#listingDataTable').DataTable({
        "pageLength": 25
    });
});
</script>
@endpush