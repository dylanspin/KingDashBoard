@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="box-title">@lang('at_location.transactions')</h4>
                    </div>
                </div>
                @if($type == 'tr')
                <div class="table-responsive">
                    <table id="transaction_table" class="table">
                        <thead>
                            <tr>
                                <th>@lang('at_location.img')</th>

                                <th>@lang('at_location.vehicle')</th>
                                <th>@lang('at_location.type')</th>
                                <th>Time</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $key => $transaction)
                            <tr>
                                <td><img src="{{$transaction['image_path']}}" class="img img-responsive h-75 w-75"></td>
                                <td>{{$transaction['vehicle']}}</td>
                                <td>{{$transaction['type']}}</td>
                                <td>{{$transaction['time']}}</td>
                                <td><a href="{{ url('/transaction/1/'.$transaction['id'])}}" class="text-link">View Details</a></td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif($type == 'ptr')
                <div class="table-responsive">
                    <table id="transaction_table" class="table">
                        <thead>
                            <tr>
                                <th>@lang('at_location.img')</th>
                                <th>@lang('at_location.vehicle')</th>
                                <th>@lang('at_location.type')</th>
                                <th>Time</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $key => $transaction)
                            <tr>
                                <td><img src="{{$transaction['image_path']}}" class="img img-responsive h-75 w-75"></td>
                                <td>{{$transaction['vehicle']}}</td>
                                <td>{{$transaction['type']}}</td>
                                <td>{{$transaction['time']}}</td>
                                <td><a href="{{ url('/transaction/1/'.$transaction['id'])}}" class="text-link">View Details</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif($type == 'pr')
                <div class="table-responsive">
                    <table id="transaction_table" class="table">
                        <thead>
                            <tr>
                                <th>@lang('at_location.img')</th>
                                <th>@lang('at_location.vehicle')</th>
                                <th>@lang('at_location.type')</th>
                                <th>Time</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $key => $transaction)
                            <tr>
                                <td><img src="{{$transaction['image_path']}}" class="img img-responsive h-75 w-75"></td>
                                <td>{{$transaction['vehicle']}}</td>
                                <td>{{$transaction['type']}}</td>
                                <td>{{$transaction['time']}}</td>
                                <td><a href="{{ url('/transaction/1/'.$transaction['id'])}}" class="text-link">View Details</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif($type == 'ptv')
                <div class="table-responsive">
                    <table id="transaction_table_vehicle" class="table">
                        <thead>
                            <tr>
                                <th>Payment Identifier</th>
                                <th>@lang('at_location.vehicle')</th>
                                <th>@lang('at_location.type')</th>
                                <th>Time</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $key => $transaction)
                            <tr>
                                <td>{{$transaction['id']}}</td>
                                <td>{{$transaction['vehicle']}}</td>
                                <td>{{$transaction['type']}}</td>
                                <td>{{$transaction['time']}}</td>
                                <td><a href="{{ url('/transaction/2/'.$transaction['id'])}}" class="text-link">View Details</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif($type == 'ptp')
                <div class="table-responsive">
                    <table id="transaction_table_person" class="table">
                        <thead>
                            <tr>
                                <th>@lang('at_location.name')</th>
                                <th>@lang('at_location.type')</th>
                                <th>Time</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $key => $transaction)
                            <tr>
                                <td>{{$transaction['quantity']}} Person(s)</td>
                                <td>{{$transaction['type']}}</td>
                                <td>{{$transaction['time']}}</td>
                                <td><a href="{{ url('/transaction/3/'.$transaction['id'])}}" class="text-link">View Details</a></td>


                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif



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
            {type: 'date-euro', targets: 3}
        ],
        "pageLength": 25,
        "order": [[3, "desc"]]
    });
    $('#transaction_table_vehicle').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        columnDefs: [
            {type: 'date-euro', targets: 3}
        ],
        "pageLength": 25,
        "order": [[3, "desc"]]
    });
    $('#transaction_table_person').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        columnDefs: [
            {type: 'date-euro', targets: 2}
        ],
        "pageLength": 25,
        "order": [[2, "desc"]]
    });
});
</script>
@endpush