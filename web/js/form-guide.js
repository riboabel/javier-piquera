App = typeof App !== 'undefined' ? App : {};
App.Guides = typeof App.Guides !== 'undefined' ? App.Guides : {};

+(App.Guides.Form = function($) {
    var initValidator = function() {
        App.Main.validate($('form#guide'));
    }

    var initControls = function() {
        $('select[name="travel_guide[providers][]"]').select2({width: '100%'});
    }

    return {
        init: function() {
            initValidator();
            initControls();
        }
    }
}(jQuery));
