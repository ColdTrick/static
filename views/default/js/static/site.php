<?php
?>
//<script>
elgg.provide("elgg.static");

elgg.static.reorder = function(elem) {
	var $parent = $(elem).parent().parent();
	var parent_guid = $parent.find(" > a").attr("rel");
	var new_order = new Array();

	$parent.find("> ul > li > a").each(function(index, child) {
		new_order[index] = $(child).attr("rel");
	});

	elgg.action('static/reorder', {
		data: {
 			guid: parent_guid,
 			order: new_order
		}
	});
};

elgg.static.reorder_root_pages = function(elem) {
	var container_guid = $('#static-pages-list').data('containerGuid');
	
	var ordered_guids = new Array();
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
};

elgg.static.init = function() {
	$(".elgg-menu-page-static > li.static-sortable").sortable({
		items: "li",
		forcePlaceholderSize: true,
		revert: true,
		tolerance: "pointer",
		containment: ".elgg-menu-page-static",
		start:  function(event, ui) {
			$(ui.item).find(" > a").addClass("dragged");
		},
		update: function(event, ui) {
			elgg.static.reorder(ui.item);
   		}
	});

	$("#static-pages-list.static-reorder").sortable({
		items: "tr",
		revert: true,
		tolerance: "pointer",
		containment: "table",
		update: function(event, ui) {
			elgg.static.reorder_root_pages(ui.item);
   		}
	});

	$(".elgg-menu-page-static li a").on("click", function(event) {
		if ($(this).hasClass("dragged")) {
			event.preventDefault();
			event.stopImmediatePropagation();
			$(this).removeClass("dragged");
		}
	});

	$(".elgg-menu-page-static li a span").on("click", function(event) {
		var href = $(this).parent().attr('href');
		document.location = href;

		event.preventDefault();
		event.stopImmediatePropagation();
	});

	
};

elgg.register_hook_handler('init', 'system', elgg.static.init);
