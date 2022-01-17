define([
    'jquery',
    'js/app/router',
    'plugins/sweetalert/sweetalert.min',
    'js/app/datatables-init',
    'plugins/jquery.blockUI'
], function($, router, swal) {
    'use strict';

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
                        fields[e.name] = e.value;
                    });

                    return $.extend(true, baseData, fields);
                },
                url: router.generate('app_admin_drivers_getdata'),
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

            var url = $(this).attr('href'),
                a = $(this);

            swal({
                showCancelButton: true,
                title: 'Confirmar eliminación',
                text: 'Se dispone a eliminar la información de un conductor. Todos los viajes realizados por este serán eliminados del sistema. ¿Desea continuar?',
                type: 'warning'
            }, function(confirm) {
                if (confirm) {
                    a.closest('td').empty().text('Eliminando...');
                    $.ajax({
                        method: 'POST',
                        success: function(json) {
                            $table.DataTable().draw(false);
                        },
                        type: 'json',
                        url: url
                    });
                }
            });
        });
    };

    var initFilter = function() {
        $('#collapseFilter select').on('change', function() {
            $table.DataTable().draw();
        });
    };

    return function() {
        initDatatable();
        initFilter();
    };
});
