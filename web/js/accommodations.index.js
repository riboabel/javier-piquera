define([
    'jquery',
    'js/app/router',
    'plugins/sweetalert/sweetalert.min',
    'js/app/datatables-init'
], function($, router, swal) {
    'use strict';

    var datatable;

    function initTable() {
        var table = $('#table-accommodations');
        datatable = table.dataTable({
            columns: [
                {
                    name: 'startDate',
                    title: 'Inicio'
                }, {
                    name: 'endDate',
                    title: 'Terminación'
                }, {
                    name: 'nights',
                    title: 'Noches'
                }, {
                    name: 'reference',
                    title: 'Referencia'
                }, {
                    name: 'leadClient',
                    title: 'Cliente'
                }, {
                    name: 'pax',
                    title: 'PAX'
                }, {
                    name: 'fromLocation',
                    title: 'Lugar'
                }, {
                    name: 'fromRegion',
                    title: 'Región'
                }, {
                    name: 'cost',
                    title: 'Costo'
                }, {
                    name: 'actions',
                    sortable: false,
                    searchable: false,
                    width: '80px'
                }
            ],
            ajax: {
                method: 'GET',
                url: router.generate('app_accommodation_getdata')
            },
            serverSide: true,
            processing: false
        });
    }

    function initControls() {
        $('#table-accommodations').on('click', '.btn-delete', function(event) {
            event.preventDefault();

            var url = $(this).attr('href');

            swal({
                showCancelButton: true,
                title: 'Comfirmar eliminación',
                text: '¿Estás seguro(a) que deseas eliminar el registro?',
                type: 'warning'
            }, function(confirm) {
                if (confirm) {
                    $('<form/>')
                        .appendTo($('body'))
                        .css('display', 'none')
                        .attr({'action': url, method: 'POST'})
                        .submit()
                        .remove();
                }
            });
        });
    }

    return function() {
        initTable();
        initControls();
    };
});