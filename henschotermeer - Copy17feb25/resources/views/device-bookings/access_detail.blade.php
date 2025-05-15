<?php

use Illuminate\Support\Facades\DB;
?>

@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/footable/css/footable.bootstrap.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
<link href="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.skinModern.css')}}" rel="stylesheet">
<style>
    .footable-filtering {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">Device Logs</h3>
                <div class="clearfix"></div>
                <hr>

                <div class="row">
                    <div class="col-md-12 text-left">
                        <form method="post" action="{{url('logs/access-detials/details/'.$id)}}" class="col-md-12 custom-search-form">
                            @csrf
                            <input type="hidden" name="device_booking_id" value="{{$id}}">

                            <div class="form-group col-md-3 text-center">
                                <input type="text" class="form-control" name="datefilter" value="{{$datefilter}}" autocomplete="off" placeholder="search by time" />
                            </div>


                            <div class="form-group col-md-2">
                                <select class="form-control" name="type">
                                    <option value="">Choose type</option>
                                    @if($type == strtolower('debug'))
                                    <option value="debug" selected>debug</option>
                                    @else
                                    <option value="debug">debug</option>
                                    @endif

                                    @if($type == strtolower('info'))
                                    <option value="info" selected>info</option>
                                    @else
                                    <option value="info">info</option>
                                    @endif

                                    @if($type == strtolower('error'))
                                    <option value="error" selected>error</option>
                                    @else
                                    <option value="error">error</option>
                                    @endif

                                    @if($type == strtolower('warning'))
                                    <option value="warning" selected>warning</option>
                                    @else
                                    <option value="warning">warning</option>
                                    @endif

                                    @if($type == strtolower('critical'))
                                    <option value="critical" selected>critical</option>
                                    @else
                                    <option value="critical">critical</option>
                                    @endif

                                </select>
                            </div>

                            <div class="form-group col-md-2">
                                <input type="text" name="message" class="form-control" value="{{$message}}" placeholder="search by message">
                            </div>

                            <div class="form-group col-md-2">
                                <input type="submit" name="search_btn" class="btn btn-primary p-l-5 p-r-5" value="@lang('bounces_email.search')">
                                <a href="{{ url('/logs/access-detials/details/'.$id) }}" type="submit" name="reset_btn" class="btn btn-danger">Reset</a>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-12">

                        <div class="table-responsive text-left">
                            <table class="table" data-sorting="true" data-filtering="true" data-filter-connectors="false" data-show-toggle="false" data-toggle-column="first" data-paging="false">
                                <thead>
                                    <tr>
                                        <th data-sortable="false" class="text-left">@sortablelink('created_at', trans('booking.timestamp'))</th>
                                        <th data-sortable="false" class="text-left">@lang('booking.type')</th>
                                        <th data-sortable="false" class="text-left">@lang('booking.message')</th>
                                        <th data-sortable="false" class="text-left">@lang('user-list.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($device_logs) > 0)
                                    @foreach ($device_logs as $key => $log)
                                    <tr>
                                        <td>
                                            {{ date('d-m-Y H:i:s',strtotime($log->created_at)) }}
                                        </td>
                                        <td>
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
                                        </td>

                                        <td>
                                            {{$log->message}}
                                        </td>

                                        <td>
                                            @if ($log->file_path)
                                            <i id="{{$log->id}}" class="fa fa-plus f-30 show_details cursor-pointer"></i>
                                            @endif

                                        </td>
                                    </tr>

                                    <tr id="extra_details_{{$log->id}}" class="hidden">
									     @if(isset($log->file_path))
                                        @php $log_images = DB::table('device_logs')
                                        ->where('device_booking_id', $log->device_booking_id)
                                       ->whereNotNull('file_path')->where('created_at',$log->created_at)
                                        ->get() @endphp
                                        @foreach ($log_images as $log_image)
                                        <td>
                                            @if ($log_image->file_path != null)
                                                <img src="{{$log_image->file_path}}" style="height: 150px;width: 200px;">
                                            @endif
                                        </td>
                                        @endforeach
										@endif

                                    </tr>

                                    @endforeach
                                    @else
                                    <tr>
                                        <td>No records found!</td>
                                    </tr>
                                    @endif

                                </tbody>
                            </table>
                            {{$device_logs->links()}}
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js" type="text/javascript"></script>
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
    $(document).ready(function() {
        $(document).on('click', '.download_image', function(e) {
            var id = $(this).data('id');
            window.location.href = "{{url('logs/licence-plates/download')}}/" + id;
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

        $(function() {

            $('input[name="datefilter"]').daterangepicker({
                autoUpdateInput: false,
                timePicker: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY/MM/DD H:mm'
                }
            });

            $('input[name="datefilter"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY/MM/DD H:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD H:mm'));
            });

            $('input[name="datefilter"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

        });

        @if(\Session::has('message'))
        $.toast({
            heading: '{{session()->get('
            heading ')}}',
            position: 'top-center',
            text: '{{session()->get('
            message ')}}',
            loaderBg: '#ff6849',
            icon: '{{session()->get('
            icon ')}}',
            hideAfter: 3000,
            stack: 6
        });
        @endif
    });
    // $('.table').footable();
</script>
@endpush