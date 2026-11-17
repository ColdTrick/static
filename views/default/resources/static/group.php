<?php
/**
 * List the main static pages of this group
 */

elgg_group_tool_gatekeeper('static');

/** @var \ElggGroup $group */
$group = elgg_get_page_owner_entity();

$manual_sorting_enabled = (bool) $group->getPluginSetting('static', 'enable_manual_sorting', false);

elgg_push_collection_breadcrumbs('object', \StaticPage::SUBTYPE, $group);

$can_write = $group->canWriteToContainer(0, 'object', \StaticPage::SUBTYPE);
if ($can_write) {
	elgg_register_title_button('add', 'object', \StaticPage::SUBTYPE);
	
	if ($manual_sorting_enabled) {
		$count = elgg_count_entities([
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'metadata_name_value_pairs' => [
				'parent_guid' => 0,
			],
			'container_guid' => $group->guid,
		]);
		
		if ($count > 1) {
			elgg_register_menu_item('title', [
				'name' => 'sort_pages',
				'icon' => 'sort',
				'text' => elgg_echo('sort'),
				'href' => elgg_generate_url('ajax', [
					'type' => 'form',
					'segments' => 'static/sort',
					'container_guid' => $group->guid,
				]),
				'class' => ['elgg-button', 'elgg-button-action', 'elgg-lightbox'],
			]);
		}
	}
}

$ignore_access = $can_write ? ELGG_IGNORE_ACCESS : 0;

$body = elgg_call($ignore_access, function() use ($group, $manual_sorting_enabled) {
	$options = [
		'type' => 'object',
		'subtype' => \StaticPage::SUBTYPE,
		'metadata_name_value_pairs' => [
			'parent_guid' => 0,
		],
		'container_guid' => $group->guid,
		'sort_by' => [
			'property' => 'title',
			'direction' => 'asc',
		],
		'no_results' => elgg_echo('static:admin:empty'),
	];
	
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

echo elgg_view_page(elgg_echo('static:groups:title'), [
	'content' => $body,
	'filter_id' => 'static',
	'filter_value' => 'group',
]);
