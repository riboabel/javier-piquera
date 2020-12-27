define([
    'jquery',
    'js/app/router',
    '//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js',
    'moment',
    'js/app/fixed-messages',
    'bootstrap',
    'js/app/datatables-init',
    'plugins/jquery.blockUI',
    'plugins/datepicker/bootstrap-datepicker',
    'jquery/select2',
    'plugins/tooltipster',
    'plugins/icheck',
    'plugins/jquery.form'
], function($, router, numeral, moment) {
    'use strict';

    var $table, datatable;

    function initDatatable(options) {
        $table = $('#dataTables-invoices');

        $table.on('draw.dt', function() {
            $(this).unblock();
        });
        $table.on('preDraw.dt', function() {
            $(this).block();
        });

        datatable = $table.dataTable({
            columns: [
                {
                    data: 'createdAt',
                    name: 'createdAt',
                    title: 'Fecha',
                    render: function(data, type) {
                        if (type == 'display') {
                            return moment(data, 'YYYY-MM-DD').format('DD/MM/YYYY HH:mm');
                        }

                        return data;
                    }
                },
                {
                    data: 'invoiceNumber',
                    name: "invoiceNumber",
                    title: 'Número'
                },
                {
                    data: 'providerName',
                    name: "providerName",
                    title: 'Proveedor'
                },
                {
                    data: 'grandTotal',
                    name: "grandTotal",
                    title: 'Importe',
                    searchable: false,
                    render: function(data, type) {
                        if ('display' == type) {
                            return numeral(data).format('$0.00');
                        }

                        return data;
                    }
                },
                {
                    name: 'actions',
                    data: 'actions',
                    sortable: false,
                    searchable: false,
                    width: '80px'
                }
            ],
            sorting: [[0, "desc"]],
            ajax: {
                method: 'GET',
                url: router.generate('app_hostinginvoices_getdata')
            },
            serverSide: true,
            processing: false
        });
    }

    return function(options) {
        $(document).ready(function () {
            initDatatable(options);
            // initDeleteModal();
            // initCancelModal();
            // initFilter();
            // initSelectionTools();
            // messageManager(settings.notices);
        });
    }
});