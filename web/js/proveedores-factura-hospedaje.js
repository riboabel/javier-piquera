define([
    'jquery',
    'plugins/sweetalert/sweetalert.min',
    'js/app/datatables-init',
    'plugins/jquery.blockUI'
], function($, swal) {
    'use strict';

    return function() {
        $(document).ready(function() {
            var $table = $('#dataTables-providers');

            $table.dataTable({
                columnDefs: [
                    {
                        sortable: false,
                        searchable: false,
                        targets: [3, 4]
                    }, {
                        width: '80px',
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

            $table.on('click', '.btn-reset', function(event) {
                var url = $(this).attr('href');

                event.preventDefault();

                function makeResetRequest(url, data) {
                    return $.ajax({
                        data: data,
                        dataType: 'json',
                        method: 'POST',
                        url: url
                    });
                }

                function processResetResponse(response) {
                    if (response.action == 'confirm') {
                        swal({
                            showCancelButton: true,
                            title: 'Confirmación',
                            text: response.message,
                            type: 'warning'
                        }, function(confirm) {
                            if (confirm) {
                                makeResetRequest(url, {confirmed: 'yes'})
                                    .done(processResetResponse);
                            } else {
                                $.unblockUI();
                            }
                        });
                    } else {
                        location.href = response.redirectUrl;
                    }
                }

                $.blockUI({
                    message: 'Verificando condiciones...'
                });

                makeResetRequest(url, {})
                    .done(processResetResponse);
            });
        });
    };
});