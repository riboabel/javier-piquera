App = typeof App !== 'undefined' ? App : {};
App.ServiceTypes = typeof App.ServiceTypes !== 'undefined' ? App.ServiceTypes : {};

+(App.ServiceTypes.Form = function($) {
    var addValidatorMethod = function() {
        if ($.validator) {
            $.validator.addMethod('validdecimal', function(value, element) {
                return this.optional(element) || /^((.|,)\d{1,2}|\d+(|.(|\d{1,2})|,(\d{1,2})))$/.test(value);
            }, 'Valor no válido');
        } else {
            console.log('Validator plugin not present');
        }
    }

    var initValidator = function() {
        App.Main.validate($('form#servicetype'));
    }

    return {
        init: function() {
            addValidatorMethod();
            initValidator();
        }
    }
}(jQuery));
