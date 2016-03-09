<?php

$widget = elgg_extract('entity', $vars);
$group = $widget->getOwnerEntity();
if (empty($group) || !elgg_instanceof($group, 'group')) {
	return;
}

if (!static_group_enabled($group)) {
	return;
}

$can_write = $group->canWriteToContainer(0, 'object', 'static');

if ($can_write) {
	$ia = elgg_set_ignore_access(true);
}

$entities = elgg_get_entities([
	'type' => 'object',
	'subtype' => StaticPage::SUBTYPE,
	'limit' => false,
	'container_guid' => $group->getGUID(),
	'joins' => ['JOIN ' . elgg_get_config('dbprefix') . 'objects_entity oe ON e.guid = oe.guid'],
	'order_by' => 'oe.title asc',
]);

if ($entities) {
	$ordered_entities = [];
	foreach ($entities as $index => $entity) {
		$order = $entity->order;
		if (empty($order)) {
			$order = (1000000 + $index);
		}
	
		$ordered_entities[$order] = elgg_view_entity($entity, ['full_view' => false]);
	}
	ksort($ordered_entities);
	echo implode($ordered_entities);
} else {
	echo elgg_echo('static:admin:empty');
}

if ($can_write) {
	elgg_set_ignore_access($ia);
}
