App = typeof App !== 'undefined' ? App : {};

App.Profile = function() {
    var initValidator = function() {
        App.Main.validate($('form#profile'), {
            rules: {
                'fos_user_profile_form[plainPassword][second]': {
                    equalTo: '#fos_user_profile_form_plainPassword_first'
                }
            }
        });
    }

    var handlerImageInputClearClick = function(event, p) {
        if ($('#fos_user_profile_form_imageFile_delete').length > 0) {
            $('#fos_user_profile_form_imageFile_delete').prop('checked', 'clear' === p);
        }
    }

    var initHanlerImageInputClear = function() {
        $('#fos_user_profile_form_imageFile_file').on('change', handlerImageInputClearClick);
    }

    return {
        init: function() {
            initValidator();
            initHanlerImageInputClear();
        }
    }
}();
