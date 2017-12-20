App = typeof App !== 'undefined' ? App : {};
App.Users = typeof App.Users !== 'undefined' ? App.Users : {};

App.Users.Form = function($) {
    var initValidator = function() {
        App.Main.validate($('form#user'), {
            rules: {
                'user[plainPassword][first]': {
                    minlength: 5
                },
                'user[plainPassword][second]': {
                    minlength: 5,
                    equalTo: '#user_plainPassword_first'
                }
            },
            messages: {
                'user[plainPassword][second]': {
                    equalTo: 'Ls contraseñas no coinciden'
                }
            }
        });
    }

    var handlerImageInputClearClick = function(event, p) {
        if ($('#user_imageFile_delete').length > 0) {
            $('#user_imageFile_delete').prop('checked', 'clear' === p);
        }
    }

    var initHanlerImageInputClear = function() {
        $('#user_imageFile_file').on('change', handlerImageInputClearClick);
    }

    return {
        init: function() {
            initValidator();
            initHanlerImageInputClear();
        }
    }
}(jQuery);
