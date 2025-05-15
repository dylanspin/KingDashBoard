"use strict";
$(document).ready(function () {
    // Daterange picker
    $('.input-daterange-datepicker').daterangepicker({
        buttonClasses: ['btn', 'btn-sm'],
        applyClass: 'btn-danger',
        cancelClass: 'btn-inverse',
        locale: {
            format: 'DD-MM-YYYY'
        }
    });
    $('.promoForm  select[name="discount_type"]').on('change', function (e) {
        var value = $(this).val();
        if (value == 'price') {
            $('.promoForm div.discount_price_wrapper').removeClass('hidden');
            $('.promoForm div.discount_percent_wrapper').addClass('hidden');
            $('.promoForm div.discount_price_wrapper .price').prop("required", true);
            $('.promoForm div.discount_percent_wrapper .percentage').prop("required", false);
        } else if (value == 'percent') {
            $('.promoForm div.discount_price_wrapper').addClass('hidden');
            $('.promoForm div.discount_percent_wrapper').removeClass('hidden');
            $('.promoForm div.discount_price_wrapper .price').prop("required", false);
            $('.promoForm div.discount_percent_wrapper .percentage').prop("required", true);
        }
    });
    $('#is_limited_time_period').click(function () {
        if ($(this).is(":checked")) {
            $('.promoForm div.valid_dates_wrapper').removeClass('hidden');
        } else {
            $('.promoForm div.valid_dates_wrapper').addClass('hidden');
        }
    });
});
$(".promoForm").bootstrapValidator({
});

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
    }});

$('#rootwizard .finish').click(function () {
    var $validator = $('#commentForm').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("commentForm").submit();
    }

});
// $('#activate').on('ifChanged', function(event){
//     $('#commentForm').bootstrapValidator('revalidateField', $('#activate'));
// });
$('#commentForm').keypress(function (event) {
    if (event.which == '13') {
        event.preventDefault();
    }
});
function generateRandomString(string_length) {
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var string = '';
    for (var i = 1; i <= string_length; i++)
    {
        var rand = Math.round(Math.random() * (characters.length - 1));
        var character = characters.substr(rand, 1);
        string = string + character;
    }
    return string;
}
function randomStringToInput(clicked_element) {
    var self = $(clicked_element);
    var random_string = generateRandomString(10);
    $('.promoForm input[name=code]').val(random_string);
}