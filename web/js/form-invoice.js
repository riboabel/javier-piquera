App = typeof App !== 'undefined' ? App : {};

+(App.FormInvoice = function() {
    var initProviderSelect = function() {
        $('#provider').on('change', handleChanegSelectProvider).select2({
            language: 'es'
        });
        $('#services').on('change', handleChangeServices);
    }

    var handleChangeServices = function() {
        var value = $(this).val(), url = $('#frmZone').data('url');

        if (value == '') {
            $('#frmZone').empty();

            return;
        }

        $('#frmZone').block({message: '<div style="margin-top: 20px; margin-bottom: 20px;">Cargando servicio...</div>'});
        $('#frmZone').load(url, {
            id: value
        }, function() {
            $('#frmZone').unblock();
            $('#frmZone form').ajaxForm({
                target: $('#frmZone')
            });
            $('#frmZone').find('input:text.calculation-dispatcher').change('change', handleChangeCalcDispatcherControls);
        });
    }

    var handleChangeCalcDispatcherControls = function() {
        var isDecimal = function(value) {
            return /^\d*(|(\.|,)(|\d+))$/.test(value);
        }
        var fixDecimal = function(value) {
            return value.replace(/,/, '.');
        }
        var formatDecimal = function(value) {
            if (/\.\d{3,}/.test(value)) {
                value = value.toString().replace(/^(\d+\.\d{2}).+$/, '$1');
            }

            return value * 1;
        }

        var calcKilometersPrice = function() {
            var
                kms = $('#invoice_invoicedKilometers').val(),
                kmPrice = $('#invoice_invoicedKilometerPrice').val(),
                ctrlTotal = $('#invoice_invoicedKilometersPrice');

            if (isDecimal(kms) && isDecimal(kmPrice)) {
                ctrlTotal.val(formatDecimal(fixDecimal(kms) * fixDecimal(kmPrice)));
            }
        }

        var calcHoursPrice = function() {
            var
                hs = $('#invoice_invoicedHours').val(),
                hPrice = $('#invoice_invoicedHourPrice').val(),
                ctrlTotal = $('#invoice_invoicedHoursPrice');

            if (isDecimal(hs) && isDecimal(hPrice)) {
                ctrlTotal.val(formatDecimal(fixDecimal(hs) * fixDecimal(hPrice)));
            }
        }

        var calcTotalPrice = function() {
            var
                ctrlKms = $('#invoice_invoicedKilometersPrice'),
                ctrlHs = $('#invoice_invoicedHoursPrice'),
                ctrlTotal = $('#invoice_invoicedTotalPrice');

            if (isDecimal(ctrlKms.val()) && isDecimal(ctrlHs.val())) {
                ctrlTotal.val((formatDecimal(fixDecimal(ctrlKms.val()) * 1) + (fixDecimal(ctrlHs.val())) * 1));
            }
        }

        calcKilometersPrice();
        calcHoursPrice();
        calcTotalPrice();
    }

    var handleChanegSelectProvider = function() {
        var value = $(this).val();

        $('#services').empty();
        $('#frmZone').empty();

        if (value == '') {
            $('#services').append($('<option/>').text('Seleccione agencia'));

            return;
        }

        $('#services').append($('<option/>').text('Cargando servicios...'));

        $.ajax($(this).data('url'), {
            dataType: 'json',
            method: 'post',
            data: {
                provider: value
            },
            success: function(json) {
                if (json.data.length > 0) {
                    $('#services').empty().append($('<option value="">Seleccione servicio</option>'));
                    $(json.data).each(function() {
                        $('#services').append($('<option/>').attr('value', this.value).text(this.text));
                    });
                } else {
                    $('#services').empty().append($('<option value="">No hay servicios por facturar</option>'));
                }

            },
            error: function() {
                alert('Error al cargar servicios');
            }
        });
    }

    return {
        init: function() {
            initProviderSelect();
        }
    }
}());

App.Invoices = typeof App.Invoices !== 'undefined' ? App.Invoices : {};

+(App.Invoices.Form = function($) {
    var initValidator = function() {
        $.validator.addMethod('norepeatedservices', function(value, element, param) {
            var ids = [], error = false;
            $('#invoice_form_lines select[name$="[service]"]').each(function() {
                var id = $(this).val();
                if (id) {
                    if (ids.indexOf(id) !== -1) {
                        error = true;
                        return false;
                    }
                    ids.push(id);
                }
            });
            return !error;
        }, 'Hay servicios repetidos en la factura');

        App.Main.validate($('form#invoice'), {
            ignore: ':hidden:not([name="services"])',
            rules: {
                'services': {
                    norepeatedservices: true,
                    min: {
                        depends: function() {
                            return $('#invoice_form_modelName').val() === 'GENERAL';
                        },
                        param: 1
                    }
                }
            },
            messages: {
                services: {
                    min: 'Agregue servicios a la factura'
                }
            }
        });
    }

    var serviceOnChange = function() {
        var data = $(this).select2('data');

        if ($('#invoice_form_modelName').val() === 'ATRIO') {
            $('input:hidden[name$="[1][service]"]').val($(this).val());
            $('input:hidden[name$="[1][serviceSerialNumber]"]').val(data[0].serialNumber);
        }


        $(this).closest('.item').find('input[name$="[serviceName]"]').val(data[0].serviceName);
        $(this).closest('.item').find('input[name$="[clientsName]"]').val(data[0].clientNames);
        $(this).closest('.item').find('input[name$="[clientReference]"]').val(data[0].reference);
        $(this).closest('.item').find('input[name$="[serviceSerialNumber]"]').val(data[0].serialNumber);
        $(this).closest('.item').find('input[name$="[totalPrice]"]').val(data[0].price);
    }

    var serviceSelect2Options = {
        ajax: {
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page
                }
            }
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        templateResult: function(repo) {
            if (repo.loading) return repo.text;

            var markup = '<div class="select2-result-repository clearfix">' +
                '<div class="select2-result-repository__title">' + repo.text + '</div>';

            markup += '<div class="select2-result-repository__description">';
            var elements = [];
            if (repo.clientNames) {
                elements.push("<strong>Clientes:</strong> " + repo.clientNames);
            }
            if (repo.reference) {
                elements.push("<strong>Referencia:</strong> " + repo.reference);
            }
            if (repo.price) {
                elements.push('<strong>Importe:</strong> ' + repo.price);
            }

            markup += elements.join(' | ', elements) + '</div></div>';

            return markup;
        },
        language: 'es',
        minimunInputLength: 1,
        width: '100%'
    }

    var initControls = function() {
        $('#invoice_form_provider').on('change', function() {
            $('#invoice_form_lines').find('.item').remove();
        }).select2({width: '100%'});
        $('#invoice_form_driver').select2({width: '100%'});
        $('#invoice_form_modelName').on('change', function() {
            if ($(this).val() === 'ATRIO') {
                $('#fakedServices').removeClass('hidden').find('.panel-body').append($('<div class="row item"><div class="col-sm-4 form-group"><label for="invoice_form_lines_0_service" class="required">Servicio</label><select id="invoice_form_lines_0_service" name="invoice_form[lines][0][service]" required="required"></select></div><div class="form-group col-sm-2"><label for="invoice_form_lines_0_meassurementUnit">Unidad</label><input type="text" id="invoice_form_lines_0_meassurementUnit" name="invoice_form[lines][0][meassurementUnit]" maxlength="49" value="Km" class="form-control"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_0_quantity">Cantidad</label><input type="number" id="invoice_form_lines_0_quantity" name="invoice_form[lines][0][quantity]" class="form-control text-right" required="required"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_0_unitPrice">Precio</label><input type="text" id="invoice_form_lines_0_unitPrice" name="invoice_form[lines][0][unitPrice]" class="form-control text-right" required="required"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_0_totalPrice" class="required">Importe</label><input type="text" id="invoice_form_lines_0_totalPrice" name="invoice_form[lines][0][totalPrice]" required="required" class="form-control text-right"></div><input type="hidden" id="invoice_form_lines_0_serviceName" name="invoice_form[lines][0][serviceName]"><input type="hidden" id="invoice_form_lines_0_clientsName" name="invoice_form[lines][0][clientsName]"><input type="hidden" id="invoice_form_lines_0_clientReference" name="invoice_form[lines][0][clientReference]"><input type="hidden" id="invoice_form_lines_0_serviceSerialNumber" name="invoice_form[lines][0][serviceSerialNumber]"><input type="hidden" id="invoice_form_lines_0_serviceSerialNumber" name="invoice_form[lines][0][notes]"></div>' + '<div class="row item"><div class="col-sm-4 form-group"><label for="invoice_form_lines_1_serviceName" class="required">Servicio</label><input id="invoice_form_lines_1_serviceName" name="invoice_form[lines][1][serviceName]" required="required" value="Horas de espera" class="form-control" readonly="readonly"></div><div class="form-group col-sm-2"><label for="invoice_form_lines_1_meassurementUnit">Unidad</label><input type="text" id="invoice_form_lines_1_meassurementUnit" name="invoice_form[lines][1][meassurementUnit]" maxlength="49" value="hora" class="form-control"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_1_quantity">Cantidad</label><input type="number" id="invoice_form_lines_1_quantity" name="invoice_form[lines][1][quantity]" class="form-control text-right" required="required"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_1_unitPrice">Precio</label><input type="text" id="invoice_form_lines_1_unitPrice" name="invoice_form[lines][1][unitPrice]" class="form-control text-right" required="required"></div><div class="col-sm-2 form-group"><label for="invoice_form_lines_1_totalPrice" class="required">Importe</label><input type="text" id="invoice_form_lines_1_totalPrice" name="invoice_form[lines][1][totalPrice]" required="required" class="form-control text-right"></div><input type="hidden" id="invoice_form_lines_1_service" name="invoice_form[lines][1][service]"><input type="hidden" id="invoice_form_lines_1_clientsName" name="invoice_form[lines][1][clientsName]"><input type="hidden" id="invoice_form_lines_1_clientReference" name="invoice_form[lines][1][clientReference]"><input type="hidden" id="invoice_form_lines_1_serviceSerialNumber" name="invoice_form[lines][1][serviceSerialNumber]"><input type="hidden" id="invoice_form_lines_1_serviceSerialNumber" name="invoice_form[lines][1][notes]"></div>'));
                $('#fakedServices').find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
                    ajax: {
                        url: Routing.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
                    }
                }));

                $('#invoice_form_lines').closest('.panel').addClass('hidden').find('.item').remove();
            } else {
                $('#fakedServices').addClass('hidden').find('.item').remove();
                $('#invoice_form_lines').closest('.panel').removeClass('hidden');
            }
        }).trigger('change');
    }

    var initCollection = function() {
        var $container = $('#invoice_form_lines');
        $container.data('index', $container.find('.item').length);

        $container.on('click', '.btn-delete-item', function() {
            $(this).closest('.item').fadeOut(function() {
                $(this).remove();
                $('input:hidden[name="services"]').val($container.find('.item').length);
            });
        });

        $('button.btn-add-item').on('click', function() {
            var index = $container.data('index'),
                prototype = $container.data('prototype').replace(/__name__/g, index),
                $item = $(prototype);

            $container.data('index', index + 1);
            $item.appendTo($container);
            $('input:hidden[name="services"]').val($container.find('.item').length);

            $item.find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
                ajax: {
                    url: Routing.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
                }
            }));
        });

        $container.find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
            ajax: {
                url: Routing.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
            }
        }));
    }

    return {
        init: function() {
            initValidator();
            initControls();
            initCollection();
        }
    }
}(jQuery));
