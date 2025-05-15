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
                <h3 class="box-title pull-left">@lang('messages.messages')</h3>
                <!--<a  class="btn btn-success pull-right" href="{{url('messages/create')}}"><i class="icon-plus"></i> @lang('messages.add_message')</a>-->
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('messages.key')</th>
                                        <th>@lang('messages.message_en_uk')</th>
                                        <th>@lang('messages.message_dutch_nl')</th>
                                        <th class="w-200">@lang('messages.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($message_details as $key=>$message)
                                    <tr>
                                        <td>{{$key}}</td>
                                        <td>{{$message->en}}</td>
                                        <td>{{$message->nl}}</td>
                                        <td>
                                            <a 
                                                class="btn btn-info btn-sm" 
                                                href="{{url('messages/edit/'.$message->id)}}">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i> @lang('messages.edit')
                                            </a>

                                            &nbsp;&nbsp;
                                            <a 
                                                class="delete btn btn-danger btn-sm"
                                                data-id="{{$message->id}}">
                                                <i class="fa fa-trash-o"></i> @lang('messages.delete')
                                            </a>
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
            <div class="modal-content">

            </div>
        </div>
    </div>
    @include('layouts.partials.right-sidebar')
</div>
@endsection

@push('js')
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script>
$(function () {
    $('#listingDataTable').DataTable({
        "columns": [
            null, null, null, {"orderable": false}
        ],
        "pageLength": 25
    });
});
</script>
<script>
$(document).ready(function () {

$(document).on('click', '.delete', function (e) {
var id = $(this).data('id');
        bootbox.confirm({
        title: "Destroy Message?",
                message: "Are you sure want to delete message?",
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
                window.location.href = "{{url('messages/delete')}}/" + id;
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
)

</script>
@endpush