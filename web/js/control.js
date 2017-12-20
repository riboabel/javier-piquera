App = typeof App !== 'undefined' ? App : {};
App.Control = typeof App.Control !== 'undefined' ? App.Control : {};

+(App.Control.Index = function($) {
    var $table = $('#table-xs');

    var handleClickControl = function(event) {
        event.preventDefault();

        $('#dR').remove();

        var $dR = $('<div class="modal fade in"/>').append('<div class="modal-dialog"><div class="modal-content"></div></div>');
        $dR.find('.modal-content').load($(this).attr('href'), function() {
            $dR.find('form').ajaxForm({
                target: $dR.find('.modal-content')
            });
            $dR.modal();
        });
    }

    var initControls = function() {
        $table.on('click', '.btn-control', handleClickControl);
    }

    var initTable = function() {
        $table
            .on('preDraw.dt', function() {
                $(this).block({message: 'Procesando...'});
            })
            .on('draw.dt', function() {
                $(this).find('[title]').tooltip({'trigger': 'hover'});
                $(this).unblock();
            })
            .DataTable({
                "aoColumns": [
                    {
                        "name": "startAt"
                    },
                    {
                        "name": "provider"
                    },
                    {
                        "name": "serialNumber"
                    },
                    {
                        "name": "providerReference"
                    },
                    {
                        "name": "clientNames"
                    },
                    {
                        "name": 'pax',
                        "searchable": false
                    },
                    {
                        "name": "serviceType"
                    },
                    {
                        "name": "driver",
                        title: 'Conductor'
                    },
                    {
                        name: 'guide',
                        title: 'Guía',
                        searchable: false,
                        sortable: false
                    },
                    {
                        "name": "control",
                        "searchable": false,
                        "sortable": false
                    }
                ],
                "aaSorting": [[0, "asc"]],
                "aLengthMenu": [200, 400, 800, 1000],
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
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
                    "url": Routing.generate('app_control_getdata')
                }
            });
    }

    return {
        init: function() {
            initTable();
            initControls();
        }
    }
}(jQuery));
