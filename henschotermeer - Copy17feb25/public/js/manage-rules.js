"use strict";
var barcode_required;
var barcode_name_required;
var status_required;
$(document).ready(function () {
    // $('[data-toggle="tooltip"]').tooltip();
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    $('#status').change(function () {
        let status = parseInt($(this).val());
console.log(status);
        let slug = $('#rule_slug').val();
        let name = $('#name').val();
        if (status === 1 && slug === "post_booking") {
            if (locate === "en") {
                $('#message_body').text(`Do you really wants to enable ${name} rule. If so then crossponding rule pre booking will be disable`);
            }
            else if (locate === "nl") {
                $('#message_body').text(`Wilt u de regel ${name} echt inschakelen? Als dit het geval is, wordt vooraf reserveren van de crossponding-regel uitgeschakeld`);
            }

            $("#pre_post_booking").modal("show");
        }
        else if (status === 0 && slug === "post_booking") {
            if (locate === "en") {
                $('#message_body').text(`Do you really wants to disable ${name} rule. If so then crossponding rule pre booking will be enable`);
            }
            else if (locate === "nl") {
                $('#message_body').text(`Wilt u de regel ${name} echt uitschakelen? Als dat zo is, wordt vooraf reserveren ingeschakeld`);
            }
            $("#pre_post_booking").modal("show");
        }

        if (status === 1 && slug === "pre_booking") {
            if (locate === "en") {
                $('#message_body').text(`Do you really wants to enable ${name} rule. If so then crossponding rule pre/post booking will be disable`);
            }
            else if (locate === "nl") {
                $('#message_body').text(`Wilt u de regel ${name} echt inschakelen? Als dat zo is, wordt de crossponding-regel vóór/na het boeken uitgeschakeld`);
            }

            $("#pre_post_booking").modal("show");
        }
        else if (status === 0 && slug === "pre_booking") {
            if (locate === "en") {
                $('#message_body').text(`Do you really wants to disable ${name} rule. If so then crossponding rule pre/post booking will be enable`);
            }
            else if (locate === "nl") {
                $('#message_body').text(`Wilt u de regel ${name} echt uitschakelen? Als dat zo is, wordt de kruispuntregel voor/na het boeken ingeschakeld`);
            }
            $("#pre_post_booking").modal("show");
        }
    });
    $("#rule-sortable").sortable({
        placeholder: "ui-state-highlight",
        update: function (e, ui) {
            var form_data = new FormData();
            var form_wrapper = "#rule_ordering";
            var itemOrder = $("#rule-sortable").sortable("toArray");
            form_data.append("itemOrder", itemOrder);
            $.ajax({
                url: "/manage-rules/sort-rule",
                type: "post",
                data: form_data, // Remember that you need to have your csrf token included
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (_response) {
                    if ($.isEmptyObject(_response.error)) {
                        printSuccessMsg(_response.success, form_wrapper);
                    } else {
                        printErrorMsg(_response.error, form_wrapper);
                    }
                },
                error: function (_response) {
                    $("div#processLoading").addClass("hide");
                    // Handle error
                    swal(
                        "Something Went Wrong!",
                        "Please Contact Administrator",
                        "error"
                    );
                },
            });
        },
    });

});
if (jQuery('.manageRule').length) {
    $(".manageRule").bootstrapValidator({
        fields: {
            name: {
                validators: {
                    notEmpty: {
                        message: barcode_name_required
                    }
                }
            }

        }
    });
}
$('#rootwizard').bootstrapWizard({
    'tabClass': 'nav nav-pills',
    'onNext': function (tab, navigation, index) {
        var $validator = $('#parkingRule').data('bootstrapValidator').validate();
        return $validator.isValid();
    },
    onTabClick: function (tab, navigation, index) {
        var $validator = $('#parkingRule').data('bootstrapValidator').validate();
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
    var $validator = $('#parkingRule').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("parkingRule").submit();
    }

});
