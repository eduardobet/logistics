

try {
    window.$ = window.jQuery = require('jquery');
	window.Popper = require('popper.js').default;
    window.Noty = require('noty');

    require('bootstrap');
    require('select2');
    require('select2/dist/js/i18n/es');
    require('bootstrap-datepicker');
    require('bootstrap-datepicker/dist/locales/bootstrap-datepicker.es.min');
    //require('bootstrap-sweetalert');
    window.swal = require('sweetalert2');
} catch (e) {}
