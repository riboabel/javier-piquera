App.Profile = function() {
    var form = function() {
        var initValidator = function() {
            App.validate($('form#profile'), {
                rules: {
                    'fos_user_profile_form[plainPassword][first]': {
                        required: true
                    },
                    'fos_user_profile_form[plainPassword][second]': {
                        equalTo: '#fos_user_profile_form_plainPassword_first'
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

    return {
        init: function() {
            form.init();
        }
    }
}();