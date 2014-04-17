<?php
?>
elgg.provide("elgg.static");

elgg.static.init = function() {
	$(".elgg-menu-page-static > li").sortable({
		items: "li",
		forcePlaceholderSize: true,
		revert: true,
		tolerance: "pointer",
		containment: ".elgg-menu-page-static",
		update: function(event, ui) {
   			elgg.static.reorder(ui.item);
   		}
	});
}

elgg.static.reorder = function(elem) {
	var $parent = $(elem).parent();
	var parent_guid = $parent.attr("rel");
	var new_order = "";

	$parent.find("tr").each(function(index, child) {
		new_order += $(child).attr("rel") + ",";
	});

	elgg.action('static/reorder', {
		data: {
 			guid: parent_guid,
 			order: new_order
		}
	});
}

elgg.register_hook_handler('init', 'system', elgg.static.init);
