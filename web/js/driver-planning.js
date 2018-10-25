require([
    'jquery',
    'jquery/ui',
    'jquery/select2',
    'datatables.net',
    'datatables.net-bs'
], function ($) {
    'use strict';

    require(['jquery/select2.i18n-es']);

    var datatable, $table;

    var handleDrawTable = function() {
        var url = $table.data('driver-url');

        $table.find('select').select2({
            language: 'es',
            minimunInputLength: 1,
            width: '100%',
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                method: 'get',
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                }
            }
        });

        $table.find('select').on('change', handleSelect2Change);
    };

    var handleSelect2Change = function() {
        var url = $(this).data('save-url');

        $.ajax(url, {
            dataType: 'json',
            method: 'post',
            data: {
                driver: $(this).val()
            },
            success: function(json) {
                datatable.DataTable().draw(false);
            }
        });
    };

    var initTable = function() {
        $table = $('table#table-records');

        $table.on('draw.dt', handleDrawTable);
        datatable = $table.dataTable({
            aoColumns: [
                {
                    name: 'serrialNumber'
                },
                {
                    name: 'provider'
                },
                {
                    name: 'providerReference'
                },
                {
                    name: 'serviceType'
                },
                {
                    name: 'serviceDescription'
                },
                {
                    name: 'startAt'
                },
                {
                    name: 'endAt'
                },
                {
                    name: 'driver',
                    sortable: false
                }
            ],
            "language": {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "No hay cobros pendientes",
                "sInfo":           "Mostrando del _START_ al _END_ (de _TOTAL_)",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    '',
                "sSearch":         'Buscar:',
                "sUrl":            '',
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            },
            serverSide: true,
            bProcessing: true,
            ajax: {
                url: $table.data('url'),
                method: 'post',
                data: function(baseData) {
                    return $.extend(true, baseData, {
                        filter: {
                            fromDate: $('#from-date').val(),
                            toDate: $('#to-date').val()
                        }
                    });
                }
            }
        });
    };

    var initDatepickers = function() {
        $('.datepicker').on('change', function() {
            datatable.DataTable().draw(true);
        });
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            clearBtn: true
        });
    };

    $(document).ready(function () {
        initTable();
        initDatepickers();
    });
});