define([
    'jquery',
    'js/app/router',
    'js/app/fixed-messages',
    'bootstrap',
    'js/app/datatables-init',
    'plugins/jquery.blockUI',
    'plugins/datepicker/bootstrap-datepicker',
    'jquery/select2',
    'plugins/tooltipster',
    'plugins/icheck',
    'plugins/jquery.form'
], function ($, router, messageManager) {
    'use strict';

    var datatable;

    var clickExecuteHandler = function(event) {
        event.preventDefault();

        $('#modalExecute').remove();

        var $modal = $('<div class="modal fade" id="modalExecute" data-backdrop="static"><div class="modal-dialog"><div class="modal-content"></div></div></div>'),
            successForm = function(json) {
                $modal.modal('hide');
                var $row = $modal.data('row');
                $row.find('td:last').empty().text('Ejecutando...');
                datatable.DataTable().draw(false);
            };

        $modal.data('row', $(this).closest('tr'));
        $modal.find('.modal-content').load($(this).attr('href'), function() {
            $modal.find('form').ajaxForm({
                dataType: 'json',
                success: successForm
            });

            $modal.modal();
        });
    };

    var clickEditIssuesHandler = function(event) {
        event.preventDefault();

        var button = $(this);
        if (button.attr('data-loading')) {
            return;
        } else {
            button.attr('data-loading', true);
        }

        $('#modalExecute').remove();

        var $modal = $('<div class="modal fade" id="modalExecute" data-backdrop="static"><div class="modal-dialog"><div class="modal-content"/></div></div>'),
            successForm = function(json) {
                $modal.modal('hide');
            };

        $modal.find('.modal-content').load($(this).attr('href'), function() {
            $modal.find('form').ajaxForm({
                dataType: 'json',
                success: successForm
            });

            $modal.modal();

            button.removeAttr('data-loading');
        });
    };

    var clickDeleteHandler = function(event) {
        event.preventDefault();

        $('.modal[data-for=dataTables-reservas] button.btn-danger').data('url', $(event.currentTarget).attr('href'));
        $('.modal[data-for=dataTables-reservas]').modal();
    };

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
                window.alert('Error ejecutando operación.');
                datatable.DataTable().draw(false);
            }
        });
    };

    var initDeleteModal = function() {
        $('.modal[data-for=dataTables-reservas]').find('button.btn-danger').on('click', clickDeleteModalHandler);
    };

    var clickCancelHandler = function(event) {
        event.preventDefault();

        $('.modal[data-for=cancel-op]').find('button.btn-warning').data('url', $(this).attr('href'));
        $('.modal[data-for=cancel-op]').modal();
    };

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
                window.alert('Error ejecutando operación.');
                datatable.DataTable().draw(false);
            }
        });

    };

    var initCancelModal = function() {
        $('.modal[data-for=cancel-op]').find('button.btn-warning').on('click', clickCancelModalHandler);
    };

    var handleDatatablePredraw = function() {
        $(this).block({
            message: 'Procesando...'
        });
    };

    var drawDatatableHandler = function() {
        $(this).find('[title]').tooltip({'trigger': 'hover'});

        $(this).find('input:checkbox').iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });

        $(this).unblock();
    };

    var initDatatable = function(settings) {
        var $table = $('#dataTables-reservas');

        $table.on('click', '.btn-delete', clickDeleteHandler);
        $table.on('click', '.btn-cancel', clickCancelHandler);
        $table.on('click', '.btn-execute', clickExecuteHandler);
        $table.on('click', '.btn-edit-issues', clickEditIssuesHandler);
        $table.on('click', '.btn-driver-confirm', handleClickConfirmDriver);

        $table.on('draw.dt', drawDatatableHandler);
        $table.on('preDraw.dt', handleDatatablePredraw);

        datatable = $table.dataTable({
            aoColumns: [
                {
                    name: 'selected',
                    sortable: false,
                    searchable: false
                },
                {
                    name: "state",
                    bSortable: false,
                    bSearchable: false
                },
                {
                    name: "startAt"
                },
                {
                    name: "provider"
                },
                {
                    name: "serialNumber"
                },
                {
                    name: "providerReference"
                },
                {
                    name: "clientNames"
                },
                {
                    name: 'pax',
                    searchable: false
                },
                {
                    name: "serviceType"
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
                    name: "issues",
                    searchable: false,
                    sortable: false
                },
                {
                    bSortable: false,
                    bSearchable: false,
                    width: '170px'
                }
            ],
            aaSorting: [[2, "asc"]],
            ajax: {
                data: function(baseData) {
                    var filter = [];
                    $.each($('form#filter').serializeArray(), function(i, e) {
                        if (/\[\]$/.test(e.name)) {
                            var sName = e.name.replace(/\[\]$/, '');
                            if (!filter[sName]) {
                                filter[sName] = [];
                            }

                            filter[sName].push(e.value);
                        } else {
                            filter[e.name] = e.value;
                        }
                    });

                    return $.extend(true, baseData, filter);
                },
                method: 'GET',
                url: router.generate('app_reservas_getdata')
            },
            iDisplayLength: settings.pageLength,
            oSearch: {
                "sSearch": settings.search
            },
            rowCallback: function (row) {
                if ($('span.fa.fa-exclamation-triangle.text-warning', row).length > 0) {
                    $(row).find('span.fa.fa-exclamation-triangle.text-warning').remove();
                    $(row).find('td').css('background-color', '#fcf8e3');
                }
            },
            serverSide: true,
            processing: false
        });
    };

    var handleClickConfirmDriver = function(event) {
        event.preventDefault();

        $(this).parents('td:first').empty().text('Confirmando...');

        $.ajax($(this).attr('href'), {
            method: 'post',
            success: function() {
                datatable.DataTable().draw(false);
            }
        });
    };

    var initFilterDatepickers = function() {
        $('#filter-form .datepicker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
            todayBtn: true,
            todayHighlight: true
        });
    };

    var initFilter = function() {
        initFilterDatepickers();

        $('#filter-form').find('input, select').on('change', function() {
            if ($(this).is('select[name="reserva_filter_form[isDriverAssigned][choice]"]')) {
                if ($(this).val() === 'with-drivers') {
                    $('#filter-form [name="reserva_filter_form[isDriverAssigned][drivers][]"]').closest('div.row').show();
                } else {
                    $('#filter-form [name="reserva_filter_form[isDriverAssigned][drivers][]"]').closest('div.row').hide();
                }
            }
            datatable.DataTable().draw();
        });

        $('[name="reserva_filter_form[isDriverAssigned][drivers][]"], select[name="reserva_filter_form[serviceType][]"]', '#filter-form').select2({
            width: '100%'
        });
    };

    var handlePrintSelectionClick = function(event) {
        event.preventDefault();

        if (datatable.find('input:checkbox:checked').length == 0) {
            return window.alert('Debe seleccionar registros para realizar esta operación');
        }

        var $f = $('<form/>').attr({
            action: $(this).attr('href'),
            method: 'post',
            target: '_blank'
        }).append(datatable.find('input:checkbox:checked').clone().attr('name', 'ids[]'))
            .appendTo($('body'));

        $f.submit().remove();
    };

    var handleClickSpecialReport = function(event) {
        event.preventDefault();

        if (datatable.find('input:checkbox:checked').length == 0) {
            return window.alert('Debe seleccionar registros para realizar esta operación');
        }

        var $f = $('<form/>').attr({
            action: $(this).attr('href'),
            method: 'post',
            target: '_blank'
        }).append(datatable.find('input:checkbox:checked').clone().attr('name', 'ids[]'))
            .appendTo($('body'));

        $f.submit().remove();
    };

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
    };

    return {
        init: function(settings) {
            $(document).ready(function () {
                initDatatable(settings);
                initDeleteModal();
                initCancelModal();
                initFilter();
                initSelectionTools();
                messageManager(settings.notices);
            });
        }
    };
});