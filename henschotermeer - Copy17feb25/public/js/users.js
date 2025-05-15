"use strict";
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    jQuery('.mydatepicker, #datepicker').datepicker({
        autoclose: true,
        todayHighlight: true
    });
});
if (jQuery('.userForm').length) {
    $(".userForm").bootstrapValidator({
        fields: {
            name: {
                validators: {
                    notEmpty: {
                        message: 'Name is required'
                    }
                },
                required: true
            },
            email: {
                validators: {
                    notEmpty: {
                        message: 'Emaill is required'
                    }
                },
                required: true
            },
            password: {
                validators: {
                    notEmpty: {
                        message: 'Password is required'
                    }
                },
                required: true,
            },

            password_confirmation: {
                validators: {
                    notEmpty: {
                        message: 'Confirm password is required'
                    }
                },
                required: true
            },
            role: {
                validators: {
                    notEmpty: {
                        message: 'Role is required'
                    }
                },
                required: true
            }

        }
    });
}


$('#rootwizard .finish').click(function () {
    var $validator = $('#userForm').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("userForm").submit();
    }
});
