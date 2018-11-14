define([
    'jquery',
    'bootstrap'
], function ($) {
    'use strict';

    var messageManager = function(messages) {
        if (messages.length === 0) {
            return;
        }

        var modal = $(
            '<div class="modal fade" data-backdrop="static">' +
            '   <div class="modal-dialog">' +
            '       <div class="modal-content">' +
            '           <div class="modal-header">' +
            '               <button class="close" data-dimiss="modal" type="button">&times;</button>' +
            '               <h4 class="modal-title">Confirmación</h4>' +
            '           </div>' +
            '           <div class="modal-body">' +
            '               <ul></ul>' +
            '           </div>' +
            '           <div class="modal-footer">' +
            '               <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cerrar</button>' +
            '           </div>' +
            '       </div>' +
            '   </div>' +
            '</div>'
        ).appendTo($('body'));

        var messagesContainer = modal.find('ul');

        $.each(messages, function(index) {
            var message = messages[index];

            messagesContainer.append($('<li/>').text(message));
        });

        modal.on('hidden.bs.modal', function () {
            modal.remove();
        });

        modal.modal();
    };

    return messageManager;
});