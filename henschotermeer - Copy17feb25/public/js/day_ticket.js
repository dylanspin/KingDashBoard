"use strict";
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
if (jQuery('.dayTicketForm').length) {
    $(".dayTicketForm").bootstrapValidator({
        fields: {
            title: {
                validators: {
                    notEmpty: {
                        message: 'Title is required'
                    }
                }
            },
            title_nl: {
                validators: {
                    notEmpty: {
                        message: 'Title NL is required'
                    }
                }
            },
            price: {
                validators: {
                    notEmpty: {
                        message: 'Price is required'
                    }
                }
            }

        }
    });
}
$('#rootwizard .finish').click(function () {
    var $validator = $('#dayTicketForm').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("dayTicketForm").submit();
    }
});

