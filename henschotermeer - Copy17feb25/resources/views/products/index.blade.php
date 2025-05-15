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
                <h3 class="box-title pull-left">@lang('products.products')</h3>
                {{-- <a  class="btn btn-success pull-right" href="{{url('products/create')}}"><i class="icon-plus"></i> @lang('products.add_product')</a> --}}
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="listingDataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>@lang('products.title_en')</th>
                                        <th>@lang('products.title_nl')</th>
                                        <th>@lang('products.type')</th>
                                        <th>@lang('products.price')</th>
                                        <th>@lang('barcode.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    <tr>
                                        <td>{{ $product->title }}</td>
                                        <td>{{ $product->title_nl }}</td>
                                        <td>{{ ucfirst(str_replace("_"," ",$product->type)) }}</td>
                                        <td> &euro; {{ $product->price }}</td>
                                        <td>
                                            <a href="{{ url('products/edit/'. $product->id) }}" class="btn btn-primary"><i class="fa fa-pencil"></i></a>
                                            <a href="{{ url('products/delete/'. $product->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
$(document).ready(function () {
    $(document).on('click', '.delete', function (e) {
        var id = $(this).data('id');
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
            window.location.href = "{{url('barcode/delete')}}/" + id;
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