var bookingCalendar;
$(document).ready(function () {
    "use strict";
    $('[data-toggle="tooltip"]').tooltip();
    jQuery('.carousel').carousel('pause');
    $('.carousel').bind('slid.bs.carousel', function (e) {
        $(".carousel .item").each(function (index) {
            if (jQuery(this).hasClass('active')) {
                var device_id = jQuery(this).attr('data-device_id');
                var vehcile_num = jQuery(this).attr('data-vehcile_num');
                var transaction_id = jQuery(this).attr('data-transaction_id');
                var redirect_link = '/transaction/1/' + transaction_id;
                jQuery('.btn-device-' + device_id).html(vehcile_num);
                jQuery('.btn-device-link-' + device_id).find('.redirect_lnk').attr('href', redirect_link);
            }
        });
    });
    jQuery('.show_person_transactions').click(function () {
        jQuery(this).parent().parent().parent().parent().find('.other_content').addClass('hidden');
        jQuery(this).parent().parent().parent().parent().find('.transactions_vehicle_con').addClass('hidden');
        jQuery(this).parent().parent().parent().parent().find('.transactions_person_con').removeClass('hidden');
        jQuery(this).parent().parent().parent().parent().find('.show_main_content').removeClass('hidden');
    });
    jQuery('.show_main_content').click(function () {
        jQuery(this).parent().parent().parent().parent().find('.other_content').removeClass('hidden');
        jQuery(this).parent().parent().parent().parent().find('.transactions_vehicle_con').addClass('hidden');
        jQuery(this).parent().parent().parent().parent().find('.transactions_person_con').addClass('hidden');
        jQuery(this).parent().parent().parent().parent().find('.show_main_content').addClass('hidden');
    });
    jQuery('.show_transactions').click(function () {
        jQuery(this).parent().parent().parent().parent().find('.other_content').addClass('hidden');
        jQuery(this).parent().parent().parent().parent().find('.transactions_con').removeClass('hidden');
        jQuery(this).parent().parent().find('.show_transactions').addClass('hidden');
        jQuery(this).parent().parent().find('.hide_transactions').removeClass('hidden');
    });
    jQuery('.hide_transactions').click(function () {
        jQuery(this).parent().parent().parent().parent().find('.other_content').removeClass('hidden');
        jQuery(this).parent().parent().parent().parent().find('.transactions_con').addClass('hidden');
        jQuery(this).parent().parent().find('.show_transactions').removeClass('hidden');
        jQuery(this).parent().parent().find('.hide_transactions').addClass('hidden');
    });
    jQuery('.btn-open-gate-active').click(function () {
        var device_id = jQuery(this).attr('data-device_id');
        jQuery('.open_gate_modal').find('.device_id').val(device_id);
        jQuery('.open_gate_modal .confirm_con').find('.open_gate_vehcile_num').val('');
        jQuery('.open_gate_modal .confirm_con').find('.open_gate_reason').val('');
        jQuery('.open_gate_modal .confirm_con').find('.message').html('');

        jQuery('.open_gate_modal .form_con').find('.open_gate_vehcile_num').val('');
        jQuery('.open_gate_modal .form_con').find('.open_gate_reason').val('');
        jQuery('.open_gate_modal .form_con').find('.message').html('');

        jQuery('.open_gate_modal .form_con').removeClass('hidden');
        jQuery('.open_gate_modal .confirm_con').addClass('hidden');
        jQuery('.open_gate_modal').modal('show');
        return false;
    });
    jQuery('.submit_open_gate_cancel').click(function () {
        jQuery('.open_gate_modal .form_con').removeClass('hidden');
        jQuery('.open_gate_modal .confirm_con').addClass('hidden');
        return false;
    });
    jQuery('.submit_open_gate_modal').click(function () {
        jQuery('.overlay_open_gate').removeClass('hidden');
        var device_id = jQuery('.open_gate_modal .form_con').find('.device_id').val();
        var open_gate_vehcile_num = jQuery('.open_gate_modal .form_con').find('.open_gate_vehcile_num').val();
        var open_gate_reason = jQuery('.open_gate_modal .form_con').find('.open_gate_reason').val();
        $.ajax({
            url: '/dashboard/open_gate',
            type: 'post',
            data: {'device_id': device_id, 'open_gate_vehcile_num': open_gate_vehcile_num, 'open_gate_reason': open_gate_reason},
            dataType: 'json',
            success: function (_response) {
                jQuery('.overlay_open_gate').addClass('hidden');
                if (_response.status == 3) {
                    jQuery('.open_gate_modal .form_con').find('.message').html(_response.message);
                } else if (_response.status == 2) {
                    jQuery('.open_gate_modal .confirm_con').find('.device_id').val(device_id);
                    jQuery('.open_gate_modal .confirm_con').find('.open_gate_vehcile_num').val(open_gate_vehcile_num);
                    jQuery('.open_gate_modal .confirm_con').find('.open_gate_reason').val(open_gate_reason);
                    jQuery('.open_gate_modal .confirm_con').find('.message').html(_response.message);
                    jQuery('.open_gate_modal .form_con').addClass('hidden');
                    jQuery('.open_gate_modal .confirm_con').removeClass('hidden');
                } else if (_response.status == 1) {
                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-active').addClass('hidden');
                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-non-active').removeClass('hidden');

                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-active').removeClass('hidden');
                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-non-active').addClass('hidden');

                    jQuery('.device_' + device_id).find('.barier-closed').addClass('hidden');
                    jQuery('.device_' + device_id).find('.barier-opened').removeClass('hidden');
                    jQuery('.open_gate_modal').modal('hide');


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
//                    jQuery('.open_gate_modal').modal('hide');
                    jQuery('.open_gate_vehcile_num').parent().addClass('has-error');
                    jQuery('.open_gate_vehcile_num').parent().find('.help-block').html(_response.message);
//                    $.toast({
//                        heading: 'Error!',
//                        position: 'top-center',
//                        text: _response.message,
//                        loaderBg: '#ff6849',
//                        icon: 'error',
//                        hideAfter: 5000,
//                        stack: 6
//                    });
                }
            }
        });
    });
    jQuery('.submit_open_gate_modal_confirm').click(function () {
        jQuery('.overlay_open_gate').removeClass('hidden');
        var device_id = jQuery('.open_gate_modal .confirm_con').find('.device_id').val();
        var open_gate_vehcile_num = jQuery('.open_gate_modal .confirm_con').find('.open_gate_vehcile_num').val();
        var open_gate_reason = jQuery('.open_gate_modal .confirm_con').find('.open_gate_reason').val();
        $.ajax({
            url: '/dashboard/no_entrance_transaction',
            type: 'post',
            data: {'device_id': device_id, 'open_gate_vehcile_num': open_gate_vehcile_num, 'open_gate_reason': open_gate_reason},
            dataType: 'json',
            success: function (_response) {
                jQuery('.overlay_open_gate').addClass('hidden');
                if (_response.status == 1) {
                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-active').addClass('hidden');
                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-non-active').removeClass('hidden');

                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-active').removeClass('hidden');
                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-non-active').addClass('hidden');

                    jQuery('.device_' + device_id).find('.barier-closed').addClass('hidden');
                    jQuery('.device_' + device_id).find('.barier-opened').removeClass('hidden');
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
                jQuery('.open_gate_modal').modal('hide');
            }
        });
    });
    jQuery('.btn-close-gate-active').click(function () {
        var device_id = jQuery(this).attr('data-device_id');
        var item = jQuery(this);
        jQuery(item).parent().parent().find('.gate_open_spinner').removeClass('hidden');
        jQuery(item).parent().parent().find('.open_con').addClass('hidden');
        jQuery(item).parent().parent().find('.close_con').addClass('hidden');
        $.ajax({
            url: '/dashboard/close_gate',
            type: 'post',
            data: {device_id: device_id},
            dataType: 'json',
            success: function (_response) {
                jQuery(item).parent().parent().find('.gate_open_spinner').addClass('hidden');
                if (_response.status === 1) {
                    jQuery(item).parent().parent().find('.open_con').removeClass('hidden');
                    jQuery(item).parent().parent().find('.open_con').find('.btn-open-gate-non-active').addClass('hidden');
                    jQuery(item).parent().parent().find('.open_con').find('.btn-open-gate-active').removeClass('hidden');
                    jQuery(item).parent().parent().find('.close_con').removeClass('hidden');
                    jQuery(item).parent().parent().find('.close_con').find('.btn-close-gate-non-active').removeClass('hidden');
                    jQuery(item).parent().parent().find('.close_con').find('.btn-close-gate-active').addClass('hidden');
                    jQuery('.barier-closed-' + device_id).removeClass('hidden');
                    jQuery('.barier-opened-' + device_id).addClass('hidden');
                } else {
                    jQuery(item).parent().parent().find('.open_con').removeClass('hidden');
                    jQuery(item).parent().parent().find('.open_con').find('.btn-open-gate-non-active').addClass('hidden');
                    jQuery(item).parent().parent().find('.open_con').find('.btn-open-gate-active').removeClass('hidden');
                    jQuery(item).parent().parent().find('.close_con').removeClass('hidden');
                    jQuery(item).parent().parent().find('.close_con').find('.btn-close-gate-non-active').addClass('hidden');
                    jQuery(item).parent().parent().find('.close_con').find('.btn-close-gate-active').removeClass('hidden');
                    jQuery('.barier-closed-' + device_id).addClass('hidden');
                    jQuery('.barier-opened-' + device_id).removeClass('hidden');
                }
            }
        });
    });
    jQuery('.close_gate').click(function () {
        if (jQuery(this).is(':checked')) {
            var item = jQuery(this);
            jQuery(this).parent().parent().find('.switchery_gate').addClass('hidden');
            jQuery(this).parent().parent().find('.gate_open_spinner').removeClass('hidden');
            var device_id = jQuery(this).attr('data-device_id');
            $.ajax({
                url: '/dashboard/close_gate',
                type: 'post',
                data: {device_id: device_id},
                dataType: 'json',
                success: function (_response) {
                    if (_response.status === 1) {
                        jQuery('.open_gate_modal .modal-body').html(_response.message);
                        jQuery('.open_gate_modal').modal('show');
                    } else {

                        jQuery('.open_gate_modal .modal-body').html(_response.message);
                        jQuery('.open_gate_modal').modal('show');
                    }
                    jQuery('.open_gate').prop('checked', false);
                    jQuery(item).parent().parent().find('.switchery_gate').removeClass('hidden');
                    jQuery(item).parent().parent().find('.gate_open_spinner').addClass('hidden');
                }
            });
        }
    });
    jQuery('.btn-open-gate-person-active').click(function () {
        var device_id = jQuery(this).attr('data-device_id');
        jQuery('.device_' + device_id).find('.open_con').addClass('hidden');
        jQuery('.device_' + device_id).find('.close_con').addClass('hidden');
        jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
        $.ajax({
            url: '/dashboard/open_gate_person',
            type: 'post',
            data: {'device_id': device_id},
            dataType: 'json',
            success: function (_response) {
                jQuery('.device_' + device_id).find('.overlay_open_gate').addClass('hidden');
                jQuery('.device_' + device_id).find('.open_con').removeClass('hidden');
                jQuery('.device_' + device_id).find('.close_con').removeClass('hidden');
                if (_response.status == 1) {
                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-person-active').addClass('hidden');
                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-person-non-active').removeClass('hidden');

                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-person-active').removeClass('hidden');
                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-person-non-active').addClass('hidden');

                    jQuery('.device_' + device_id).find('.barier-closed').addClass('hidden');
                    jQuery('.device_' + device_id).find('.barier-opened').removeClass('hidden');


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
    jQuery('.btn-close-gate-person-active').click(function () {
        var device_id = jQuery(this).attr('data-device_id');
        var item = jQuery(this);
        jQuery('.device_' + device_id).find('.open_con').addClass('hidden');
        jQuery('.device_' + device_id).find('.close_con').addClass('hidden');
        jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
        $.ajax({
            url: '/dashboard/close_gate_person',
            type: 'post',
            data: {device_id: device_id},
            dataType: 'json',
            success: function (_response) {
                jQuery('.device_' + device_id).find('.overlay_open_gate').addClass('hidden');
                jQuery('.device_' + device_id).find('.open_con').removeClass('hidden');
                jQuery('.device_' + device_id).find('.close_con').removeClass('hidden');
                if (_response.status === 1) {
                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-person-active').removeClass('hidden');
                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-person-non-active').addClass('hidden');

                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-person-active').addClass('hidden');
                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-person-non-active').removeClass('hidden');

                    jQuery('.device_' + device_id).find('.barier-closed').removeClass('hidden');
                    jQuery('.device_' + device_id).find('.barier-opened').addClass('hidden');
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
    jQuery('.btn-open-gate-emergency-active').click(function () {
        var device_id = jQuery(this).attr('data-device_id');
        var booking_id = jQuery(this).attr('data-booking_id');
        jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
        $.ajax({
            url: '/dashboard/open_gate_vehicle',
            type: 'post',
            data: {'device_id': device_id, 'booking_id': booking_id},
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
    $('.js-switch').each(function () {
        new Switchery($(this)[0], $(this).data());
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    jQuery('.devices_collapse_actions_show').click(function () {

    });
	
	jQuery('.btn-open-gate-vehicle-active').click(function () {
        jQuery('body').find('.preloader').css({display: 'block'});
        var device_id = jQuery(this).attr('data-device_id');
//        jQuery('.device_' + device_id).find('.open_con').addClass('hidden');
//        jQuery('.device_' + device_id).find('.close_con').addClass('hidden');
//        jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
        $.ajax({
            url: '/dashboard/open_gate_vehicle',
            type: 'post',
            data: {'device_id': device_id},
            dataType: 'json',
            success: function (_response) {
                jQuery('.device_' + device_id).find('.overlay_open_gate').addClass('hidden');
                jQuery('.device_' + device_id).find('.open_con').removeClass('hidden');
                jQuery('.device_' + device_id).find('.close_con').removeClass('hidden');
                if (_response.status == 1) {
//                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-vehicle-active-con').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-vehicle-non-active').removeClass('hidden');
//                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-vehicle-active-con').removeClass('hidden');
//                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-vehicle-non-active').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-closed').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-opened').removeClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-always-access').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-locked-closed').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-locked-opened').addClass('hidden');
                    location.reload();
                    jQuery('body').find('.preloader').css({display: 'none'});
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
                    location.reload();
                    jQuery('body').find('.preloader').css({display: 'none'});
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
    jQuery('.btn-close-gate-vehicle-active').click(function () {
        jQuery('body').find('.preloader').css({display: 'block'});
        var device_id = jQuery(this).attr('data-device_id');
//        jQuery('.device_' + device_id).find('.open_con').addClass('hidden');
//        jQuery('.device_' + device_id).find('.close_con').addClass('hidden');
//        jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
        $.ajax({
            url: '/dashboard/close_gate_vehicle',
            type: 'post',
            data: {device_id: device_id},
            dataType: 'json',
            success: function (_response) {
                jQuery('.device_' + device_id).find('.open_con').removeClass('hidden');
                jQuery('.device_' + device_id).find('.close_con').removeClass('hidden');
                jQuery('.device_' + device_id).find('.overlay_open_gate').addClass('hidden');
                if (_response.status === 1) {
//                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-vehicle-active-con').removeClass('hidden');
//                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-vehicle-non-active').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-vehicle-active-con').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-vehicle-non-active').removeClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-closed').removeClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-opened').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-always-access').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-locked-closed').addClass('hidden');
//                    jQuery('.device_' + device_id).find('.barier-locked-opened').addClass('hidden');
                    location.reload();
                    jQuery('body').find('.preloader').css({display: 'none'});
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
                    location.reload();
                    jQuery('body').find('.preloader').css({display: 'none'});
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
    
});
function change_barrier_status(device, status) {
    jQuery('body').find('.preloader').css({display: 'block'});
    $.ajax({
        url: '/dashboard/change_gate_barrier_status',
        type: 'post',
        data: {
            'device_id': device,
            'status': status
        },
        dataType: 'json',
        success: function (_response) {
            if (_response.status == 1) {
                location.reload();
                jQuery('body').find('.preloader').css({display: 'none'});
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
                location.reload();
                jQuery('body').find('.preloader').css({display: 'none'});
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
}


