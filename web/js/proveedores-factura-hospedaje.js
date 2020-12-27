define([
    'jquery',
    'js/app/datatables-init',
    'plugins/jquery.blockUI'
], function($) {
    'use strict';

    return function() {
        $(document).ready(function() {
            var $table = $('#dataTables-providers');

            $table.dataTable({
                columnDefs: [
                    {
                        sortable: false,
                        searchable: false,
                        targets: [4]
                    }
                ]
            });

            $table.on('click', '.btn-remove', function(event) {
                var url = $(event.currentTarget).attr('href'),
                    form;

                event.preventDefault();

                if (window.confirm('¿Seguro quieres eliminar este registro?')) {
                    form = $('<form></form>').appendTo($('body')).attr({
                        action: url,
                        method: 'POST'
                    });

                    $.blockUI();
                    form.submit();
                    form.remove();
                }
            });
        });
    };
});