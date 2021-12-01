define([
    'jquery',
    'js/app/router',
    'js/app/main',
    'plugins/handlebars',
    'jquery/select2'
], function($, router, utils, handlebars) {
    'use strict';

    var initValidator = function() {
        $.validator.addMethod('norepeatedservices', function(value, element, param) {
            var ids = [], error = false;
            $('#invoice_form_lines select[name$="[service]"]').each(function() {
                var id = $(this).val();
                if (id) {
                    if (ids.indexOf(id) !== -1) {
                        error = true;
                        return false;
                    }
                    ids.push(id);
                }
            });
            return !error;
        }, 'Hay servicios repetidos en la factura');

        utils.validate($('form#invoice'), {
            ignore: ':hidden:not([name="services"])',
            rules: {
                'services': {
                    norepeatedservices: true,
                    min: {
                        depends: function() {
                            return $('#invoice_form_modelName').val() === 'GENERAL';
                        },
                        param: 1
                    }
                }
            },
            messages: {
                services: {
                    min: 'Agregue servicios a la factura'
                }
            }
        });
    };

    var serviceOnChange = function() {
        var data = $(this).select2('data');

        if ($('#invoice_form_modelName').val() === 'ATRIO') {
            $('input:hidden[name$="[1][service]"]').val($(this).val());
            $('input:hidden[name$="[1][serviceSerialNumber]"]').val(data[0].serialNumber);
        }

        $(this).closest('.item').find('input[name$="[serviceName]"]').val(data[0].serviceName);
        $(this).closest('.item').find('input[name$="[clientsName]"]').val(data[0].clientNames);
        $(this).closest('.item').find('input[name$="[clientReference]"]').val(data[0].reference);
        $(this).closest('.item').find('input[name$="[serviceSerialNumber]"]').val(data[0].serialNumber);
        $(this).closest('.item').find('input[name$="[totalPrice]"]').val(data[0].price).trigger('change');
    };

    var serviceSelect2Options = {
        ajax: {
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page
                }
            }
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        templateResult: function(repo) {
            if (repo.loading) return repo.text;

            var markup = '<div class="select2-result-repository clearfix">' +
                '<div class="select2-result-repository__title">' + repo.text + '</div>';

            markup += '<div class="select2-result-repository__description">';
            var elements = [];
            if (repo.clientNames) {
                elements.push("<strong>Clientes:</strong> " + repo.clientNames);
            }
            if (repo.reference) {
                elements.push("<strong>Referencia:</strong> " + repo.reference);
            }
            if (repo.price) {
                elements.push('<strong>Importe:</strong> ' + repo.price);
            }

            markup += elements.join(' | ', elements) + '</div></div>';

            return markup;
        },
        language: 'es',
        minimunInputLength: 1,
        width: '100%'
    };

    var reindexAtrioItems = function() {
        var items = $('#fakedServices .panel-body .item');

        items.each(function(index) {
            if (index === items.length - 1) {
                return;
            }

            var oldIndex = $(this).attr('data-index');

            $('input:text, input:hidden, input[type=number], select', this).each(function() {
                $(this).attr({
                    name: $(this).attr('name').replace('[' + oldIndex + ']', '[' + index + ']'),
                    id: $(this).attr('id').replace('_' + oldIndex + '_', '_' + index + '_')
                });
            });
            $('label', this).each(function() {
                $(this).attr('for', $(this).attr('for').replace('_' + oldIndex + '_', '_' + index + '_'));
            });

            $(this).attr('data-index', index);
        });
    };

    var updateTotalCharge = function() {
        var value = 0;
        $.map($('input[name$="[totalPrice]"]'), function(element) {
            value += $(element).val() ? $(element).val() * 1 : 0;
        });

        $('input[name="invoice_form[totalCharge]"]').val(value.toFixed(2));
    };

    var initControls = function() {
        $('#invoice_form_provider').on('change', function() {
            $('#invoice_form_lines').find('.item').remove();
        }).select2({width: '100%'});
        $('#invoice_form_driver').select2({width: '100%'});
        $('#invoice_form_modelName').on('change', function() {
            var entry = handlebars.compile(document.getElementById('entry-atrio').innerHTML),
                item = handlebars.compile(document.getElementById('item-atrio').innerHTML);

            if ($(this).val() === 'ATRIO') {
                $('#fakedServices').removeClass('hidden').find('.panel-body').append(entry({item: item({index: 0, index0: false})}));
                $('#fakedServices').find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
                    ajax: {
                        url: router.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
                    }
                }));

                $('#invoice_form_lines').closest('.panel').addClass('hidden').find('.item').remove();
            } else {
                $('#fakedServices').addClass('hidden').find('.item').remove();
                $('#invoice_form_lines').closest('.panel').removeClass('hidden');
            }
        }).trigger('change');

        $('.btn-add-atrio-item').on('click', function() {
            var htmlItem = handlebars.compile(document.getElementById('item-atrio').innerHTML),
                currentItems = $('#fakedServices .item'),
                item = $(htmlItem({index: currentItems.length - 2, index0: true}));

            item.insertBefore(currentItems[currentItems.length - 2]);
            reindexAtrioItems();
            updateTotalCharge();

            item.find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
                ajax: {
                    url: router.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
                }
            }));
        });
        $('body').on('click', 'button.btn-remove-atrio-item', function(event) {
            $(event.currentTarget).closest('.item')
                .fadeOut(function() {
                    $(this).remove();
                    reindexAtrioItems();
                    updateTotalCharge();
                });
        }).on('mouseover', 'button.btn-remove-atrio-item, button.btn-delete-item', function(event) {
            $(event.currentTarget).closest('.item').css({backgroundColor: '#f3e1e1'});
        }).on('mouseout', 'button.btn-remove-atrio-item, button.btn-delete-item', function(event) {
            $(event.currentTarget).closest('.item').css({backgroundColor: ''});
        });
    }

    var initCollection = function() {
        var $container = $('#invoice_form_lines');
        $container.data('index', $container.find('.item').length);

        $container.on('click', '.btn-delete-item', function() {
            $(this).closest('.item').fadeOut(function() {
                $(this).remove();
                $('input:hidden[name="services"]').val($container.find('.item').length);
            });
        });

        $('button.btn-add-item').on('click', function() {
            var index = $container.data('index'),
                prototype = $container.data('prototype').replace(/__name__/g, index),
                $item = $(prototype);

            $container.data('index', index + 1);
            $item.appendTo($container);
            $('input:hidden[name="services"]').val($container.find('.item').length);

            $item.find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
                ajax: {
                    url: router.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
                }
            }));
        });

        $container.find('select[name$="[service]"]').on('change', serviceOnChange).select2($.extend(true, serviceSelect2Options, {
            ajax: {
                url: router.generate('app_invoices_getservices', {id: $('#invoice_form_provider').val()})
            }
        }));

        $('body').on('change', 'input[name$="[totalPrice]"]', function() {
            updateTotalCharge();
        });

        $('body').on('change', 'input[name$="[quantity]"], input[name$="[unitPrice]"]', function() {
            var $item = $(this).closest('.item'),
                $up = $item.find('input[name$="[unitPrice]"]'),
                $q = $item.find('input[name$="[quantity]"]');

            if ($up.val() === '' || $q.val() === '' || isNaN($q.val() * $up.val())) {
                if ($('#invoice_form_modelName').val() === 'GENERAL') {
                    var data = $item.find('select[name$="[service]"]').select2('data');
                    $item.find('input[name$="[totalPrice]"]').val(data.length > 0 ? data[0].price.toFixed(2) : "0.00").trigger('change');
                } else {
                    $item.find('input[name$="[totalPrice]"]').val("0.00").trigger('change');
                }
            } else {
                var t = ($up.val() * $q.val()).toFixed(2);
                $item.find('input[name$="[totalPrice]"]').val(t).trigger('change');
            }
        });
    };

    return function() {
        initValidator();
        initControls();
        initCollection();
    };
});