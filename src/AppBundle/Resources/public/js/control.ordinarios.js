App = typeof App !== 'undefined' ? App : {};
App.Control = typeof App.Control !== 'undefined' ? App.Control : {};
App.Control.Ordinarios = typeof App.Control.Ordinarios !== 'undefined' ? App.Control.Ordinarios : {};

+(App.Control.Ordinarios.Index = function($) {
    'use strict';

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
    };

    var initControls = function() {
        $table.on('click', '.btn-control', handleClickControl);
    };

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
                aoColumns: [
                    {
                        name: "startAt",
                        title: 'Inicio'
                    },
                    {
                        name: "provider",
                        title: 'Cliente'
                    },
                    {
                        name: "serialNumber",
                        title: 'Número'
                    },
                    {
                        name: "providerReference",
                        title: 'Referencia'
                    },
                    {
                        name: "clientNames",
                        title: 'Nombre(s)'
                    },
                    {
                        name: 'pax',
                        searchable: false,
                        title: 'PAX'
                    },
                    {
                        name: "serviceType",
                        title: 'Servicio'
                    },
                    {
                        name: "driver",
                        title: 'Conductor'
                    },
                    {
                        name: 'guide',
                        title: 'Guía',
                        searchable: false,
                        sortable: false
                    },
                    {
                        name: "control",
                        title: 'Control',
                        searchable: false,
                        sortable: false
                    }
                ],
                "aaSorting": [[0, "asc"]],
                "aLengthMenu": [200, 400, 800, 1000],
                "responsive": true,
                bServerSide: true,
                bProcessing: false,
                ajax: {
                    method: 'GET',
                    url: Routing.generate('app_control_getdata')
                }
            });
    };

    return {
        init: function() {
            initTable();
            initControls();
        }
    };
}(jQuery));
