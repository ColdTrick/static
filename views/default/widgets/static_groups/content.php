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

$list = elgg_view_menu('static_group_widget', array(
	'entity' => $container,
	'show_children' => elgg_instanceof($container, 'object', 'static'),
	'sort_by' => 'priority',
	'class' => 'elgg-menu-page elgg-menu-page-static'
));
if (empty($list)) {
	$list = elgg_echo("static:admin:empty");
}
echo $list;
