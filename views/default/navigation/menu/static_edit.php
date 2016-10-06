<?php
/**
 * Site navigation menu
 *
 * @uses $vars['menu']['static']
 */

$items = elgg_extract('static', $vars['menu'], []);
if (empty($items) || empty($items[0]->getChildren())) {
	return;
}

$options = '';
foreach ($items as $menu_item) {
	$options .= elgg_view('navigation/menu/static_edit/item', [
		'item' => $menu_item,
		'root_entity' => elgg_extract('root_entity', $vars),
		'selected_item_guid' => (int) elgg_extract('selected_item_guid', $vars),
		'parent_item_guid' => (int) elgg_extract('parent_item_guid', $vars),
	]);
}

if (empty($options)) {
	return;
}

echo elgg_format_element('select', ['name' => 'parent_guid', 'class' => 'static-edit-sub-parent-select elgg-input-dropdown'], $options);