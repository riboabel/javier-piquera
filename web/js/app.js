App = typeof App !== 'undefined' ? App : {};

App.Main = function($) {
    "use strict";

    var initMetisMenu = function() {
        $('#side-menu').metisMenu();
    }

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
    }

    var initTooltipster = function() {
        if ($.fn.tooltipster) {
            $('[title]').not('.cke *').tooltip({'trigger': 'hover'});
        } else {
            console.log('Tooltip plugin not present');
        }
    }

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
    }

    var initDatetimepicker = function() {
        if ($.datepicker) {
            var options = {
                //Datepicker options
                closeText: "Listo", // Display text for close link
                prevText: "Anterior", // Display text for previous month link
                nextText: "Siguiente", // Display text for next month link
                currentText: "Hoy", // Display text for current month link
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
            }

            $('.datetimepicker').each(function() {
                var $element = $(this);
                $($element).datetimepicker(options);
            });
        } else {
            console.log('Datepicker is not present');
        }
    }

    var initializeReportLinks = function() {
        $('a.link-report').on('click', function(event) {
            event.preventDefault();

            var createModal = function(id) {
                return $('<div class="modal fade" data-backdrop="static" id="'+ id +'"><div class="modal-dialog"><div class="modal-content"/></div></div>').appendTo($('body'));
            }

            $('#_mR').remove();
            var mR = createModal('_mR');
            mR.modal().find('.modal-content').load($(this).attr('href'));
        });
    }

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

    return {
        init: function() {
            initMetisMenu();
            initResizeWindowHandler();
            initNavigator();
            initTooltipster();
            initDatetimepicker();
            initializeReportLinks();
            initializeMessageSystem();
            initWeekPdfsControls();
        },
        validate: initValidator
    }
}(jQuery);
