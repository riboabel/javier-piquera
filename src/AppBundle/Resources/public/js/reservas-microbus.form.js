App = typeof App !== 'undefined' ? App : {};
App.ReservasMicrobus = typeof App.ReservasMicrobus !== 'undefined' ? App.ReservasMicrobus :{};

+(App.ReservasMicrobus.Form = function($) {
    var initValidator = function() {
        $.validator.addMethod('validdatetime', function(value, element) {
            return this.optional(element) || /^([0-2]\d|3[0-1])\/(0\d|1[0-2])\/\d{4}\s([0-1]\d|2[0-3]):[0-5]\d$/.test(value);
        }, 'Valor no válido. Formato válido: DD/MM/YYYY HH:MM');

        $.validator.addMethod('endafterstart', function(value, element, params) {
            var format = 'DD/MM/YYYY HH:mm';
            var startAt = moment($(params.startElement).val(), format),
                now = moment(value, format);

            if (!now.isValid() || !startAt.isValid()) {
                return true;
            }

            return startAt.isBefore(now);
        }, 'Terminación no puede estar antes de o ser igual a inicio');

        App.Main.validate($('form#reserva-microbus'), {
            'rules': {
                'reserva_microbus_form[startAt]': 'validdatetime',
                'reserva_microbus_form[endAt]': {
                    'validdatetime': true,
                    'endafterstart': {
                        'startElement': '#reserva_microbus_form_startAt'
                    }
                }
            }
        });
    }

    var initControls = function() {
        $('#reserva_microbus_form_startIn, #reserva_microbus_form_endIn').select2({
            ajax: {
                url: Routing.generate('app_reservasmicrobus_getplaces'),
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page
                    }
                }
            },
            language: 'es',
            minimunInputLength: 1,
            placeholder: 'Seleccione un lugar',
            width: 'resolve'
        });
    }

    return {
        init: function() {
            initControls();
            initValidator();
        }
    }
}(jQuery));
