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
	"limit" => false,
	"full_view" => false,
	"pagination" => false,
	"show_children" => elgg_instanceof($container, 'object', 'static'),
);
$entities = static_get_ordered_children($container);

if (!empty($entities)) {
	$list = elgg_view_entity_list($entities, $options);
	
	unset($entities);
} else {
	$list = elgg_echo("static:admin:empty");
}
echo $list;
