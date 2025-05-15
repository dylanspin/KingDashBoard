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
                    <h3 class="box-title pull-left text-capitalize">@lang('booking.alert_details')</h3>
                    <div class="clearfix"></div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="listingDataTable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>@lang('booking.no')</th>
                                        <th>@lang('booking.device')</th>
                                        <th>@lang('booking.status')</th>
                                        <th>@lang('booking.message')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($device_alerts as $key=>$device_alert)
                                        <tr>
                                            <td>{{$key+1}}</td>
                                            <td>{{$device_alert->location_devices->device_name}}</td>
                                            <td>{!! $device_alert->status == 0 ? '<span class="label label-danger">ERROR</span>' : '<span class="label label-warning">WARNING</span>' !!}</td>
                                            <td>{{$device_alert->message ? $device_alert->message : 'N/A'}}</td>
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
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script> 
$(function () {
    $('#listingDataTable').DataTable({
        "pageLength": 25
    });
});
</script>
@endpush