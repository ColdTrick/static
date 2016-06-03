define(function(require) {
	var $ = require('jquery');
	var elgg = require('elgg');
	
	$('.static-list-reorder').sortable({
		items: '> ul > li',
		revert: true,
		update: function(event, ui) {
			var container_guid = $(this).data('containerGuid');
			
			var ordered_guids = [];
			var guidString = '';
			$(this).find('> ul > li').each(function(list_item) {
				guidString = $(this).attr('id');
				guidString = guidString.substr(guidString.indexOf('elgg-object-') + "elgg-object-".length);
				ordered_guids.push(guidString);
			});

			if (container_guid && ordered_guids) {
				elgg.action('static/reorder_root_pages', {
					data: {
						container_guid: container_guid,
						ordered_guids: ordered_guids
					}
				});
			}
		}
	});
});