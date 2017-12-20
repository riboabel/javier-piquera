App = typeof App !== 'undefined' ? App : {};
App.Cobros = typeof App.Cobros !== 'undefined' ? App.Cobros : {};

+(App.Cobros.Index = function($) {

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
        var $table = $('#dataTables-cobros'),
            options = {
                "aoColumns": [
                    {
                        "bSortable": false,
                        "bSearchable": false
                    }, {
                        "name": "serialNumber",
                        "sortable": false,
                        "searchable": false
                    }, {
                        "name": "startAt"
                    }, {
                        "name": "provider"
                    }, {
                        "name": "providerReference"
                    }, {
                        name: 'clientNames',
                        sortable: false
                    }, {
                        'name': "serviceType"
                    }, {
                        "name": "price",
                        "sortable": false,
                        "searchable": false
                    }
                ],
                aaSorting: [[2, "asc"]],
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "No hay cobros pendientes",
                    "sInfo":           "Mostrando del _START_ al _END_ (de _TOTAL_)",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    '',
                    "sSearch":         'Buscar:',
                    "sUrl":            '',
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
                "bServerSide": true,
                "bProcessing": false,
                "ajax": {
                    data: function(data) {
                        var filter = [];
                        $.each($('#frmFilter').serializeArray(), function(i, e) {
                            filter[e['name']] = e['value'];
                        });

                        return $.extend(true, data, filter);
                    },
                    'method': 'post',
                    'url': $table.data('ajax-url')
                }
            }

        $table
            .on('draw.dt', drawDatatableHandler)
            .on('preDraw.dt', function() {
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

    var loadCobrosModalHandler = function() {
        App.Main.validate($('.modal[data-for=cobrar-op] form'));
    }

    var clickLinkCobrarHandler = function(event) {
        event.preventDefault();

        if ($('#dataTables-cobros').find('input:checkbox:checked').length == 0) {
            $('.modal[data-for=cobrar-error]').modal();
            return;
        }

        var ids = [];
        $('#dataTables-cobros').find('input:checkbox:checked').each(function() {
            ids.push(this.value);
        });

        $('.modal[data-for=cobrar-op] .modal-body').empty().text('Cargando información de servicios seleccionados...');
        $('.modal[data-for=cobrar-op]').modal();
        $('.modal[data-for=cobrar-op] div.modal-body').load($(this).attr('href'), {
            ids: ids
        }, loadCobrosModalHandler);
    }

    var clickPostCobros = function(event) {
        event.preventDefault();

        $(this).parents('.modal-content:first').find('form').submit();
        $(this).off('click').text('Guardando información...');
    }

    var handlePrintButtonClick = function(event) {
        event.preventDefault();

        var url = $(this).data('url');

        var $f = $('.modal[data-for=cobrar-op] form').clone();
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
        $('.modal[data-for=cobrar-op] .btn-success').on('click', clickPostCobros);
        $('.modal[data-for=cobrar-op] .btn-print').on('click', handlePrintButtonClick);
    }

    var clickSelectAllHandler = function(event) {
        event.preventDefault();

        $('#dataTables-cobros input:checkbox').prop('checked', true);
        $('#dataTables-cobros input:checkbox').iCheck('update');
    }

    var clickSelectNoneHandler = function(event) {
        event.preventDefault();

        $('#dataTables-cobros input:checkbox').prop('checked', false);
        $('#dataTables-cobros input:checkbox').iCheck('update');
    }

    var initTools = function() {
        $('#linkCobrar').on('click', clickLinkCobrarHandler);
        $('#linkSelectAll').on('click', clickSelectAllHandler);
        $('#linkSelectNone').on('click', clickSelectNoneHandler);
    }

    var initOldCobrosHandler = function() {
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

    var initFilter = function() {
        $('#frmFilter input.datepicker').on('change', function() {
            datatable.DataTable().draw();
        }).datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true
        });
        $('#frmFilter select').on('change', function() {
            datatable.DataTable().draw();
        });
    }

    return {
        init: function() {
            initDatatable();
            initTools();
            initModal();
            addValidationMethod();
            initOldCobrosHandler();
            initFilter();
        }
    }
}(jQuery));
