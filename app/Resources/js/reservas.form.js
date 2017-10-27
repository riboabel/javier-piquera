App = typeof App !== 'undefined' ? App : {};
App.Reservas = typeof App.Reservas !== 'undefined' ? App.Reservas : {};

+(App.Reservas.Form = function($) {
    var validator;

    var addValidatorMethods = function() {
        if ($.validator) {
            $.validator.addMethod('validdatetime', function(value, element) {
                return this.optional(element) || /^([0-2]\d|3[0-1])\/(0\d|1[0-2])\/\d{4}\s([0-1]\d|2[0-3]):[0-5]\d$/.test(value);
            }, 'Valor no válido. Formato válido: DD/MM/YYYY HH:MM');
            $.validator.addMethod('endafterstart', function(value, element) {
                var startAt = $('input[name="reserva[startAt]"]').val();
                if (!/^([0-2]\d|3[0-1])\/(0\d|1[0-2])\/\d{4}\s([0-1]\d|2[0-3]):[0-5]\d$/.test(value) || !/^([0-2]\d|3[0-1])\/(0\d|1[0-2])\/\d{4}\s([0-1]\d|2[0-3]):[0-5]\d$/.test(startAt)) {
                    return true;
                }

                var parseDate = function(value) {
                    var parts = value.split('/');
                    return {
                        date: parts[2] + '-' + parts[1] + '-' + parts[0],
                        number: parts[2] + parts[1] + parts[0],
                        year: parts[2],
                        month: parts[1],
                        day: parts[0]
                    }
                }

                var parseTime = function(value) {
                    var parts = value.split(':');
                    return {
                        time: parts[0] + ':' + parts[1],
                        number: parts[0] + parts[1],
                        hour: parts[0],
                        minute: parts[1]
                    }
                }

                var parseDateTime = function(value) {
                    var parts = value.split(' ');
                    var parsedDate = parseDate(parts[0]);
                    var parsedTime = parseTime(parts[1]);
                    return {
                        datetime: parsedDate.date + ' ' + parsedTime.time,
                        number: parsedDate.number + parsedTime.number
                    }
                }

                return parseDateTime(startAt).number < parseDateTime(value).number;
            }, 'Terminación no puede ser antes de o igual a inicio');
        } else {
            console.log('Validator plugin not present');
        }
    }

    var initValidator = function() {
        validator = App.Main.validate($('form#reserva'), {
            rules: {
                'reserva[startAt]': 'validdatetime',
                'reserva[endAt]': 'validdatetime endafterstart',
                'reserva[pax]': {
                    min: 1
                }
            }
        });

        $('#reserva_startPlace, #reserva_endPlace').on('change', function() {
            $(this).valid();
        });
    }

    var clickAddItemToCollectionHandler = function(event) {
        event.preventDefault();

        var $container = $('#places-container');
        var prototype = $container.data('prototype');
        var index = $container.data('index');

        var $newItem = $(prototype.replace(/__name__/g, index));

        $container.data('index', index + 1);
        $container.append($newItem);

        $newItem.find('select').select2(placesSelect2Options());
        $newItem.find('.select2-container').css('width', '');
        $newItem.find('.select2-selection').css('height', '30px');

        $newItem.find('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true
        });

        if ($container.find('.item').length > 1) {
            console.log($newItem.prev().find('.datepicker input').val());
            $newItem.find('.datepicker').datepicker('setDate', $newItem.prev().find('.datepicker input').val());
        }

        $newItem.find('button.btn-delete').on('click', clickDeleteItemHandler);
    }

    var clickDeleteItemHandler = function(event) {
        event.preventDefault();

        $(this).parents('.item:first').removeClass('item').fadeOut(function() {
            $(this).remove();
        });
    }

    var initializeCollection = function() {
        $('#places-container').data('index', $('#places-container .item').length);

        $('#linkAddPassingPlace').on('click', clickAddItemToCollectionHandler);

        $('#places-container .datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true
        });

        $('#places-container').find('button.btn-delete').on('click', clickDeleteItemHandler);

        $('#places-container').find('select').select2(placesSelect2Options());
        $('#places-container').find('.select2-selection').css('height', '30px');
    }

    var placesSelect2Options = function() {
        return {
            ajax: {
                url: $('#reserva_startPlace').data('ajax'),
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
        }
    }

    var initializePlacesSelectors = function() {
        if ($.fn.select2) {
            $('#reserva_startPlace, #reserva_endPlace').select2(placesSelect2Options());

            $('.select2-container').css('width', '');
        } else {
            console.log('slect2 plugin not present');
        }
    }

    var initControls = function() {
        $('#reserva_isDriverConfirmed').iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });
    }

    return {
        init: function() {
            initValidator();
            addValidatorMethods();
            initializeCollection();
            initializePlacesSelectors();
            initControls();
        }
    }
}(jQuery));
