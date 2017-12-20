App = typeof App !== 'undefined' ? App : {};
App.Places = typeof App.Places !== 'undefined' ? App.Places : {};

+(App.Places.Index = function($) {
    var datatable;

    var clickDeleteHandler = function(event) {
        event.preventDefault();

        $('.modal[data-for=dataTables-places] button.btn-danger').data('url', $(event.currentTarget).attr('href'));
        $('.modal[data-for=dataTables-places]').modal();
    }

    var clickDeleteModalHandler = function(event) {
        event.preventDefault();

        var url = $(this).data('url');

        $('#dataTables-places').find('a[href="' + url + '"]').parents('td:first').empty().text('Eliminando...');
        $(this).parents('.modal:first').modal('hide');
        $.ajax(url, {
            method: 'post',
            type: 'json',
            success: function(json) {
                datatable.DataTable().draw(false);
            },
            error: function() {
                alert('Error ejecutando operación.');
                datatable.DataTable().draw(false);
            }
        });
    }

    var initDeleteModal = function() {
        $('.modal[data-for=dataTables-places]').find('button.btn-danger').on('click', clickDeleteModalHandler);
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
        $('#dataTables-places')
            .on('draw.dt', drawDatatableHandler)
            .on('preDraw.dt', handleDatatablePredraw);
        datatable = App.Tables.initDatatable($('#dataTables-places'), {
            bProcessing: false
        });
    }

    var handleNoticeModal = function() {
        if ($('.modal[data-for=flash]').length) {
            $('.modal[data-for=flash]').modal();
        }
    }

    return {
        init: function() {
            initDatatable();
            initDeleteModal();
            handleNoticeModal();
        }
    }
}(jQuery));
