"use strict";
$(document).ready(function () {
    $(".input_slider").ionRangeSlider({
        onChange: function (data) {
            var wrapper_selector = data.input.context.name +'_con';
            $('.'+wrapper_selector +' input[name="'+data.input.context.name+'_from"]').val(data.from);
            $('.'+wrapper_selector +' input[name="'+data.input.context.name+'_to"]').val(data.to);
        }
    });
    $('.phone_mask').mask("000-0000000");

//    $('.is_whitelist').on('change', function () {
//        if ($(this).val() === '1') {
//            $('.working-hours-panel').removeClass('hidden');
//            $('#tab4').removeClass('hidden');
//        } else {
//            $('.working-hours-panel').addClass('hidden');
//            $('#tab4').addClass('hidden');
//        }
//    });
    $('.height_restriction').on('change', function () {
        if ($(this).val() === '1') {
            $('.height_resstriction_value_con').removeClass('hidden');
            $('.height_resstriction_value').prop("required", true);
        } else {
            $('.height_resstriction_value_con').addClass('hidden');
            $('.height_resstriction_value').prop("required", false);
        }
    });
    $('.is_barcode_series_available').on('change', function () {
        if ($(this).val() === '1') {
            $('.barcode_series_con').removeClass('hidden');
            $('.barcode_series').prop("required", true);
        } else {
            $('.barcode_series_con').addClass('hidden');
            $('.barcode_series').prop("required", false);
        }
    });
    $('.is_max_stay').on('change', function () {
        if ($(this).val() === '1') {
            $('.maximum_stay_con').removeClass('hidden');
            $('.maximum_stay').prop("required", true);
        } else {
            $('.maximum_stay_con').addClass('hidden');
            $('.maximum_stay').prop("required", false);
        }
    });
    $('.is_advance_booking_limit').on('change', function () {
        if ($(this).val() === '1') {
            $('.advance_booking_time_con').removeClass('hidden');
            $('.advance_booking_time').prop("required", true);
        } else {
            $('.advance_booking_time_con').addClass('hidden');
            $('.advance_booking_time').prop("required", false);
        }
    });
    $('.is_doors').on('change', function () {
        if ($(this).val() === '1') {
            $('.door_selector_con').removeClass('hidden');
            $('.door_selector').prop("required", true);
        } else {
            $('.door_selector_con').addClass('hidden');
            $('.door_selector').prop("required", false);
        }
    });
    $('.is_bikes').on('change', function () {
        if ($(this).val() === '1') {
            $('.bike_selector_con').removeClass('hidden');
            $('.bike_selector').prop("required", true);
        } else {
            $('.bike_selector_con').addClass('hidden');
            $('.bike_selector').prop("required", false);
        }
    });
    $('.is_ev_charger_available').on('change', function () {
        if ($(this).val() === '1') {
            $('.ev_charger_range_con').removeClass('hidden');
            $('.ev_charger_energy_con').removeClass('hidden');
            $('.ev_charger_range').prop("required", true);
            $('.ev_charger_energy').prop("required", true);
        } else {
            $('.ev_charger_range_con').addClass('hidden');
            $('.ev_charger_energy_con').addClass('hidden');
            $('.ev_charger_range').prop("required", false);
            $('.ev_charger_energy').prop("required", false);
        }
    });
});
//bootstrap wizard//
$("#gender, #gender1").select2({
    theme: "bootstrap",
    placeholder: "",
    width: '100%'
});
$('input[type="checkbox"].custom-checkbox, input[type="radio"].custom-radio').iCheck({
    checkboxClass: 'icheckbox_minimal-blue',
    radioClass: 'iradio_minimal-blue',
    increaseArea: '20%'
});
$(".locationForm").bootstrapValidator({
    fields: {
        barcode_series: {
            validators: {
                regexp: {
                    regexp: /^[0-9]+[-][0-9]+$/,
                    message: 'Enter valid value like (0-100)'
                }
            }
        }
    }
});

$('#rootwizard').bootstrapWizard({
    'tabClass': 'nav nav-pills',
    'onNext': function (tab, navigation, index) {
        var $validator = $('#commentForm').data('bootstrapValidator').validate();
        return $validator.isValid();
    },
    onTabClick: function (tab, navigation, index) {
        var $validator = $('#commentForm').data('bootstrapValidator').validate();
        return $validator.isValid();
    },
    onTabShow: function (tab, navigation, index) {
        var $total = navigation.find('li').length;
        var $current = index + 1;

        // If it's the last tab then hide the last button and show the finish instead
        if ($current >= $total) {
            $('#rootwizard').find('.pager .next').hide();
            $('#rootwizard').find('.pager .finish').show();
            $('#rootwizard').find('.pager .finish').removeClass('disabled');
        } else {
            $('#rootwizard').find('.pager .next').show();
            $('#rootwizard').find('.pager .finish').hide();
        }
    }});

$('#rootwizard .finish').click(function () {
    var $validator = $('#commentForm').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("commentForm").submit();
    }

});
// $('#activate').on('ifChanged', function(event){
//     $('#commentForm').bootstrapValidator('revalidateField', $('#activate'));
// });
$('#commentForm').keypress(function (event) {
            if (event.which == '13') {
                event.preventDefault();
            }
        });

function add_location_weekdays_checkboxes(item) {
    if ($(item).prop("checked") == true) {
        var week_num = $(item).data('week_day_num');
        $('.opening_time_day' + week_num).prop("required", true);
        $('.closing_time_day' + week_num).prop("required", true);
    } else {
        var week_num = $(item).data('week_day_num');
        $('.opening_time_day' + week_num).prop("required", false);
        $('.closing_time_day' + week_num).prop("required", false);
        $('.opening_time_day' + week_num).val("00:00");
        $('.closing_time_day' + week_num).val("00:00");
    }
    $('.sametime_for_days').prop("checked", false);
}

function sametime_for_all_weekdays(item) {
    if ($(item).prop("checked") === true) {
        if ($('.weekday_checkbox_1').prop("checked") === true) {
            if ($('.opening_time_day1').val() == "") {
                $('.opening_time_day1').prop('required',true);
            }
            if ($('.closing_time_day1').val() == "") {
                $('.closing_time_day1').prop('required',true);
            }
            if ($('.closing_time_day1').val() != "" && $('.opening_time_day1').val() != "") {
                $('.weekdays_checkbox').prop("checked", true);
                $('.opening_time_day').val($('.opening_time_day1').val());
                $('.closing_time_day').val($('.closing_time_day1').val());
                $('.closing_time_day').prop('required',true);
                $('.opening_time_day').prop('required',true);
            }
            $('.error_message_con').empty();
        }else {
            $(item).prop("checked", false);
            $('.error_message_con').empty();
            $('.error_message_con').html('<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please Select Monday</div>');
        }
    }
}

function add_location_whitelist_weekdays_checkboxes(item) {
    if ($(item).prop("checked") == true) {
        var week_num = $(item).data('week_day_num');
        $('.w_opening_time_day' + week_num).prop("required", true);
        $('.w_closing_time_day' + week_num).prop("required", true);
    } else {
        var week_num = $(item).data('week_day_num');
        $('.w_opening_time_day' + week_num).prop("required", false);
        $('.w_closing_time_day' + week_num).prop("required", false);
        $('.w_opening_time_day' + week_num).val("00:00");
        $('.w_closing_time_day' + week_num).val("00:00");
    }
    $('.w_sametime_for_days').prop("checked", false);
}

function w_sametime_for_all_weekdays(item) {
    if ($(item).prop("checked") === true) {
        if ($('.w_weekday_checkbox_1').prop("checked") === true) {
            if ($('.w_opening_time_day1').val() == "") {
                $('.w_opening_time_day1').prop('required',true);
            }
            if ($('.w_closing_time_day1').val() == "") {
                $('.w_closing_time_day1').prop('required',true);
            }
            if ($('.w_closing_time_day1').val() != "" && $('.w_opening_time_day1').val() != "") {
                $('.w_weekdays_checkbox').prop("checked", true);
                $('.w_opening_time_day').val($('.w_opening_time_day1').val());
                $('.w_closing_time_day').val($('.w_closing_time_day1').val());
                $('.w_closing_time_day').prop('required',true);
                $('.w_opening_time_day').prop('required',true);
            }
            $('.error_message_con').empty();
        }else {
            $(item).prop("checked", false);
            $('.error_message_con').empty();
            $('.error_message_con').html('<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please Select Monday</div>');
        }
    }
}

function add_location_person_weekdays_checkboxes(item) {
    if ($(item).prop("checked") == true) {
        var week_num = $(item).data('week_day_num');
        $('.p_opening_time_day' + week_num).prop("required", true);
        $('.p_closing_time_day' + week_num).prop("required", true);
    } else {
        var week_num = $(item).data('week_day_num');
        $('.p_opening_time_day' + week_num).prop("required", false);
        $('.p_closing_time_day' + week_num).prop("required", false);
        $('.p_opening_time_day' + week_num).val("00:00");
        $('.p_closing_time_day' + week_num).val("00:00");
    }
    $('.p_sametime_for_days').prop("checked", false);
}

function p_sametime_for_all_weekdays(item) {
    if ($(item).prop("checked") === true) {
        if ($('.p_weekday_checkbox_1').prop("checked") === true) {
            if ($('.p_opening_time_day1').val() == "") {
                $('.p_opening_time_day1').prop('required',true);
            }
            if ($('.p_closing_time_day1').val() == "") {
                $('.p_closing_time_day1').prop('required',true);
            }
            if ($('.p_closing_time_day1').val() != "" && $('.p_opening_time_day1').val() != "") {
                $('.p_weekdays_checkbox').prop("checked", true);
                $('.p_opening_time_day').val($('.p_opening_time_day1').val());
                $('.p_closing_time_day').val($('.p_closing_time_day1').val());
                $('.p_closing_time_day').prop('required',true);
                $('.p_opening_time_day').prop('required',true);
            }
            $('.error_message_con').empty();
        }else {
            $(item).prop("checked", false);
            $('.error_message_con').empty();
            $('.error_message_con').html('<div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please Select Monday</div>');
        }
    }
}