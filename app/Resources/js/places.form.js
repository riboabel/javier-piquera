App = typeof App !== 'undefined' ? App : {};
App.Places = typeof App.Places !== 'undefined' ? App.Places : {};

+(App.Places.Form = function($) {
    var initControls = function() {
        $('form#place input[type=tel]').intlTelInput({
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

    var initValidator = function() {
        App.Main.validate($('form#place'));
    }

    return {
        init: function() {
            initControls();
            initValidator();
        }
    }
}(jQuery));
