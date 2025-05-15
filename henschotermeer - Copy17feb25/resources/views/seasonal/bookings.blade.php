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
                    <h3 class="box-title pull-left">@lang('sidebar.bookings')</h3>
                    <div class="clearfix"></div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <form method="post" action="{{url('/tommy-reservations/bookings')}}" class="col-md-6 text-right custom-search-form">
                                @csrf
                                <div class="form-group col-md-3">
                                    <select class="form-control" name="search_type">
                                        <option value="" {{ $search_type == '' ? 'selected' :  ''}}>Search In</option>
                                        <option value="first_name" {{ $search_type == 'first_name' ? 'selected' :  ''}}>Name</option>
                                        <option value="email" {{ $search_type == 'email' ? 'selected' :  ''}}>Email</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" name="search_val" value="{{$search_val}}" class="form-control" placeholder="Search">
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" name="booking_id" id="booking_id" class="form-control" placeholder="Booking Id" value={{ $booking_id ?? "" }}>
                                </div>
                                <div class="form-group col-md-3">
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
                                        <th>@sortablelink('first_name',trans('white-list.first_name'))</th>
                                        <th>@sortablelink('last_name',trans('white-list.last_name'))</th>
                                        <th>@sortablelink('email',trans('tommy-reservation.email'))</th>
                                        <th>@lang('tommy-reservation.t_members')</th>
                                        <th>@sortablelink('ref_booking_id',trans('booking.booking_id'))</th>
                                        <th>@sortablelink('checkin_time',trans('tommy-reservation.arrival_date'))</th>
                                        <th>@sortablelink('checkout_time',trans('tommy-reservation.departure_date'))</th>
                                        <th>@lang('tommy-reservation.actions')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($onLineBookings as $booking)
                                        <tr>
                                            <td>{{$booking->first_name ? $booking->first_name : 'N/A'}}</td>
                                            <td>{{$booking->last_name ? $booking->last_name : 'N/A'}}</td>
                                            <td>{{$booking->email ? $booking->email : 'N/A'}}</td>
                                            <td>{{$booking->members}}</td>
                                            <td>{{$booking->ref_booking_id ?? "N/A"}}</td>
                                            <td>{{date('d/m/Y', strtotime($booking->checkin_time))}}</td>
                                            <td>{{date('d/m/Y', strtotime($booking->checkout_time))}}</td>
                                            <td>
                                                <a 
                                                    title="@lang('tommy-reservation.send_ticket')" 
                                                    class="btn btn-info btn-sm mt-10" 
                                                    href="{{url('tommy-reservations/bookings/send-ticket/'.$booking->id)}}">
                                                    <i class="fa fa-send-o"></i>
                                                </a>
                                                &nbsp;
                                                <a 
                                                    title="@lang('tommy-reservation.delete')" 
                                                    class="delete btn btn-danger btn-sm mt-10"
                                                    data-id="{{$booking->id}}" 
                                                    style="cursor:pointer;">
                                                    <i class="fa fa-trash-o"></i>
                                                </a>
                                                &nbsp;
                                                <a 
                                                    title="@lang('tommy-reservation.t_members')" 
                                                    class="btn btn-success btn-sm mt-10"
                                                    href="{{url('tommy-reservations/bookings/view-members/'.$booking->id)}}">
                                                    <i class="icon-list"></i>
                                                </a>
                                                <a 
                                                    
                                                    title="@lang('tommy-reservation.print')" 
                                                    class="btn btn-info btn-sm mt-10"
                                                    href="{{url('tommy-reservations/bookings/print-all/'.$booking->id)}}">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                {!! $bookings->appends(\Request::except('page'))->render() !!}
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
//$(function () {
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
//            null, null, null, null, null, null, {"orderable": false}
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
//}); 
</script>
<script>
$(function () {
    $(document).on('click','.delete',function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Destroy Member?",
            message: "Are you sure want to delete bookings?",
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
                    window.location.href = "{{url('tommy-reservations/bookings/delete')}}/"+id;
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
});
</script>
@endpush