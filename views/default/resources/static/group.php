<?php
/**
 * List the top statics of this group
 */

elgg_gatekeeper();
elgg_group_gatekeeper();

$group = elgg_get_page_owner_entity();
if (!($group instanceof ElggGroup)) {
	forward(REFERER);
}

if (!static_group_enabled($group)) {
	forward(REFERER);
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

if ($can_write) {
	elgg_set_ignore_access($ia);
}

if ($entities) {
	
	elgg_require_js('static/list_reorder');
	
	$ordered_entities = [];
	foreach ($entities as $index => $entity) {
		$order = $entity->order;
		if (empty($order)) {
			$order = (1000000 + $index);
		}
	
		$ordered_entities[$order] = $entity;
	}
	
	ksort($ordered_entities);
	
	$body = '';
	if ($group->canEdit()) {
		$body .= elgg_format_element('div', ['class' => 'mbm'], elgg_echo('static:list:info'));
	}
	
	$body .= elgg_format_element('div', [
		'class' => 'static-list-reorder',
		'data-container-guid' => $group->guid,
	], elgg_view_entity_list($ordered_entities, ['full_view' => false]));
} else {
	$body = elgg_echo('static:admin:empty');
}

if ($can_write) {
	elgg_register_title_button();
}

$title_text = elgg_echo('static:groups:title');

// build page
$body = elgg_view_layout('content', [
	'title' => $title_text,
	'content' => $body,
]);

// draw page
echo elgg_view_page($title_text, $body);
