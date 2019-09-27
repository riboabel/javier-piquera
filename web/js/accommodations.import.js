define([
    'jquery',
    'app/main',
    'plugins/icheck'
], function($, utils) {
    'use strict';

    function initControls() {
        $('#import_accommodation_form_removeBeforeImport').on('ifChanged', function() {
            var state = $(this).prop('checked');

            if (state) {
                $('#zPellS').show();
            } else {
                $('#zPellS').hide();
                $('#import_accommodation_form_year').val('');
                $('#import_accommodation_form_month').val('');
            }
        }).iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });
    }

    function initValidation() {
        utils.validate($('form#import', {
            rules: {
                'import_accommodation_form[year]': {
                    required: true
                },
                'import_accommodation_form[month]': 'required'
            }
        }));
    }

    return function() {
        initControls();
        initValidation();
    };
});