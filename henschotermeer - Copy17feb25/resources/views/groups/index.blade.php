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
                <h3 class="box-title pull-left">@lang('groups.groups')</h3>
                <a  class="btn btn-success pull-right" href="{{url('group/create')}}">
                    <i class="icon-plus"></i> @lang('groups.add_group')
                </a>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('groups.no')</th>
                                        <th>@lang('groups.group_name')</th>
                                        <th>@lang('groups.no_of_devices')</th>
                                        <th>@lang('groups.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($groups)  > 0 )
                                    @foreach($groups as $key=>$group)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$group->name}}</td>
                                        <td>{{$group->devices}}</td>
                                        <td>
                                            <a 
                                                class="btn btn-info btn-sm" 
                                                href="{{url('group/edit/'.$group->id)}}">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i> @lang('groups.edit')
                                            </a>
                                            &nbsp;&nbsp;
                                            <a 
                                                class="delete btn btn-danger btn-sm"
                                                data-id="{{$group->id}}" 
                                                style="cursor:pointer;">
                                                <i class="fa fa-trash-o"></i> @lang('groups.delete')
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
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
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>

<script>
$(document).ready(function () {

$(document).on('click', '.delete', function (e) {
var id = $(this).data('id');
bootbox.confirm({
title: "Destroy Group?",
        message: "Are you sure want to delete group?",
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
        window.location.href = "{{url('group/delete')}}/" + id;
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
                hideAfter: 3000,
                stack: 6
        });
@endif
}
);
</script>
@endpush