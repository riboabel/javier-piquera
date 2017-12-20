App = typeof App !== 'undefined' ? App : {};
App.Drivers = typeof App.Drivers !== 'undefined' ? App.Drivers : {};

+(App.Drivers.Index = function($) {
    var $table = $('table#dataTables-drivers');

    var initDatatable = function() {
        $table.DataTable({
            aoColumns: [
                {
                    name: 'name',
                    title: 'Nombre'
                },
                {
                    title: 'Móvil',
                    searchable: false,
                    sortable: false
                },
                {
                    title: 'Fijo',
                    searchable: false,
                    sortable: false
                },
                {
                    name: 'contactInfo',
                    title: 'Información de contacto',
                    sortable: false
                },
                {
                    name: 'isDriverGuide',
                    title: 'Driver guide',
                    searchable: false,
                    width: '100px'
                },
                {
                    title: 'Acciones',
                    searchable: false,
                    sortable: false,
                    width: '80px'
                }
            ],
            bProcessing: true,
            bServerSide: true,
            ajax: {
                url: Routing.generate('app_drivers_getdata'),
                method: 'POST'
            },
            aLengthMenu: [ 50, 100, 200 ],
            language: {
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
        });

        $table.on('click', '.btn-delete', function(event) {
            event.preventDefault();

            if ($('.modal[data-for="' + $table.attr('id') + '"]').length > 0) {
                $('.modal[data-for="' + $table.attr('id') + '"] .btn-danger').attr('href', $(this).attr('href'));
                $('.modal[data-for="' + $table.attr('id') + '"]').modal();
            }
        });
    }

    return {
        init: function() {
            initDatatable();
        }
    }
}(jQuery));
