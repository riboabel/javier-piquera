define([
    'jquery',
    'plugins/toastr/toastr'
], function ($, toastr) {
    'use strict';

    var messageManager = function(messages) {
        $.each(messages, function(index) {
            var message = messages[index];

            toastr.success(message, 'Éxito', {
                timeOut: 5000,
                progressBar: true
            });
        });
    };

    return messageManager;
});