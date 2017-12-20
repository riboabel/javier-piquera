App = typeof App !== 'undefined' ? App : {};
App.ReservasMicrobus = typeof App.ReservasMicrobus !== 'undefined' ? App.ReservasMicrobus : {};

+(App.ReservasMicrobus.Index = function() {
    var $table = $('#dataTables-reservas');

    var initTable = function() {
        $table.on('preDraw.dt', function() {
            $(this).block();
        }).on('draw.dt', function() {
            $(this).unblock();
            $(this).find('input:checkbox').iCheck({
                checkboxClass: 'icheckbox_flat-blue'
            });
        });

        $table.dataTable({
            ajax: {
                url: Routing.generate('app_reservasmicrobus_getdata'),
                method: 'GET',
                error: function() {
                    alert('Error obteniendo datos de listado')
                }
            },
            columns: [
                {
                    sortable: false,
                    searchable: false,
                    width: '24px'
                },
                {
                    name: 'startat',
                    title: 'Fecha'
                },
                {
                    name: 'service',
                    title: 'Servicio'
                },
                {
                    name: 'client',
                    title: 'Agencia'
                },
                {
                    name: 'clientSerial',
                    title: 'Referencia de la agencia'
                },
                {
                    name: 'provider',
                    title: 'Proveedor'
                },
                {
                    name: 'providerSerial',
                    title: 'Referencia del proveedor'
                },
                {
                    name: 'clientNames',
                    title: 'Nombres de los clientes'
                },
                {
                    name: 'pax',
                    title: 'Pax'
                },
                {
                    sortable: false,
                    searchable: false
                }
            ],
            serverSide: true,
            sorting: [[1, 'desc']]
        });
    }

    var initActions = function() {
        $table.on('click', '.btn-delete', function(event) {
            event.preventDefault();

            if (confirm('¿Seguro desea borrar esta reserva?')) {
                var url = $(this).attr('href');
                $(this).closet('td').text('Eliminando...')
                $.ajax({
                    method: 'POST',
                    success: function(json) {
                        $table.DataTable().draw(true);
                    },
                    dataType: 'json',
                    url: url
                });
            }
        });
    }

    return {
        init: function() {
            initTable();
            initActions();
        }
    }
}(jQuery));
