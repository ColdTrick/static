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

	$(".elgg-menu-page-static li a").live("click", function(event) {
		if ($(this).hasClass("dragged")) {
			event.preventDefault();
			event.stopImmediatePropagation();
			$(this).removeClass("dragged");
		}
	});
};

elgg.register_hook_handler('init', 'system', elgg.static.init);
