"use strict";
$(document).ready(function () {
    // $('.current-location').addClass('hidden');
    // $('.imported-location').addClass('hidden');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    let current_url = getCurrentUrl();
    let is_imported = $('#is_imported').text();
    is_imported = parseInt(is_imported);
    if (current_url.trim() != "regression") {
        resetJsonFile();
        let location = $('#location_setting').val();
        if (location === "other_location" && is_imported) {
            $('#imported-location-test').removeClass('hidden')

        }
    }
    $('#confidence').keyup(function () {
        if ($(this).val() > 100) {
            $(this).val('')
        }
    });
    $('#import_confidence').keyup(function () {
        if ($(this).val() > 100) {
            $(this).val('')
        }
    });
    $('#location_setting').change(function () {
        if ($(this).val() == "current_location") {
            resetCurrentLocationForm();
            $('#current-location-test').removeClass('hidden')
            $('#imported-location-test').addClass('hidden')
            $('#other_location').addClass('hidden')
            $('#testing_response').empty();
            $('#info').addClass('hidden');
        }
        else if ($(this).val() == "other_location") {
            if (is_imported) {
                resetImportLocationForm();
            }
            $('#imported-location-test').removeClass('hidden')
            $('#current-location-test').addClass('hidden')
            if (!is_imported) {
                $('#other_location').removeClass('hidden')
            }
            $('#testing_response').empty();
            $('#info').addClass('hidden');

        }
        else {
            resetCurrentLocationForm();
            if (is_imported) {
                resetImportLocationForm();
            }
            $('#current-location-test').addClass('hidden')
            $('#imported-location-test').addClass('hidden')
            $('#other_location').addClass('hidden')
            $('#testing_response').empty();
            $('#info').addClass('hidden');
        }
    })
    $('#location_devices').change(function () {
        let case_id = parseInt($(this).val());
        let label = document.querySelector('select[name="device_id"] option:checked').parentElement.id
        switch (parseInt(label)) {
            case 1:
                showfields();
                hideFileType();
                showVehicleTicketNumber()
                break;
            case 2:
                showfields();
                hideVehicleTicketNumber();
                hideFileType();
                break;
            case 3:
                showfields();
                showFileType()
                hideVehicleTicketNumber()
                break;
            case 4:
                showVehicleTicketNumber()
                showFileType()
                break;
            default:
                hidefields()
                hideFileType()
                hideVehicleTicketNumber()
                break;
        }
    })
    $('#import_location_devices').change(function () {
        let case_id = parseInt($(this).val());
        let label = document.querySelector('select[name="import_device_id"] option:checked').parentElement.id
        console.log(label)
        switch (parseInt(label)) {
            case 1:
                showfields();
                hideImportFileType();
                showImportVehicleTicketNumber();
                break;
            case 2:
                showfields();
                hideImportVehicleTicketNumber();
                hideImportFileType();
                break;
            case 3:
                showfields();
                showImportFileType();
                hideImportVehicleTicketNumber();
                break;
            case 4:
                showImportVehicleTicketNumber();
                showImportFileType();
                break;
            default:
                hidefields();
                hideImportFileType();
                hideImportVehicleTicketNumber();
                break;
        }
    })
    $('#image_type').change(function () {
        let type = $(this).val()
        if (type == "file") {
            $('#vehicle_image').removeClass('hidden');
            $('#base_encoded').addClass('hidden');
        }
        else if (type == "base_encoded") {
            $('#vehicle_image').addClass('hidden');
            $('#base_encoded').removeClass('hidden');
        }
        else {
            $('#vehicle_image').addClass('hidden');
            $('#base_encoded').addClass('hidden');
        }
    })
    $('#import_image_type').change(function () {
        let type = $(this).val()
        if (type == "file") {
            $('#import_vehicle_image').removeClass('hidden');
            $('#base_encoded').addClass('hidden');
        }
        else if (type == "base_encoded") {
            $('#import_vehicle_image').addClass('hidden');
            $('#import_base_encoded').removeClass('hidden');
        }
        else {
            $('#import_vehicle_image').addClass('hidden');
            $('#import_base_encoded').addClass('hidden');
        }
    })
    $("#ruleListId").sortable({
        placeholder: "ui-state-highlight",
        update: function (e, ui) {
            var itemOrder = $("#ruleListId").sortable("toArray");
            $('#sorted_rules').val(itemOrder);
        }
    });
    $("#importruleListId").sortable({
        placeholder: "ui-state-highlight",
        update: function (e, ui) {
            var itemOrder = $("#ruleListId").sortable("toArray");
            $('#sorted_rules').val(itemOrder);
        }
    });
    $("#sortable-grid").sortable({
        placeholder: "ui-state-highlight"
    });
    // $("input[name='rules[]']").change(function () {
    //     var tmp = $(this).val();
    //     if ($(this).is(':checked')) {
    //         checkbox.push(tmp)
    //     } else {
    //         checkbox.splice($.inArray(tmp, checkbox), 1);
    //     }
    // });
    $('#current-location-test').submit(function (e) {
        var checkbox = [];
        var special_rule = ['comfort_security_check', 'has_always_access', 'pre_booking', 'post_booking'];
        $('input.parking-rules:checkbox:checked').each(function () {
            checkbox.push($(this).val());
        });
        e.preventDefault();
        let response = checkFormField()
        if (response) {
            return true
        }
        $('#testing_response').empty();
        jQuery("body").find(".preloader").css({ display: "block" });
        var formData = new FormData(this);
        formData.append('sorted_rules', checkbox);

        let ajax_url = "/regression/run-test"
        $.ajax({
            url: ajax_url,
            type: 'POST',
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function (data) {
                jQuery("body").find(".preloader").fadeOut(1000);
                let logs = data.logs;
                let response = data.response
                var end_res = ""
                let bg = "";
                let status = "";
                let success = [];
                let denied = [];
                let rules_status = [];
                logs.forEach(function (item, index) {
                    status = checkRuleStatus(response, item)
                    rules_status.push(status)
                    if (status.status == "success") {
                        success.push(status)
                        bg = "custom-success";
                    }
                    else if (status.status == "denied") {
                        denied.push(status)
                        bg = "custom-danger";
                    }
                    else if (status.status == "not_applicable") {
                        bg = "custom-secondary"
                    }
                    else {
                        bg = "bg-success"
                    }
                    $('#info').removeClass('hidden')
                    // if (status.rule !== "matching_enable") {
                    if (index % 3 === 0) {
                        $('#testing_response').append('<div class="row"></div>');
                    }
                    var column = `
                        <div class="col-md-4 col-4">
                            <div class="card ">
                                <span class="badge ${bg} f-40">${index + 1}</span>
                                <h4>${item.message}</h4>
                                <button class="btn btn-info show-detail" id="${item.rule_id}"
                                    onclick="showDetail(${item.rule_id},'${item.testing_session_id}')"
                                    data-toggle="modal" data-target="#rule-${item.rule_id}">View Detail </button>
                            </div>
                        </div>
                        <div id="rule-${item.rule_id}" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">${item.message}</h4>
                                        </div>
                                        <div class="modal-body" id=log-${item.rule_id}>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    $('#testing_response .row:last').append(column);
                    // }
                });
                if (response.access.access_status == "success") {
                    let rule_response = ''
                    let lastresponse = success.slice(-1);
                    for (let i = 0; i < lastresponse.length; i++) {
                        const item = lastresponse[i];
                        rule_response = `Due to "${item.rule}" system has given the access.`;
                    }
                    let bg_response = "custom-success";
                    end_res = `<div class="col-md-offset-2 col-md-8">
                                <div class="card"> 
                                    <span class="badge f-40 ${bg_response}">${response.access.access_status}</span>
                                    <h4>${response.access.message}</h4>
                                    <button class="btn btn-info show-detail" id="res-data""
                                        data-toggle="modal" data-target="#response_out">View Detail </button>
                                </div>
                            </div>
                            <div id="response_out" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">${response.access.message}</h4>
                                        </div>
                                        <div class="modal-body" id="res_success">
                                        <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12 col-12">
                                <div class="booking-info">
                                    <div class="booking-box-wrapper">
                                        <span class="bBox info"></span>
                                        <span class="bDetials">Info</span>
                                    </div>
                                    <div class="booking-box-wrapper">
                                        <span class="bBox available"></span>
                                        <span class="bDetials">Success</span>
                                    </div>
                                    <div class="booking-box-wrapper">
                                        <span class="bBox booked"></span>
                                        <span class="bDetials">Denied</span>
                                    </div>
                                    <div class="booking-box-wrapper">
                                        <span class="bBox charge-in"></span>
                                        <span class="bDetials">Not Applicable</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                                            ${rules_status.map(function (item, index) {
                                                if (item == undefined || item?.status == undefined) return null
                                                if (item.status == 'info') {
                                                    return (
                                                        `<ul class="list-unstyled custom-border">
                                                        <li class="border">${item.rule} <i class="fa fa-arrow-right"></i> ${item.status}<li>
                                                    </ul>`
                                                    )


                                                } else if (item.status == 'denied') {
                                                    return (
                                                        `<ul class="list-unstyled custom-border">
                                                        <li class="border">${item.rule} <i class="fa fa-arrow-right"></i> ${item.status}<li>
                                                    </ul>`
                                                    )


                                                } else if (item.status == 'not_applicable') {
                                                    return (
                                                        `<ul class="list-unstyled custom-border">
                                                         <li class="border">${item.rule} <i class="fa fa-arrow-right"></i> ${item.status}<li>
                                                    </ul>`
                                                    )


                                                } else {
                                                    return (
                                                        `<ul class="list-unstyled custom-border">
                                                         <li class="border">${item.rule} <i class="fa fa-arrow-right"></i> ${item.status}<li>
                                                    </ul>`
                                                    )


                                                }

                                            })}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>

                                </div>
                            </div>`
                }
                else if (response.access.access_status == "denied") {
                    let rule_response = ''
                    let lastresponse = denied.slice(-1);
                    for (let i = 0; i < lastresponse.length; i++) {
                        const item = lastresponse[i];
                        rule_response = `System has denied because (${item.rule}) not meet the given condition.`;
                    }
                    let bg_response = "custom-danger";
                    end_res = `<div class="col-md-offset-2 col-md-8">
                                <div class="card"> 
                                    <span class="badge f-40 ${bg_response}">${response.access.access_status}</span>
                                    <h4>${response.access.message}</h4>
                                    <button class="btn btn-info show-detail" id="res-data""
                                        data-toggle="modal" data-target="#response_out">View Detail </button>
                                </div>
                            </div>
                            <div id="response_out" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">${response.access.message}</h4>
                                        </div>
                                        <div class="modal-body custom-border" id="res_denied">
                                        <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12 col-12">
                                <div class="booking-info">
                                    <div class="booking-box-wrapper">
                                        <span class="bBox info"></span>
                                        <span class="bDetials">Info</span>
                                    </div>
                                    <div class="booking-box-wrapper">
                                        <span class="bBox available"></span>
                                        <span class="bDetials">Success</span>
                                    </div>
                                    <div class="booking-box-wrapper">
                                        <span class="bBox booked"></span>
                                        <span class="bDetials">Denied</span>
                                    </div>
                                    <div class="booking-box-wrapper">
                                        <span class="bBox charge-in"></span>
                                        <span class="bDetials">Not Applicable</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                                          ${rules_status.map(function (item, index) {
                                              if (item == undefined || item?.status == undefined) return null
                                              if (item.status == 'info') {
                                                  return (
                                                      `<ul class="list-unstyled custom-border">
                                                         <li class="border">${item.rule} <i class="fa fa-arrow-right"></i> ${item.status}<li>
                                                    </ul>`
                                                  )


                                              } else if (item.status == 'denied') {
                                                  return (
                                                      `<ul class="list-unstyled custom-border">
                                                         <li class="border">${item.rule} <i class="fa fa-arrow-right"></i> ${item.status}<li>
                                                    </ul>`
                                                  )


                                              } else if (item.status == 'not_applicable') {
                                                  return (
                                                      `<ul class="list-unstyled custom-border">
                                                         <li class="border">${item.rule} <i class="fa fa-arrow-right"></i> ${item.status}<li>
                                                    </ul>`
                                                  )


                                              } else {
                                                  return (
                                                      `<ul class="list-unstyled custom-border">
                                                         <li class="border">${item.rule} <i class="fa fa-arrow-right"></i> ${item.status}<li>
                                                    </ul>`
                                                  )


                                              }

                                          })}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>

                                </div>
                            </div>`
                }
                $('#testing_response').append(end_res);
                $('#testing_response').removeClass('hidden');
                $('#end_response').removeClass('hidden');
            },
            error: function (error) {
                console.error("Error fetching data: " + error);
            },
            complete: function (data) {
                $('html, body').animate({
                    scrollTop: $("#testing_response").offset().top
                }, 2000);
            }
        });
    })
    $('#imported-location-test').submit(function (e) {
        var checkbox = [];
        $('input.import-parking-rules:checkbox:checked').each(function () {
            checkbox.push($(this).val());
        });
        e.preventDefault();
        let response = checkImportFormField()
        if (response) {
            return true
        }
        $('#testing_response').empty();
        jQuery("body").find(".preloader").css({ display: "block" });
        var formData = new FormData(this);
        formData.append('sorted_rules', checkbox);
        let ajax_url = "/regression/run-test"
        $.ajax({
            url: ajax_url,
            type: 'POST',
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function (data) {
                jQuery("body").find(".preloader").fadeOut(1000);
                let logs = data.logs;
                let response = data.response
                var end_res = ""
                let bg = "";
                let status = "";
                let success = [];
                let denied = [];
                logs.forEach(function (item, index) {
                    status = checkRuleStatus(response, item)
                    if (status.status == "success") {
                        success.push(status)
                        bg = "custom-success";
                    }
                    else if (status.status == "denied") {
                        denied.push(status)
                        bg = "custom-danger";
                    }
                    else if (status.status == "not_applicable") {
                        bg = "custom-secondary"
                    }
                    else {
                        bg = "bg-success"
                    }
                    $('#info').removeClass('hidden')
                    // if (status.rule !== "matching_enable") {
                    if (index % 3 === 0) {
                        $('#testing_response').append('<div class="row"></div>');
                    }
                    var column = `
                        <div class="col-md-4 col-4">
                            <div class="card ">
                                <span class="badge ${bg} f-40">${index + 1}</span>
                                <h4>${item.message}</h4>
                                <button class="btn btn-info show-detail" id="${item.rule_id}"
                                    onclick="showDetail(${item.rule_id},'${item.testing_session_id}')"
                                    data-toggle="modal" data-target="#rule-${item.rule_id}">View Detail </button>
                            </div>
                        </div>
                        <div id="rule-${item.rule_id}" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">${item.message}</h4>
                                        </div>
                                        <div class="modal-body" id=log-${item.rule_id}>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    $('#testing_response .row:last').append(column);
                    // }
                });
                if (response.access.access_status == "success") {
                    let rule_response = ''
                    let lastresponse = success.slice(-1);
                    for (let i = 0; i < lastresponse.length; i++) {
                        const item = lastresponse[i];
                        rule_response = `Due to "${item.rule}" system has given the access.`;
                    }
                    let bg_response = "custom-success";
                    end_res = `<div class="col-md-offset-2 col-md-8">
                                <div class="card"> 
                                    <span class="badge f-40 ${bg_response}">${response.access.access_status}</span>
                                    <h4>${response.access.message}</h4>
                                    <button class="btn btn-info show-detail" id="res-data""
                                        data-toggle="modal" data-target="#response_out">View Detail </button>
                                </div>
                            </div>
                            <div id="response_out" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">${response.access.message}</h4>
                                        </div>
                                        <div class="modal-body custom-border" id="endres">
                                            ${rule_response}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>

                                </div>
                            </div>`
                }
                else if (response.access.access_status == "denied") {
                    let rule_response = ''
                    let lastresponse = denied.slice(-1);
                    for (let i = 0; i < lastresponse.length; i++) {
                        const item = lastresponse[i];
                        rule_response = `System has denied because (${item.rule}) not meet the given condition.`;
                    }
                    let bg_response = "custom-danger";
                    end_res = `<div class="col-md-offset-2 col-md-8">
                                <div class="card"> 
                                    <span class="badge f-40 ${bg_response}">${response.access.access_status}</span>
                                    <h4>${response.access.message}</h4>
                                    <button class="btn btn-info show-detail" id="res-data""
                                        data-toggle="modal" data-target="#response_out">View Detail </button>
                                </div>
                            </div>
                            <div id="response_out" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">${response.access.message}</h4>
                                        </div>
                                        <div class="modal-body custom-border" id="endres">
                                            ${rule_response}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>

                                </div>
                            </div>`
                }
                $('#testing_response').append(end_res);
                $('#testing_response').removeClass('hidden');
                $('#end_response').removeClass('hidden');
            },
            error: function (error) {
                console.error("Error fetching data: " + error);
            },
            complete: function (data) {
                $('html, body').animate({
                    scrollTop: $("#testing_response").offset().top
                }, 2000);
            }
        });
    })
});
function resetCurrentLocationForm() {
    document.getElementById("current-location-test").reset();
}
function resetImportLocationForm() {
    document.getElementById("imported-location-test").reset();
}
function resetJsonFile() {
    if (!is_imported) {
        document.getElementById("import-location-json").reset();
    }
    else {
        document.getElementById("location-json").reset();
    }


}
function showDetail(id, session_id) {
    if (!$.trim($('#log-' + id).html()).length) {
        jQuery("body").find(".preloader").css({ display: "block" });
        let ajax_url = "/regression/get-detail";
        var data = {
            rule_id: id,
            session_id: session_id
        }
        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: data,
            success: function (data) {
                jQuery("body").find(".preloader").fadeOut(1000);
                data.forEach(function (item, index) {
                    if (index === 0) return;
                    var column = `<ul class="list-unstyled custom-border">
                                <li class="border">${item.type == "info" ? `<span class="badge badge-primary"><i class="fa fa-arrow-right"></i></span>` : `<span class="badge badge-danger"><i class="fa fa-arrow-right"></i></span>`} <span>${item.message}</span></li>
                                </ul>`
                    $('#log-' + id).append(column);
                });
            },
            error: function (error) {
                console.error("Error fetching data: " + error);
            }
        });
    }
}
function showEndResponse() {

}
function getIdsOfRules() {
    var values = [];
    $('.rules-group').each(function (index, value) {
        values.push($(this).attr("id").replace(value));
    });
    $('#sorted_rules').val(values);
}

function formatDateToEuropean(dateString) {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    const hours = date.getHours();
    const minutes = date.getMinutes();
    const seconds = date.getSeconds();

    // Create the European date format string (dd/mm/yyyy)
    const europeanDate = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;

    return europeanDate;
}
function getCurrentUrl() {
    let path = window.location.pathname.split('/regression')[1];
    console.log(path)
    return path.trim();
}
function hideVehicleTicketNumber() {
    $('#ticket_vehicle_number').addClass('hidden')
}
function hideImportVehicleTicketNumber() {
    $('#import_ticket_vehicle_number').addClass('hidden')
}
function showVehicleTicketNumber() {
    $('#ticket_vehicle_number').removeClass('hidden')
}
function showImportVehicleTicketNumber() {
    $('#import_ticket_vehicle_number').removeClass('hidden')
}
function showFileType() {
    $('#vehicle_image_type').removeClass('hidden')
}
function showImportFileType() {
    $('#import_vehicle_image_type').removeClass('hidden')
}
function hideFileType() {
    $('#vehicle_image_type').addClass('hidden')
    $('#vehicle_image').addClass('hidden')
    $('#base_encoded').addClass('hidden')
}
function hideImportFileType() {
    $('#import_vehicle_image_type').addClass('hidden')
    $('#import_vehicle_image').addClass('hidden')
    $('#import_base_encoded').addClass('hidden')
}
function hidefields() {
    $('#identifier').addClass('hidden');
    $('#confidence').addClass('hidden')
    $('#lang').addClass('hidden')
    $('#regression_test').addClass('hidden')
}
function showfields() {
    $('#identifier').removeClass('hidden');
    $('#confidence').removeClass('hidden')
    $('#lang').removeClass('hidden')
    $('#regression_test').removeClass('hidden')
}
function checkFormField() {
    let locationdevice = $('#location_devices').val().trim();
    let identifier = $('#identifier').val().trim();
    let confidence = $('#confidence').val().trim();
    return locationdevice === '' || identifier === '' || confidence === '';
}

function checkImportFormField() {
    let import_location_device = $('#import_location_devices').val().trim();
    let identifier = $('#import_identifier').val().trim();
    let confidence = $('#import_confidence').val().trim();
    return import_location_device === '' || identifier === '' || confidence === '';
}

function checkRule(item) {
    if (item.rule_log)
        return item.rule_log.slug;
}
function checkRuleStatus(response, item) {
    let rule = item.rule_log;
    let security = "comfort_security_check";
    let status = {};
    if (rule === null || rule === undefined) {
        return false;
    }
    for (const [key, value] of Object.entries(response)) {
        if (key != "access" && key != "validBooking" && key != "booking_status") {
            if (rule.slug == "matching_enable") {
                return { "rule": key, 'status': 'success' }
            }
            if (rule.slug == key) {
                if (key == "comfort_security_check") {
                    return { "rule": key, 'status': value[key].status }
                }
                return { "rule": key, 'status': value.status }
            }
        }
    }
}

if (jQuery('.regression-test').length) {
    $(".regression-test").bootstrapValidator({
        fields: {
            available_devices: {
                validators: {
                    notEmpty: {
                        message: 'Please Select Case'
                    },
                }
            },
            device_id: {
                validators: {
                    notEmpty: {
                        message: 'Please Select Device'
                    },
                }
            },
            identifier: {
                validators: {
                    notEmpty: {
                        message: 'Enter Plate Number / Barcode'
                    },
                }
            },
            confidence: {
                validators: {
                    notEmpty: {
                        message: 'Enter Confidence'
                    },
                }
            }
        }
    });
}


