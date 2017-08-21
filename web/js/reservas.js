App = typeof App !== 'undefined' ? App : {};
App.Reservas = typeof App.Reservas !== 'undefined' ? App.Reservas : {};

App.Reservas.Index = function($) {
    var datatable;

    var clickExecuteHandler = function(event) {
        event.preventDefault();

        if ($('#modalExecute').length > 0) {
            $('#modalExecute').remove();
        }

        var $modal = $('<div class="modal fade" id="modalExecute" data-backdrop="static"><div class="modal-dialog"><div class="modal-content"></div></div></div>'),
            successForm = function(json) {
                $modal.modal('hide');
                var $row = $modal.data('row');
                $row.find('td:last').empty().text('Ejecutando...');
                datatable.DataTable().draw(false);
            }

        $modal.data('row', $(this).closest('tr'));
        $modal.find('.modal-content').load($(this).attr('href'), function() {
            $modal.find('form').ajaxForm({
                dataType: 'json',
                success: successForm
            });
        });
        $modal.modal();
    }

    var clickDeleteHandler = function(event) {
        event.preventDefault();

        $('.modal[data-for=dataTables-reservas] button.btn-danger').data('url', $(event.currentTarget).attr('href'));
        $('.modal[data-for=dataTables-reservas]').modal();
    }

    var clickDeleteModalHandler = function(event) {
        event.preventDefault();

        var url = $(this).data('url');

        $('#dataTables-reservas').find('a[href="' + url + '"]').parents('td:first').empty().text('Eliminando...');
        $(this).parents('.modal:first').modal('hide');
        $.ajax(url, {
            method: 'post',
            type: 'json',
            success: function(json) {
                datatable.DataTable().draw(false);
            },
            error: function() {
                alert('Error ejecutando operación.');
                datatable.DataTable().draw(false);
            }
        });
    }

    var initDeleteModal = function() {
        $('.modal[data-for=dataTables-reservas]').find('button.btn-danger').on('click', clickDeleteModalHandler);
    }

    var clickCancelHandler = function(event) {
        event.preventDefault();

        $('.modal[data-for=cancel-op]').find('button.btn-warning').data('url', $(this).attr('href'));
        $('.modal[data-for=cancel-op]').modal();
    }

    var clickCancelModalHandler = function(event) {
        event.preventDefault();

        var url = $(this).data('url');
        $('#dataTables-reservas').find('a[href="' + url + '"]').parents('td:first').empty().text('Cancelando...');
        $(this).parents('.modal:first').modal('hide');

        $.ajax(url, {
            method: 'post',
            type: 'json',
            success: function(json) {
                datatable.DataTable().draw(false);
            },
            error: function() {
                alert('Error ejecutando operación.');
                datatable.DataTable().draw(false);
            }
        });

    }

    var initCancelModal = function() {
        $('.modal[data-for=cancel-op]').find('button.btn-warning').on('click', clickCancelModalHandler);
    }

    var drawDatatableHandler = function() {
        $(this).find('.btn-delete').on('click', clickDeleteHandler);
        $(this).find('.btn-cancel').on('click', clickCancelHandler);
        $(this).find('.btn-execute').on('click', clickExecuteHandler);
        $(this).find('.btn-driver-confirm').on('click', handleClickConfirmDriver);
        $(this).find('[title]').tooltip({'trigger': 'hover'});

        $(this).find('input:checkbox').iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });

        $(this).unblock();
    }

    var handleDatatablePredraw = function() {
        $(this).block({
            message: 'Procesando...'
        });
    }

    var initDatatable = function(settings) {
        var $table = $('#dataTables-reservas');

        $table.on('draw.dt', drawDatatableHandler);
        $table.on('preDraw.dt', handleDatatablePredraw);

        var options = {
                "aoColumns": [
                    {
                        "name": 'selected',
                        "sortable": false,
                        "searchable": false
                    },
                    {
                        "name": "state",
                        "bSortable": false,
                        "bSearchable": false
                    },
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
                        "name": "issues",
                        "searchable": false,
                        "sortable": false
                    },
                    {
                        "bSortable": false,
                        "bSearchable": false
                    }
                ],
                "aaSorting": [[2, "asc"]],
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
                    "url": $table.data('ajax-url'),
                    "data": function(baseData) {
                        var filter = [];
                        $.each($('form#filter').serializeArray(), function(i, e) {
                            if (/^(?!filter\[)/.test(e['name'])) {
                                filter[e['name']] = e['value'];
                            }
                        });
                        return $.extend(true, baseData, {
                            "filter": {
                                "isExecuted": $('[name="filter[isExecuted]"]').val(),
                                "isCancelled": $('[name="filter[isCancelled]"]').val(),
                                "isDriverConfirmed": $('[name="filter[isDriverConfirmed]"]').val(),
                                "isDriverAssigned": $('select[name="filter[isDriverAssigned]"]').val(),
                                "withDrivers": $('select[name="filter[withDrivers]"]').val()
                            }
                        }, filter);
                    }
                }
            }

        options.oSearch = {"sSearch": settings.search};

        datatable = $table.dataTable(options);
    }

    var handleNoticeModal = function() {
        if ($('.modal[data-for=flash]').length) {
            $('.modal[data-for=flash]').modal();
        }
    }

    var handleClickConfirmDriver = function(event) {
        event.preventDefault();

        $(this).parents('td:first').empty().text('Confirmando...');

        $.ajax($(this).attr('href'), {
            method: 'post',
            success: function() {
                datatable.DataTable().draw(false);
            }
        });
    }

    var initFilterDatepickers = function() {
        $('#filter-form .datepicker').datepicker({
            dateFormat: 'dd/mm/yy'
        });
    }

    var initFilter = function() {
        initFilterDatepickers();

        $('#filter-form').find('input, select').on('change', function() {
            if ($(this).is('select[name="reserva_filter_form[isDriverAssigned][choice]"]')) {
                if ($(this).val() === 'with-drivers') {
                    $('#filter-form [name="reserva_filter_form[isDriverAssigned][drivers][]"]').parent().show();
                } else {
                    $('#filter-form [name="reserva_filter_form[isDriverAssigned][drivers][]"]').parent().hide();
                }
            }
            datatable.DataTable().draw();
        });

        $('#filter-form [name="reserva_filter_form[isDriverAssigned][drivers][]"]').select2({
            width: '100%'
        });
    }

    var handlePrintSelectionClick = function(event) {
        event.preventDefault();

        if (datatable.find('input:checkbox:checked').length == 0) {
            return alert('Debe seleccionar registros para realizar esta operación');
        }

        var $f = $('<form/>').attr({
            action: $(this).attr('href'),
            method: 'post',
            target: '_blank'
        }).append(datatable.find('input:checkbox:checked').clone().attr('name', 'ids[]'))
            .appendTo($('body'));

        $f.submit().remove();
    }

    var handleClickSpecialReport = function(event) {
        event.preventDefault();

        if (datatable.find('input:checkbox:checked').length == 0) {
            return alert('Debe seleccionar registros para realizar esta operación');
        }

        var $f = $('<form/>').attr({
            action: $(this).attr('href'),
            method: 'post',
            target: '_blank'
        }).append(datatable.find('input:checkbox:checked').clone().attr('name', 'ids[]'))
            .appendTo($('body'));

        $f.submit().remove();
    }

    var initSelectionTools = function() {
        $('#link-select-all').on('click', function(event) {
            event.preventDefault();
            datatable.find('input:checkbox').iCheck('check');
        });

        $('#link-select-none').on('click', function(event) {
            event.preventDefault();
            datatable.find('input:checkbox').iCheck('uncheck');
        });

        $('a#linkPrintSelection').on('click', handlePrintSelectionClick);
        $('a#linkPrintSpecialReport').on('click', handleClickSpecialReport);
    }

    return {
        init: function(settings) {
            initDatatable(settings);
            initDeleteModal();
            initCancelModal();
            initFilter();
            initSelectionTools();
            handleNoticeModal();
        }
    }
}(jQuery);
