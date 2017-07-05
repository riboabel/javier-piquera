App = typeof App !== 'undefined' ? App : {};
App.Pagos = typeof App.Pagos !== 'undefined' ? App.Pagos : {};

+(App.Pagos.Index = function($) {

    var datatable;

    var drawDatatableHandler = function() {
        if ($.fn.tooltip) {
            $(this).find('[title]').tooltip({
                trigger: 'hover'
            });
        }

        $(this).unblock();

        $(this).find('input:checkbox').iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });
    }

    var initDatatable = function() {
        var $table = $('#dataTables-pays'),
            options = {
                "aoColumns": [
                    {
                        "bSortable": false,
                        "bSearchable": false
                    },
                    {
                        "name": "startAt"
                    },
                    {name: "driver"},
                    {name: 'client'},
                    {name: "providerReference"},
                    {name: "serviceType"},
                    {name: "charge", "sortable": false, "searchable": false}
                ],
                aaSorting: [[1, "asc"]],
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "No hay pagoss pendientes",
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
                'ajax': {
                    data: function(data) {
                        return $.extend(true, data, {
                            filter: {
                                from: $('#fromDate').val(),
                                to: $('#toDate').val(),
                                driver: $('#filter_driver').val(),
                                state: $('#filter_state').val()
                            }
                        });
                    },
                    "method": 'post',
                    "url": $table.data('ajax-url')
                }
            }

        $table.on('draw.dt', drawDatatableHandler).on('preDraw.dt', function() {
            $(this).block({'message': 'Procesando...'});
        });
        datatable = $table.dataTable(options);
    }

    var addValidationMethod = function() {
        if ($.validator) {
            $.validator.addMethod('validdecimal', function(value, element) {
                return this.optional(element) || /^((.|,)\d{1,2}|\d+(|.(|\d{1,2})|,(\d{1,2})))$/.test(value);
            }, 'Valor no válido');
        } else {
            console.log('Validator plugin not present');
        }
    };

    var loadPaysModalHandler = function() {
        App.Main.validate($('.modal[data-for=pay-op] form'));
    }

    var clickLinkPayHandler = function(event) {
        event.preventDefault();

        if ($('#dataTables-pays').find('input:checkbox:checked').length == 0) {
            $('.modal[data-for=pay-error]').modal();
            return;
        }

        var ids = [];
        $('#dataTables-pays').find('input:checkbox:checked').each(function() {
            ids.push(this.value);
        });

        $('.modal[data-for=pay-op] .modal-body').empty().text('Cargando información de servicios seleccionados...');
        $('.modal[data-for=pay-op]').modal();
        $('.modal[data-for=pay-op] div.modal-body').load($(this).attr('href'), {
            ids: ids
        }, loadPaysModalHandler);
    }

    var clickPayHandler = function(event) {
        event.preventDefault();

        $(this).parents('.modal-content:first').find('form').submit();
        $(this).off('click').text('Guardando información...');
    }

    var handlePrintButtonClick = function() {
        var url = $(this).data('url');

        var $f = $('.modal[data-for=pay-op] form').clone();
        $f.attr({
            action: url,
            method: 'post',
            target: '_blank'
        }).appendTo($('body')).submit();

        setTimeout(function() {
            $f.remove();
        }, 100);
    }

    var initModal = function() {
        $('.modal[data-for=pay-op] .btn-success').on('click', clickPayHandler);
        $('.modal[data-for=pay-op] .btn-print').on('click', handlePrintButtonClick);
    }

    var clickSelectAllHandler = function(event) {
        event.preventDefault();

        $('#dataTables-pays input:checkbox').prop('checked', true);
        $('#dataTables-pays input:checkbox').iCheck('update');
    }

    var clickSelectNoneHandler = function(event) {
        event.preventDefault();

        $('#dataTables-pays input:checkbox').prop('checked', false);
        $('#dataTables-pays input:checkbox').iCheck('update');
    }

    var initTools = function() {
        $('#linkPay').on('click', clickLinkPayHandler);
        $('#linkSelectAll').on('click', clickSelectAllHandler);
        $('#linkSelectNone').on('click', clickSelectNoneHandler);
    }

    var initOldPaysHandler = function() {
        $('#modal-historico button.btn-primary').on('click', function(event) {
            event.preventDefault();

            $('#modal-historico .modal-body form').submit();
            $('#modal-historico').modal('hide');
        });

        $('#modal-historico select').select2({
            language: './i18n/es',
            width: 'style'
        });
    }

     var handleClickFilter = function() {
        $('#filter').slideToggle();
    }

    var initFilter = function() {
        $('#btnFilter').on('click', handleClickFilter);
        $('#fromDate, #toDate').on('change', function() {
            datatable.DataTable().draw(true);
        }).datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $('#filter_driver, #filter_state').on('change', function() {
            datatable.DataTable().draw();
        });
    }

    return {
        init: function() {
            initDatatable();
            initTools();
            initModal();
            addValidationMethod();
            initOldPaysHandler();
            initFilter();
        }
    }
}(jQuery));
