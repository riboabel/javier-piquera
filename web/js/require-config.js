var require = {
    baseUrl: '/',
    paths: {
        'jquery': 'plugins/jquery/jquery-3.3.1.min',
        'jquery/ui': 'plugins/jquery.ui/js/jquery-ui-1.11.4.min',
        'bootstrap': 'plugins/bootstrap/js/bootstrap',
        'jquery/select2': 'plugins/select2/js/select2',
        'jquery/select2.i18n-es': 'plugins/select2/js/i18n/es',
        'datatables.net': 'plugins/jquery.dataTables/amd/datatables',
        'datatables.net-bs': 'plugins/jquery.dataTables/amd/datatables.bootstrap',
        'plugins/tooltipster': 'plugins/tooltipster-master/js/jquery.tooltipster',
        'plugins/icheck': 'plugins/iCheck/icheck',
        'plugins/metisMenu': 'plugins/metisMenu/dist/metisMenu'
    },
    shim: {
        'bootstrap': ['jquery'],
        'plugins/datepicker/bootstrap-datepicker': ['jquery'],
        'plugins/tooltipster': ['jquery'],
        'plugins/icheck': ['jquery'],
        'plugins/metisMenu': ['jquery'],
        'plugins/jquery.copiq': ['jquery']
    }
};