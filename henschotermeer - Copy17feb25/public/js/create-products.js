"use strict";
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('#type').change(function () {
        if ($(this).val() === "person_ticket" || $(this).val() === "year_ticket_person") {
            $('#no_of_vehicles').addClass('hidden')
        }
        else {
            $('#no_of_vehicles').removeClass('hidden')
        }

    })
    $("#ticket_count").click(function () {
        if ($(this).prop('checked')) {
            $('#num_of_time').removeClass('hidden')
        }
        else {
            $('#num_of_time').addClass('hidden')
        }
    });
    $("#vehicle_count").click(function () {
        if ($(this).prop('checked')) {
            $('#nums_of_vehicle').removeClass('hidden')
        }
        else {
            $('#nums_of_vehicle').addClass('hidden')
        }
    });
});
if (jQuery('.createProduct').length) {
    $(".createProduct").bootstrapValidator({
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
    var $validator = $('#createProduct').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("createProduct").submit();
    }
});

