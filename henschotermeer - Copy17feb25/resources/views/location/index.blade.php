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
                    <h3 class="box-title pull-left">@lang('reviews.loc_ratings')</h3>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('reviews.title')</th>
                                        <th>@lang('reviews.address')</th>
                                        <th>@lang('reviews.rating')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{$locationOptions->title}}</td>
                                        <td>{{$locationOptions->address}}</td>
                                        <td>
                                            @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $locationOptions->star_rank)
                                            <i 
                                                class="icon-star fa-fw color-star-fill" 
                                                aria-hidden="true"></i>
                                            @else
                                            <i 
                                                class="icon-star fa-fw" 
                                                aria-hidden="true"></i>
                                            @endif
                                            @endfor
                                        </td>
                                    </tr>
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