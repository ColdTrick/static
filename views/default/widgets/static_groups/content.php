<?php

$widget = elgg_extract("entity", $vars);
$group = $widget->getOwnerEntity();
if (empty($group) || !elgg_instanceof($group, "group")) {
	return;
}

$options = array(
	"type" => "object",
	"subtype" => "static",
	"limit" => false,
	"container_guid" => $group->getGUID(),
	"joins" => array("JOIN " . elgg_get_config("dbprefix") . "objects_entity oe ON e.guid = oe.guid"),
	"order_by" => "oe.title asc"
);

$entities = elgg_get_entities($options);
$body = "";
if ($entities) {
	$body = "<table class='elgg-table-alt' id='static-pages-list'>";
	$body .= "<thead><tr>";
	$body .= "<th>" . elgg_echo("title") . "</th>";
	$body .= "</tr></thead>";

	foreach ($entities as $entity) {
		
		$body .= elgg_view_entity($entity, array("full_view" => false, "show_edit" => false));

	}
	$body .= "</table>";
}

if (empty($body)) {
	$body = elgg_echo("static:admin:empty");
}

echo $body;