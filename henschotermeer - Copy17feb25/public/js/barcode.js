"use strict";
var barcode_required;
var barcode_name_required;
var barcode_vehicle_no_required;
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
if (jQuery('.barcodeForm').length) {
    $(".barcodeForm").bootstrapValidator({
        fields: {
            barcode: {
                validators: {
                    notEmpty: {
                        message: barcode_required
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: 'Enter numbers only'
                    }
                }
            },
//            name: {
//                validators: {
//                    notEmpty: {
//                        message: barcode_name_required
//                    }
//                }
//            },
//            vehicle_no: {
//                validators: {
//                    notEmpty: {
//                        message: barcode_vehicle_no_required
//                    }
//                }
//            }
          
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

