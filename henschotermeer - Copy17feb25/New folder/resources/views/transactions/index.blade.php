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
                <div class="table-responsive">
                    <table id="transaction_table" class="table">
                        <thead>
                            <tr>
                                <!--<th>#</th>-->
                                <th>@lang('at_location.img')</th>
                                <th>@lang('at_location.name')</th>
                                <th>@lang('at_location.contact_details')</th>
<!--                                <th>@lang('at_location.email')</th>
                                <th>@lang('at_location.phone')</th>-->
                                <th>@lang('at_location.vehicle')</th>
                                <th>@lang('at_location.type')</th>
                                <th>@lang('at_location.entry') / @lang('at_location.exit')</th>
                                <!--<th>@lang('at_location.amount')</th>-->
                                <th>@lang('at_location.check_in')</th>
                                <th>@lang('at_location.check_out')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($get_last_5_transactions as $key => $transaction)
                            <tr>
                                <td><img src="{{$transaction->image}}" class="img img-responsive h-75 w-75"></td>
                                <td><a href="{{ url('/vehicle/'.$transaction->id)}}" class="text-link">{{$transaction->name}}</a></td>
                                <td>
                                    <?php
                                    if($transaction->email != 'N/A' && $transaction->phone_number != 'N/A'){
                                        echo $transaction->email.'('.$transaction->phone_number.')';
                                    }
                                    else if($transaction->email == 'N/A' && $transaction->phone_number != 'N/A'){
                                        echo $transaction->phone_number;
                                    }
                                    else if($transaction->email != 'N/A' && $transaction->phone_number == 'N/A'){
                                        echo $transaction->email;
                                    }
                                    else{
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>{{$transaction->vehicle}}</td>
                                <td>{{$transaction->type}}</td>
                                <td>{{$transaction->entry_device}} / {{$transaction->exit_device}}</td>
                                <td>{{$transaction->check_in}}</td>
                                <td>{{$transaction->check_out}}</td> 
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
        } 
        else {
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
            {type: 'date-euro', targets: 6},
            {type: 'date-euro', targets: 7}
        ],
        "pageLength": 25,
        "order": [[6, "desc"]]
    });
}); 
</script>
@endpush