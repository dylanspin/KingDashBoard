"use strict";
var user_name_required;
var user_confirm_password_not_match;
var user_email_required;
var user_email_not_valid;
var user_gender_required;
// bootstrap wizard//
$("#gender, #gender1").select2({
    theme:"bootstrap",
    placeholder:"",
    width: '100%'
});
$('input[type="checkbox"].custom-checkbox').iCheck({
    checkboxClass: 'icheckbox_minimal-blue',
    increaseArea: '20%'
});

$("#commentForm").bootstrapValidator({
    fields: {
        name: {
            validators: {
                notEmpty: {
                    message: user_name_required
                }
            },
            required: true,
            minlength: 3
        },
        password_confirmation: {
            validators: {
                identical: {
                    field: 'password',
                    message: user_confirm_password_not_match
                }
            }
        },
        email: {
            validators: {
                notEmpty: {
                    message: user_email_required
                },
                emailAddress: {
                    message: user_email_not_valid
                }
            }
        },
        gender: {
            validators:{
                notEmpty:{
                    message: user_gender_required
                }
            }
        }
    }
});

$('#rootwizard').bootstrapWizard({
    'tabClass': 'nav nav-pills',
    'onNext': function(tab, navigation, index) {
        var $validator = $('#commentForm').data('bootstrapValidator').validate();
        return $validator.isValid();
    },
    onTabClick: function(tab, navigation, index) {
        var $validator = $('#commentForm').data('bootstrapValidator').validate();
        return $validator.isValid();
    },
    onTabShow: function(tab, navigation, index) {
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
$('#activate').on('ifChanged', function(event){
    $('#commentForm').bootstrapValidator('revalidateField', $('#activate'));
});