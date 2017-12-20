App = typeof App !== 'undefined' ? App : {};
App.Drivers = App.Drivers !== 'undefined' ? App.Drivers : {};

+(App.Drivers.Form = function($) {
    var initValidator = function() {
        App.Main.validate($('form#driver'));
    }

    var initControls = function() {
        $('form#driver input:checkbox').iCheck({
            checkboxClass: 'icheckbox_flat-blue'
        });

        $('form#driver input[type=tel]').intlTelInput({
            allowExtensions: true,
            autoFormat: false,
            autoHideDialCode: true,
            autoPlaceholder: false,
            defaultCountry: 'auto',
            geoIpLookup: function(callback) {
                $.get('http://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            nationalMode: false,
            numberType: 'MOBILE',
            preferredCountries: ['cu'],
            utilsScript: '/plugins/jquery.intlTelInput/libphonenumber/build/utils.js'
        });
    }

    return {
        init: function() {
            initValidator();
            initControls();
        }
    }
}(jQuery));
