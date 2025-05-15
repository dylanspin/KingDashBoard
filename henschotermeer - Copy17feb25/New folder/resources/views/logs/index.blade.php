@extends('layouts.master')

@push('css')
<link href="{{asset('plugins/components/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('plugins/components/footable/css/footable.bootstrap.css')}}" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/components/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
<link href="{{asset('plugins/components/icheck/skins/all.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
<!--<link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">-->
<link href="{{asset('plugins/components/jqueryui/jquery-ui.min.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css">
<link href="{{asset('plugins/components/clockpicker/dist/jquery-clockpicker.min.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.css')}}" rel="stylesheet">
<link href="{{asset('plugins/components/ion-rangeslider/css/ion.rangeSlider.skinModern.css')}}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('logs.logs')</h3>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table 
                                class="table" 
                                data-sorting="true" 
                                data-filtering="true" 
                                data-toggle-column="first"
                                data-paging="true" 
                                data-paging-size="25">
                                <thead>
                                    <tr>
                                        <th data-sortable="false"></th>
                                        <th data-filterable="false">@lang('logs.message')</th>
                                        <th>@lang('logs.type')</th>
                                        <th>@lang('logs.line')</th>
                                        <th data-type="date" data-format-string="DD/MM/YYYY" data-sorted="true" data-direction="DESC">@lang('logs.added_at')</th>
                                        <th data-sortable="false">@lang('logs.actions')</th>
                                        <th data-breakpoints="all"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($error_logs as $key=>$data)
                                    <tr class="record{{$data->id}}">
                                        <td></td>
                                        <td>{!! $data->error_message !!}</td>
                                        <td>{{$data->controller.'/'.str_replace("-", ">", $data->type)}}</td>
                                        <td>{{$data->error_line}}</td>
                                        <td>{{date('d/m/Y', strtotime($data->created_at))}}</td>
                                        <td>
                                            <a 
                                                class="resolved btn btn-success btn-sm"
                                                data-id="{{$data->id}}" 
                                                style="cursor:pointer;">
                                                <i class="fa fa-trash-o"></i> @lang('logs.resolved')
                                            </a>
                                        </td>
                                        <td>{!! $data->error_stack_trace !!}</td>
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
<script>
$(document).ready(function () {
    $(document).on('click', '.resolved', function (e) {
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Resolved Issue?",
            message: "Are you sure want to resolved this issue?",
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
                    window.location.href = "{{url('logs/resolved')}}/"+id;
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

$('.table').footable();

</script>
@endpush