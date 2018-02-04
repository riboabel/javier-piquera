App = typeof App !== 'undefined' ? App : {};
App.ThirdProviders = typeof App.ThirdProviders !== 'undefined' ? App.ThirdProviders : {};

+(App.ThirdProviders.Index = function($) {
    var initValidator = function() {
        App.Main.validate($('form#provider'), {
            rules: {
                'third_provider_form[serialPrefix]': {
                    required: {
                        depends: function() {
                            return $('input:checkbox[name="third_provider_form[isSerialGenerator]"]').prop('checked');
                        }
                    }
                }
            },
            messages: {
                'third_provider_form[serialPrefix]': {
                    required: 'Este campo es obligatorio si desea que el sistema genere los números de confirmación'
                }
            }
        });
    }

    return {
        init: function() {
            initValidator();
        }
    }
}(jQuery));
