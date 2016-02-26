define(function(require) {
	var $ = require('jquery');
	var elgg = require('elgg');
	
	$('#static-pages-list.static-reorder').sortable({
		items: 'tbody tr',
		revert: true,
		update: function(event, ui) {
			var container_guid = $('#static-pages-list').data('containerGuid');
			
			var ordered_guids = [];
			$('#static-pages-list.static-reorder tbody tr').each(function() {
				ordered_guids.push($(this).data('guid'));
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