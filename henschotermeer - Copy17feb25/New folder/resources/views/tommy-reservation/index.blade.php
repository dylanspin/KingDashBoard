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
                <h3 class="box-title pull-left">@lang('tommy-reservation.title')</h3>
                <a  class="btn btn-success pull-right" href="{{url('tommy-reservations/import')}}">
                    <!--<i class="icon-plus"></i> @lang('tommy-reservation.import_data')-->
                        <img src="{{asset('/plugins/images/icons/Import.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:30px;padding:0px;"> @lang('tommy-reservation.import_data')
                </a>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <form method="post" action="{{url('/tommy-reservations/')}}" class="col-md-6 text-right custom-search-form">
                            @csrf
                            <div class="form-group col-md-4">
                                <select class="form-control" name="search_type">
                                    <option value="" {{ $search_type == '' ? 'selected' :  ''}}>Search In</option>
                                    <option value="email" {{ $search_type == 'email' ? 'selected' :  ''}}>Email</option>
                                    <option value="license_plate" {{ $search_type == 'license_plate' ? 'selected' :  ''}}>Licence Plate</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <input type="text" name="search_val" value="{{$search_val}}" class="form-control" placeholder="Search">
                            </div>
                            <div class="form-group col-md-4">
                                <input type="submit" name="search_btn" class="btn btn-primary" value="Search">
                                <input type="submit" name="reset_btn" class="btn btn-danger" value="Reset">
                            </div>
                        </form>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped tommy_reservation_listing">
                                <thead>
                                    <tr>
                                        <th>@lang('tommy-reservation.no')</th>
                                        <th>@lang('tommy-reservation.family_name')</th>
                                        <th>@sortablelink('email',trans('tommy-reservation.email'))</th>
                                        <th>@lang('tommy-reservation.t_members')</th>
                                        <th>@sortablelink('date_of_arrival',trans('tommy-reservation.arrival_date'))</th>
                                        <th>@sortablelink('date_of_departure',trans('tommy-reservation.departure_date'))</th>
                                        <th>@sortablelink('license_plate',trans('tommy-reservation.license_plate'))</th>
                                        <th>@lang('tommy-reservation.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($tommyReservations) > 0)
                                    @foreach($tommyReservations as $key=>$tommyReservation)
                                    @if(isset($tommyReservation->tommy_reservation_childrens[0]))
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{ isset($tommyReservation->tommy_reservation_childrens[0]) ? $tommyReservation->tommy_reservation_childrens[0]->name : 'N/A'}}</td>
                                        <td>{{$tommyReservation->email ? $tommyReservation->email : 'N/A'}}</td>
                                        <td>{{$tommyReservation->total_members}}</td>
                                        <td>{{date('d/m/Y', strtotime($tommyReservation->date_of_arrival))}}</td>
                                        <td>{{date('d/m/Y', strtotime($tommyReservation->date_of_departure))}}</td>
                                        <td>{{$tommyReservation->license_plate ? $tommyReservation->license_plate : 'N/A'}}</td>
                                        <td>
                                            <a 
                                                title="@lang('tommy-reservation.send_ticket')" 
                                                class="btn btn-info btn-sm mt-10" 
                                                href="{{url('tommy-reservations/send-ticket/'.$tommyReservation->id)}}">
                                                <i class="fa fa-send-o"></i>
                                            </a>
                                            &nbsp;
                                            <a 
                                                title="@lang('tommy-reservation.delete')" 
                                                class="delete btn btn-danger btn-sm mt-10"
                                                data-id="{{$tommyReservation->id}}" 
                                                style="cursor:pointer;">
                                                <i class="fa fa-trash-o"></i>
                                            </a>
                                            &nbsp;
                                            <a 
                                                title="@lang('tommy-reservation.t_members')" 
                                                class="btn btn-success btn-sm mt-10"
                                                href="{{url('tommy-reservations/view-members/'.$tommyReservation->id)}}">
                                                <i class="icon-list"></i>
                                            </a>
                                            &nbsp;
                                          
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                    @else
                                    <tr>
                                        <td colspan="8" style="text-align:center;">No Record Found.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                            {!! $tommyReservations->appends(\Request::except('page'))->render() !!}
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
<script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
$(function () {
//    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
//        "date-euro-pre": function (a) {
//        var x;
//        if ($.trim(a) !== '') {
//            var frDatea = $.trim(a).split(' ');
//            var frTimea = (undefined != frDatea[1]) ? frDatea[1].split(':') : [00, 00, 00];
//            var frDatea2 = frDatea[0].split('/');
//            x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + ((undefined != frTimea[2]) ? frTimea[2] : 0)) * 1;
//        } 
//        else {
//            x = Infinity;
//        }
//        return x;
//        },
//        "date-euro-asc": function (a, b) {
//            return a - b;
//        },
//        "date-euro-desc": function (a, b) {
//            return b - a;
//        }
//    });
//    $('#listingDataTable').DataTable({
//        "columns": [
//            null, null, null, null, null, null, null, {"orderable": false}
//        ],
//        columnDefs: [
//            {type: 'date-euro', targets: 4},
//            {type: 'date-euro', targets: 5}
//        ],
//        "paging": false,
//        "bInfo" : false,
////        "pageLength": 25,
//        "order": [[4, "desc"]]
//    });
    });
</script>
<script>
$(document).ready(function () {
    $(document).on('click','.delete',function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Destroy Member?",
            message: "Are you sure want to delete member from Reservation List?",
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
                if(result){
                    window.location.href = "{{url('tommy-reservations/delete')}}/"+id;
        }
            }
});
    });

    $(document).on('click','.send-ticket',function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Send Ticket?",
            message: "Are you sure want to send Parking Ticket to user?",
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
                if(result){
                    window.location.href = "{{url('tommy-reservations/send-ticket')}}/"+id;
        }
            }
});
    });

    @if(\Session::has('message'))
        $.toast({
        heading: '{{session()->get('heading')}}',
            position: 'top-center',
            text: '{{session()->get('message')}}',
            loaderBg: '#ff6849',
            icon: '{{session()->get('icon')}}',
            hideAfter: 3000,
            stack: 6
        });
    @endif
})
</script>
@endpush