App = typeof App !== 'undefined' ? App: {};
App.Prices = typeof App.Prices !== 'undefined' ? App.Prices : {};

+(App.Prices.Index = function($) {
    "use strict";

    var $table = $('table#table-x');

    var changeSelect = function() {
        $table.DataTable().draw(false);

        var id = $(this).val(),
            name = this.options[this.selectedIndex].text;
        $('#link-print').attr('href', id !== '' ? Routing.generate('app_prices_print', {id: id}) : Routing.generate('app_prices_print'));
        $('#link-print').empty().append('<span class="fa fa-print"/> Imprimir precios' + ('' !== id ? ' para ' + name : ''));
    }

    var initPrintControls = function() {
        $('#link-print').on('click', function(event) {
            event.preventDefault();

            $('#modalPrint').remove();
            var $modal = $('<div class="modal fade" id="modalPrint"><div class="modal-dialog"><div class="modal-content"></div></div></div>').appendTo($('body'));
            $modal.modal();
            $modal.find('.modal-content').load($(this).attr('href'), function() {
                $modal.find('select').select2({'width': '100%'});
            });
        });
    }

    var initFilter = function() {
        $('form#filter select').on('change', changeSelect).select2({
            "width": '100%'
        });
    }

    var initTable = function() {
        $('#table-x').on('preDraw.dt', function() {
            $(this).block({
                message: 'Procesando...'
            });
        }).on('draw.dt', function() {
            $(this).unblock();
        });

        $('#table-x').dataTable({
            "aLengthMenu": [100, 200],
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
            'serverSide': true,
            'ajax': {
                'url': Routing.generate('app_prices_getdata'),
                'method': 'POST',
                'data': function(data) {
                    return $.extend(true, data, {
                        'filter': {
                            'provider': $('form#filter select').val()
                        }
                    });
                }
            },
            'aoColumns': [
                {
                    'name': 'name',
                    'title': 'Servicio'
                }, {
                    'name': 'cobrar',
                    'title': 'Cobrar',
                    'width': '80px'
                }, {
                    'name': 'pagar',
                    'title': 'Pagar',
                    'width': '80px'
                }
            ]
        });
    }

    var initPrices = function() {
        $table.on('click', 'button.btn-clear-price', function() {
            var val = $('form#filter select').val() !== '' ? '' : '0.00';
            $(this).closest('.input-group').find('input:text').val(val).trigger('change');
        });

        $table.on('change', 'input:text', function() {
            if ($(this).data('xhr-saving')) {
                $(this).data('xhr-saving').abort();
                $(this).removeData('xhr-saving');
            }

            var now = $.now(),
                $input = $(this),
                xhr = $.ajax(Routing.generate('app_prices_save'), {
                    beforeSend: function() {
                        $input.css('border-color', '#cb0316');
                    },
                    dataType: 'json',
                    data: {
                        id: $(this).data('id'),
                        value: $(this).val(),
                        now: now
                    },
                    method: 'POST',
                    success: function(json) {
                        $input.val(json.value);
                        $input.removeData('xhr-saving').css('border-color', '');
                    },
                    error: function() {
                        alert('Error intentando guardar precio');
                    }
                });

            $(this).data('xhr-saving', xhr);
        });
    }

    return {
        init: function() {
            initFilter();
            initPrintControls();
            initTable();
            initPrices();
        }
    }
}(jQuery));
