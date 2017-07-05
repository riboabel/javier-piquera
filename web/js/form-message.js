App = typeof App !== 'undefined' ? App : {};

App.Message = function() {
    var initValidator = function() {
        App.Main.validate($('form#message'), {
            messages: {
                'message[content]': 'Ingrese un texto de más de 3 caracteres'
            },
            rules: {
                'message[content]': {
                    minlength: 4
                }
            }
        });
    }

    return {
        init: function() {
            initValidator();
        }
    }
}();
