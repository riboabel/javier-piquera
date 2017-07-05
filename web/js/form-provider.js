App = typeof App !== 'undefined' ? App : {};
App.Providers = typeof App.Providers !== 'undefined' ? App.Providers : {};

+(App.Providers.Form = function($) {
    var initValidator = function() {
        App.Main.validate($('form#provider'));
    }

    var initControls = function() {
        $('form#provider input:checkbox').iCheck({
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
