jQuery(document).ready(function () {
    jQuery(".import_settings").change(function () {
            if (this.checked) {
                jQuery(this).parent().find('.import_settings_hidden').val('1');
            } else {
                jQuery(this).parent().find('.import_settings_hidden').val('0');
            }
        });
});