define([
    'bundles/fosjsrouting/js/router',
    routesUrl
], function(router, data) {
    'use strict';

    router.setRoutingData(data);

    return router;
});