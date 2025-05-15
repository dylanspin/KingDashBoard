@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
<link href="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.css')}}" rel="stylesheet">
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
                <h3 class="box-title pull-left">@lang('bounces_email.bounces_email')</h3>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <form method="post" action="{{url('/email/bounces')}}" class="col-md-12 custom-search-form">
                        <div class="col-md-6">
                            
                        </div>
                        <div class="col-md-6 text-left">
                            @csrf
                            <div class="form-group col-md-4">
                                <select class="form-control" name="search_type">
                                    <option value="" {{ $search_type == '' ? 'selected' :  ''}}>Search In</option>
                                    <option value="email" {{ $search_type == 'email' ? 'selected' :  ''}}>Email</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <input 
                                    type="text" 
                                    name="search_val" 
                                    value="{{$search_val}}" 
                                    class="form-control" 
                                    placeholder="Search">
                            </div>
                            <div class="form-group col-md-4">
                                <input type="submit" name="search_btn" class="btn btn-primary btn-sm" value="Search">
                                <a href="{{url('/email/bounces')}}" class="btn btn-danger btn-sm">Reset</a>
                            </div>
                        </div>
<!--                        <div class="col-md-12 text-left">
                            <div class="form-group col-md-3">
                                <input type="submit" name="search_btn" class="btn btn-primary btn-sm" value="Search">
                                <a href="{{url('/email/bounces')}}" class="btn btn-danger btn-sm">Reset</a>
                                <input type="submit" name="export_btn" class="btn btn-primary btn-sm" value="Export to Excel">
                            </div>
                        </div>-->
                    </form>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('bounces_email.no')</th>
                                        <th>@sortablelink('email',trans('bounces_email.email'))</th>
                                        <th>@sortablelink('reason',trans('bounces_email.reason'))</th>
                                        <th>@sortablelink('created_at',trans('bounces_email.bounce_date'))</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($email_bounces) > 0)
                                    @foreach($email_bounces as $key => $email_bounce)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$email_bounce->email}}</td>
                                        <td>{!! $email_bounce->reason !!}</td>
                                        <td>{{date('d/m/Y h:i A', strtotime($email_bounce->created_at))}}</td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr>
                                        <td colspan="4" style="text-align:center;">No Record Found.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                            {!! $email_bounces->appends(\Request::except('page'))->render() !!}
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
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
<script src="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
$(function () {
    $('.input-daterange-datepicker').daterangepicker({
        autoUpdateInput: false,
        buttonClasses: ['btn', 'btn-xs'],
        applyClass: 'btn-danger',
        cancelClass: 'btn-inverse'
    });
    $('.input-daterange-datepicker').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY')+' - '+picker.endDate.format('MM/DD/YYYY'));
    });
    $('.input-daterange-datepicker').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
@endpush