define([
    'jquery',
    'js/app/main'
], function($, utils) {
    'use strict';

    var initValidator = function() {
        utils.validate($('form#provider'));
    };

    var initControls = function() {
        $('form#provider input:checkbox').iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });
    };

    return function() {
        initValidator();
        initControls();
    };
});
