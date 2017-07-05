App = typeof App !== 'undefined' ? App : {};
App.ServiceTypes = typeof App.ServiceTypes !== 'undefined' ? App.ServiceTypes : {};

+(App.ServiceTypes.Index = function($) {
    var initDatatable = function() {
        App.Tables.initDatatable($('table#dataTables-servicetypes'));
        App.Tables.initDeleteButtonHandler($('table#dataTables-servicetypes'));
    }

    return {
        init: function() {
            initDatatable();
        }
    }
}(jQuery));
