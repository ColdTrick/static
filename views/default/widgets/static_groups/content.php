<?php

/* @var $widget ElggWidget */
$widget = elgg_extract('entity', $vars);
$group = $widget->getOwnerEntity();
if (!$group instanceof \ElggGroup) {
	return;
}

if (!static_group_enabled($group)) {
	return;
}

$ignore_access = $group->canWriteToContainer(0, 'object', \StaticPage::SUBTYPE) ? ELGG_IGNORE_ACCESS : 0;
echo elgg_call($ignore_access, function () use ($group) {
	$options = [
		'type' => 'object',
		'subtype' => \StaticPage::SUBTYPE,
		'metadata_name_value_pairs' => [
			'parent_guid' => 0,
		],
		'limit' => false,
		'container_guid' => $group->guid,
		'sort_by' => [
			'property' => 'title',
			'direction' => 'asc',
		],
		'item_view' => 'object/static/widget',
		'no_results' => elgg_echo('static:admin:empty'),
	];

	$manual_sorting_enabled = (bool) $group->getPluginSetting('static', 'enable_manual_sorting', false);
	if ($manual_sorting_enabled) {
		$options['sort_by'] = [
			'property' => 'order',
			'direction' => 'asc',
			'join_type' => 'left',
			'signed' => true,
		];
	}
	
	return elgg_list_entities($options);
});
