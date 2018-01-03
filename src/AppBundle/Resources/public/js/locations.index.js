App = typeof App !== 'undefined' ? App : {};
App.Locations = typeof App.Locations !== 'undefined' ? App.Locations : {};

+(App.Locations.Index = function($) {
    var datatable;

    var clickDeleteHandler = function(event) {
        event.preventDefault();

        $('.modal[data-for=dataTables-locations] button.btn-danger').data('url', $(event.currentTarget).attr('href'));
        $('.modal[data-for=dataTables-locations]').modal();
    }

    var clickDeleteModalHandler = function(event) {
        event.preventDefault();

        var url = $(this).data('url');

        $('#dataTables-locations').find('a[href="' + url + '"]').parents('td:first').empty().text('Eliminando...');
        $(this).parents('.modal:first').modal('hide');
        $.ajax(url, {
            method: 'post',
            type: 'json',
            success: function(json) {
                location.href = location.href;
            },
            error: function() {
                alert('Error ejecutando operación.');
                location.href = location.href;
            }
        });
    }

    var initDeleteModal = function() {
        $('.modal[data-for=dataTables-locations]').find('button.btn-danger').on('click', clickDeleteModalHandler);
    }

    var drawDatatableHandler = function() {
        $(this).find('.btn-delete').on('click', clickDeleteHandler);
        $(this).find('[title]').tooltip({'trigger': 'hover'});
        $(this).unblock();
    }

    var handleDatatablePredraw = function() {
        $(this).block({
            message: 'Procesando...'
        });
    }

    var initDatatable = function() {
        $('#dataTables-locations')
            .on('draw.dt', drawDatatableHandler)
            .on('preDraw.dt', handleDatatablePredraw);
        datatable = App.Tables.initDatatable($('#dataTables-locations'), {
            bProcessing: false
        });

    }

    return {
        init: function() {
            initDatatable();
            initDeleteModal();
        }
    }
}(jQuery));
