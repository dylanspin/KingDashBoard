"use strict";
function sync_devices() {
    if (jQuery('.devices_listing .sync_device').length) {
        $.ajax({
            url: '/check_changes_timer',
            type: "POST",
            data: {},
            dataType: 'json',
            success: function (res) {

                $.each(res.devices, function (key, value)
                {
                    if (value === 0) {
                        jQuery('.sync_details_btn_' + key).addClass('hidden');
                        jQuery('.update_server_time_' + key).addClass('hidden');
                        jQuery('.initialize_btn_' + key).removeClass('hidden');
                    } else {
                        jQuery('.sync_details_btn_' + key).removeClass('hidden');
                        jQuery('.update_server_time_' + key).removeClass('hidden');
                        jQuery('.initialize_btn_' + key).addClass('hidden');
                    }
                });

            },
            error: function (res) {

            },
            complete: function (res) {
                setTimeout(sync_devices, 30000);
            }
        });
    }
}
function sync_transaction_container() {
    if (jQuery('.devices_section .transactions_container').length && !jQuery('.person_dashboard').length) {
        $.ajax({
            url: '/check_latest_device_transactions',
            type: "POST",
            data: {},
            dataType: 'json',
            success: function (res) {
                $.each(res, function (key, value1)
                {
                    $.each(value1, function (key, value)
                    {
                        if (value.type === 6) {
                            jQuery('.device_' + value.id).find('.transactions_vehicle_con').html(value.vehicle_transactions_html);
                            jQuery('.device_' + value.id).find('.footer-latest-transaction').html(value.footer_latest_transaction);

                        } else {
                            jQuery('.device_' + value.id).find('.transactions_con').html(value.transactions_html);
                            if (value.type === 3) {
                                jQuery('.device_' + value.id).find('.other_content_transaction').html(value.latest_transaction);
                                jQuery('.device_' + value.id).find('.latest_transaction_footer').html(value.footer_latest_transaction);
                            }

                        }
                    });

                });
                $('.btn-open-gate-emergency-active').click(function () {
                    var device_id = jQuery(this).attr('data-device_id');
                    jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
                    $.ajax({
                        url: '/dashboard/open_gate_vehicle',
                        type: 'post',
                        data: {'device_id': device_id},
                        dataType: 'json',
                        success: function (_response) {
                            jQuery('.device_' + device_id).find('.overlay_open_gate').addClass('hidden');
                            if (_response.status == 1) {
                                $.toast({
                                    heading: 'Success!',
                                    position: 'top-center',
                                    text: _response.message,
                                    loaderBg: '#ff6849',
                                    icon: 'success',
                                    hideAfter: 5000,
                                    stack: 6
                                });
                            } else {
                                $.toast({
                                    heading: 'Error!',
                                    position: 'top-center',
                                    text: _response.message,
                                    loaderBg: '#ff6849',
                                    icon: 'error',
                                    hideAfter: 5000,
                                    stack: 6
                                });
                            }
                        }
                    });
                });

            },
            error: function (res) {

            },
            complete: function (res) {
                setTimeout(sync_transaction_container, 25000);
            }
        });
    }
}
function sync_transaction_container_p() {
    if (jQuery('.devices_section .transactions_container').length && jQuery('.person_dashboard').length) {
        $.ajax({
            url: '/check_latest_device_transactions_p',
            type: "POST",
            data: {},
            dataType: 'json',
            success: function (res) {
                $.each(res, function (key, value1)
                {
                    $.each(value1, function (key, value)
                    {
                        if (value.type === 6) {
                            jQuery('.device_' + value.id).find('.transactions_person_con').html(value.person_transactions_html);
                            jQuery('.device_' + value.id).find('.footer-latest-transaction').html(value.footer_latest_transaction);
                        } else {
                            jQuery('.device_' + value.id).find('.transactions_con').html(value.transactions_html);
                        }
                    });
                });
            },
            error: function (res) {

            },
            complete: function (res) {
                setTimeout(sync_transaction_container_p, 35000);
            }
        });
    }
}
function manage_stuck_plate_reader() {
    if (jQuery('.plate_reader_devices').length) {
        $.ajax({
            url: '/dashboard/manage_stucked_plate_readers',
            type: "POST",
            data: {},
            dataType: 'json',
            success: function (res) {
                $.each(res.stucked, function (key, value)
                {
                    jQuery('.device_stucked_' + key).find('.btn-confidence').html(value.confidence);
                    jQuery('.device_stucked_' + key).find('.confidence').val(value.confidence);
                    if (value.file_path != null) {
                        jQuery('.device_stucked_' + key).find('.device_strucked_image').attr('src', value.file_path);
                        jQuery('.device_stucked_' + key).find('.default_img').addClass('hidden');
                        jQuery('.device_stucked_' + key).find('.device_strucked_image').removeClass('hidden');
                    }
                    jQuery('.device_stucked_' + key).find('.file_path').val(value.file_path);
                    jQuery('.device_stucked_' + key).find('.device_booking_id').val(value.id);
                    jQuery('.device_stucked_' + key).find('.vehicle_num').val(value.vehicle_num);
                    var num_plate = jQuery('.device_stucked_' + key).find('.plate_num_textbox').val();
                    if (num_plate == '') {
                        jQuery('.device_stucked_' + key).find('.plate_num_textbox').val(value.vehicle_num);
                    }
                    jQuery('.device_non_stucked_' + key).addClass('hidden');
                    jQuery('.device_stucked_' + key).removeClass('hidden');
                });
                $.each(res.non_stucked, function (key, value)
                {
                    jQuery('.device_stucked_' + key).addClass('hidden');
                    if (jQuery('.device_non_stucked_' + key).hasClass('hidden')) {
                        jQuery('.device_non_stucked_' + key).removeClass('hidden');
                    }
                });
            },
            error: function (res) {

            },
            complete: function (res) {
                setTimeout(manage_stuck_plate_reader, 10000);
            }
        });
    }
}
function check_changes() {
    if ($('.person_dashboard').length) {
        $.ajax({
            url: '/check_person_dashboard_changes_timer',
            type: "POST",
            data: {},
            dataType: 'json',
            success: function (res) {
                $.each(res.door_changes, function (key, value) {
                    if (value.is_opened === 0) {
                        if (value.direction === 'in') {
                            jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-active').removeClass('hidden');
                            jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-non-active').addClass('hidden');
                        }
                        jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-active').addClass('hidden');
                        jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-non-active').removeClass('hidden');
                        jQuery('.device_' + key).find('.barier-closed').removeClass('hidden');
                        jQuery('.device_' + key).find('.barier-opened').addClass('hidden');
                    } else {
                        if (value.direction === 'in') {
                            jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-active').addClass('hidden');
                            jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-non-active').removeClass('hidden');
                        }
                        jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-active').removeClass('hidden');
                        jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-non-active').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-closed').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-opened').removeClass('hidden');
                    }
                });
                $('div.widget1_con').html(res.widgets.widget1_con);
                $('div.widget2_con').html(res.widgets.widget2_con);
                $('div.widget3_con').html(res.widgets.widget3_con);
                $('div.persons_on_location_con').html(res.persons_on_location_con.at_location_con);
                $('div.transaction_on_location_con').html(res.transaction_on_location_con.at_location_con);
            },
            error: function (res) {

            },
            complete: function (res) {
                setTimeout(check_changes, 20000);
            }
        });
    }
}
function check_changes_dashboard() {
    if (jQuery('.vehicle_dashboard').length) {
        $.ajax({
            url: '/check_dashboard_changes_timer',
            type: "POST",
            data: {},
            dataType: 'json',
            success: function (res) {
                $.each(res.door_changes, function (key, value) {
                    if (value.is_opened === 0) {
                        if (value.direction === 'in') {
                            jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-active').removeClass('hidden');
                            jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-non-active').addClass('hidden');
                        }
                        jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-active').addClass('hidden');
                        jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-non-active').removeClass('hidden');
                        jQuery('.device_' + key).find('.barier-closed').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-opened').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-always-access').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-locked-closed').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-locked-opened').addClass('hidden');
                        if (value.barrier_status === 1) {
                            jQuery('.device_' + key).find('.barier-locked-opened').removeClass('hidden');
                        } else if (value.barrier_status === 2) {
                            jQuery('.device_' + key).find('.barier-locked-closed').removeClass('hidden');
                        } else if (value.barrier_status === 3) {
                            jQuery('.device_' + key).find('.barier-always-access').removeClass('hidden');
                        } else {
                            jQuery('.device_' + key).find('.barier-closed').removeClass('hidden');
                        }
                    } else {
                        if (value.direction === 'in') {
                            jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-active').addClass('hidden');
                            jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-non-active').removeClass('hidden');
                        }
                        jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-active').removeClass('hidden');
                        jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-non-active').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-closed').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-opened').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-always-access').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-locked-closed').addClass('hidden');
                        jQuery('.device_' + key).find('.barier-locked-opened').addClass('hidden');
                        if (value.barrier_status === 1) {
                            jQuery('.device_' + key).find('.barier-locked-opened').removeClass('hidden');
                        } else if (value.barrier_status === 2) {
                            jQuery('.device_' + key).find('.barier-locked-closed').removeClass('hidden');
                        } else if (value.barrier_status === 3) {
                            jQuery('.device_' + key).find('.barier-always-access').removeClass('hidden');
                        } else {
                            jQuery('.device_' + key).find('.barier-opened').removeClass('hidden');
                        }
                    }
                });
                $('div.widget1_con').html(res.widgets.widget1_con);
                $('div.widget2_con').html(res.widgets.widget2_con);
                $('div.widget3_con').html(res.widgets.widget3_con);
                //                        $('div.devices_con').html(res.devices.devices_con);
                $('div.vehicles_on_location_con').html(res.vehicles_on_location_con.at_location_con);
                //                        $('div.persons_on_location_con').html(res.persons_on_location_con.at_location_con);
                $('div.transaction_on_location_con').html(res.transaction_on_location_con.at_location_con);
                //                        $('#calendar').fullCalendar('refresh');
            },
            error: function (res) {

            },
            complete: function (res) {
                setTimeout(check_changes_dashboard, 13000);
            }
        });
    }
}
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$(document).ready(function () {
    sync_devices();
    sync_transaction_container();
    sync_transaction_container_p();
    manage_stuck_plate_reader();
    check_changes();
    check_changes_dashboard();
});