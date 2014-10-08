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
	"order_by" => "oe.title asc",
	"no_results" => elgg_echo("static:admin:empty")
);

echo elgg_list_entities($options);
