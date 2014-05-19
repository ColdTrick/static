<?php
?>
//<script>
elgg.provide("elgg.static");

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
};

elgg.static.validate_form = function() {
	var error = "";
	var result = false;

	if ($(this).find("input[name='title']").val() == "") {
		error = elgg.echo("static:action:edit:error:title_description");
	}

	if ($(this).find("textarea[name='description']").val() == "") {
		error = elgg.echo("static:action:edit:error:title_description");
	}

	if (error != "") {
		alert(error);
	} else {
		result = true;
	}
	
	return result;
};

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
	
	$(".elgg-form-static-edit").on("submit", elgg.static.validate_form);
};

elgg.register_hook_handler('init', 'system', elgg.static.init);
