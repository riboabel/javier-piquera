App = typeof App !== 'undefined' ? App : {};

App.FormInvoice = function() {
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
}();
