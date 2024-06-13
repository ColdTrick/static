import 'jquery';
import 'jquery-ui';
import Ajax from 'elgg/Ajax';

var ajax = new Ajax();

$('.elgg-menu[data-menu-section="static"] > li.static-sortable ul').sortable({
	items: '> li',
	connectWith: '.elgg-menu[data-menu-section="static"] > li.static-sortable ul',
	forcePlaceholderSize: true,
	revert: true,
	tolerance: 'pointer',
	containment: '.elgg-menu[data-menu-section="static"]',
	start: function(event, ui) {
		$(ui.item).find(' > a').addClass('dragged');
	},
	update: function(event, ui) {
		
		if (!$(this).is($(ui.item).parent())) {
			// only trigger update on receiving sortable
			return;
		}
		
		var $parent = $(ui.item).parent().parent();
		var parent_guid = $parent.find(' > a').attr('rel');
		var new_order = [];

		$parent.find('> ul > li > a').each(function(index, child) {
			new_order[index] = $(child).attr('rel');
		});
		
		ajax.action('static/reorder', {
			data: {
				guid: parent_guid,
				order: new_order
			}
		});
	}
});

$('.elgg-menu[data-menu-section="static"] li a').on('click', function(event) {
	if (!$(this).hasClass('dragged')) {
		return;
	}
	
	event.preventDefault();
	event.stopImmediatePropagation();
	
	$(this).removeClass('dragged');
});

$('.elgg-menu[data-menu-section="static"] li a span').on('click', function(event) {
	if ($(this).closest('a').hasClass('dragged')) {
		return;
	}
	
	event.preventDefault();
	event.stopImmediatePropagation();

	document.location = $(this).closest('a').attr('href');
});
