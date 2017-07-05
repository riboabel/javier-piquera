App = typeof App !== 'undefined' ? App : {};

App.Place = function() {
    var initValidator = function() {
        App.Main.validate($('form#place'));
    }

    return {
        init: function() {
            initValidator();
        }
    }
}();
