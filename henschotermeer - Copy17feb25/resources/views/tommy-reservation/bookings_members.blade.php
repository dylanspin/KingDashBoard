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
                    <h3 class="box-title pull-left">@lang('tommy-reservation.mem_of') {{ strtoupper($familyHead) }}</h3>
                    <a class="btn btn-success pull-right" href="{{ url('tommy-reservations/bookings/') }}">
                        <i class="icon-list"></i> @lang('tommy-reservation.view_reservations')
                    </a>
                    <div class="clearfix"></div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="listingDataTable" class="table table-striped tommy_reservation_listing">
                                    <thead>
                                        <tr>
                                            <th>@lang('white-list.first_name')</th>
                                            <th>@lang('white-list.last_name')</th>
                                            <th>@lang('tommy-reservation.family_status')</th>
                                            <th>@lang('tommy-reservation.booking_status')</th>
                                            <th>@lang('tommy-reservation.dob')</th>
                                            <th>@lang('tommy-reservation.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($onLineMembers as $tommyReservation)
                                            <tr>
                                                <td>{{ $tommyReservation->first_name ? $tommyReservation->first_name : 'N/A' }}
                                                  </td>
                                                <td>{{ $tommyReservation->last_name ? $tommyReservation->last_name : 'N/A' }}
                                                </td>
                                                <td>{{ $tommyReservation->family_status }}</td>
                                                @if ($tommyReservation->is_blocked)
                                                    <td><span class="badge badge-danger">@lang('tommy-reservation.block')</span></td>
                                                @else
                                                    <td><span class="badge badge-success">@lang('tommy-reservation.unblock')</span></td>
                                                @endif
                                                <td>{{ $tommyReservation->dob || $tommyReservation->dob != date('Y-m-d', strtotime('1970-01-01')) ? date('d/m/Y', strtotime($tommyReservation->dob)) : 'N/A' }}
                                                </td>
                                                <td>
                                                    <a target="_blank" title="@lang('tommy-reservation.print')"
                                                        class="btn btn-info btn-sm mt-10"
                                                        href="{{ url('tommy-reservations/bookings/print-ticket/' . $tommyReservation->id) }}">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                    &nbsp;
                                                    @if ($tommyReservation->is_blocked)
                                                        <a title="@lang('tommy-reservation.unblock')" class="btn btn-danger btn-sm mt-10"
                                                            href="#"
                                                            onclick="unblocked_member_single({{ $tommyReservation->id }})">
                                                            <i class="fa fa-lock"></i>
                                                        </a>
                                                    @else
                                                        <a title="@lang('tommy-reservation.block')" class="btn btn-success btn-sm mt-10"
                                                            href="#"
                                                            onclick="blocked_member_single({{ $tommyReservation->id }})">
                                                            <i class="fa fa-unlock"></i>
                                                        </a>
                                                    @endif
                                                    &nbsp;
                                                    @if ($tommyReservation->family_status != 'Familie')
                                                        <a title="@lang('tommy-reservation.delete')"
                                                            class="delete btn btn-danger btn-sm mt-10"
                                                            href="{{ url('tommy-reservations/delete-member/' . $tommyReservation->id) }}">
                                                            <i class="fa fa-trash-o"></i>
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
        <div id="blockSingleMember" class="modal fade">
            <div class="modal-dialog modal-confirm">
                <div class="modal-content">
                    <form action="{{ url('tommy-reservations/bookings/block-unblock-single') }}" method="POST">
                        @csrf
                        <div class="modal-header flex-column">
                            <div class="icon-box">
                                <i class="fa fa-exclamation"></i>
                            </div>
                            <h4 class="text">@lang('reservations.sure')</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>@lang('tommy-reservation.block_really_single') </p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <input type="hidden" name="booking_id" id="blocked_booking_id" value="" />
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">@lang('reservations.cancel')</button>
                            <button type="submit" class="btn btn-danger">@lang('payments.block')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="unBlockSingleMember" class="modal fade">
            <div class="modal-dialog modal-confirm">
                <div class="modal-content">
                    <form action="{{ url('tommy-reservations/bookings/block-unblock-single') }}" method="POST">
                        @csrf
                        <div class="modal-header flex-column">
                            <div class="icon-box">
                                <i class="fa fa-exclamation"></i>
                            </div>
                            <h4 class="text">@lang('reservations.sure')</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>@lang('tommy-reservation.unblock_really_single') </p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <input type="hidden" name="booking_id" id="unblocked_booking_id" value="" />
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">@lang('reservations.cancel')</button>
                            <button type="submit" class="btn btn-success">@lang('payments.unblock')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('layouts.partials.right-sidebar')
    </div>
@endsection

@push('js')
    <script src="{{ asset('plugins/components/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/components/toast-master/js/jquery.toast.js') }}"></script>
    <script src="{{ asset('/js/datatable_lang.js') }}"></script>
    <script>
        $(function() {
            $('#listingDataTable').DataTable({
                "pageLength": 25
            });
            @if (\Session::has('message'))
                $.toast({
                    heading: '{{ session()->get('heading') }}',
                    position: 'top-center',
                    text: '{{ session()->get('message') }}',
                    loaderBg: '#ff6849',
                    icon: '{{ session()->get('icon') }}',
                    hideAfter: 3000,
                    stack: 6
                });
            @endif

        });

        function blocked_member_single(booking_id) {
            $("#blocked_booking_id").val(booking_id);
            $("#blockSingleMember").modal('show');
        }

        function unblocked_member_single(booking_id) {
            $("#unblocked_booking_id").val(booking_id);
            $("#unBlockSingleMember").modal('show');
        }
    </script>
@endpush
