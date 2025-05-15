@extends('layouts.master')

@push('css')
    <link href="{{ asset('plugins/components/datatables/jquery.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid">
        <!-- .row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <h3 class="box-title pull-left">@lang('access-rules.manage_rule')</h3>
                    <a class="btn btn-success pull-right" href="{{ url('manage-rules/create') }}"><i class="icon-plus"></i>
                        @lang('access-rules.add_rule')</a>
                    <div class="clearfix"></div>
                    <div class="pull-right">
                        @lang('access-rules.status_enable'):{{ $enable ?? "" }}
                    </div>
                    <br>
                    <div class="pull-right">
                        @lang('access-rules.status_disable'):{{ $disable ?? "" }}
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="listingDataTable" class="table table-striped devices_listing">
                                    <thead>
                                        <tr>
                                            <th>@lang('devices.no')</th>
                                            <th>@lang('access-rules.rule_name')</th>
                                            <th>@lang('access-rules.rule_status')</th>
                                            <th>@lang('access-rules.rule_actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rules as $key => $rule)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                
                                                <td>
                                                    {{ $rule->name ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    @if (isset($rule->access->enable) && $rule->access->enable )
                                                        <span class="badge badge-success">@lang('access-rules.status_enable')</span>
                                                    @else
                                                        <span class="badge badge-danger">@lang('access-rules.status_disable')</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('manage-rules/edit/' . $rule->id) }}"
                                                        class="btn btn-primary"><i class="fa fa-edit"></i></a>
                                                    {{-- <a href="{{ url('manage-rules/delete/' . $rule->id) }}"
                                                        class="btn btn-danger"><i class="fa fa-trash"></i></a> --}}
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

        <div id="view_sync_settings" class="modal fade view_sync_settings" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content col-md-12 p-0">

                </div>
            </div>
        </div>
        @include('layouts.partials.right-sidebar')
    </div>
@endsection

@push('js')
    <script src="{{ asset('plugins/components/toast-master/js/jquery.toast.js') }}"></script>
    <script src="{{ asset('plugins/components/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="{{ asset('/js/device.js') }}"></script>
    <script src="{{ asset('/js/datatable_lang.js') }}"></script>
    <script>
        $(function() {
            var data_table_locale = json_lang_en;
            if (lang_locale === 'nl') {
                data_table_locale = json_lang_nl
            }
            $('#listingDataTable').DataTable({
                "columns": [
                    null, null, null, null, null, null, {
                        "orderable": false
                    }
                ],
                "pageLength": 25,
                "oLanguage": data_table_locale
            });
        });
    </script>
    <script>
        $(document).ready(function() {

            $(document).on('click', '.delete', function(e) {
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
                    callback: function(result) {
                        if (result) {
                            window.location.href = "{{ url('devices/delete') }}/" + id;
                        }
                    }
                });
            });
            @if (\Session::has('message'))
                $.toast({
                    heading: '{{ session()->get('heading') }}',
                    position: 'top-center',
                    text: '{{ session()->get('message') }}',
                    loaderBg: '#ff6849',
                    icon: '{{ session()->get('icon') }}',
                    hideAfter: 5000,
                    stack: 6
                });
            @endif
        });
    </script>
@endpush
