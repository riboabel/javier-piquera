App = typeof App !== 'undefined' ? App : {};
App.ReservasGuia = typeof App.ReservasGuia !== 'undefined' ? App.ReservasGuia :{};

+(App.ReservasGuia.Form = function($) {
    'use strict';

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

        App.Main.validate($('form#reserva-tercero'), {
            'rules': {
                'reserva_tercero_form[startAt]': 'validdatetime',
                'reserva_tercero_form[endAt]': {
                    'validdatetime': true,
                    'endafterstart': {
                        'startElement': '#reserva_tercero_form_startAt'
                    }
                }
            }
        });
    };

    var initControls = function() {
        $('#reserva_tercero_form_startIn, #reserva_tercero_form_endIn').select2({
            ajax: {
                url: Routing.generate('app_reservasclasicos_getplaces'),
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

        $('select[name="reserva_tercero_form[provider]"]').on('change', function() {
            var option = this.options[this.selectedIndex];

            if ($(option).data('show-provider-serial')) {
                $('input:text[name="reserva_tercero_form[providerSerial]"]').closest('.form-group').fadeIn();
            } else {
                $('input:text[name="reserva_tercero_form[providerSerial]"]').closest('.form-group').fadeOut(function() {
                    $(this).find('input:text').val('');
                });
            }

        }).trigger('change');
    };

    return {
        init: function() {
            initControls();
            initValidator();
        }
    };
}(jQuery));
