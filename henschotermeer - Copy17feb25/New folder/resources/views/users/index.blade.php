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
                <h3 class="box-title pull-left">@lang('sidebar.employees')</h3>
                <a  class="btn btn-success pull-right" href="{{url('user/create')}}"><i class="icon-plus"></i> @lang('sidebar.add_new_employee')</a>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $key=> $user)
                                   
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$user->name}}</td>
                                        <td>{{$user->email}}</td>
                                        <td class="text-capitalize">{{$user->roles()->pluck('name')->implode(', ')}}</td>
                                        <td>
                                            <a class="btn btn-info btn-sm" href="{{url('user/edit/'.$user->id)}}"><i class="icon-pencil"></i>Edit</a>&nbsp;&nbsp
                                            <a class="btn btn-danger btn-sm" href="{{url('user/delete/'.$user->id)}}"><i class="icon-trash"></i>Delete</a>&nbsp;&nbsp;
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
<script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
<script>
$(document).ready(function () {
$(document).on('click', '.delete', function (e) {
if (confirm('Are you sure want to delete?'))
        {
        }
else
        {
        return false;
                }
});
        @if (\Session::has('message'))
        $.toast({
        heading: 'Success!',
                position: 'top-center',
                text: '{{session()->get('message')}}',
                loaderBg: '#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 6
        });
        @endif
        }
)

$(function () {
    $('#listingDataTable').DataTable({
        "columns": [
            null, null, null,null, {"orderable": false}
        ],
        "pageLength": 25
    });
});
</script>

@endpush