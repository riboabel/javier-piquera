App = typeof App !== 'undefined' ? App : {};
App.ServiceTypes = typeof App.ServiceTypes !== 'undefined' ? App.ServiceTypes : {};

+(App.ServiceTypes.Form = function($) {
    var initValidator = function() {
        $.validator.addMethod('validdecimal', function(value, element) {
            return this.optional(element) || /^((.|,)\d{1,2}|\d+(|.(|\d{1,2})|,(\d{1,2})))$/.test(value);
        }, 'Valor no válido');

        App.Main.validate($('form#servicetype'));
    }

    var initControls = function() {
        $('input:checkbox[name="service_type_form[isMultiple]"]').iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });
    }

    return {
        init: function() {
            initValidator();
            initControls();
        }
    }
}(jQuery));
