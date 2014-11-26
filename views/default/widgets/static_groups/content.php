<?php

$widget = elgg_extract("entity", $vars);
$group = $widget->getOwnerEntity();
if (empty($group) || !elgg_instanceof($group, "group")) {
	return;
}

$can_write = $group->canWriteToContainer(0, "object", "static");

$options = array(
	"type" => "object",
	"subtype" => "static",
	"limit" => false,
	"container_guid" => $group->getGUID(),
	"joins" => array("JOIN " . elgg_get_config("dbprefix") . "objects_entity oe ON e.guid = oe.guid"),
	"order_by" => "oe.title asc",
	"no_results" => elgg_echo("static:admin:empty")
);

if ($can_write) {
	$ia = elgg_set_ignore_access(true);
}
echo elgg_list_entities($options);
if ($can_write) {
	elgg_set_ignore_access($ia);
}
