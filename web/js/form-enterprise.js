App = typeof App !== 'undefined' ? App : {};

App.Enterprise = function() {
    var handlerImageInputClearClick = function(event, p) {
        if ($('#enterprise_logoFile_delete').length > 0) {
            $('#enterprise_logoFile_delete').prop('checked', 'clear' === p);
        }
    }

    var initHandlerImageInputClear = function() {
        $('#enterprise_logoFile_file').on('change', handlerImageInputClearClick);
    }

    return {
        init: function() {
            initHandlerImageInputClear();
        }
    }
}();
