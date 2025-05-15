@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<style>
    .cursor-pointer{
        cursor:pointer;
    }
</style>
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="box-title">@lang('at_location.curr_location')</h4>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="transaction_table" class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('at_location.name')</th>
                                <th>@lang('at_location.contact_details')</th>
<!--                                <th>@lang('at_location.email')</th>
                                <th>@lang('at_location.phone')</th>-->
                                <th>@lang('at_location.vehicle')</th>
                                <th>@lang('at_location.amount')</th>
                                <th>@lang('at_location.checkin')</th>
                                <th>@lang('at_location.checkout')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($at_location as $key => $booking)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td><a href="{{ url('/vehicle/'.$booking->id)}}" class="text-link">{{$booking->name}}</a></td>
<!--                                <td>{{$booking->email}}</td>
                                <td>{{$booking->phone_number}}</td>-->
                                <td>
                                    <?php
                                    if ($booking->email != 'N/A' && $booking->phone_number != 'N/A') {
                                        echo $booking->email . '(' . $booking->phone_number . ')';
                                    } else if ($booking->email == 'N/A' && $booking->phone_number != 'N/A') {
                                        echo $booking->phone_number;
                                    } else if ($booking->email != 'N/A' && $booking->phone_number == 'N/A') {
                                        echo $booking->email;
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                @if($booking->low_confidence)
                                <td class="cursor-pointer text-link" onclick="edit_vehicle_num({{$booking->id}})">{{$booking->vehicle_num}}</td>
                                @else
                                <td>{{$booking->vehicle_num}}</td>
                                @endif
                                <td>{{$booking->amount != 'N/A' ? number_format($booking->amount, 2, ',', '.') : '0,00'}} &euro;</td>
                                <td>{{$booking->checkin}}</td> 
                                <td>{{$booking->checkout}}</td> 
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="edit_booking_vehicle_num" class="modal fade" tabindex="-1" role="dialog" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<!-- start - This is for export functionality only -->
<script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
<script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
<script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
<script src="{{asset('plugins/components/switchery/dist/switchery.min.js')}}"></script>
<script src='{{asset('plugins/components/moment/moment.js')}}'></script>
<script src='{{asset('plugins/components/fullcalendar/fullcalendar.js')}}'></script>
<script src="{{asset('plugins/components/Magnific-Popup-master/dist/jquery.magnific-popup.min.js')}}"></script>
<script src="{{asset('plugins/components/Magnific-Popup-master/dist/jquery.magnific-popup-init.js')}}"></script>
<script src="{{asset('plugins/components/owl.carousel/owl.carousel.min.js')}}"></script>
<script src="{{asset('plugins/components/owl.carousel/owl.custom.js')}}"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('js/db3.js')}}"></script>
<!-- end - This is for export functionality only -->
<script>
                                            $(function () {
                                            jQuery.extend(jQuery.fn.dataTableExt.oSort, {
                                            "date-euro-pre": function (a) {
                                            var x;
                                            if ($.trim(a) !== '') {
                                            var frDatea = $.trim(a).split(' ');
                                            var frTimea = (undefined != frDatea[1]) ? frDatea[1].split(':') : [00, 00, 00];
                                            var frDatea2 = frDatea[0].split('/');
                                            x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + ((undefined != frTimea[2]) ? frTimea[2] : 0)) * 1;
                                            } else {
                                            x = Infinity;
                                            }

                                            return x;
                                            },
                                                    "date-euro-asc": function (a, b) {
                                                    return a - b;
                                                    },
                                                    "date-euro-desc": function (a, b) {
                                                    return b - a;
                                                    }
                                            });
                                            $('#transaction_table').DataTable({
                                            dom: 'Bfrtip',
                                                    buttons: [
                                                            'copy', 'csv', 'excel', 'pdf', 'print'
                                                    ],
                                                    columnDefs: [
                                                    {type: 'date-euro', targets: 5},
                                                    {type: 'date-euro', targets: 6}
                                                    ],
                                                    "pageLength": 25,
                                                    "order": [[5, "desc"]]
                                            });
                                            });
</script>
@endpush