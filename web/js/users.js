App = typeof App !== 'undefined' ? App : {};
App.Users = typeof App.Users !== 'undefined' ? App.Users : {};

App.Users.Index = function($) {
    var initDatatable = function() {
        App.Tables.initDatatable($('table#dataTables-users'));
        App.Tables.initDeleteButtonHandler($('table#dataTables-users'));
    }

    return {
        init: function() {
            initDatatable();
        }
    }
}(jQuery);
