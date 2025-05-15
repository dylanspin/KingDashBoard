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
                    <h3 class="box-title pull-left">@lang('white-list.white_list')</h3>
                    <a  class="btn btn-success pull-right" href="{{url('white-list/create')}}">
                        <i class="icon-plus"></i> @lang('white-list.add_user')
                    </a>
                    <div class="clearfix"></div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="listingDataTable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>@lang('white-list.no')</th>
                                        <th>@lang('white-list.user_name')</th>
                                        <th>@lang('white-list.email')</th>
                                        <th>@lang('white-list.group')</th>
<!--                                        <th>@lang('white-list.added_at')</th>
                                        <th>@lang('white-list.ticket_status')</th>-->
                                        <th>@lang('white-list.send_ins')</th>
                                        <th>@lang('white-list.actions')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($whiteListUsers as $key=>$whiteList)
                                        <tr>
                                            <td>{{$key+1}}</td>
                                            <td>
                                                @if(isset($whiteList->customer))
                                                    @if(isset($whiteList->customer->profile->first_name) && $whiteList->customer->profile->first_name != Null)
                                                        @if(isset($whiteList->customer->profile->last_name) && $whiteList->customer->profile->last_name != Null)
                                                            {{$whiteList->customer->profile->first_name .' '.$whiteList->customer->profile->last_name}}
                                                        @else
                                                            {{$whiteList->customer->profile->first_name}}
                                                        @endif
                                                    @elseif(isset($whiteList->customer->name) && $whiteList->customer->name != Null)
                                                        {{$whiteList->customer->name}}
                                                    @else
                                                        N/A
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            <td>{{$whiteList->email}}</td>
                                            <td>{{!empty($whiteList->group) ? $whiteList->group->name : 'N/A'}}</td>

                                            <td>
                                                <a 
                                                    class="send-instructions btn btn-sm" 
                                                    data-id="{{$whiteList->id}}" 
                                                    style="cursor:pointer;">
                                                    <u>@lang('white-list.send')</u>
                                                </a>
                                            </td>
                                            <td>
                                                <a 
                                                    class="btn btn-info btn-sm" 
                                                    href="{{url('white-list/edit/'.$whiteList->id)}}">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                </a>
                                                &nbsp;&nbsp;
                                                <a 
                                                    class="delete btn btn-danger btn-sm" 
                                                    data-id="{{$whiteList->id}}" 
                                                    style="cursor:pointer;">
                                                    <i class="fa fa-trash-o"></i> @lang('white-list.delete')
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

        @include('layouts.partials.right-sidebar')
    </div>
@endsection

@push('js')
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{asset('plugins/components/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('/js/datatable_lang.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script> 
$(function () {
    $('#listingDataTable').DataTable({
        "pageLength": 25
    });
});
</script>
<script>
    $(document).ready(function () {
        $(document).on('click','.delete',function (e) {
            var id = $(this).data('id');
            bootbox.confirm({
                title: "Destroy User?",
                message: "Are you sure want to delete user from Whitelist?",
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
                    if(result){
                        window.location.href = "{{url('white-list/delete')}}/"+id;
                    }
                }
            });
        });

        $(document).on('click','.send-instructions',function (e) {
            var id = $(this).data('id');
            bootbox.confirm({
                title: "Send Instructions?",
                message: "Are you sure want to send Instructions to user?",
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
                    if(result){
                        window.location.href = "{{url('white-list/send-instructions')}}/"+id;
                    }
                }
            });
        });

        @if(\Session::has('message'))
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
</script>
@endpush