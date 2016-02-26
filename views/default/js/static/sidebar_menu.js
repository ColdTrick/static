define(function(require) {
	var $ = require('jquery');
	var elgg = require('elgg');

	var init = function() {
		$('.elgg-menu-page-static > li.static-sortable ul').sortable({
			items: '> li',
			connectWith: '.elgg-menu-page-static > li.static-sortable ul',
			forcePlaceholderSize: true,
			revert: true,
			tolerance: 'pointer',
			containment: '.elgg-menu-page-static',
			start:  function(event, ui) {
				$(ui.item).find(' > a').addClass('dragged');
			},
			update: function(event, ui) {
				var $parent = $(ui.item).parent().parent();
				var parent_guid = $parent.find(' > a').attr('rel');
				var new_order = [];

				$parent.find('> ul > li > a').each(function(index, child) {
					new_order[index] = $(child).attr('rel');
				});

				elgg.action('static/reorder', {
					data: {
						guid: parent_guid,
						order: new_order
					}
				});
			}
		});

		$('.elgg-menu-page-static li a').on('click', function(event) {
			if ($(this).hasClass('dragged')) {
				event.preventDefault();
				event.stopImmediatePropagation();
				$(this).removeClass('dragged');
			}
		});

		$('.elgg-menu-page-static li a span').on('click', function(event) {
			var href = $(this).parent().attr('href');
			document.location = href;

			event.preventDefault();
			event.stopImmediatePropagation();
		});
	};

	elgg.register_hook_handler('init', 'system', init);
});