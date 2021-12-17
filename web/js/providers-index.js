define([
    'jquery',
    'js/app/datatables-init'
], function($) {
    'use strict';

    var $table = $('table#dataTables-providers');

    var initDatatable = function() {
        $table.dataTable({
            columns: [{
                name: 'name',
                title: 'Nombre'
            }, {
                title: 'Acciones'
            }]
        });
        //App.Tables.initDeleteButtonHandler($('table#dataTables-providers'));
    };

    var initDeleteButtonHandler = function() {
        if ($table.find('.btn-delete').length && $('.modal[data-for="' + $table.attr('id') + '"]').length) {
            $table.find('.btn-delete').on('click', function(event) {
                event.preventDefault();
                $('.modal[data-for="' + $table.attr('id') + '"] .btn-danger').attr('href', $(this).attr('href'));
                $('.modal[data-for="' + $table.attr('id') + '"]').modal();
            });
        }
    };

    return function() {
        initDatatable();
        initDeleteButtonHandler();
    };
});
