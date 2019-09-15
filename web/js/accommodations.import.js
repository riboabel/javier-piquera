define([
    'jquery'
], function($) {
    'use strict';

    function initControls() {
        $('#import_accommodation_form_removeBeforeImport').on('click', function() {
            var state = $(this).prop('checked');

            if (state) {
                $('#zPellS').show();
            } else {
                $('#zPellS').hide();
                $('#import_accommodation_form_year').val('');
                $('#import_accommodation_form_month').val('');
            }
        });
    }

    return function() {
        initControls();
    };
});