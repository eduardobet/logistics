

try {
    window.$ = window.jQuery = require('jquery');
	window.Popper = require('popper.js').default;
    window.Noty = require('noty');

    require('bootstrap');
} catch (e) {}
