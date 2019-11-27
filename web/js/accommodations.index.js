define([
    'jquery',
    'js/app/router',
    'plugins/sweetalert/sweetalert.min',
    'js/app/datatables-init',
    'plugins/jquery.blockUI',
    'plugins/tooltipster',
    'plugins/datepicker/bootstrap-datepicker',
    'plugins/icheck'
], function($, router, swal) {
    'use strict';

    var table = $('#table-accommodations'),
        datatable;

    function initTableCheckboxs() {
        table.find('input[type=checkbox].cb-pay').on('ifChanged', function() {
            var id = $(this).attr('data-params'),
                url = router.generate('app_accommodation_pay', {id: id});

            $.ajax({
                dataType: 'json',
                method: 'POST',
                url: url
            }).done(function() {
                datatable.DataTable().draw(false);
            });

        }).iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });

        table.find('button.btn-unpay').on('click', function() {
            var id = $(this).attr('data-params'),
                url = router.generate('app_accommodation_pay', {id: id, op: 'un'});

            $.ajax({
                dataType: 'json',
                method: 'POST',
                url: url
            }).done(function() {
                datatable.DataTable().draw(false);
            });
        });
    }

    function initTable() {
        table.on('preDraw.dt', function() {
            $(this).block({
                message: 'Procesando...'
            });
        }).on('draw.dt', function() {
            $(this).unblock();

            $(this).find('[title]').tooltip({'trigger': 'hover'});

            initTableCheckboxs();
        });

        datatable = table.dataTable({
            ajax: {
                data: function(data) {
                    var filter = [];
                    $.each($('form#filter').serializeArray(), function(i, e) {
                        if (/\[]$/.test(e.name)) {
                            var sName = e.name.replace(/\[]$/, '');
                            if (!filter[sName]) {
                                filter[sName] = [];
                            }

                            filter[sName].push(e.value);
                        } else {
                            filter[e.name] = e.value;
                        }
                    });

                    return $.extend(true, data, filter);
                },
                method: 'GET',
                url: router.generate('app_accommodation_getdata')
            },
            columns: [
                {
                    name: 'startDate',
                    title: 'Inicio'
                }, {
                    name: 'endDate',
                    title: 'Terminación'
                }, {
                    name: 'nights',
                    title: 'Noches'
                }, {
                    name: 'reference',
                    title: 'Referencia'
                }, {
                    name: 'leadClient',
                    title: 'Cliente'
                }, {
                    name: 'pax',
                    title: 'PAX'
                }, {
                    name: 'fromLocation',
                    title: 'Lugar'
                }, {
                    name: 'fromRegion',
                    title: 'Región'
                }, {
                    name: 'cost',
                    title: 'Costo'
                }, {
                    name: 'paidAt',
                    title: 'Pagado',
                    sortable: false
                }, {
                    name: 'actions',
                    sortable: false,
                    searchable: false,
                    width: '80px'
                }
            ],
            processing: false,
            searching: false,
            serverSide: true
        });
    }

    function initControls() {
        $('#table-accommodations').on('click', '.btn-delete', function(event) {
            event.preventDefault();

            var url = $(this).attr('href');

            swal({
                showCancelButton: true,
                title: 'Comfirmar eliminación',
                text: '¿Estás seguro(a) que deseas eliminar el registro?',
                type: 'warning'
            }, function(confirm) {
                if (confirm) {
                    $('<form/>')
                        .appendTo($('body'))
                        .css('display', 'none')
                        .attr({'action': url, method: 'POST'})
                        .submit()
                        .remove();
                }
            });
        });
    }

    function initFilters() {
        var selector = 'startDate_left_date startDate_right_date endDate_left_date endDate_right_date'.split(' ').map(function(id) {
            return '#accommodation_filter_form_' + id;
        }).join(', ');

        $(selector).datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
            todayBtn: true,
            todayHighlight: true
        });

        $('#filter-form').find('input:text, select').on('change', function() {
            datatable.DataTable().draw();
        });
    }

    return function() {
        initTable();
        initControls();
        initFilters();
    };
});