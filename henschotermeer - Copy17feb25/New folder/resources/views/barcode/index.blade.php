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
                <h3 class="box-title pull-left">@lang('barcode.barcodes')</h3>
                <a  class="btn btn-success pull-right" href="{{url('barcode/create')}}"><i class="icon-plus"></i> @lang('barcode.add_barcode')</a>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('barcode.key')</th>
                                        <th>@lang('barcode.barcode')</th>
                                        <th>@lang('barcode.name')</th>
                                        <th>@lang('barcode.vehicle_no')</th>
                                        <th>@lang('promo.total_bookings')</th>
                                        <!--<th>@lang('promo.total_arrivals')</th>-->
                                        <th>@lang('barcode.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($barcodes as $key=>$barcode)
                                    <?php
                                        $barcode_booking = \App\Bookings::whereIn('barcode', [$barcode->barcode, $barcode->id])
                                                ->get();
                                    ?>
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$barcode->barcode}}</td>
                                        <td>{{$barcode->name ? $barcode->name : 'N/A'}}</td>
                                        <td>{{$barcode->vehicle_no ? $barcode->vehicle_no : 'N/A'}}</td>
                                        <td>{{count($barcode_booking) > 0 ? count($barcode_booking) : 0}}</td>
<!--                                        <td>
                                            <?php
//                                                $countArrivals = 0;
//                                                foreach($barcode->bookings as $booking){
//                                                    $attendant = \App\Attendants::where('booking_id', $booking->id)->first();
//                                                    if($attendant){
////                                                        $attendantTransaction = \App\AttendantTransactions::where('attendant_id', $attendant->id)->count();
//                                                        $attendantTransaction = \App\AttendantTransactions::where('attendant_id', $attendant->id)
//                                                                ->first();
//                                                        if($attendantTransaction){
//                                                            $countArrivals = $countArrivals + 1;
////                                                            $countArrivals = $countArrivals + $attendantTransaction;
//                                                        }
//                                                    }
//                                                }
//                                                echo $countArrivals;
                                            ?>
                                        </td>-->
                                        <td>
                                            <a 
                                                class="btn btn-info btn-sm" 
                                                href="{{url('barcode/edit/'.$barcode->id)}}">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i> @lang('barcode.edit')
                                            </a>
                                            <a 
                                                class="btn btn-info btn-sm" 
                                                href="{{url('barcode/download/'.$barcode->id.'/en')}}">
                                                <i class="fa fa-info-circle" aria-hidden="true"></i> @lang('barcode.download_en')
                                            </a>
                                            <a 
                                                class="btn btn-info btn-sm" 
                                                href="{{url('barcode/download/'.$barcode->id.'/nl')}}">
                                                <i class="fa fa-info-circle" aria-hidden="true"></i> @lang('barcode.download_nl')
                                            </a>
                                            <a 
                                                class="delete btn btn-danger btn-sm"
                                                data-id="{{$barcode->id}}">
                                                <i class="fa fa-trash-o"></i> @lang('barcode.delete')
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

    <div id="view_sync_settings" class="modal fade view_sync_settings" tabindex="-1" role="dialog" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

            </div>
        </div>
    </div>
    @include('layouts.partials.right-sidebar')
</div>
@endsection

@push('js')
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
$(function () {
    $('#listingDataTable').DataTable({
        "columns": [
            null, null, null, null, null, {"orderable": false}
        ],
        "pageLength": 25
    });
});
</script>
<script>
$(document).ready(function () {
    $(document).on('click', '.delete', function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
        title: "Destroy Barcode?",
            message: "Are you sure want to delete barcode?",
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
            window.location.href = "{{url('barcode/delete')}}/" + id;
            }
            }
        });
    });
    @if (\Session::has('message'))
        $.toast({
            heading: '{{session()->get('heading')}}',
            position: 'top-center',
            text: '{{session()->get('message')}}',
            loaderBg: '#ff6849',
            icon: '{{session()->get('icon')}}',
            hideAfter: 5000,
            stack: 6
        });
    @endif
})

</script>
@endpush