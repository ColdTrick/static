<?php

elgg_gatekeeper();

$site = elgg_get_site_entity();

$entities = elgg_call(ELGG_IGNORE_ACCESS, function() use ($site) {
	return elgg_get_entities([
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'metadata_name_value_pairs' => [
			'parent_guid' => 0,
		],
		'limit' => false,
		'container_guid' => $site->guid,
		'order_by_metadata' => ['title' => 'ASC'],
	]);
});

if ($entities) {
	
	elgg_require_js('static/list_reorder');
	
	$ordered_entities = [];
	foreach ($entities as $index => $entity) {
	
		if (!has_access_to_entity($entity) && !$entity->canEdit()) {
			continue;
		}
	
		$order = $entity->order;
		if (empty($order)) {
			$order = (1000000 + $index);
		}
	
		$ordered_entities[$order] = $entity;
	}
	ksort($ordered_entities);
	
	$body = '';
	if ($site->canEdit()) {
		$body .= elgg_format_element('div', ['class' => 'mbm'], elgg_echo('static:list:info'));
	}

	$body .= elgg_format_element('div', [
		'class' => 'static-list-reorder',
		'data-container-guid' => $site->guid,
	], elgg_view_entity_list($ordered_entities, ['full_view' => false]));
} else {
	$body = elgg_echo('static:admin:empty');
}

if ($site->canWriteToContainer(elgg_get_logged_in_user_guid(), 'object', StaticPage::SUBTYPE)) {
	elgg_register_title_button('static', 'add', 'object', StaticPage::SUBTYPE);
}

$title_text = elgg_echo('static:all');

// build page
$body = elgg_view_layout('one_column', [
	'title' => $title_text,
	'content' => $body,
	'filter_id' => 'static',
]);

// draw page
echo elgg_view_page($title_text, $body);
