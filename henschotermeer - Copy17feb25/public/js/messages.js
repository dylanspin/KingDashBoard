"use strict";
var message_key_required;
var message_lang_en_required;
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $(".select2").select2();
});
if (jQuery('.messageForm').length) {
    $(".messageForm").bootstrapValidator({
        fields: {
            message_key: {
                validators: {
                    notEmpty: {
                        message: message_key_required
                    }
                }
            },
            lang_en: {
                validators: {
                    notEmpty: {
                        message: message_lang_en_required
                    }
                }
            },
          
        }
    });
}


$('#rootwizard .finish').click(function () {
    var $validator = $('#commentForm').data('bootstrapValidator').validate();
    if ($validator.isValid()) {
        document.getElementById("commentForm").submit();
    }

});

