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
                        width: '10px'
                    },
                    {
                        name: "serialNumber",
                        title: 'Número'
                    },
                    {
                        name: "startAt",
                        title: 'Fecha'
                    },
                    {
                        name: "provider",
                        title: 'Agencia'
                    },
                    {
                        name: "providerReference",
                        title: 'Referencia'
                    },
                    {
                        name: 'clientNames',
                        title: 'Clientes'
                    },
                    {
                        name: "serviceType",
                        title: 'Tipo de servicio'
                    },
                    {
                        name: "price",
                        title: 'Importe'
                    }
                ],
                columnDefs: [
                    {
                        targets: [0, 1, 4, 5, 7],
                        sortable: false
                    }
                ],
                aaSorting: [[2, 'asc']],
                searching: false,
                serverSide: true,
                processing: false,
                ajax: {
                    data: function(data) {
                        var filter = [];
                        $.each($('#frmFilter').serializeArray(), function(i, e) {
                            filter[e['name']] = e['value'];
                        });

                        return $.extend(true, data, filter);
                    },
                    method: 'GET',
                    url: $table.data('ajax-url')
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
            toastr.error('Debe seleccionar registros para esta operación', 'Error', {
                timeOut: 5000,
                progressBar: true
            });
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

        $('#dataTables-cobros input:checkbox')
            .prop('checked', true)
            .iCheck('update');
    }

    var clickSelectNoneHandler = function(event) {
        event.preventDefault();

        $('#dataTables-cobros input:checkbox')
            .prop('checked', false)
            .iCheck('update');
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
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy'
        });
        $('#frmFilter select').on('change', function() {
            datatable.DataTable().draw();
        });
        $('#frmFilter input:text').on('keyup', function() {
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
