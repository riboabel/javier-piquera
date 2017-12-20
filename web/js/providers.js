App = typeof App !== 'undefined' ? App : {};
App.Providers = typeof App.Providers !== 'undefined' ? App.Providers : {};

+(App.Providers.Index = function($) {
    var initDatatable = function() {
        App.Tables.initDatatable($('table#dataTables-providers'));
        App.Tables.initDeleteButtonHandler($('table#dataTables-providers'));
    }

    return {
        init: function() {
            initDatatable();
        }
    }
}(jQuery));
