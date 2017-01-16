<?php

elgg_gatekeeper();

$site = elgg_get_site_entity();

$ia = elgg_set_ignore_access(true);

$entities = elgg_get_entities_from_metadata([
	'type' => 'object',
	'subtype' => StaticPage::SUBTYPE,
	'metadata_name_value_pairs' => [
		'parent_guid' => 0,
	],
	'limit' => false,
	'container_guid' => $site->getGUID(),
	'joins' => [
		'JOIN ' . elgg_get_config('dbprefix') . 'objects_entity oe ON e.guid = oe.guid',
	],
	'order_by' => 'oe.title asc',
]);

elgg_set_ignore_access($ia);

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
	], elgg_view_entity_list($ordered_entities, ['item_view' => 'object/static/simple']));
} else {
	$body = elgg_echo('static:admin:empty');
}

$filter = '';
if (static_out_of_date_enabled()) {
	$filter = elgg_view('page/layouts/elements/filter');
}

if ($site->canWriteToContainer(elgg_get_logged_in_user_guid(), 'object', 'static')) {
	elgg_register_title_button();
}

$title_text = elgg_echo('static:all');

// build page
$body = elgg_view_layout('one_column', [
	'title' => $title_text,
	'content' => $filter . $body,
]);

// draw page
echo elgg_view_page($title_text, $body);
