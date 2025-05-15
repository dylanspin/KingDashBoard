"use strict";
var device_name_required;
var device_type_required;
var device_direction_required;
var device_ip_required;
var popup_time_required;
var device_port_required;
var switch_port;
var device_anti_passback_required;
var device_enable_log_required;
var device_enable_idle_screen_required;
var device_qr_code_type_required;
var device_camera_enabled_required;
$(document).ready(function () {
  $('[data-toggle="tooltip"]').tooltip();
  if (jQuery(".deviceForm").length) {
    $(".select2").select2();
  }
  $.ajaxSetup({
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
  });
  $(".deviceForm .finish").on("click", function () {
    jQuery("body").find(".preloader").css({ display: "block" });
    $(function () {
      setTimeout(function () {
        jQuery("body").find(".preloader").fadeOut(5000);
      }, 5000);
    });
  });

  $(".deviceForm .device_ip").on("keyup", function () {
    // Using Regex expression for validating IPv4
    var ip_address =
      /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
    var content = $("#device_ip").val();
    if (content.length > 0) {
      if (ip_address.test(content)) {
        $(".custom-ip-help-block").addClass("d-none");
        $(".custom-ip-help-block").text("");
      } else {
        $(".check-ip").addClass("has-error");
        $(".custom-ip-help-block").addClass("d-block");
        $(".custom-ip-help-block").addClass("text-danger");
        $(".custom-ip-help-block").text("IP Address is Invalid");
      }
    } else {
      $(".help-block").hide();
      $(".custom-ip-help-block").show();
    }
  });
  $(".deviceForm .anti_passback").on("change", function () {
    if (jQuery(this).val() === "1") {
      jQuery(".deviceForm .time_passback_con").removeClass("hidden");
      jQuery(".deviceForm .time_passback_con .time_passback").prop(
        "required",
        true
      );
    } else {
      jQuery(".deviceForm .time_passback_con").addClass("hidden");
      jQuery(".deviceForm .time_passback_con .time_passback").prop(
        "required",
        false
      );
    }
  });
  $(".pager .next a, .tab2_t").on("click", function () {
    var is_error = 0;
    if (!jQuery(this).parent().hasClass("finish")) {
      jQuery(".deviceForm #tab1 .form-group").each(function (index) {
        if (jQuery(this).hasClass("has-error")) {
          is_error = 1;
        }
      });
      if (is_error === 1) {
        return false;
      }
    }
  });
  $(".deviceForm .has_gate").on("change", function () {
    if (jQuery(this).val() === "1") {
      jQuery(".deviceForm .barrier_close_time_con").removeClass("hidden");
      jQuery(".deviceForm .barrier_close_time_con .barrier_close_time").prop(
        "required",
        true
      );
    } else {
      jQuery(".deviceForm .barrier_close_time_con").addClass("hidden");
      jQuery(".deviceForm .barrier_close_time_con .barrier_close_time").prop(
        "required",
        true
      );
    }
  });

  $(".deviceForm .device_type").on("change", function () {
    jQuery(".deviceForm .anti_passback_con").addClass("hidden");
    jQuery(".deviceForm .related_od_con").addClass("hidden");
    jQuery(".deviceForm .related_device_con").addClass("hidden");
    jQuery(".deviceForm .related_ticket_readers_con").addClass("hidden");
    jQuery(".deviceForm .focus_away_con").addClass("hidden");
    jQuery(".deviceForm .opacity_input_con").addClass("hidden");
    jQuery(".deviceForm .od_enabled_con").addClass("hidden");
    jQuery(".deviceForm .has_gate_con").addClass("hidden");
    jQuery(".deviceForm .barrier_close_time_con").addClass("hidden");
    jQuery(".deviceForm .enable_log_con").addClass("hidden");
    jQuery(".deviceForm .enable_idle_screen_con").addClass("hidden");
    jQuery(".deviceForm .qr_code_type_con").addClass("hidden");
    jQuery(".deviceForm .message_text_size_con").addClass("hidden");
    jQuery(".deviceForm .time_text_size_con").addClass("hidden");
    jQuery(".deviceForm .date_text_size_con").addClass("hidden");
    jQuery(".deviceForm .bottom_tray_text_size_con").addClass("hidden");
    jQuery(".deviceForm .keyboard_key_size_con").addClass("hidden");
    jQuery(".deviceForm .background_color_con").addClass("hidden");
    jQuery(".deviceForm .text_color_con").addClass("hidden");
    jQuery(".deviceForm .device_direction_con").removeClass("hidden");
    jQuery(".deviceForm .confidence_level_con").addClass("hidden");
    jQuery(".deviceForm .num_tries_con").addClass("hidden");
    jQuery(".deviceForm .confidence_level_lowest_con").addClass("hidden");
    jQuery(".deviceForm .character_match_limit_con").addClass("hidden");
    jQuery(".deviceForm .ccv_pos_ip_con").addClass("hidden");
    jQuery(".deviceForm .ccv_pos_port_con").addClass("hidden");
    jQuery(".deviceForm .has_sdl_con").addClass("hidden");
    jQuery(".deviceForm .gate_close_transaction_enabled_con").addClass(
      "hidden"
    );
    jQuery(".deviceForm .has_pdl_con").addClass("hidden");
    jQuery(".deviceForm .plate_correction_enabled_con").addClass("hidden");
    jQuery(".deviceForm .has_enable_person_ticket_con").addClass("hidden");
    jQuery(".deviceForm .has_enable_parking_ticket_con").addClass("hidden");
    jQuery(
      ".deviceForm .has_enable_person_ticket_con #has_enable_person_ticket"
    ).prop("checked", false);
    jQuery(
      ".deviceForm .has_enable_parking_ticket_con #has_enable_parking_ticket"
    ).prop("checked", false);
    jQuery(".deviceForm .advert_image_file_con").addClass("hidden");
    jQuery(".deviceForm #device_port").val("");
    jQuery(".ideal_screen_image").addClass("hidden");
    jQuery('.deviceForm .has_emergency_con').addClass('hidden');
    jQuery('.deviceForm .plate_length_con').addClass('hidden');
    jQuery('.deviceForm .character_height_con').addClass('hidden');
    jQuery('.deviceForm .triple_exposure_con').addClass('hidden');
    jQuery('.deviceForm .disable_night_mode_con').addClass('hidden');
    jQuery('.deviceForm .light_condition_con').addClass('hidden');
    if (jQuery(this).val() === "1" || jQuery(this).val() === "2") {
      jQuery(".deviceForm .anti_passback_con").removeClass("hidden");
      jQuery(".deviceForm .related_payment_terminal").addClass("hidden");
      jQuery(".deviceForm .focus_away_con").removeClass("hidden");
      jQuery(".deviceForm .opacity_input_con").removeClass("hidden");
      jQuery(".deviceForm .od_enabled_con").removeClass("hidden");
      jQuery(".deviceForm .related_od_con").removeClass("hidden");
      jQuery(".deviceForm .has_gate_con").removeClass("hidden");
      jQuery(".deviceForm .enable_log_con").removeClass("hidden");
      jQuery(".deviceForm .enable_idle_screen_con").removeClass("hidden");
      jQuery(".deviceForm .qr_code_type_con").removeClass("hidden");
      jQuery(".deviceForm .gate_close_transaction_enabled_con").removeClass(
        "hidden"
      );
      jQuery(".ideal_screen_image").removeClass("hidden");
      if (jQuery(this).val() === "1") {
        jQuery(".deviceForm .has_sdl_con").removeClass("hidden");
        jQuery(".deviceForm .has_pdl_con").removeClass("hidden");
        jQuery(".deviceForm .plate_correction_enabled_con").removeClass(
          "hidden"
        );
        jQuery('.deviceForm .has_emergency_con').addClass('hidden');
        jQuery('.deviceForm .plate_length_con').addClass('hidden');
        jQuery('.deviceForm .character_height_con').addClass('hidden');
        jQuery('.deviceForm .triple_exposure_con').addClass('hidden');
        jQuery('.deviceForm .disable_night_mode_con').addClass('hidden');
        jQuery('.deviceForm .light_condition_con').addClass('hidden');
        jQuery(".deviceForm .tr_version_con").removeClass(
          "hidden"
        );
      }
      jQuery(".deviceForm .related_switch").addClass("hidden");
      jQuery(".deviceForm .advert_image_file_con").removeClass("hidden");
      jQuery(".deviceForm #device_port").val("8085");
      jQuery('.deviceForm .has_emergency_con').addClass('hidden');
      jQuery('.deviceForm .plate_length_con').addClass('hidden');
      jQuery('.deviceForm .character_height_con').addClass('hidden');
      jQuery('.deviceForm .triple_exposure_con').addClass('hidden');
      jQuery('.deviceForm .disable_night_mode_con').addClass('hidden');
      jQuery('.deviceForm .light_condition_con').addClass('hidden');
      jQuery(".deviceForm .related_switch").addClass("hidden");
      jQuery(".deviceForm .advert_image_file_con").removeClass("hidden");
      jQuery(".deviceForm #device_port").val("8085");
      jQuery('.deviceForm .matching_distance_con').addClass('hidden');
    } else if (jQuery(this).val() === "3") {
      jQuery(".deviceForm .related_ticket_readers_con").removeClass("hidden");
      jQuery(".deviceForm .enable_log_con").removeClass("hidden");
      jQuery(".deviceForm #device_port").val("8085");
      jQuery(".deviceForm .confidence_level_con").removeClass("hidden");
      jQuery(".deviceForm .num_tries_con").removeClass("hidden");
      jQuery(".deviceForm .has_sdl_con").removeClass("hidden");
      jQuery(".deviceForm .gate_close_transaction_enabled_con").removeClass(
        "hidden"
      );
      jQuery(".deviceForm .related_payment_terminal").addClass("hidden");
      jQuery(".deviceForm .related_switch").addClass("hidden");
      jQuery(".deviceForm .has_gate_con").removeClass("hidden");
      jQuery('.deviceForm .has_emergency_con').removeClass('hidden');
      jQuery('.deviceForm .plate_length_con').removeClass('hidden');
      jQuery('.deviceForm .character_height_con').removeClass('hidden');
      jQuery('.deviceForm .triple_exposure_con').removeClass('hidden');
      jQuery('.deviceForm .disable_night_mode_con').removeClass('hidden');
      jQuery('.deviceForm .light_condition_con').removeClass('hidden');
      jQuery('.deviceForm .matching_distance_con').removeClass('hidden');
      jQuery(".deviceForm .tr_version_con").addClass(
        "hidden"
      );

    } else if (jQuery(this).val() === "4") {
      jQuery(".deviceForm .device_direction_con").addClass("hidden");
      jQuery(".deviceForm .related_device_con").removeClass("hidden");
      jQuery(".deviceForm .message_text_size_con").removeClass("hidden");
      jQuery(".deviceForm .time_text_size_con").removeClass("hidden");
      jQuery(".deviceForm .date_text_size_con").removeClass("hidden");
      jQuery(".deviceForm .bottom_tray_text_size_con").removeClass("hidden");
      jQuery(".deviceForm .enable_idle_screen_con").removeClass("hidden");
      jQuery(".deviceForm .advert_image_file_con").removeClass("hidden");
      jQuery(".deviceForm #device_port").val("8090");
      jQuery(".deviceForm .related_switch").addClass("hidden");
      jQuery(".deviceForm .related_payment_terminal").addClass("hidden");
      jQuery('.deviceForm .has_emergency_con').addClass('hidden');
      jQuery('.deviceForm .plate_length_con').addClass('hidden');
      jQuery('.deviceForm .character_height_con').addClass('hidden');
      jQuery('.deviceForm .triple_exposure_con').addClass('hidden');
      jQuery('.deviceForm .disable_night_mode_con').addClass('hidden');
      jQuery('.deviceForm .light_condition_con').addClass('hidden');
      jQuery('.deviceForm .matching_distance_con').addClass('hidden');
      jQuery(".deviceForm .tr_version_con").addClass(
        "hidden"
      );
    } else if (jQuery(this).val() === "6") {
      jQuery(".deviceForm .device_direction_con").addClass("hidden");
      jQuery(".deviceForm .related_switch").removeClass("hidden");
      jQuery(".deviceForm .device-related-port").addClass("hidden");
      jQuery(".deviceForm .enable_log_con").removeClass("hidden");
      jQuery(".deviceForm .ccv_pos_ip_con").removeClass("hidden");
      jQuery(".deviceForm .ccv_pos_port_con").removeClass("hidden");
      jQuery(".deviceForm .has_enable_person_ticket_con").removeClass("hidden");
      jQuery(".deviceForm .has_enable_parking_ticket_con").removeClass(
        "hidden"
      );
      jQuery(".deviceForm .advert_image_file_con").removeClass("hidden");
      jQuery(".deviceForm .related_payment_terminal").addClass("hidden");
      jQuery('.deviceForm .has_emergency_con').addClass('hidden');
      jQuery('.deviceForm .plate_length_con').addClass('hidden');
      jQuery('.deviceForm .character_height_con').addClass('hidden');
      jQuery('.deviceForm .triple_exposure_con').addClass('hidden');
      jQuery('.deviceForm .disable_night_mode_con').addClass('hidden');
      jQuery('.deviceForm .light_condition_con').addClass('hidden');
      jQuery('.deviceForm .matching_distance_con').addClass('hidden');
      jQuery(".deviceForm .tr_version_con").addClass(
        "hidden"
      );
    } else if (jQuery(this).val() === "12") {
      jQuery(".deviceForm .related_payment_terminal").removeClass("hidden");
      jQuery(".deviceForm .single_device_port").addClass("hidden");
      jQuery(".deviceForm .device-related-port").removeClass("hidden");
      jQuery(".deviceForm .device-password").removeClass("hidden");
      jQuery(".deviceForm .barrier_close_time_con").removeClass("hidden");
      jQuery(".deviceForm .related_payment_terminal").removeClass("hidden");
      jQuery(".deviceForm .device_direction_con").addClass("hidden");
      jQuery(".deviceForm .enable_log_con").addClass("hidden");
      jQuery(".deviceForm .related_switch").addClass("hidden");
      jQuery(".deviceForm .popup_time_con").addClass("hidden");
      jQuery(".deviceForm .has_always_con").addClass("hidden");
      jQuery(".deviceForm .has_enable_person_ticket_con").addClass("hidden");
      jQuery(".deviceForm .has_enable_parking_ticket_con").addClass("hidden");
      jQuery('.deviceForm .has_emergency_con').addClass('hidden');
      jQuery('.deviceForm .plate_length_con').addClass('hidden');
      jQuery('.deviceForm .character_height_con').addClass('hidden');
      jQuery('.deviceForm .triple_exposure_con').addClass('hidden');
      jQuery('.deviceForm .disable_night_mode_con').addClass('hidden');
      jQuery('.deviceForm .light_condition_con').addClass('hidden');
      jQuery('.deviceForm .matching_distance_con').addClass('hidden');
      jQuery(".deviceForm .tr_version_con").addClass(
        "hidden"
      );
    }
  });
  var wrapper = $(".device-related-port");
  var add_button = $(".add_ports");
  var x = 1;
  $(add_button).click(function (e) {
    e.preventDefault();
    var port = `<div class="form-group device-related-port" style="display: flex;">
    <label for= "device-related-port-label" class= "col-sm-2 control-label" >Relay</label>
    <div class="col-sm-10" style="padding-right: 25px; padding-left: 20px;">
    <input type = "text" placeholder="Relay" class= "form-control" required name = "relays[]"  />
    </div>
    <button class="delete delete_ports"><i class="fa fa-trash"></i></button>
    </div>`;
    $(wrapper).append(port);
  });

  $(wrapper).on("click", ".delete", function (e) {
    e.preventDefault();
    $(this).parent("div").remove();
    x--;
  });
  $(wrapper).on("click", ".remove_port", function (e) {
    e.preventDefault();
    $(this).parent().parent().remove();
  });
  $(".remove_port").click(function () {
    let data = $(this).data("id");
    console.log(data);
    let device = $("#device_related_switch").val();
    console.log(device);
    $("#remove_relay").attr("data-id", data);
    $("#device_switch").val(device);
  });
  $(".sync_device").click(function () {
    var device = jQuery(this).attr("data-device_id");
    $.ajax({
      url: "/devices/sync",
      type: "get",
      data: { device: device },
      success: function (_response) {
        jQuery(".view_sync_settings .modal-content").html(_response);
        jQuery(".view_sync_settings").modal("show");
        $('[data-toggle="tooltip"]').tooltip();
      },
      error: function (_response) {
        jQuery(".view_sync_settings .modal-content").html(
          "Something went wrong. Please try again."
        );
        jQuery(".view_sync_settings").modal("show");
        $('[data-toggle="tooltip"]').tooltip();
      },
    });
  });
  // $("#device_name").blur(function () {
  //   if (jQuery("#device_name").val() !== "") {
  //     var current_item = jQuery(this);
  //     var device_name = jQuery(this).val();
  //     var device_id = 0;
  //     if (jQuery(".device_id_hidden").length) {
  //       device_id = jQuery(".device_id_hidden").val();
  //     }

  //     $.ajax({
  //       url: "/devices/name_exist",
  //       type: "post",
  //       data: { device_name: device_name, device_id: device_id },
  //       success: function (_response) {
  //         if (_response >= 1) {
  //           jQuery(".deviceForm .device_name_con")
  //             .find(".custom-help-block")
  //             .text(
  //               "Device with same name already exists please add another name"
  //             );
  //           jQuery(".deviceForm .device_name_con")
  //             .find(".custom-help-block")
  //             .show();
  //           jQuery(".deviceForm .device_name_con").addClass("has-error");
  //           jQuery(".deviceForm .device_name_con").removeClass("has-success");
  //           //                    jQuery(current_item).val('');
  //         } else {
  //           jQuery(".deviceForm .device_name_con")
  //             .find(".custom-help-block")
  //             .hide();
  //           jQuery(".deviceForm .device_name_con").removeClass("has-error");
  //           jQuery(".deviceForm .device_name_con").addClass("has-success");
  //         }
  //       },
  //     });
  //   }
  // });
  $("#switch_id").change(function () {
    if (jQuery("#switch_id").val() !== "") {
      var device_id = jQuery("#switch_id").val();
      $.ajax({
        url: "/devices/payment_terminal_exist",
        type: "post",
        data: { device_id: device_id },
        success: function (_response) {
          $(".deviceForm .open_relays").removeClass("hidden");
          $(".deviceForm .close_relays").removeClass("hidden");
          $("#open-relay-section").html(_response.ports);
          $("#close-relay-section").html(_response.ports);
          $('select[name="open_relay"]').empty();
          $('select[name="close_relay"]').empty();
          if (_response) {
            $.each(_response.ports, function (key, value) {
              $('select[name="open_relay"]').append(
                '<option value=" ' + value.id + '">' + value.relay + "</option>"
              );
              $('select[name="close_relay"]').append(
                '<option value="' + value.id + '">' + value.relay + "</option>"
              );
            });
            // jQuery(".deviceForm .related_switch")
            //   .find(".custom-help-block")
            //   .hide();
            // jQuery(".deviceForm .related_switch").removeClass("has-error");
            // jQuery(".deviceForm .related_switch").addClass("has-success");
          }
        },
      });
    }
  });
  $("#sortable").sortable({
    placeholder: "ui-state-highlight",
    update: function (e, ui) {
      var form_data = new FormData();
      var form_wrapper = "#device_ordering";
      var itemOrder = $("#sortable").sortable("toArray");
      form_data.append("itemOrder", itemOrder);
      $.ajax({
        url: "/devices/sort/vehicle",
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
  $("#sortable-person").sortable({
    placeholder: "ui-state-highlight",
    update: function (e, ui) {
      var form_data = new FormData();
      var form_wrapper = "#device_ordering";
      var itemOrder = $("#sortable-person").sortable("toArray");
      form_data.append("itemOrder", itemOrder);
      $.ajax({
        url: "/devices/sort/person",
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
if (jQuery(".deviceForm").length) {
  $(".deviceForm").bootstrapValidator({
    fields: {
      device_name: {
        validators: {
          notEmpty: {
            message: device_name_required,
          },
        },
        required: true,
        minlength: 3,
      },
      device_type: {
        validators: {
          notEmpty: {
            message: device_type_required,
          },
        },
      },
      device_ip: {
        validators: {
          notEmpty: {
            message: device_ip_required,
          },
        },
        required: true,
      },
      switch_ports: {
        validators: {
          notEmpty: {
            message: switch_port,
          },
        },
        required: true,
      },
      popup_time: {
        validators: {
          notEmpty: {
            message: popup_time_required,
          },
        },
        required: true,
      },
      //            device_port: {
      //                validators: {
      //                    notEmpty: {
      //                        message: device_port_required
      //                    }
      //                },
      //                required: true
      //            },
    },
  });

  $("#rootwizard").bootstrapWizard({
    tabClass: "nav nav-pills",
    onNext: function (tab, navigation, index) {
      var $validator = $("#commentForm").data("bootstrapValidator").validate();
      return $validator.isValid();
    },
    onTabClick: function (tab, navigation, index) {
      var $validator = $("#commentForm").data("bootstrapValidator").validate();
      return $validator.isValid();
    },
    onTabShow: function (tab, navigation, index) {
      var $total = navigation.find("li").length;
      var $current = index + 1;

      let device_id = $(".device_type").val();
      // If it's the last tab then hide the last button and show the finish instead
      if ($current >= $total) {
        $("#rootwizard").find(".pager .next").hide();
        $("#rootwizard").find(".pager .finish").show();
        $("#rootwizard").find(".pager .finish").removeClass("disabled");
      } else {
        $("#rootwizard").find(".pager .next").show();
        $("#rootwizard").find(".pager .finish").hide();
        if (device_id == 6) {
          $(".deviceForm .device_direction_con").hide();
        }
        if (device_id == 12) {
          $("#rootwizard").find(".pager .next").show();
          $("#rootwizard").find(".pager .finish").hide();
        }
      }
    },
  });

  $("#rootwizard .finish").click(function () {
    var $validator = $("#commentForm").data("bootstrapValidator").validate();
    if ($validator.isValid()) {
      document.getElementById("commentForm").submit();
    }
  });
}

function printSuccessMsg(msg, wrapper) {
  $(".print-success-msg").css("display", "none");
  $(".print-error-msg").css("display", "none");
  $(wrapper + " .print-success-msg")
    .find("ul")
    .html("");
  $(wrapper + " .print-success-msg").css("display", "block");
  $.each(msg, function (key, value) {
    $(wrapper + " .print-success-msg")
      .find("ul")
      .append('<li style="list-style-type: none;">' + value + "</li>");
  });
}

function printErrorMsg(msg, wrapper) {
  $(".print-success-msg").css("display", "none");
  $(".print-error-msg").css("display", "none");
  $(wrapper + " .print-error-msg")
    .find("ul")
    .html("");
  $(wrapper + " .print-error-msg").css("display", "block");
  $.each(msg, function (key, value) {
    $(wrapper + " .print-error-msg")
      .find("ul")
      .append('<li style="list-style-type: none;">' + value + "</li>");
  });
}
function validateIp(device_ip) {
  var ip = $(device_ip).val();
  console.log(ip);
  var test_ip =
    /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
  if (ip.length > 0) {
    if (test_ip.test(ip)) {
      $(".custom-ip-help-block").addClass("d-none");
      $(".custom-ip-help-block").text("");
    } else {
      $(".check-ip").addClass("has-error");
      $(".custom-ip-help-block").addClass("d-block");
      $(".custom-ip-help-block").addClass("text-danger");
      $(".custom-ip-help-block").text("IP Address is Invalid");
    }
  }
  $(".help-block").hide();
  $(".custom-ip-help-block").show();
}
function showAndHide() {
  let checkbox = document.getElementsByClassName('light_condition');
  let pan_input = document.getElementsByClassName('light_levels_on');
  //if ($(this).prop('checked', true)) {
  if ($('.light_condition').is(':checked')) {
    $('.light_levels_on').removeClass('hidden')
    $('.light_gain_con').removeClass('hidden')
    $('.light_exposure_time').removeClass('hidden')
  }
  else {
    $('.light_levels_on').addClass('hidden')
    $('.light_gain_con').addClass('hidden')
    $('.light_exposure_time').addClass('hidden')
  }
}
