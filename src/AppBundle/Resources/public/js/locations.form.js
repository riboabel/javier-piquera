App = typeof App !== 'undefined' ? App : {};
App.Locations = typeof App.Locations !== 'undefined' ? App.Locations : {};

+(App.Locations.Form = function($) {
    var initValidator = function() {
        App.Main.validate($('form#location'));
    }

    return {
        init: function() {
            initValidator();
        }
    }
}(jQuery));
