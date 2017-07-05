App = typeof App !== 'undefined' ? App : {};
App.Drivers = typeof App.Drivers !== 'undefined' ? App.Drivers : {};

+(App.Drivers.Index = function($) {
    var initDatatable = function() {
        App.Tables.initDatatable($('table#dataTables-drivers'));
        App.Tables.initDeleteButtonHandler($('table#dataTables-drivers'));
    }

    return {
        init: function() {
            initDatatable();
        }
    }
}(jQuery));
