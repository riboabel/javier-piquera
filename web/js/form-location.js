App = typeof App !== 'undefined' ? App : {};

App.Location = function() {
    var initValidator = function() {
        App.Main.validate($('form#location'));
    }

    return {
        init: function() {
            initValidator();
        }
    }
}();
