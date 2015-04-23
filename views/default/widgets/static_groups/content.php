<?php

$widget = elgg_extract("entity", $vars);
$group = $widget->getOwnerEntity();
if (empty($group) || !elgg_instanceof($group, "group")) {
	return;
}

if (!static_group_enabled($group)) {
	return;
}

$container = false;
$main_page = (int) $widget->main_page;
if (!empty($main_page)) {
	$container = get_entity($main_page);
	if (empty($container) || !elgg_instanceof($container, 'object', 'static')) {
		unset($container);
	}
}

if (empty($container)) {
	$container = $group;
}

$options = array(
	"type" => "object",
	"subtype" => "static",
	"limit" => false,
	"container_guid" => $container->getGUID(),
	"full_view" => false,
	"pagination" => false
);

$pages = new ElggBatch('elgg_get_entities', $options);
$entities = array();
foreach ($pages as $page) {
	$order = (int) $page->order;
	if (empty($order)) {
		$order = (int) $page->time_created;
	}
	
	while(isset($entities[$order])) {
		$order++;
	}
	
	$entities[$order] = $page;
}

if (!empty($entities)) {
	ksort($entities);
	$list = elgg_view_entity_list($entities, $options);
	
	unset($entities);
} else {
	$list = elgg_echo("static:admin:empty");
}
echo $list;
