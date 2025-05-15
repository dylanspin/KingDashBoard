@extends('layouts.master')

@push('css')
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
{{--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">--}}
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css"><link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />

<style>

    #rootwizard .nav.nav-pills {
        margin-bottom: 25px;
    }

    .help-block {
        display: block;
        margin-top: 5px;
        margin-bottom: 10px;
    }
    .nav-pills>li>a{
        cursor: default;;
        background-color: inherit;
    }
    .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover {
        background: #0283cc!important;
        color: #fff!important;
    }
    .nav-pills>li>a:focus,.nav-tabs>li>a:focus, .nav-pills>li>a:hover, .nav-tabs>li>a:hover {
        border: 1px solid transparent!important;
        background-color: inherit!important;
    }

    .has-error .help-block {
        color: #EF6F6C;
    }

    .select2 {
        width: 100% !important;
    }
    .error-block{
        background-color: #ff9d9d;
        color: red;
    }
</style>
@endpush

@section('content')
    @if($fail_safe)
    <div class="container-fluid">
        <!-- .row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <h3 class="box-title pull-left">{{ $type == 'person' ? __('sidebar.fail_safe_person') : __('sidebar.fail_safe_parking') }}</h3>
                    <div class="clearfix"></div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 list-fail-safe-container">
                            <div class="table-responsive">
                                <table id="listingDataTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>@lang('barcode.key')</th>
                                            <th>@lang('barcode.type')</th>
                                            <th>@lang('barcode.barcode')</th>
                                            <th>@lang('barcode.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>{{ucfirst($fail_safe->type)}}</td>
                                            <td>{{$fail_safe->barcode}}</td>
                                            <td>
                                                <a 
                                                    class="edit btn btn-info btn-sm">
                                                    <i 
                                                        class="fa fa-pencil-square-o" 
                                                        aria-hidden="true"></i> @lang('barcode.edit')
                                                </a>
                                                <a 
                                                    class="btn btn-info btn-sm" 
                                                    href="{{url('barcode/fail-safe/download/'.$type.'/'.$fail_safe->id)}}">
                                                    <i class="fa fa-download" aria-hidden="true"></i> Download
                                                </a>
                                                <a 
                                                    class="delete btn btn-danger btn-sm"
                                                    data-id="{{$fail_safe->id}}"
                                                    data-type="{{$fail_safe->type}}">
                                                    <i class="fa fa-trash-o"></i> @lang('barcode.delete')
                                                </a>

                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-sm-12 edit-fail-safe-container hidden">
                            <div class="white-box">
                                <div class="clearfix"></div>
                                @if(count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach($errors->all() as $error)
                                        <li>{{$error}}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                <form 
                                    id="commentForm" 
                                    action="{{url('barcode/fail-safe/edit/'.$type.'/'.$fail_safe->id)}}" 
                                    method="POST" 
                                    enctype="multipart/form-data" 
                                    class="form-horizontal barcodeForm">
                                    <!-- CSRF Token -->
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                                    <div id="rootwizard">
                                        <ul class="nav nav-tabs">
                                            <li class="active">
                                                <a 
                                                    href="#tab1" 
                                                    data-toggle="tab">@lang('barcode.details')</a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="tab1">
                                                <h2 class="hidden">&nbsp;</h2>
                                                <div class="form-group {{ $errors->first('barcode', 'has-error') }}">
                                                    <label for="barcode" class="col-sm-2 control-label">@lang('barcode.barcode') *</label>
                                                    <div class="col-sm-10">
                                                        <input 
                                                            id="barcode" 
                                                            name="barcode" 
                                                            type="text" 
                                                            placeholder="@lang('barcode.barcode')" 
                                                            class="form-control required" 
                                                            value="{!! old('barcode', $fail_safe->barcode) !!}"/>

                                                        {!! $errors->first('barcode', '<span class="help-block">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <ul class="pager wizard">
                                                <li class="next finish" ><a href="javascript:;">@lang('barcode.submit')</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </form>
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
    @else
    <div class="container-fluid">
        <!-- .row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    
                    <h3 class="box-title pull-left">{{ $type == 'person' ? __('sidebar.fail_safe_person') : __('sidebar.fail_safe_parking') }}</h3>
                    <div class="clearfix"></div>
                    @if(count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <form 
                        id="commentForm" 
                        action="{{url('barcode/fail-safe/'.$type)}}" 
                        method="POST" 
                        enctype="multipart/form-data" 
                        class="form-horizontal barcodeForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                        <div id="rootwizard">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a 
                                        href="#tab1" 
                                        data-toggle="tab">@lang('barcode.details')</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <h2 class="hidden">&nbsp;</h2>
                                    <div class="form-group {{ $errors->first('barcode', 'has-error') }}">
                                        <label for="barcode" class="col-sm-2 control-label">@lang('barcode.barcode') *</label>
                                        <div class="col-sm-10">
                                            <input 
                                                id="barcode" 
                                                name="barcode" 
                                                type="text" 
                                                placeholder="@lang('barcode.barcode')" 
                                                class="form-control required" 
                                                value="{!! old('barcode') !!}"/>

                                            {!! $errors->first('barcode', '<span class="help-block">:message</span>') !!}
                                        </div>
                                    </div>
                                </div>
                                <ul class="pager wizard">
                                    <li class="next finish" ><a href="javascript:;">@lang('barcode.submit')</a></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('layouts.partials.right-sidebar')
    </div>
    @endif
@endsection

@push('js')
<script src="{{ asset('plugins/components/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
<script src="{{asset('plugins/components/icheck/icheck.min.js')}}"></script>
<script src="{{asset('plugins/components/icheck/icheck.init.js')}}"></script>
<script src="{{asset('plugins/components/moment/moment.js')}}"></script>
<script src="{{asset('plugins/components/jqueryui/jquery-ui.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap-wizard/1.2/jquery.bootstrap.wizard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js" type="text/javascript"></script>
<script src="{{asset('plugins/components/toast-master/js/jquery.toast.js')}}"></script>
<script src="{{ asset('/js/jquery.mask.js') }}"></script>
<script src="{{ asset('/js/barcode.js') }}"></script>
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
    $(document).on('click', '.edit', function (e) {
        $('.list-fail-safe-container').addClass('hidden');
        $('.edit-fail-safe-container').removeClass('hidden');
    });
    $(document).on('click', '.delete', function (e) {
        var id = $(this).data('id');
        var type = $(this).data('type');
        bootbox.confirm({
        title: "Destroy Barcode?",
            message: "Are you sure want to delete barcode?",
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
            window.location.href = "{{url('barcode/fail-safe/delete')}}/"+ type + "/" + id;
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
})

</script>
@endpush