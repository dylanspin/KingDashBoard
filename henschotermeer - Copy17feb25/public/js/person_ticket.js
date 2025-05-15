"use strict";
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
if (jQuery('.personTicketForm').length) {
    $(".personTicketForm").bootstrapValidator({
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
    var $validator = $('#personTicketForm').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("personTicketForm").submit();
    }

});

