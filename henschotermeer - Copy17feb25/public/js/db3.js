var bookingCalendar;
$(document).ready(function () {
  "use strict";
  $('[data-toggle="tooltip"]').tooltip();
  jQuery(".carousel").carousel("pause");
  $(".carousel").bind("slid.bs.carousel", function (e) {
    $(".carousel .item").each(function (index) {
      if (jQuery(this).hasClass("active")) {
        var device_id = jQuery(this).attr("data-device_id");
        var vehcile_num = jQuery(this).attr("data-vehcile_num");
        var transaction_id = jQuery(this).attr("data-transaction_id");
        var redirect_link = "/transaction/1/" + transaction_id;
        jQuery(".btn-device-" + device_id).html(vehcile_num);
        jQuery(".btn-device-link-" + device_id)
          .find(".redirect_lnk")
          .attr("href", redirect_link);
      }
    });
  });
  jQuery(".show_vehicle_transactions").click(function () {
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".other_content")
      .addClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".transactions_person_con")
      .addClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".transactions_vehicle_con")
      .removeClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".show_main_content")
      .removeClass("hidden");
  });
  jQuery(".show_person_transactions").click(function () {
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".other_content")
      .addClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".transactions_vehicle_con")
      .addClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".transactions_person_con")
      .removeClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".show_main_content")
      .removeClass("hidden");
  });
  jQuery(".show_main_content").click(function () {
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".other_content")
      .removeClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".transactions_vehicle_con")
      .addClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".transactions_person_con")
      .addClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".show_main_content")
      .addClass("hidden");
  });
  jQuery(".show_transactions").click(function () {
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".other_content")
      .addClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".transactions_con")
      .removeClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .find(".show_transactions")
      .addClass("hidden");

    jQuery(this)
      .parent()
      .parent()
      .find(".hide_transactions")
      .removeClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .find(".transactions_vehicle_con")
      .removeClass("hidden");
  });
  jQuery(".hide_transactions").click(function () {
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".other_content")
      .removeClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .parent()
      .parent()
      .find(".transactions_con")
      .addClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .find(".show_transactions")
      .removeClass("hidden");
    jQuery(this)
      .parent()
      .parent()
      .find(".hide_transactions")
      .addClass("hidden");
  });
  jQuery(".transaction_type_dropdown").change(function () {
    var id = jQuery(this).val();
    if (id !== "") {
      var transaction_key = jQuery(this).attr("data-transaction_key");
      jQuery(
        "#carousel-example-captions-" + transaction_key + " .carousel-inner"
      )
        .find(".item")
        .removeClass("active");
      jQuery(
        "#carousel-example-captions-" + transaction_key + " .carousel-inner"
      )
        .find(".transaction_" + id)
        .addClass("active");
    }
  });
  jQuery(".btn-open-gate-active").click(function () {
    var device_id = jQuery(this).attr("data-device_id");
    jQuery(".open_gate_modal").find(".device_id").val(device_id);
    jQuery(".open_gate_modal .confirm_con")
      .find(".open_gate_vehcile_num")
      .val("");
    jQuery(".open_gate_modal .confirm_con").find(".open_gate_reason").val("");
    jQuery(".open_gate_modal .confirm_con").find(".message").html("");

    jQuery(".open_gate_modal .form_con").find(".open_gate_vehcile_num").val("");
    jQuery(".open_gate_modal .form_con").find(".open_gate_reason").val("");
    jQuery(".open_gate_modal .form_con").find(".message").html("");

    jQuery(".open_gate_modal .form_con").removeClass("hidden");
    jQuery(".open_gate_modal .confirm_con").addClass("hidden");
    jQuery(".open_gate_modal").modal("show");
    return false;
  });
  jQuery(".submit_open_gate_cancel").click(function () {
    jQuery(".open_gate_modal .form_con").removeClass("hidden");
    jQuery(".open_gate_modal .confirm_con").addClass("hidden");
    return false;
  });
  jQuery(".submit_open_gate_modal").click(function () {
    jQuery(".overlay_open_gate").removeClass("hidden");
    var device_id = jQuery(".open_gate_modal .form_con")
      .find(".device_id")
      .val();
    var open_gate_vehcile_num = jQuery(".open_gate_modal .form_con")
      .find(".open_gate_vehcile_num")
      .val();
    var open_gate_reason = jQuery(".open_gate_modal .form_con")
      .find(".open_gate_reason")
      .val();
    $.ajax({
      url: "/dashboard/open_gate",
      type: "post",
      data: {
        device_id: device_id,
        open_gate_vehcile_num: open_gate_vehcile_num,
        open_gate_reason: open_gate_reason,
      },
      dataType: "json",
      success: function (_response) {
        jQuery(".overlay_open_gate").addClass("hidden");
        if (_response.status == 3) {
          jQuery(".open_gate_modal .form_con")
            .find(".message")
            .html(_response.message);
        } else if (_response.status == 2) {
          jQuery(".open_gate_modal .confirm_con")
            .find(".device_id")
            .val(device_id);
          jQuery(".open_gate_modal .confirm_con")
            .find(".open_gate_vehcile_num")
            .val(open_gate_vehcile_num);
          jQuery(".open_gate_modal .confirm_con")
            .find(".open_gate_reason")
            .val(open_gate_reason);
          jQuery(".open_gate_modal .confirm_con")
            .find(".message")
            .html(_response.message);
          jQuery(".open_gate_modal .form_con").addClass("hidden");
          jQuery(".open_gate_modal .confirm_con").removeClass("hidden");
        } else if (_response.status == 1) {
          jQuery(".device_" + device_id)
            .find(".open_con")
            .find(".btn-open-gate-active")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".open_con")
            .find(".btn-open-gate-non-active")
            .removeClass("hidden");

          jQuery(".device_" + device_id)
            .find(".close_con")
            .find(".btn-close-gate-active")
            .removeClass("hidden");
          jQuery(".device_" + device_id)
            .find(".close_con")
            .find(".btn-close-gate-non-active")
            .addClass("hidden");

          jQuery(".device_" + device_id)
            .find(".barier-closed")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-opened")
            .removeClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-always-access")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-locked-closed")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-locked-opened")
            .addClass("hidden");
          jQuery(".open_gate_modal").modal("hide");

          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          //                    jQuery('.open_gate_modal').modal('hide');
          jQuery(".open_gate_vehcile_num").parent().addClass("has-error");
          jQuery(".open_gate_vehcile_num")
            .parent()
            .find(".help-block")
            .html(_response.message);
          //                    $.toast({
          //                        heading: 'Error!',
          //                        position: 'top-center',
          //                        text: _response.message,
          //                        loaderBg: '#ff6849',
          //                        icon: 'error',
          //                        hideAfter: 5000,
          //                        stack: 6
          //                    });
        }
      },
    });
  });
  jQuery(".submit_open_gate_modal_confirm").click(function () {
    jQuery(".overlay_open_gate").removeClass("hidden");
    var device_id = jQuery(".open_gate_modal .confirm_con")
      .find(".device_id")
      .val();
    var open_gate_vehcile_num = jQuery(".open_gate_modal .confirm_con")
      .find(".open_gate_vehcile_num")
      .val();
    var open_gate_reason = jQuery(".open_gate_modal .confirm_con")
      .find(".open_gate_reason")
      .val();
    $.ajax({
      url: "/dashboard/no_entrance_transaction",
      type: "post",
      data: {
        device_id: device_id,
        open_gate_vehcile_num: open_gate_vehcile_num,
        open_gate_reason: open_gate_reason,
      },
      dataType: "json",
      success: function (_response) {
        jQuery(".overlay_open_gate").addClass("hidden");
        if (_response.status == 1) {
          jQuery(".device_" + device_id)
            .find(".open_con")
            .find(".btn-open-gate-active")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".open_con")
            .find(".btn-open-gate-non-active")
            .removeClass("hidden");

          jQuery(".device_" + device_id)
            .find(".close_con")
            .find(".btn-close-gate-active")
            .removeClass("hidden");
          jQuery(".device_" + device_id)
            .find(".close_con")
            .find(".btn-close-gate-non-active")
            .addClass("hidden");

          jQuery(".device_" + device_id)
            .find(".barier-closed")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-opened")
            .removeClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-always-access")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-locked-closed")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-locked-opened")
            .addClass("hidden");
          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          $.toast({
            heading: "Error!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 5000,
            stack: 6,
          });
        }
        jQuery(".open_gate_modal").modal("hide");
      },
    });
  });
  jQuery(".btn-close-gate-active").click(function () {
    var device_id = jQuery(this).attr("data-device_id");
    var item = jQuery(this);
    jQuery(item)
      .parent()
      .parent()
      .find(".gate_open_spinner")
      .removeClass("hidden");
    jQuery(item).parent().parent().find(".open_con").addClass("hidden");
    jQuery(item).parent().parent().find(".close_con").addClass("hidden");
    $.ajax({
      url: "/dashboard/close_gate",
      type: "post",
      data: { device_id: device_id },
      dataType: "json",
      success: function (_response) {
        jQuery(item)
          .parent()
          .parent()
          .find(".gate_open_spinner")
          .addClass("hidden");
        if (_response.status === 1) {
          jQuery(item)
            .parent()
            .parent()
            .find(".open_con")
            .removeClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".open_con")
            .find(".btn-open-gate-non-active")
            .addClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".open_con")
            .find(".btn-open-gate-active")
            .removeClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".close_con")
            .removeClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".close_con")
            .find(".btn-close-gate-non-active")
            .removeClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".close_con")
            .find(".btn-close-gate-active")
            .addClass("hidden");
          jQuery(".barier-closed-" + device_id).removeClass("hidden");
          jQuery(".barier-opened-" + device_id).addClass("hidden");
          jQuery(".barier-always-access-" + device_id).addClass("hidden");
          jQuery(".barier-locked-closed-" + device_id).addClass("hidden");
          jQuery(".barier-locked-opened-" + device_id).addClass("hidden");
        } else {
          jQuery(item)
            .parent()
            .parent()
            .find(".open_con")
            .removeClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".open_con")
            .find(".btn-open-gate-non-active")
            .addClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".open_con")
            .find(".btn-open-gate-active")
            .removeClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".close_con")
            .removeClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".close_con")
            .find(".btn-close-gate-non-active")
            .addClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".close_con")
            .find(".btn-close-gate-active")
            .removeClass("hidden");
          jQuery(".barier-closed-" + device_id).addClass("hidden");
          jQuery(".barier-opened-" + device_id).removeClass("hidden");
          jQuery(".barier-always-access-" + device_id).addClass("hidden");
          jQuery(".barier-locked-closed-" + device_id).addClass("hidden");
          jQuery(".barier-locked-opened-" + device_id).addClass("hidden");
        }
      },
    });
  });
  jQuery(".close_gate").click(function () {
    if (jQuery(this).is(":checked")) {
      var item = jQuery(this);
      jQuery(this).parent().parent().find(".switchery_gate").addClass("hidden");
      jQuery(this)
        .parent()
        .parent()
        .find(".gate_open_spinner")
        .removeClass("hidden");
      var device_id = jQuery(this).attr("data-device_id");
      $.ajax({
        url: "/dashboard/close_gate",
        type: "post",
        data: { device_id: device_id },
        dataType: "json",
        success: function (_response) {
          if (_response.status === 1) {
            jQuery(".open_gate_modal .modal-body").html(_response.message);
            jQuery(".open_gate_modal").modal("show");
          } else {
            jQuery(".open_gate_modal .modal-body").html(_response.message);
            jQuery(".open_gate_modal").modal("show");
          }
          jQuery(".open_gate").prop("checked", false);
          jQuery(item)
            .parent()
            .parent()
            .find(".switchery_gate")
            .removeClass("hidden");
          jQuery(item)
            .parent()
            .parent()
            .find(".gate_open_spinner")
            .addClass("hidden");
        },
      });
    }
  });

  jQuery(".btn-open-gate-person-active").click(function () {
    var device_id = jQuery(this).attr("data-device_id");
    jQuery(".device_" + device_id)
      .find(".open_con")
      .addClass("hidden");
    jQuery(".device_" + device_id)
      .find(".close_con")
      .addClass("hidden");
    jQuery(".device_" + device_id)
      .find(".overlay_open_gate")
      .removeClass("hidden");
    $.ajax({
      url: "/dashboard/open_gate_person",
      type: "post",
      data: { device_id: device_id },
      dataType: "json",
      success: function (_response) {
        jQuery(".device_" + device_id)
          .find(".overlay_open_gate")
          .addClass("hidden");
        jQuery(".device_" + device_id)
          .find(".open_con")
          .removeClass("hidden");
        jQuery(".device_" + device_id)
          .find(".close_con")
          .removeClass("hidden");
        if (_response.status == 1) {
          jQuery(".device_" + device_id)
            .find(".open_con")
            .find(".btn-open-gate-person-active")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".open_con")
            .find(".btn-open-gate-person-non-active")
            .removeClass("hidden");

          jQuery(".device_" + device_id)
            .find(".close_con")
            .find(".btn-close-gate-person-active")
            .removeClass("hidden");
          jQuery(".device_" + device_id)
            .find(".close_con")
            .find(".btn-close-gate-person-non-active")
            .addClass("hidden");

          jQuery(".device_" + device_id)
            .find(".barier-closed")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-opened")
            .removeClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-always-access")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-locked-closed")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-locked-opened")
            .addClass("hidden");

          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          $.toast({
            heading: "Error!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 5000,
            stack: 6,
          });
        }
      },
    });
  });

  jQuery(".btn-close-gate-person-active").click(function () {
    var device_id = jQuery(this).attr("data-device_id");
    var item = jQuery(this);
    jQuery(".device_" + device_id)
      .find(".open_con")
      .addClass("hidden");
    jQuery(".device_" + device_id)
      .find(".close_con")
      .addClass("hidden");
    jQuery(".device_" + device_id)
      .find(".overlay_open_gate")
      .removeClass("hidden");
    $.ajax({
      url: "/dashboard/close_gate_person",
      type: "post",
      data: { device_id: device_id },
      dataType: "json",
      success: function (_response) {
        jQuery(".device_" + device_id)
          .find(".overlay_open_gate")
          .addClass("hidden");
        jQuery(".device_" + device_id)
          .find(".open_con")
          .removeClass("hidden");
        jQuery(".device_" + device_id)
          .find(".close_con")
          .removeClass("hidden");
        if (_response.status === 1) {
          jQuery(".device_" + device_id)
            .find(".open_con")
            .find(".btn-open-gate-person-active")
            .removeClass("hidden");
          jQuery(".device_" + device_id)
            .find(".open_con")
            .find(".btn-open-gate-person-non-active")
            .addClass("hidden");

          jQuery(".device_" + device_id)
            .find(".close_con")
            .find(".btn-close-gate-person-active")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".close_con")
            .find(".btn-close-gate-person-non-active")
            .removeClass("hidden");

          jQuery(".device_" + device_id)
            .find(".barier-closed")
            .removeClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-opened")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-always-access")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-locked-closed")
            .addClass("hidden");
          jQuery(".device_" + device_id)
            .find(".barier-locked-opened")
            .addClass("hidden");
          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          $.toast({
            heading: "Error!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 5000,
            stack: 6,
          });
        }
      },
    });
  });
  jQuery(".btn-open-gate-vehicle-active").click(function () {
    jQuery("body").find(".preloader").css({ display: "block" });
    var device_id = jQuery(this).attr("data-device_id");
    //        jQuery('.device_' + device_id).find('.open_con').addClass('hidden');
    //        jQuery('.device_' + device_id).find('.close_con').addClass('hidden');
    //        jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
    $.ajax({
      url: "/dashboard/open_gate_vehicle",
      type: "post",
      data: { device_id: device_id },
      dataType: "json",
      success: function (_response) {
        jQuery(".device_" + device_id)
          .find(".overlay_open_gate")
          .addClass("hidden");
        jQuery(".device_" + device_id)
          .find(".open_con")
          .removeClass("hidden");
        jQuery(".device_" + device_id)
          .find(".close_con")
          .removeClass("hidden");
        if (_response.status == 1) {
          //                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-vehicle-active-con').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-vehicle-non-active').removeClass('hidden');
          //                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-vehicle-active-con').removeClass('hidden');
          //                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-vehicle-non-active').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-closed').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-opened').removeClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-always-access').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-locked-closed').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-locked-opened').addClass('hidden');
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Error!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 5000,
            stack: 6,
          });
        }
      },
    });
  });
  jQuery(".btn-close-gate-vehicle-active").click(function () {
    jQuery("body").find(".preloader").css({ display: "block" });
    var device_id = jQuery(this).attr("data-device_id");
    //        jQuery('.device_' + device_id).find('.open_con').addClass('hidden');
    //        jQuery('.device_' + device_id).find('.close_con').addClass('hidden');
    //        jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
    $.ajax({
      url: "/dashboard/close_gate_vehicle",
      type: "post",
      data: { device_id: device_id },
      dataType: "json",
      success: function (_response) {
        jQuery(".device_" + device_id)
          .find(".open_con")
          .removeClass("hidden");
        jQuery(".device_" + device_id)
          .find(".close_con")
          .removeClass("hidden");
        jQuery(".device_" + device_id)
          .find(".overlay_open_gate")
          .addClass("hidden");
        if (_response.status === 1) {
          //                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-vehicle-active-con').removeClass('hidden');
          //                    jQuery('.device_' + device_id).find('.open_con').find('.btn-open-gate-vehicle-non-active').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-vehicle-active-con').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.close_con').find('.btn-close-gate-vehicle-non-active').removeClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-closed').removeClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-opened').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-always-access').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-locked-closed').addClass('hidden');
          //                    jQuery('.device_' + device_id).find('.barier-locked-opened').addClass('hidden');
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Error!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 5000,
            stack: 6,
          });
        }
      },
    });
  });
  jQuery(".btn-open-gate-emergency-active").click(function () {
    jQuery("body").find(".preloader").css({ display: "block" });
    var device_id = jQuery(this).attr("data-device_id");
    //        jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
    $.ajax({
      url: "/dashboard/open_gate_vehicle",
      type: "post",
      data: { device_id: device_id },
      dataType: "json",
      success: function (_response) {
        jQuery(".device_" + device_id)
          .find(".overlay_open_gate")
          .addClass("hidden");
        if (_response.status === 1) {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Error!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 5000,
            stack: 6,
          });
        }
      },
    });
  });
  jQuery(".btn-close-gate-emergency-active").click(function () {
    alert("hi");
    jQuery("body").find(".preloader").css({ display: "block" });
    var device_id = jQuery(this).attr("data-device_id");
    //        jQuery('.device_' + device_id).find('.overlay_open_gate').removeClass('hidden');
    $.ajax({
      url: "/dashboard/close_gate_vehicle",
      type: "post",
      data: { device_id: device_id },
      dataType: "json",
      success: function (_response) {
        jQuery(".device_" + device_id)
          .find(".overlay_open_gate")
          .addClass("hidden");
        if (_response.status === 1) {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Error!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 5000,
            stack: 6,
          });
        }
      },
    });
  });
  $(".js-switch").each(function () {
    new Switchery($(this)[0], $(this).data());
  });
  $.ajaxSetup({
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
  });
  jQuery(".devices_collapse_actions_show").click(function () {});
  if (jQuery("#calendar").length) {
    bookingCalendar = jQuery("#calendar").fullCalendar({
      //Random default events
      header: {
        left: "prev",
        center: "title",
        right: "next",
      },
      firstDay: 1,
      handleWindowResize: true,
      fixedWeekCount: false,
      editable: false,
      droppable: false,
      eventLimit: 3,
      displayEventTime: false,
      viewRender: function (view, element) {
        //Last parameter is used to manage calender start and end dates
        // 1 for render view as start and end date
        //2 for  month start and end date
        refresh_calendar_overview(
          view.start.format("YYYY-MM-DD"),
          view.end.format("YYYY-MM-DD"),
          view.intervalStart.format("YYYY-MM-DD"),
          view.intervalEnd.format("YYYY-MM-DD"),
          2
        );
      },
      eventClick: function (event, jsEvent, view) {
        show_calender_event_details_overview(event.data);
      },
      //        timezone: 'UTC'
    });
  }
  //    setInterval(function () {
  //        if (jQuery('.open_con').length) {
  //            $.ajax({
  //                url: '/check_dashboard_changes_timer',
  //                type: "POST",
  //                data: {},
  //                dataType: 'json',
  //                success: function (res) {
  //                    $.each(res.door_changes, function (key, value){
  //                        if (value.is_opened === 0) {
  //                            if (value.direction === 'in') {
  //                                jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-active').removeClass('hidden');
  //                                jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-non-active').addClass('hidden');
  //                            }
  //                            jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-active').addClass('hidden');
  //                            jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-non-active').removeClass('hidden');
  //
  //                            jQuery('.device_' + key).find('.barier-closed').removeClass('hidden');
  //                            jQuery('.device_' + key).find('.barier-opened').addClass('hidden');
  //                            jQuery('.device_' + device_id).find('.barier-always-access').addClass('hidden');
  //                            jQuery('.device_' + device_id).find('.barier-locked-closed').addClass('hidden');
  //                            jQuery('.device_' + device_id).find('.barier-locked-opened').addClass('hidden');
  //                        } else {
  //                            if (value.direction === 'in') {
  //                                jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-active').addClass('hidden');
  //                                jQuery('.device_' + key).find('.open_con').find('.btn-open-gate-non-active').removeClass('hidden');
  //                            }
  //                            jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-active').removeClass('hidden');
  //                            jQuery('.device_' + key).find('.close_con').find('.btn-close-gate-non-active').addClass('hidden');
  //
  //                            jQuery('.device_' + key).find('.barier-closed').addClass('hidden');
  //                            jQuery('.device_' + key).find('.barier-opened').removeClass('hidden');
  //                            jQuery('.device_' + device_id).find('.barier-always-access').addClass('hidden');
  //                            jQuery('.device_' + device_id).find('.barier-locked-closed').addClass('hidden');
  //                            jQuery('.device_' + device_id).find('.barier-locked-opened').addClass('hidden');
  //                        }
  //                    });
  //                    $('div.widget1_con').html(res.widgets.widget1_con);
  //                    $('div.widget2_con').html(res.widgets.widget2_con);
  //                    $('div.widget3_con').html(res.widgets.widget3_con);
  ////                    $('div.devices_con').html(res.devices.devices_con);
  //                    $('div.vehicles_on_location_con').html(res.vehicles_on_location_con.at_location_con);
  //                    $('div.persons_on_location_con').html(res.persons_on_location_con.at_location_con);
  //                    $('div.transaction_on_location_con').html(res.transaction_on_location_con.at_location_con);
  //                    $('#calendar').fullCalendar('refresh');
  //                },
  //                error: function (res) {
  //
  //                }
  //            });
  //        }
  //    }, 10000);
});
function edit_vehicle_num(booking_id) {
  jQuery.ajax({
    type: "POST",
    url: "/dashboard/edit_vehicle_num",
    data: { booking_id: booking_id },
    success: function (response) {
      if (response.is_success === 1) {
        jQuery("#edit_booking_vehicle_num .modal-content").html(
          response.response_html
        );
        jQuery("#edit_booking_vehicle_num").modal("show");
      }
    },
    error: function (xhr, textStatus, errorThrown) {},
  });
}
function update_vehicle_number() {
  var vehicle_num = jQuery(".update_vehicle_form .vehicle_num").val();
  if (vehicle_num === "") {
    jQuery(".update_vehicle_form .vehicle_num").addClass("required-missing");
    return false;
  }
  jQuery(".updated_vehicle_num_form .updated_vehicle_num").val(vehicle_num);
  jQuery(".updated_vehicle_num_form").submit();
}
function refresh_calendar_overview(
  start_render_view,
  end_render_view,
  start_month,
  end_month,
  render_view_status
) {
  jQuery.ajax({
    url: "/dashboard/calendar",
    type: "POST",
    data: {
      start_render_view: start_render_view,
      end_render_view: end_render_view,
      start_month: start_month,
      end_month: end_month,
      render_view_status: render_view_status,
    },
    success: function (response) {
      bookingCalendar.fullCalendar("renderEvents", response);
    },
    error: function (xhr, textStatus, errorThrown) {},
  });
}
function show_calender_event_details_overview(data) {
  jQuery.ajax({
    type: "POST",
    url: "/dashboard/calendar_event_details",
    data: { data: data },
    success: function (response) {
      jQuery("#view_calender_event .modal-content").html(response);
      jQuery("#view_calender_event").modal("show");
    },
    error: function (xhr, textStatus, errorThrown) {},
  });
}
function edit_device_vehicle_num(item) {
  var device_booking_id = jQuery(item).parent().find(".id").val();
  jQuery.ajax({
    type: "POST",
    url: "/dashboard/edit_device_vehicle_num",
    data: { device_booking_id: device_booking_id },
    success: function (response) {
      if (response.is_success === 1) {
        jQuery("#edit_device_booking_vehicle_num .modal-content").html(
          response.response_html
        );
        jQuery("#edit_device_booking_vehicle_num").modal("show");
      }
    },
    error: function (xhr, textStatus, errorThrown) {},
  });
}
function update_device_vehicle_number(item) {
  var current_item = jQuery(item).parent().parent();
  var vehicle_num = jQuery(current_item).find(".plate_num_textbox").val();
  var device_booking_id = jQuery(current_item).find(".device_booking_id").val();
  //    if (vehicle_num === '') {
  //        jQuery('.plate_num_textbox').addClass('required-missing');
  //        return false;
  //    }
  jQuery(item)
    .parent()
    .find(".updated_device_vehicle_num_form .updated_device_vehicle_num")
    .val(vehicle_num);
  window.open("/update-confidence/" + device_booking_id, "_blank");
  //    jQuery(item).parent().find('.updated_device_vehicle_num_form').submit();
}
function open_gate_emergency_active(device_id) {
  jQuery("body").find(".preloader").css({ display: "block" });
  $.ajax({
    url: "/dashboard/open_gate_vehicle",
    type: "post",
    data: { device_id: device_id },
    dataType: "json",
    success: function (_response) {
      jQuery(".device_" + device_id)
        .find(".overlay_open_gate")
        .addClass("hidden");
      if (_response.status === 1) {
        location.reload();
        jQuery("body").find(".preloader").css({ display: "none" });
        $.toast({
          heading: "Success!",
          position: "top-center",
          text: _response.message,
          loaderBg: "#ff6849",
          icon: "success",
          hideAfter: 5000,
          stack: 6,
        });
      } else {
        location.reload();
        jQuery("body").find(".preloader").css({ display: "none" });
        $.toast({
          heading: "Error!",
          position: "top-center",
          text: _response.message,
          loaderBg: "#ff6849",
          icon: "error",
          hideAfter: 5000,
          stack: 6,
        });
      }
    },
  });
}
function close_gate_emergency_active(device_id) {
  jQuery("body").find(".preloader").css({ display: "block" });
  $.ajax({
    url: "/dashboard/close_gate_vehicle",
    type: "post",
    data: { device_id: device_id },
    dataType: "json",
    success: function (_response) {
      jQuery(".device_" + device_id)
        .find(".overlay_open_gate")
        .addClass("hidden");
      if (_response.status === 1) {
        location.reload();
        jQuery("body").find(".preloader").css({ display: "none" });
        $.toast({
          heading: "Success!",
          position: "top-center",
          text: _response.message,
          loaderBg: "#ff6849",
          icon: "success",
          hideAfter: 5000,
          stack: 6,
        });
      } else {
        location.reload();
        jQuery("body").find(".preloader").css({ display: "none" });
        $.toast({
          heading: "Error!",
          position: "top-center",
          text: _response.message,
          loaderBg: "#ff6849",
          icon: "error",
          hideAfter: 5000,
          stack: 6,
        });
      }
    },
  });
}
function open_payment_terminal(device_id, switch_id, relay) {
  jQuery("body").find(".preloader").css({ display: "block" });
  if ((device_id && switch_id != null) || "") {
    $.ajax({
      url: "/dashboard/open_payment_terminal",
      type: "post",
      data: { device_id: device_id, switch_id: switch_id, relay: relay },
      dataType: "json",
      success: function (_response) {
        jQuery(".device_" + device_id).addClass("hidden");
        console.log(_response);
        if (_response.status === 1) {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Error!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 5000,
            stack: 6,
          });
        }
      },
    });
  } else {
    jQuery("body").find(".preloader").css({ display: "none" });
    $.toast({
      heading: "Error!",
      position: "top-center",
      text: "Something went wrong!",
      loaderBg: "#ff6849",
      icon: "error",
      hideAfter: 5000,
      stack: 6,
    });
    location.reload();
  }
}
function close_payment_terminal(device_id, switch_id, relay) {
  jQuery("body").find(".preloader").css({ display: "block" });
  if ((device_id && switch_id != null) || "") {
    $.ajax({
      url: "/dashboard/close_payment_terminal",
      type: "post",
      data: { device_id: device_id, switch_id: switch_id, relay: relay },
      dataType: "json",
      success: function (_response) {
        jQuery(".device_" + device_id).addClass("hidden");
        console.log(_response);
        if (_response.status === 1) {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Success!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "success",
            hideAfter: 5000,
            stack: 6,
          });
        } else {
          location.reload();
          jQuery("body").find(".preloader").css({ display: "none" });
          $.toast({
            heading: "Error!",
            position: "top-center",
            text: _response.message,
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 5000,
            stack: 6,
          });
        }
      },
    });
  } else {
    jQuery("body").find(".preloader").css({ display: "none" });
    $.toast({
      heading: "Error!",
      position: "top-center",
      text: "Something went wrong!",
      loaderBg: "#ff6849",
      icon: "error",
      hideAfter: 5000,
      stack: 6,
    });
    location.reload();
  }
}
function change_barrier_status(device, status) {
  jQuery("body").find(".preloader").css({ display: "block" });
  $.ajax({
    url: "/dashboard/change_gate_barrier_status",
    type: "post",
    data: {
      device_id: device,
      status: status,
    },
    dataType: "json",
    success: function (_response) {
      if (_response.status == 1) {
        location.reload();
        jQuery("body").find(".preloader").css({ display: "none" });
        $.toast({
          heading: "Success!",
          position: "top-center",
          text: _response.message,
          loaderBg: "#ff6849",
          icon: "success",
          hideAfter: 5000,
          stack: 6,
        });
      } else {
        location.reload();
        jQuery("body").find(".preloader").css({ display: "none" });
        $.toast({
          heading: "Error!",
          position: "top-center",
          text: _response.message,
          loaderBg: "#ff6849",
          icon: "error",
          hideAfter: 5000,
          stack: 6,
        });
      }
    },
  });
}
var current_device_booking_popup = 0;
get_operator_booking();
function get_operator_booking() {
  $.ajax({
    url: "/get/operator/bookings",
    type: "get",
    data: { current_device_booking_popup: current_device_booking_popup },
    dataType: "json",
    success: function (_response) {
      if (_response.status == 1) {
        if (current_device_booking_popup != _response.data.device_booking.id) {
          beeps_count = 1;
          playAudio();
          operator_sound_timer = setInterval(function () {
            if (beeps_count == 5) {
              console.log("timer cleared");
              clearInterval(operator_sound_timer);
            }
            playAudio();
          }, 10000);
          if (_response.data.location_device !== undefined) {
            jQuery(".low_confidence_open_gate_modal")
              .find(".device-title")
              .html(_response.data.location_device.device_name);
          }
          jQuery(".low_confidence_open_gate_modal")
            .find(".open_gate_vehcile_image")
            .attr("src", "plugins/images/assets/no_image.png");
          jQuery(".low_confidence_open_gate_modal")
            .find(".device_image_path")
            .val("");
          jQuery(".low_confidence_open_gate_modal").find(".device_id").val("");
          jQuery(".low_confidence_open_gate_modal")
            .find(".device_booking_id")
            .val("");
          jQuery(".low_confidence_open_gate_modal")
            .find(".open_gate_confidence")
            .val("");
          jQuery(".low_confidence_open_gate_modal")
            .find(".open_gate_vehcile_num")
            .val("");
          jQuery(".low_confidence_open_gate_modal")
            .find(".open_gate_reason")
            .html("");
          jQuery(".low_confidence_open_gate_modal")
            .find(".open_gate_booking_type")
            .val("1");
          jQuery(".low_confidence_open_gate_modal")
            .find(".vehicle_booking_id")
            .val(0);
          jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
            .find(".name")
            .html("");
          jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
            .find(".vehicle")
            .html("");
          jQuery(
            ".low_confidence_open_gate_modal .vehicle_booking_con"
          ).addClass("hidden");
          jQuery(
            ".low_confidence_open_gate_modal .allow_access_btn_con"
          ).addClass("hidden");
          jQuery(".low_confidence_open_gate_modal .not_allow_btn_con").addClass(
            "hidden"
          );
          jQuery(
            ".low_confidence_open_gate_modal .update_vehicle_btn_con"
          ).addClass("hidden");
          jQuery(
            ".low_confidence_open_gate_modal .open_gate_booking_type_con"
          ).addClass("hidden");
          jQuery(
            ".low_confidence_open_gate_modal .open_gate_customer_con"
          ).addClass("hidden");
          jQuery(
            ".low_confidence_open_gate_modal .open_gate_booking_range_con"
          ).addClass("hidden");
          if (_response.data.device_booking != undefined) {
            current_device_booking_popup = _response.data.device_booking.id;
            jQuery(".low_confidence_open_gate_modal")
              .find(".open_gate_vehcile_image")
              .attr("src", _response.data.device_booking.file_path);
            jQuery(".low_confidence_open_gate_modal")
              .find(".device_image_path")
              .val(_response.data.device_booking.file_path);
            jQuery(".low_confidence_open_gate_modal")
              .find(".device_id")
              .val(_response.data.device_booking.device_id);
            jQuery(".low_confidence_open_gate_modal")
              .find(".device_booking_id")
              .val(_response.data.device_booking.id);
            jQuery(".low_confidence_open_gate_modal")
              .find(".open_gate_confidence")
              .val(_response.data.device_booking.confidence);
            jQuery(".low_confidence_open_gate_modal")
              .find(".open_gate_vehcile_num")
              .val(_response.data.device_booking.vehicle_num);
            jQuery(".low_confidence_open_gate_modal")
              .find(".open_gate_reason")
              .html(_response.data.device_booking.reason);
            if (_response.data.device_booking.confidence >= 80) {
              jQuery(
                ".low_confidence_open_gate_modal .allow_access_btn_con"
              ).removeClass("hidden");
              jQuery(
                ".low_confidence_open_gate_modal .not_allow_btn_con"
              ).removeClass("hidden");
              jQuery(
                ".low_confidence_open_gate_modal .update_vehicle_btn_con"
              ).removeClass("hidden");
              if (
                _response.data.location_device != undefined &&
                _response.data.location_device.device_direction == "in"
              ) {
                if (_response.data.vehicle_booking == undefined) {
                  jQuery(
                    ".low_confidence_open_gate_modal .open_gate_booking_type_con"
                  ).removeClass("hidden");
                }
              }
            } else {
              jQuery(
                ".low_confidence_open_gate_modal .update_vehicle_btn_con"
              ).removeClass("hidden");
              jQuery(
                ".low_confidence_open_gate_modal .not_allow_btn_con"
              ).removeClass("hidden");
            }
          }
          if (_response.data.vehicle_booking != undefined) {
            jQuery(
              ".low_confidence_open_gate_modal .vehicle_booking_con"
            ).removeClass("hidden");
            jQuery(".low_confidence_open_gate_modal")
              .find(".vehicle_booking_id")
              .val(_response.data.vehicle_booking.id);
            jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
              .find(".name")
              .html(
                "<label><b>Name : </b></label> " +
                  _response.data.vehicle_booking.first_name
              );
            jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
              .find(".vehicle")
              .html(
                "<label><b>Vehicle : </b></label> " +
                  _response.data.vehicle_booking.vehicle_num
              );
            jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
              .find(".check_in")
              .html(
                "<label><b>CheckIn : </b></label> " +
                  _response.data.vehicle_booking.checkin_time
              );
            jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
              .find(".check_out")
              .html(
                "<label><b>CheckOut : </b></label> " +
                  _response.data.vehicle_booking.checkout_time
              );
            jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
              .find(".amount")
              .html(
                "<label><b>Amount : </b></label> " +
                  _response.data.vehicle_booking.amount
              );
          }
          jQuery(".low_confidence_open_gate_modal").modal("show");
        }
      } else {
        current_device_booking_popup = 0;
        clearInterval(operator_sound_timer);
        jQuery(".low_confidence_open_gate_modal").modal("hide");
      }
    },
    error: function (_response) {},
    complete: function (res) {
		//get_operator_booking();
        setTimeout(get_operator_booking, 2000);
    },
  });
}
jQuery(".submit_low_confidence_open_gate_cancel").click(function () {
  jQuery(".overlay_open_gate").removeClass("hidden");
  var device_booking_id = jQuery(".low_confidence_open_gate_modal")
    .find(".device_booking_id")
    .val();
  $.ajax({
    url: "/access/denied",
    type: "post",
    data: { device_booking_id: device_booking_id },
    dataType: "json",
    success: function (_response) {
      jQuery(".overlay_open_gate").addClass("hidden");
      current_device_booking_popup = 0;
      if (_response.status == 1) {
        jQuery(".low_confidence_open_gate_modal").modal("hide");
      }
    },
  });
  clearInterval(operator_sound_timer);
  jQuery(".low_confidence_open_gate_modal").modal("hide");
  return false;
});
jQuery(".submit_low_confidence_open_gate_modal").click(function () {
  jQuery(".overlay_open_gate").removeClass("hidden");
  var device_booking_id = jQuery(".low_confidence_open_gate_modal")
    .find(".device_booking_id")
    .val();
  var device_id = jQuery(".low_confidence_open_gate_modal")
    .find(".device_id")
    .val();
  var open_gate_vehcile_num = jQuery(".low_confidence_open_gate_modal")
    .find(".open_gate_vehcile_num")
    .val();
  var open_gate_reason = "Operator allow access";
  $.ajax({
    url: "/update/device/vehicle",
    type: "post",
    data: {
      device_id: device_id,
      device_booking_id: device_booking_id,
      open_gate_vehcile_num: open_gate_vehcile_num,
      open_gate_reason: open_gate_reason,
    },
    dataType: "json",
    success: function (_response) {
      jQuery(".overlay_open_gate").addClass("hidden");
      if (_response.status == 2) {
        current_device_booking_popup = 0;
        $.toast({
          heading: "Success!",
          position: "top-center",
          text: _response.message,
          loaderBg: "#ff6849",
          icon: "success",
          hideAfter: 5000,
          stack: 6,
        });
        clearInterval(operator_sound_timer);
        jQuery(".low_confidence_open_gate_modal").modal("hide");
      } else if (_response.status == 1) {
        jQuery(".low_confidence_open_gate_modal .vehicle_booking_con").addClass(
          "hidden"
        );
        jQuery(
          ".low_confidence_open_gate_modal .allow_access_btn_con"
        ).removeClass("hidden");
        jQuery(
          ".low_confidence_open_gate_modal .not_allow_btn_con"
        ).removeClass("hidden");
        jQuery(
          ".low_confidence_open_gate_modal .update_vehicle_btn_con"
        ).addClass("hidden");
        jQuery(
          ".low_confidence_open_gate_modal .open_gate_booking_type_con"
        ).addClass("hidden");
        jQuery(
          ".low_confidence_open_gate_modal .open_gate_customer_con"
        ).addClass("hidden");
        jQuery(
          ".low_confidence_open_gate_modal .open_gate_booking_range_con"
        ).addClass("hidden");
        jQuery(".low_confidence_open_gate_modal")
          .find(".open_gate_reason")
          .html(_response.data.device_booking.reason);
        if (_response.data.vehicle_booking != undefined) {
          jQuery(
            ".low_confidence_open_gate_modal .vehicle_booking_con"
          ).removeClass("hidden");
          jQuery(".low_confidence_open_gate_modal")
            .find(".vehicle_booking_id")
            .val(_response.data.vehicle_booking.id);
          jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
            .find(".name")
            .html(
              "<label><b>Name : </b></label> " +
                _response.data.vehicle_booking.first_name
            );
          jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
            .find(".vehicle")
            .html(
              "<label><b>Vehicle : </b></label> " +
                _response.data.vehicle_booking.vehicle_num
            );
          jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
            .find(".check_in")
            .html(
              "<label><b>CheckIn : </b></label> " +
                _response.data.vehicle_booking.checkin_time
            );
          jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
            .find(".check_out")
            .html(
              "<label><b>CheckOut : </b></label> " +
                _response.data.vehicle_booking.checkout_time
            );
          jQuery(".low_confidence_open_gate_modal .vehicle_booking_con")
            .find(".amount")
            .html(
              "<label><b>Amount : </b></label> " +
                _response.data.vehicle_booking.amount
            );
        } else {
          if (
            _response.data.location_device != undefined &&
            _response.data.location_device.device_direction == "in"
          ) {
            jQuery(
              ".low_confidence_open_gate_modal .open_gate_booking_type_con"
            ).removeClass("hidden");
          }
        }
      } else {
        jQuery(".low_confidence_open_gate_modal")
          .find(".message")
          .html(_response.message);
      }
    },
    error: function (_response) {
      jQuery(".overlay_open_gate").addClass("hidden");
      console.log(_response);
    },
  });
});
jQuery(".submit_low_confidence_open_gate_modal_confirm").click(function () {
  jQuery(".overlay_open_gate").removeClass("hidden");
  var device_booking_id = jQuery(".low_confidence_open_gate_modal")
    .find(".device_booking_id")
    .val();
  var vehicle_booking_id = jQuery(".low_confidence_open_gate_modal")
    .find(".vehicle_booking_id")
    .val();
  var device_image_path = jQuery(".low_confidence_open_gate_modal")
    .find(".device_image_path")
    .val();
  var device_id = jQuery(".low_confidence_open_gate_modal")
    .find(".device_id")
    .val();
  var open_gate_booking_type = jQuery(".low_confidence_open_gate_modal")
    .find(".open_gate_booking_type")
    .val();
  var open_gate_customer = jQuery(".low_confidence_open_gate_modal")
    .find(".open_gate_customer")
    .val();
  var open_gate_booking_range = jQuery(".low_confidence_open_gate_modal")
    .find(".open_gate_booking_range")
    .val();
  var open_gate_vehcile_num = jQuery(".low_confidence_open_gate_modal")
    .find(".open_gate_vehcile_num")
    .val();
  var open_gate_reason = "Operator allow access";
  $.ajax({
    url: "/access/allow",
    type: "post",
    data: {
      device_id: device_id,
      device_image_path: device_image_path,
      device_booking_id: device_booking_id,
      vehicle_booking_id: vehicle_booking_id,
      open_gate_vehcile_num: open_gate_vehcile_num,
      open_gate_reason: open_gate_reason,
      open_gate_booking_type: open_gate_booking_type,
      open_gate_customer: open_gate_customer,
      open_gate_booking_range: open_gate_booking_range,
    },
    dataType: "json",
    success: function (_response) {
      jQuery(".overlay_open_gate").addClass("hidden");
      if (_response.status == 1) {
        current_device_booking_popup = 0;
        $.toast({
          heading: "Success!",
          position: "top-center",
          text: _response.message,
          loaderBg: "#ff6849",
          icon: "success",
          hideAfter: 5000,
          stack: 6,
        });
      } else {
        $.toast({
          heading: "Error!",
          position: "top-center",
          text: _response.message,
          loaderBg: "#ff6849",
          icon: "error",
          hideAfter: 5000,
          stack: 6,
        });
      }
      clearInterval(operator_sound_timer);
      jQuery(".low_confidence_open_gate_modal").modal("hide");
    },
  });
});
jQuery(".open_gate_booking_type").change(function () {
  var booking_type = jQuery(this).val();
  if (booking_type == 3) {
    jQuery(
      ".low_confidence_open_gate_modal .open_gate_customer_con"
    ).removeClass("hidden");
    jQuery(
      ".low_confidence_open_gate_modal .open_gate_booking_range_con"
    ).removeClass("hidden");
  } else {
    jQuery(".low_confidence_open_gate_modal .open_gate_customer_con").addClass(
      "hidden"
    );
    jQuery(
      ".low_confidence_open_gate_modal .open_gate_booking_range_con"
    ).addClass("hidden");
  }
});
