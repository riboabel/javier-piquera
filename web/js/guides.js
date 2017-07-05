App = typeof App !== 'undefined' ? App : {};
App.Guides = typeof App.Guides !== 'undefined' ? App.Guides : {};

+(App.Guides.Index = function($) {
    var datatable;

    var clickDeleteHandler = function(event) {
        event.preventDefault();

        $('.modal[data-for=dataTables-guides] button.btn-danger').data('url', $(event.currentTarget).attr('href'));
        $('.modal[data-for=dataTables-guides]').modal();
    }

    var clickDeleteModalHandler = function(event) {
        event.preventDefault();

        var url = $(this).data('url');

        $('#dataTables-guides').find('a[href="' + url + '"]').parents('td:first').empty().text('Eliminando...');
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
        $('.modal[data-for=dataTables-guides]').find('button.btn-danger').on('click', clickDeleteModalHandler);
    }

    var drawDatatableHandler = function() {
        $(this).find('.btn-delete').on('click', clickDeleteHandler);
        $(this).find('[title]').tooltip({'trigger': 'hover'});
    }

    var initDatatable = function() {
        datatable = App.Tables.initDatatable($('#dataTables-guides'));
        $('#dataTables-guides').on('draw.dt', drawDatatableHandler);
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
