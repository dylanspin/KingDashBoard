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
                    <h3 class="box-title pull-left">@lang('unwanted_character.manage_character')</h3>
                    <a class="btn btn-success pull-right" href="{{ url('unwanted-character/create') }}"><i class="icon-plus"></i>
                        @lang('unwanted_character.add_character')</a>
                    <div class="clearfix"></div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="listingDataTable" class="table table-striped devices_listing">
                                    <thead>
                                        <tr>
                                            <th>@lang('unwanted_character.character_unwanted')</th>
                                            <th>@lang('unwanted_character.valid_character')</th>
                                            <th>@lang('access-rules.rule_actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($unwanted_characters as $key => $unwanted_character)
                                            <tr>
                                                <td>
                                                    {{ $unwanted_character->unwanted_character ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    {{ $unwanted_character->valid_character ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    <a href="{{ url('unwanted-character/edit/' . $unwanted_character->id) }}"
                                                        class="btn btn-primary"><i class="fa fa-edit"></i></a>
                                                    <a href="{{ url('unwanted-character/delete/' . $unwanted_character->id) }}"
                                                        class="btn btn-danger"><i class="fa fa-trash"></i></a>
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
