App.Invoices = function() {
    var drawDatatableHandler = function() {
        if ($.fn.tooltip) {
            $(this).find('[title]').tooltip({
                trigger: 'hover'
            });
        }

        $(this).unblock();
    }

    var handlePredrawDatatable = function() {
        $(this).block({'message': 'Procesando...'});
    }

    var initDatatable = function() {
        var $table = $('#dataTables-invoices'),
            options = {
                "aoColumns": [
                    {
                        title: 'Fecha',
                        name: "invoicedAt",
                        searchable: false,
                        sortable: false
                    },
                    {
                        title: 'Número',
                        name: 'invoiceNumber',
                        searchable: false
                    },
                    {
                        title: 'Agencia',
                        name: "provider"
                    },
                    {
                        title: 'Referencia',
                        name: "providerReference"
                    },
                    {
                        title: 'Tipo de servicio',
                        name: "serviceType"
                    },
                    {
                        title: 'Importe',
                        name: "invoicedPrice",
                        searchable: false,
                        sortable: false
                    },
                    {
                        title: 'Acciones',
                        searchable: false,
                        sortable: false,
                        width: 80
                    }
                ],
                aaSorting: [[0, "asc"]],
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "No hay facturas creadas",
                    "sInfo":           "Mostrando del _START_ al _END_ (de _TOTAL_)",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
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
                "responsive": true,
                "bServerSide": true,
                "bProcessing": false,
                "ajax": {
                    "method": 'post',
                    "url": $table.data('ajax-url')
                }
            }

        $table
            .on('draw.dt', drawDatatableHandler)
            .on('preDraw.dt', handlePredrawDatatable);

        datatable = $table.dataTable(options);
    }

    var handleClickCreateOrder = function(event) {
        event.preventDefault();

        
    }

    return {
        init: function() {
            initDatatable();
        }
    }
}();