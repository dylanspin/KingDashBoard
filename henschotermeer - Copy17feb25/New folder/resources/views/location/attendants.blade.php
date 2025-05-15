@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<style>
    .color-star-fill {
        color: #fde16d;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- .row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('attendants.attendants')</h3>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('attendants.no')</th>
                                        <th>@lang('attendants.users')</th>
                                        <th>@lang('attendants.vehicle')</th>
                                        <th>@lang('attendants.phone')</th>
                                        <th>@lang('attendants.type')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendants as $key=>$attendant)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$attendant->users}}</td>
                                        <td>{{$attendant->vehicle}}</td>
                                        <td>{{$attendant->phone}}</td>
                                        <td>{{$attendant->type}}</td>
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