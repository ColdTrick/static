define(function(require) {
	
	var $ = require('jquery');
	var Ajax = require('elgg/Ajax');
	
	var out_of_date = function(event) {
		event.preventDefault();
		
		if ((typeof event.result !== 'undefined') && (event.result === false)) {
			return false;
		}
		
		var $link = $(this);
		var ajax = new Ajax();
		ajax.action($link.attr('href'), {
			success: function() {
				$link.closest('.static-out-of-date-message').remove();
			}
		});
		
		return false;
	};
	
	$(document).on('click', '#static-out-of-date-touch-link', out_of_date);
});
