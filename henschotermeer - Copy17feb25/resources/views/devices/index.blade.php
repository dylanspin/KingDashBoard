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
                <h3 class="box-title pull-left">@lang('devices.loc_devices')</h3>
                <a  class="btn btn-success pull-right" href="{{url('devices/create')}}"><i class="icon-plus"></i> @lang('devices.add_device')</a>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped devices_listing">
                                <thead>
                                    <tr>
                                        <th>@lang('devices.num')</th>
                                        <th>@lang('devices.device_name')</th>
                                        <th>@lang('devices.device_type')</th>
                                        <th>@lang('devices.device_directions')</th>
                                        <th>@lang('devices.device_ip')</th>
                                        <th>@lang('devices.device_port')</th>
                                        <th class="w-300">@lang('devices.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($devices as $key=>$device)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$device->device_name}}</td>
                                        <td>{{$device->available_devices->name}}</td>
                                        <td class=" text-capitalize">{{ str_replace('-',' ',$device->device_direction)}}</td>
                                        <td>{{$device->device_ip}}</td>
                                        <td>{{$device->device_port}}</td>
                                        <td>
                                            <a data-toggle="tooltip" title="@lang('devices.edit')"
                                               class="btn btn-info btn-sm" 
                                               href="{{url('devices/edit/'.$device->id)}}">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true" ></i>
                                            </a>

<!--                                            <a  data-toggle="tooltip" title="@lang('devices.delete')"
                                                class="delete btn btn-danger btn-sm"
                                                data-id="{{$device->id}}" 
                                                style="cursor:pointer;">
                                                <i class="fa fa-trash-o" ></i>
                                            </a>-->
                                            @if($device->is_synched)
                                            <a data-device_id="{{$device->id}}" data-toggle="tooltip" title="@lang('devices.sync')" class="sync_device sync_details_btn_{{$device->id}} btn btn-success btn-sm" >
                                                <i class="fa fa-wifi" ></i>
                                            </a>
                                            <a data-toggle="tooltip" title="@lang('devices.update_device_time')" 
                                               class=" btn btn-info btn-sm update_server_time_{{$device->id}}" 
                                               href="{{url('devices/update-server-time/'.$device->id)}}">
                                                <i class="fa fa-clock-o"  aria-hidden="true"></i> 
                                            </a>
                                            <a  data-toggle="tooltip" title="@lang('devices.initialize')"
                                                class="hidden btn btn-success btn-sm initialize_btn_{{$device->id}}"
                                                href="{{url('devices/initialize/'.$device->id)}}">
                                                <i class="fa fa-wifi" ></i>
                                            </a>
                                            <a data-id="{{$device->id}}" href="logs/access-detials/device-details/{{$device->id}}" class="btn btn-info btn-sm view_details" style="cursor:pointer;">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            @else
                                            <a data-device_id="{{$device->id}}" data-toggle="tooltip" title="@lang('devices.sync')" class="hidden sync_details_btn_{{$device->id}} sync_device btn btn-success btn-sm" >
                                                <i class="fa fa-wifi" ></i>
                                            </a>
                                            <a data-toggle="tooltip" title="@lang('devices.update_device_time')" 
                                               class=" hidden btn btn-info btn-sm update_server_time_{{$device->id}}" 
                                               href="{{url('devices/update-server-time/'.$device->id)}}">
                                                <i class="fa fa-clock-o"  aria-hidden="true"></i> 
                                            </a>
                                            <a  data-toggle="tooltip" title="@lang('devices.initialize')"
                                                class="btn btn-success btn-sm initialize_btn_{{$device->id}}"
                                                href="{{url('devices/initialize/'.$device->id)}}">
                                                <i class="fa fa-wifi" ></i>
                                            </a>
                                            @endif

                                        </td>
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

    <div id="view_sync_settings" class="modal fade view_sync_settings" tabindex="-1" role="dialog" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content col-md-12 p-0">

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
<script src="{{ asset('/js/device.js') }}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
$(function () {
    var data_table_locale = json_lang_en;
    if (lang_locale === 'nl') {
        data_table_locale = json_lang_nl
    }
    $('#listingDataTable').DataTable({
        "columns": [
            null, null, null, null, null, null, {"orderable": false}
        ],
        "pageLength": 25,
        "oLanguage": data_table_locale
    });
});
</script>
<script>
$(document).ready(function () {

$(document).on('click', '.delete', function (e) {
var id = $(this).data('id');
        bootbox.confirm({
        title: "Destroy Device?",
                message: "Are you sure want to delete device?",
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
                if (result){
                window.location.href = "{{url('devices/delete')}}/" + id;
                }
                }
        });
        });
        @if (\Session::has('message'))
        $.toast({
        heading: '{{session()->get('heading')}}',
                position: 'top-center',
                text: '{{session()->get('message')}}',
                loaderBg: '#ff6849',
                icon: '{{session()->get('icon')}}',
                hideAfter: 5000,
                stack: 6
        });
        @endif
        }
);


</script>
@endpush