App = typeof App !== 'undefined' ? App : {};

App.Tables = function($){
    var initDatatable = function(element, options) {
        return $(element).each(function() {
            var $table = $(this);

            var defaultOptions = {
                aoColumns: [],
                responsive: true,
                bServerSide: false
            };

            if ($table.data('serverside')) {
                defaultOptions.bProcessing = true;
                defaultOptions.bServerSide = true;
                defaultOptions.ajax = {
                    method: $table.data('ajax-method') ? $table.data('ajax-method') : 'post',
                    url: $table.data('ajax-url')
                }
            }

            defaultOptions.aaSorting = [[$table.data('sort-col') ? $table.data('sort-col') : 0, $table.data('sort-order') ? $table.data('sort-order') : 'asc']];

            $table.find('thead th').each(function(i) {
                var $th = $(this), colOptions = {};
                if ($th.hasClass('no-sort')) {
                    colOptions.bSortable = false;
                }
                if ($th.hasClass('no-search')) {
                    colOptions.bSearchable = false;
                }
                if ($th.data('name')) {
                    colOptions.name = $th.data('name');
                }
                defaultOptions.aoColumns[i] = colOptions;
            });

            $table.dataTable($.extend({}, defaultOptions, options));
        });
    }

    var initDeleteButtonHandler = function(table) {
        var $table = $(table);

        if ($table.find('.btn-delete').length && $('.modal[data-for="' + $table.attr('id') + '"]').length) {
            $table.find('.btn-delete').on('click', function(event) {
                event.preventDefault();
                $('.modal[data-for="' + $table.attr('id') + '"] .btn-danger').attr('href', $(this).attr('href'));
                $('.modal[data-for="' + $table.attr('id') + '"]').modal();
            });
        }
    }

    return {
        initDatatable: initDatatable,
        initDeleteButtonHandler: initDeleteButtonHandler
    }
}(jQuery);
