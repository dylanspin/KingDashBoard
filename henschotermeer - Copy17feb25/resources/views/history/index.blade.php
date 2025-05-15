@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<style>
    .pagination-container {
    overflow-x: auto;
    white-space: nowrap;
}
</style>
@endpush
@section('content')
<div class="container-fluid">
    <!-- .row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('history.bookings')</h3>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
				<div class="col-md-12 text-right">
                        <form method="post" action="{{url('/history/search')}}" class="col-md-6 text-right custom-search-form">
                            @csrf
                            <div class="form-group col-md-4">
                                <select class="form-control" name="search_type">
                                    <option value="vehicle_number"{{$search_type =="vehicle_num" ? "selected" :""}}>@lang('history.vehicle_num')</option>
                                    <option value="name"{{$search_type =="name" ? "selected" :""}}>@lang('history.name')</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <input type="text" name="search_val"  class="form-control" placeholder="@lang('bounces_email.search')" value={{$search_val ?? ""}}>
                            </div>
                            <div class="form-group col-md-4">
                                <input type="submit" name="search_btn" class="btn btn-primary" value="@lang('bounces_email.search')">
                                <input type="submit" name="reset_btn" class="btn btn-danger" value="@lang('bounces_email.reset')">
                            </div>
                        </form>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable1" class="table table-striped tommy_reservation_listing">
                                <thead>
                                    <tr>
                                        <th>@lang('history.no')</th>
                                        <th>@sortablelink('vehicle_num',trans('history.name'))</th>
                                        <th>@sortablelink('vehicle_num',trans('history.vehicle_num'))</th>
                                        <th>@sortablelink('date_of_arrival',trans('tommy-reservation.arrival_date'))</th>
                                        <th>@sortablelink('date_of_departure',trans('tommy-reservation.departure_date'))</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($history as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->first_name}} {{$item->last_name}} </td>
                                        <td>{{$item->vehicle_num ?? "N/A"}}</td>
                                       
                                        <td>{{$item->checkin_time}}</td>
                                        <td>{{$item->checkout_time}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {!! $history->appends(\Request::except('page'))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>

$(document).ready(function () {
    
    $(document).on('click','.delete',function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "{{ __('javascript.destroy_member') }}",
            message: "{{ __('javascript.are_you_sure_delete_member') }}",
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> {{ __('javascript.Cancel') }}',
                    className: 'btn-danger'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> {{ __('javascript.Confirm') }}',
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
            title: "{{ __('javascript.send_ticket') }}",
            message: "{{ __('javascript.are_you_sure_to_Send_ticket') }}",
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> {{ __('javascript.Cancel') }}',
                    className: 'btn-danger'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> {{ __('javascript.Confirm') }}',
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