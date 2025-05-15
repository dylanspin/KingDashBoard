"use strict";
var first_name_required;
var last_name_required;
var email_required;
var email_valid;
var phone_num_required;
var checkin_date_required;
var checkin_time_required;
var checkout_date_required;
var checkout_time_required;
var vehicle_num_required;
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
$('.phone_mask').mask("000-0000000");
//Clock pickers
$('.clockpicker').clockpicker({
    donetext: 'Done',
}).find('input').change(function() {
    console.log(this.value);
});
//Date Picker
$('.mydatepicker').datepicker({
    autoclose: true,
    todayHighlight: true,
    format: 'dd/mm/yyyy',
    startDate: new Date()
});
if (jQuery('.sendTicketForm').length) {
    $(".sendTicketForm").bootstrapValidator({
        fields: {
            first_name: {
                validators: {
                    notEmpty: {
                        message: first_name_required
                    }
                }
            },
            last_name: {
                validators: {
                    notEmpty: {
                        message: last_name_required
                    }
                }
            },
            email: {
                validators: {
                    notEmpty: {
                        message: email_required
                    },
                    emailAddress: {
                        message: email_valid
                    }
                }
            },
            phone_num: {
                validators: {
                    notEmpty: {
                        message: phone_num_required
                    }
                }
            },
            checkin_date: {
                validators: {
                    notEmpty: {
                        message: checkin_date_required
                    }
                }
            },
            checkin_time: {
                validators: {
                    notEmpty: {
                        message: checkin_time_required
                    }
                }
            },
            checkout_date: {
                validators: {
                    notEmpty: {
                        message: checkout_date_required
                    }
                }
            },
            checkout_time: {
                validators: {
                    notEmpty: {
                        message: checkout_time_required
                    }
                }
            },
            vehicle_num: {
                validators: {
                    notEmpty: {
                        message: vehicle_num_required
                    }
                }
            }
        }
    });
}


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
    }
});
$('#rootwizard .finish').click(function () {
    var $validator = $('#commentForm').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("commentForm").submit();
    }

});

