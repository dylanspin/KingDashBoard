"use strict";
var userlist_name_required;
var userlist_email_required;
var userlist_email_valid;
var userlist_phone_required;
var userlist_vehicle_name_required;
var userlist_vehicle_no_required;
var userlist_energy_limit_required;
var userlist_language_required;
var add_more;
var new_plate;
var new_license ='License';
var remove;
$(document).ready(function () {
	$(".add_more_btn").click(function (e) {

        e.preventDefault();
        var add_vehile = '<div class="col-md-6"><div class="form-group">'
                + '<label>' + add_more + ' </label><div class="row"><div class="col-md-8">'
                + '<p><input type="text" class="form-control" name="plates[]" onblur="duplicatePlate(this)" placeholder="' + new_plate + ' "></p>'
                + '</div><div class="col-md-4">'
                + '<button type="button" class="btn btn-danger remove_btn" onclick="remove_vehicle(this)">'+remove+'</button></div></div></div></div>';
        $(".add_new_vehicles").append(add_vehile);
    });
        $(".add_more_license_btn").click(function (e) {
        e.preventDefault();
        var add_license = '<div class="col-md-6"><div class="form-group">'
                + '<label>' + add_more + ' </label><div class="row"><div class="col-md-8">'
                + '<input type="text" class="form-control" name="license[]" placeholder="' + new_license + ' ">'
                + '</div><div class="col-md-4">'
                + '<button type="button" class="btn btn-danger remove_btn" onclick="remove_vehicle(this)">'+remove+'</button></div></div></div></div>';
        $(".add_new_vehicles").append(add_license);
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('.vehicles_drop_down .vehicle_id').on('change', function () {
        if (jQuery(this).val() === 'add_new') {
            $('.userListWithEmailForm').bootstrapValidator('enableFieldValidators', 'vehicle_no',  true, 'notEmpty');
            jQuery('.add_vehicle_form').show();
        } else {
            $('.userListWithEmailForm').bootstrapValidator('enableFieldValidators', 'vehicle_no',  false, 'notEmpty');
            jQuery('.add_vehicle_form').hide();
        }
    });
    $(".input_slider").ionRangeSlider({
        onChange: function (data) {
            var wrapper_selector = data.input.context.name + '_con';
            $('.' + wrapper_selector + ' input[name="' + data.input.context.name + '_from"]').val(data.from);
            $('.' + wrapper_selector + ' input[name="' + data.input.context.name + '_to"]').val(data.to);
        }
    });
    $('.phone_mask').mask("00000000000");
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

$(".userListWithEmailForm").bootstrapValidator({
    fields: {
        name: {
            validators: {
                notEmpty: {
                    message: userlist_name_required
                }
            },
            required: true,
            minlength: 3
        },
//        email: {
//            validators: {
//                notEmpty: {
//                    message: userlist_email_required
//                },
//                emailAddress: {
//                    message: userlist_email_valid
//                }
//            }
//        },
//        phone: {
//            validators: {
//                notEmpty: {
//                    message: userlist_phone_required
//                }
//            },
//            required: true
//        },
        vehicle_id: {
            validators: {
                notEmpty: {
                    message: userlist_vehicle_name_required
                }
            },
            required: true
        },
        vehicle_no: {
            validators: {
                notEmpty: {
                    message: userlist_vehicle_no_required
                }
            },
            required: true
        },
        energy_limit: {
            validators: {
                notEmpty: {
                    message: userlist_energy_limit_required
                }
            },
            required: true
        },
        language: {
            validators: {
                notEmpty: {
                    message: userlist_language_required
                }
            }
        }
    }
});
$('.userListWithEmailForm #rootwizard').bootstrapWizard({
    'tabClass': 'nav nav-pills',
    'onNext': function (tab, navigation, index) {
        var $validator = $('#commentForm1').data('bootstrapValidator').validate();
        if (index === 1) {
            var email = $('.userListWithEmailForm .email').val();
            var name = $('.userListWithEmailForm .name').val();
            if($validator.isValid()){
                $.ajax({
                    url: '/user-list/check-user-status',
                    type: "POST",
                    data: {email: email},
                    dataType: 'json',
                    success: function (res) {
                        if (res.response.status) {
                            var existing_name = res.response.data.first_name + ' ' + res.response.data.last_name;
                            $('.use_profile_name_val').val(existing_name);
                            if (name !== existing_name) {
                                $('.view_userlist_name_change_form .user-info').html('User Already Exist in System. <br>User Profile Name is <b>' + res.response.data.first_name + ' ' + res.response.data.last_name + '</b>. <br> Userlist Name is ' + name);
                                $('.view_userlist_name_change_form .userlist_name_btn').html('Use ' + name);
                                $('.view_userlist_name_change_form .profile_name_btn').html('Use ' + existing_name);
                                $('.view_userlist_name_change_form').modal('show');
                            }
                        }
                        if (res.response.vehicles_found) {
                            $('.vehicles_drop_down .vehicle_id').html('<option value="">Select Vehicle</option>');
                            $.each(res.response.vehicles, function (index, value) {
                                $('.vehicles_drop_down .vehicle_id').append('<option value="' + value.id + '">' + value.title + '</option>');
                            });
                            $('.vehicles_drop_down .vehicle_id').append('<option value="add_new">Add New Vehicle</option>');
                            jQuery('.vehicles_drop_down').show();
                            jQuery('.add_vehicle_form').hide();
                            $('.vehicles_drop_down .vehicle_id').on('change', function () {
                                if (jQuery(this).val() === 'add_new') {
                                    $('.userListWithEmailForm').bootstrapValidator('enableFieldValidators', 'vehicle_no',  true, 'notEmpty');
                                    jQuery('.add_vehicle_form').show();
                                } else {
                                    $('.userListWithEmailForm').bootstrapValidator('enableFieldValidators', 'vehicle_no',  false, 'notEmpty');
                                    jQuery('.add_vehicle_form').hide();
                                }
                            });
                        }else{
                            jQuery('.vehicles_drop_down').hide();
                            $('.userListWithEmailForm').bootstrapValidator('enableFieldValidators', 'vehicle_no',  true, 'notEmpty');
                            jQuery('.add_vehicle_form').show();
                            return $validator.isValid();
                        }
                    },
                    error: function (res) {
                        return $validator.isValid();
                    }
                });
            }
            else{
                return $validator.isValid();
            }
        } 
        else {
            return $validator.isValid();
        }
    },
    onTabClick: function (tab, navigation, index) {
        var $validator = $('#commentForm1').data('bootstrapValidator').validate();
        return $validator.isValid();
    },
    onTabShow: function (tab, navigation, index) {
        var $total = navigation.find('li').length;
        var $current = index + 1;

        // If it's the last tab then hide the last button and show the finish instead
        if ($current >= $total) {
            $('.userListWithEmailForm #rootwizard').find('.pager .next').hide();
            $('.userListWithEmailForm #rootwizard').find('.pager .finish').show();
            $('.userListWithEmailForm #rootwizard').find('.pager .finish').removeClass('disabled');
        } else {
            $('.userListWithEmailForm #rootwizard').find('.pager .next').show();
            $('.userListWithEmailForm #rootwizard').find('.pager .finish').hide();
        }
    }
});
$('.userListWithEmailForm #rootwizard .finish').click(function () {
    var $validator = $('#commentForm1').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("commentForm1").submit();
    }
});
// $('#activate').on('ifChanged', function(event){
//     $('#commentForm1').bootstrapValidator('revalidateField', $('#activate'));
// });
$('#commentForm1').keypress(
    function (event) {
        if (event.which == '13') {
            event.preventDefault();
        }
    }
);

$(".userListWithOutEmailForm").bootstrapValidator({
    fields: {
        name: {
            validators: {
                notEmpty: {
                    message: userlist_name_required
                }
            },
            required: true,
            minlength: 3
        },
        vehicle_no: {
            validators: {
                notEmpty: {
                    message: userlist_vehicle_no_required
                }
            },
            required: true
        }
    }
});
$('.userListWithOutEmailForm #rootwizard').bootstrapWizard({
    'tabClass': 'nav nav-pills',
    'onNext': function (tab, navigation, index) {
        var $validator = $('#commentForm2').data('bootstrapValidator').validate();
        return $validator.isValid();
    },
    onTabClick: function (tab, navigation, index) {
        var $validator = $('#commentForm2').data('bootstrapValidator').validate();
        return $validator.isValid();
    },
    onTabShow: function (tab, navigation, index) {
        var $total = navigation.find('li').length;
        var $current = index + 1;

        // If it's the last tab then hide the last button and show the finish instead
        if ($current >= $total) {
            $('.userListWithOutEmailForm #rootwizard').find('.pager .next').hide();
            $('.userListWithOutEmailForm #rootwizard').find('.pager .finish').show();
            $('.userListWithOutEmailForm #rootwizard').find('.pager .finish').removeClass('disabled');
        } else {
            $('.userListWithOutEmailForm #rootwizard').find('.pager .next').show();
            $('.userListWithOutEmailForm #rootwizard').find('.pager .finish').hide();
        }
    }
});
$('.userListWithOutEmailForm #rootwizard .finish').click(function () {
    var $validator = $('#commentForm2').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("commentForm2").submit();
    }
});
$('#commentForm2').keypress(
    function (event) {
        if (event.which == '13') {
            event.preventDefault();
        }
    }
);

function saveChangeFormSettings(val) {
    $(".userListWithEmailForm .use_profile_name").val(val);
    if (val === 1) {
        $('.userListWithEmailForm #name').val($(".userListWithEmailForm .use_profile_name_val").val());
    }
    $('.view_userlist_name_change_form').modal('hide');
}

function saveUserListQuickEditFormChanges(id) {
    event.preventDefault();
    var form_data = new FormData();
    var form_wrapper = "form.userListQuickEditForm"+id;
    form_data.append("id", id);
    var _token = $(form_wrapper+" input[name='_token']").val();
    form_data.append("_token", _token);
    var name = $(form_wrapper+" input[name='name']").val();
    form_data.append("name", name);
    var email = $(form_wrapper+" input[name='email']").val();
    form_data.append("email", email);
    var phone = $(form_wrapper+" input[name='phone']").val();
    form_data.append("phone", phone);
    var group = $(form_wrapper+" select[name='group']").val();
    form_data.append("group", group);
    var energy_limit = $(form_wrapper+" input[name='energy_limit']").val();
    form_data.append("energy_limit", energy_limit);
    var language = $(form_wrapper+" select[name='language']").val();
    form_data.append("language", language);
    $.ajax({
        url: '/user-list/update/'+id,
        type: 'post',
        data: form_data, // Remember that you need to have your csrf token included
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (_response) {
            if ($.isEmptyObject(_response.error)) {
                $('tr.record'+id+' td.name').html(name);
                $('tr.record'+id+' td.email').html(email);
                printSuccessMsg(_response.success, form_wrapper);
            } else {
                printErrorMsg(_response.error, form_wrapper);
            }
        },
        error: function (_response) {
            $("div#processLoading").addClass('hide');
            // Handle error
            swal(
                'Something Went Wrong!',
                'Please Contact Administrator',
                'error'
            );
        }
    });
}

function saveUserListQuickEditWithOutEmailFormChanges(id) {
    event.preventDefault();
    var form_data = new FormData();
    var form_wrapper = "form.userListQuickEditWithOutEmailForm"+id;
    form_data.append("id", id);
    var _token = $(form_wrapper+" input[name='_token']").val();
    form_data.append("_token", _token);
    var name = $(form_wrapper+" input[name='name']").val();
    form_data.append("name", name);
    var vehicle_no = $(form_wrapper+" input[name='vehicle_no']").val();
    form_data.append("vehicle_no", vehicle_no);
    var group = $(form_wrapper+" select[name='group']").val();
    form_data.append("group", group);
    $.ajax({
        url: '/user-list/update-without-email/'+id,
        type: 'post',
        data: form_data, // Remember that you need to have your csrf token included
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (_response) {
            if ($.isEmptyObject(_response.error)) {
                $('tr.record'+id+' td.name').html(name);
                printSuccessMsg(_response.success, form_wrapper);
            } else {
                printErrorMsg(_response.error, form_wrapper);
            }
        },
        error: function (_response) {
            $("div#processLoading").addClass('hide');
            // Handle error
            swal(
                'Something Went Wrong!',
                'Please Contact Administrator',
                'error'
            );
        }
    });
}

function printSuccessMsg(msg, wrapper) {
    $(".print-success-msg").css('display', 'none');
    $(".print-error-msg").css('display', 'none');
    $(wrapper+" .print-success-msg").find("ul").html('');
    $(wrapper+" .print-success-msg").css('display', 'block');
    $.each(msg, function (key, value) {
        $(wrapper+" .print-success-msg").find("ul").append('<li style="list-style-type: none;">' + value + '</li>');
    });
}

function printErrorMsg(msg, wrapper) {
    $(".print-success-msg").css('display', 'none');
    $(".print-error-msg").css('display', 'none');
    $(wrapper+" .print-error-msg").find("ul").html('');
    $(wrapper+" .print-error-msg").css('display', 'block');
    $.each(msg, function (key, value) {
        $(wrapper+" .print-error-msg").find("ul").append('<li style="list-style-type: none;">' + value + '</li>');
    });
}

$('.userListWithOutEmailForm .has_email').click(function () {
    if($(this).is(':checked')){
        $(this).prop('checked', false);
        $('.userlist-without-email-con').addClass('hidden');
        $('.userlist-with-email-con').removeClass('hidden');
        $('.userlist-with-email-con .userListWithEmailForm .name').val($('.userlist-without-email-con .userListWithOutEmailForm .name').val());
        $('.userlist-with-email-con .userListWithEmailForm .vehicle_no').val($('.userlist-without-email-con .userListWithOutEmailForm .vehicle_no').val());
    }
});

$('.userListWithEmailForm .has_email').click(function () {
    if(!$(this).is(':checked')){
        $(this).prop('checked', true);
        $('.userlist-with-email-con').addClass('hidden');
        $('.userlist-without-email-con').removeClass('hidden');
        $('.userlist-without-email-con .userListWithOutEmailForm .name').val($('.userlist-with-email-con .userListWithEmailForm .name').val());
        $('.userlist-without-email-con .userListWithOutEmailForm .vehicle_no').val($('.userlist-with-email-con .userListWithEmailForm .vehicle_no').val());
    }
});