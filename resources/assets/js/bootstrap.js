

try {
    window.$ = window.jQuery = require('jquery');
	window.Popper = require('popper.js').default;
    window.Noty = require('noty');

    require('bootstrap');
    require('select2');
    //require('bootstrap-sweetalert');
    window.swal = require('sweetalert2');
} catch (e) {}
