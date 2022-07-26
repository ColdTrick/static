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

$ignore_access = $group->canWriteToContainer(0, 'object', StaticPage::SUBTYPE) ? ELGG_IGNORE_ACCESS : 0;
echo elgg_call($ignore_access, function () use ($group) {
	
	$entities = elgg_get_entities([
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'metadata_name_value_pairs' => [
			'parent_guid' => 0,
		],
		'limit' => false,
		'container_guid' => $group->guid,
		'sort_by' => [
			'property' => 'title',
			'direction' => 'asc',
		],
	]);
	
	if (empty($entities)) {
		return elgg_echo('static:admin:empty');
	}
	
	$ordered_entities = [];
	foreach ($entities as $index => $entity) {
		$order = $entity->order;
		if (empty($order)) {
			$order = (1000000 + $index);
		}
	
		$ordered_entities[$order] = elgg_view('object/static/widget', ['entity' => $entity]);
	}
	ksort($ordered_entities);
	return implode($ordered_entities);
});
