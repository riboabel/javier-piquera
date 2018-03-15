App = typeof App !== 'undefined' ? App : {};
App.Drivers = typeof App.Drivers !== 'undefined' ? App.Drivers : {};

+(App.Drivers.Index = function($) {
    var $table = $('table#dataTables-drivers');

    var initDatatable = function() {
        $table.on('preDraw.dt', function() {
            $(this).block({
                message: 'Cargando datos...'
            });
        }).on('draw.dt', function() {
            $(this).unblock();
        });

        $table.DataTable({
            ajax: {
                data: function(baseData) {
                    var fields = [];
                    $.each($('#formFilter').serializeArray(), function(i, e) {
                        fields[e['name']] = e['value'];
                    });

                    return $.extend(true, baseData, fields);
                },
                url: Routing.generate('app_admin_drivers_getdata'),
                method: 'GET'
            },
            columns: [
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
            bServerSide: true
        });

        $table.on('click', '.btn-delete', function(event) {
            event.preventDefault();

            if ($('.modal[data-for="' + $table.attr('id') + '"]').length > 0) {
                $('.modal[data-for="' + $table.attr('id') + '"] .btn-danger').attr('href', $(this).attr('href'));
                $('.modal[data-for="' + $table.attr('id') + '"]').modal();
            }
        });
    }

    var initFilter = function() {
        $('#collapseFilter select').on('change', function() {
            $table.DataTable().draw();
        });
    }

    return {
        init: function() {
            initDatatable();
            initFilter();
        }
    }
}(jQuery));
