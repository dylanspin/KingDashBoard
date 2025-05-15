@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/footable/css/footable.bootstrap.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
<link href="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.skinModern.css')}}" rel="stylesheet">
<style>
    .footable-filtering{
        display:none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('sidebar.licence_plate_logs')</h3>
                <div class="clearfix"></div>
                @if (session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                <hr>
                <div class="col-md-12 text-right">
                    <form method="post" action="{{url('logs/licence-plates')}}" class="col-md-12 text-right custom-search-form">
                        @csrf
                        <div class="form-group col-md-3">
                            <select class="form-control" name="search_device">
                                <option value="" {{ $search_device == '' ? 'selected' :  ''}}>@lang('booking.choose_device')</option>
                                @foreach($plate_readers as $plate_reader)
                                <option value="{{$plate_reader->id}}" {{ $search_device == $plate_reader->id ? 'selected' :  ''}}>{{$plate_reader->device_name.' ( '.$plate_reader->device_direction.' )'}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <select class="form-control" name="search_type">
                                <option value="" {{ $search_type == '' ? 'selected' :  ''}}>@lang('booking.choose_confidence_level')</option>
                                <option value="low" {{ $search_type == 'low' ? 'selected' :  ''}}>@lang('booking.low')</option>
                                <option value="high" {{ $search_type == 'high' ? 'selected' :  ''}}>@lang('booking.high')</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <input 
                                type="text" 
                                name="search_val" 
                                value="{{$search_val}}" 
                                class="form-control" 
                                placeholder="@lang('booking.search_vehicle_no')">
                        </div>
                        <div class="form-group col-md-3">
                            <input type="submit" name="search_btn" class="btn btn-primary p-l-5 p-r-5" value="@lang('bounces_email.search')">
                            <input type="submit" name="reset_btn" class="btn btn-info p-l-5 p-r-5" value="@lang('bounces_email.reset')">
                            <input type="submit" name="download_btn" class="btn btn-default p-l-5 p-r-5" value="@lang('booking.download')">
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive text-center">
                            <table 
                                class="table" 
                                data-sorting="true" 
                                data-filtering="true" 
                                data-filter-connectors="false"
                                data-show-toggle="false"
                                data-toggle-column="first"
                                data-paging="false">
                                <thead>
                                    <tr>
                                        <th data-sortable="false" class="text-center">@lang('at_location.img')</th>
                                        <th class="text-center">@lang('booking.device')</th>
                                        <th data-sortable="false" class="text-center">@sortablelink('vehicle_num', trans('booking.vehicle_no'))</th>
                                        <th data-sortable="false" class="text-center">@sortablelink('confidence', trans('booking.confidence'))</th>
                                        <th data-sortable="false" class="text-center">@sortablelink('created_at', trans('user-list.added_at'))</th>
                                        <th data-sortable="false" class="text-center">@lang('booking.reason')</th>
                                        <th data-sortable="false" class="text-center">@lang('user-list.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $key=>$booking)
                                    <tr class="record{{$booking->id}}">
                                        <td>
                                            <a href="{{url('/').$booking->file_path}}" target="_blank">
                                                <img src="{{$booking->file_path}}" class="img img-responsive h-75 w-75">
                                            </a>
                                        </td>
                                        <td>{{$booking->location_devices ? $booking->location_devices->device_name ?: 'N/A' : 'N/A'}}</td>
                                        <td>{{$booking->vehicle_num ? $booking->vehicle_num : 'N/A'}}</td>
                                        <td>{{$booking->confidence ? $booking->confidence : 'N/A'}}</td>
                                        <td>{{date('d/m/Y H:i', strtotime($booking->created_at))}}</td>
                                        <td class="w-35p">{{$booking->reason ? $booking->reason : 'N/A'}}</td>
                                        <td>
                                            <a 
                                                data-id="{{$booking->id}}" 
                                                title="@lang('booking.download_image')" 
                                                class="btn btn-info btn-sm download_image"
                                                style="cursor:pointer;">
                                                 <i class="fa fa-download" aria-hidden="true"></i> 
                                            </a>
                                            <a data-id="{{$booking->id}}" class="btn btn-info btn-sm view_details" style="cursor:pointer;">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr id="extra_details_{{$booking->id}}" class="hidden">
                                        <td colspan="9">
                                            <div class="col text-left">
                                                @if(isset($logs) && count($logs) > 0)
                                                @foreach ($logs as $key => $log)
                                                <p>
                                                    <i class="fa fa-arrow-right"></i>
                                                    {{ $log->created_at }},
                                                    @if (strtolower($log->type) == "info")
                                                    <span class="badge badge-primary"> {{ $log->type  }}</span>
                                                    @endif
                                                    @if (strtolower($log->type) == "debug")
                                                    <span class="badge badge-info"> {{ $log->type  }}</span>
                                                    @endif
                                                    @if (strtolower($log->type) == "error")
                                                    <span class="badge badge-danger"> {{ $log->type  }}</span>
                                                    @endif
                                                    @if (strtolower($log->type) == "critical")
                                                    <span class="badge badge-danger"> {{ $log->type  }}</span>
                                                    @endif
                                                    @if (strtolower($log->type) == "warning")
                                                    <span class="badge badge-warning"> {{ $log->type  }}</span>
                                                    @endif
                                                    ,
                                                    {{$log->message}}
                                                </p>
                                                @endforeach
                                                @else
                                                <p>no records exist</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                           {{$bookings->links()}}
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
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('plugins/components/footable/js/footable.min.js') }}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>

<script src="{{ asset('plugins/components/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
<script src="{{asset('plugins/components/icheck/icheck.min.js')}}"></script>
<script src="{{asset('plugins/components/icheck/icheck.init.js')}}"></script>
<!--<script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>-->
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"
type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<!-- Clock Plugin JavaScript -->
<script src="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.js')}}"></script>
<!-- Color Picker Plugin JavaScript -->
<script src="{{asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asColor.js')}}"></script>
<script src="{{asset('plugins/components/jquery-asColorPicker-master/libs/jquery-asGradient.js')}}"></script>
<script src="{{asset('plugins/components/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js')}}"></script>
<script src="{{asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider.min.js')}}"></script>
<script src="{{asset('plugins/components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider-init.js')}}"></script>
<script src="{{ asset('/js/jquery.mask.js') }}"></script>
<script>
$(document).ready(function () {
    $(document).on('click', '.download_image', function (e) {
        var id = $(this).data('id');
        window.location.href = "{{url('logs/licence-plates/download')}}/" + id;
    });
    $(document).on('click', '.view_details', function(e) {
            var id = $(this).data('id');
            window.location.href = "{{url('logs/access-detials/details/')}}/" + id;
        });
    $(document).on('click', '.show_details', function(e) {
            var id = "#extra_details_" + e.target.id;
            var icon_id = "#" + e.target.id;
            if ($(id).hasClass('hidden')) {
                $(id).removeClass('hidden');
                $(icon_id).addClass('fa-minus');
                $(icon_id).removeClass('fa-plus');
            } else {
                $(id).addClass('hidden');
                $(icon_id).addClass('fa-plus');
                $(icon_id).removeClass('fa-minus');
            }

        });
    @if (\Session::has('message'))
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
$('.table').footable();

</script>
@endpush