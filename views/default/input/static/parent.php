<?php

$name = elgg_extract('name', $vars);
$owner = elgg_extract('owner', $vars);
if (!$owner instanceof \ElggEntity) {
	return;
}

elgg_import_esm('input/static/parent');

$parent_guid = (int) elgg_extract('value', $vars, $owner->guid);

$entity = elgg_extract('entity', $vars);

$parent_entity = get_entity($parent_guid);

$top_parent_guid = 0;
$root_entity = null;
if ($parent_entity instanceof \StaticPage) {
	$root_entity = $parent_entity->getRootPage();

	$top_parent_guid = $root_entity->guid;
}

echo elgg_view('input/hidden', [
	'name' => $name,
	'value' => $parent_guid,
]);

// are there root pages in the container?
$parent_pages = elgg_get_entities([
	'type' => 'object',
	'subtype' => \StaticPage::SUBTYPE,
	'container_guid' => $owner->guid,
	'metadata_name_value_pairs' => [
		'parent_guid' => 0,
	],
	'limit' => false,
	'batch' => true,
]);

$parent_select = [0 => elgg_echo('static:new:parent:top_level')];
foreach ($parent_pages as $parent_page) {
	$parent_select[$parent_page->guid] = $parent_page->getDisplayName();
}

if ($parent_entity) {
	$vars['parent_item_guid'] = $parent_entity->guid;
}

if ($entity) {
	unset($parent_select[$entity->guid]);
	$vars['selected_item_guid'] = $entity->guid;
}

echo elgg_view('input/select', [
	'name' => $name,
	'options_values' => $parent_select,
	'value' => $top_parent_guid,
	'class' => 'static-edit-top-parent-select',
]);

if (!$parent_entity instanceof \StaticPage) {
	return;
}

$vars['root_entity'] = $root_entity;
$vars['sort_by'] = 'priority';

echo elgg_view_menu('static_edit', $vars);
