<?php

/* @var $widget ElggWidget */
$widget = elgg_extract('entity', $vars);
$group = $widget->getOwnerEntity();
if (!$group instanceof ElggGroup) {
	return;
}

if (!static_group_enabled($group)) {
	return;
}

$can_write = $group->canWriteToContainer(0, 'object', StaticPage::SUBTYPE);

if ($can_write) {
	$ia = elgg_set_ignore_access(true);
}

$entities = elgg_get_entities([
	'type' => 'object',
	'subtype' => StaticPage::SUBTYPE,
	'metadata_name_value_pairs' => [
		'parent_guid' => 0,
	],
	'limit' => false,
	'container_guid' => $group->guid,
	'order_by_metadata' => ['title' => 'ASC'],
]);

if ($entities) {
	$ordered_entities = [];
	foreach ($entities as $index => $entity) {
		$order = $entity->order;
		if (empty($order)) {
			$order = (1000000 + $index);
		}
	
		$ordered_entities[$order] = elgg_view('object/static/widget', ['entity' => $entity]);
	}
	ksort($ordered_entities);
	echo implode($ordered_entities);
} else {
	echo elgg_echo('static:admin:empty');
}

if ($can_write) {
	elgg_set_ignore_access($ia);
}
