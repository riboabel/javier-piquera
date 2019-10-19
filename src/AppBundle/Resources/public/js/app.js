App = typeof App !== 'undefined' ? App : {};

+(App.Main = function($) {
    "use strict";

    var loadingImageSrc;

    var initMetisMenu = function() {
        $('#side-menu').metisMenu();
    };

    var resizeWindowHandler = function() {
        var topOffset = 50,
            width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        var height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    }

    var initResizeWindowHandler = function() {
        $(window).on('load resize', resizeWindowHandler);
    }

    var initNavigator = function() {
        var url = window.location;
        var element = $('ul.nav a').filter(function() {
            return this.href == url || url.href.indexOf(this.href) == 0;
        }).addClass('active').parent().parent().addClass('in').parent();
        if (element.is('li')) {
            element.addClass('active');
        }
    };

    var initTooltipster = function() {
        if ($.fn.tooltipster) {
            $('[title]').not('.cke *').tooltip({'trigger': 'hover'});
        } else {
            console.log('Tooltip plugin not present');
        }
    };

    var initValidator = function(element, options) {
        $(element).each(function() {
            var $form = $(this);

            $form.validate($.extend({}, {
                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: function(error, element) {
                    if (element.is(':hidden')) {
                        element = element.parent();
                    } else if (element.is('.select2-hidden-accessible')) {
                        element = element.parent().find('.select2-container');
                    }
                    if (!element.data('tooltipster-ns')) {
                        element.tooltipster({
                            trigger: 'custom',
                            onlyOne: false,
                            position: 'bottom-left',
                            positionTracker: true
                        });
                    }
                    element.tooltipster('update', $(error).text());
                    element.tooltipster('show');
                },
                success: function (label, element) {
                    if ($(element).is(':hidden')) {
                        element = $(element).parent();
                    } else if ($(element).is('.select2-hidden-accessible')) {
                        element = $(element).parent().find('.select2-container');
                    }

                    $(element).tooltipster('hide');
                }
            }, options));
        });
    };

    var initDatetimepicker = function() {
        if ($.datepicker) {
            var options = {
                //jQuery UI Datepicker options
                prevText: "Anterior", // Display text for previous month link
                nextText: "Siguiente", // Display text for next month link
                monthNames: ["Enero","Febrero","Marzo","Abril","Mayo","Junio",
                    "Julio","Agosto","Septiembre","Octubre","Noviembre","Deciembre"], // Names of months for drop-down and formatting
                monthNamesShort: ["Ene", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"], // For formatting
                dayNames: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"], // For formatting
                dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sáb"], // For formatting
                dayNamesMin: ["Do","Lu","Ma","Mi","Ju","Vi","Sá"], // Column headings for days starting at Sunday
                weekHeader: "sem", // Column header for week of the year
                firstDay: 1, // The first day of the week, Sun = 0, Mon = 1, ...
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,

                //Timepicker options
                currentText: 'Ahora',
                closeText: 'Listo',
                amNames: ['AM', 'A'],
                pmNames: ['PM', 'P'],
                timeFormat: 'HH:mm',
                timeSuffix: '',
                timeOnlyTitle: 'Seleccionar hora',
                timeText: 'Time',
                hourText: 'Hora',
                minuteText: 'Minuto',
                secondText: 'Segundo',
                controlType: 'slider',
                showTime: false
            };

            // Usamos el datepicker de jQuery UI para estos controles
            // pero mantenemos una referencia al nuevo para posterior uso
            var newDP = $.fn.datepicker.noConflict();

            $('.datetimepicker').each(function() {
                var $element = $(this);
                $element.datetimepicker(options);
            });

            $.fn.datepicker = newDP;
        } else {
            console.log('Datepicker is not present');
        }
    };

    var initializeReportLinks = function() {
        $('a.link-report').on('click', function(event) {
            event.preventDefault();

            var mR = $('#_mR');
            if (!mR.length) {
                mR = $('<div id="_mR"/>').appendTo($('body'));
            }

            if (loadingImageSrc) {
                $(this).append($('<img src="__src__" class="pull-right"/>'.replace(/__src__/, loadingImageSrc)));
            }

            var self = $(this);
            mR.empty().load($(this).attr('href'), function() {
                mR.find('.modal').modal();
                self.find('img').remove();
            });
        });
    };

    var clickMessageHandler = function(event) {
        event.preventDefault();

        var url = $(this).attr('href');
        $('.modal[data-for=message] .modal-body').empty().append($('<div class="row"><div class="col-xs-12">Cargando mensaje..</div></div>'));
        $('.modal[data-for=message]').off('shown.bs.modal').on('shown.bs.modal', function() {
            $(this).find('.modal-body').load(url);
        }).modal();

        $(this).parents('li:first').remove();
    }

    var initializeMessageSystem = function() {
        $('a.link-message').on('click', clickMessageHandler);
    }

    var initWeekPdfsControls = function() {
        $('body').on('click', 'a#linkGenerateWeekPdfs', function(event) {
            event.preventDefault();

            $('#_aK').remove();

            var $aK = $('<div id="_aK" class="modal fade"><div class="modal-dialog"><div class="modal-content"></div></div></div>').appendTo($('body'));
            $aK.find('.modal-content').load($(this).attr('href'), function() {
                $aK.modal({backdrop: 'static'});
            });
        });

        $('body').on('click', '.btn-generate-week-conceal', function() {
            $(this).empty().append($('<span class="fa fa-check-square"/>'));
        });

        $('.dropdown-tasks').parent().on('shown.bs.dropdown', function() {
            $(this).find('.dropdown-tasks').empty().append($('<li><div><p class="text-center">Cargando...</p></div></li>')).load(Routing.generate('app_default_gettasksdropdowncontent'));
        });
    }

    var initDatatable = function() {
        $.extend(true, $.fn.dataTable.defaults, {
            iDisplayLength: 200,
            aLengthMenu: [10, 20, 50, 100, 200, 400, 800, 1000],
            oLanguage: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando del _START_ al _END_ (de _TOTAL_)",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
    };

    var initIcheckControls = function() {
        $('input:checkbox.icheck').iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });
    };

    return {
        init: function(options) {
            options = $.extend({
                loadingImageSrc: false,
                notices: [],
            }, options);

            loadingImageSrc = options.loadingImageSrc;

            initMetisMenu();
            initResizeWindowHandler();
            initNavigator();
            initTooltipster();
            initDatetimepicker();
            initializeReportLinks();
            initializeMessageSystem();
            initWeekPdfsControls();
            initDatatable();
            initIcheckControls();

            if (options.notices.length > 0) {
                $.each(options.notices, function(index, message) {
                    toastr.success(message, 'Éxito', {
                        timeOut: 5000,
                        progressBar: true
                    });
                });
            }
        },
        validate: initValidator
    }
}(jQuery));
