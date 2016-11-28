<?php
/**
 * A single element of a menu.
 *
 * @package Elgg.Core
 * @subpackage Navigation
 *
 * @uses $vars['item']       ElggMenuItem
 * @uses $vars['item_class'] Additional CSS class for the menu item
 */

$selected_item_guid = (int) elgg_extract('selected_item_guid', $vars);
$parent_item_guid = (int) elgg_extract('parent_item_guid', $vars);

$item = elgg_extract('item', $vars);

$indent = (int) elgg_extract('indent', $vars);

$root_entity = elgg_extract('root_entity', $vars);
if ($root_entity->guid == $item->getName()) {
	$text = elgg_echo('static:edit:menu:parent:direct_child');
} else {
	$text = $item->getText();
	$text = ltrim(str_repeat('-', $indent) . ' ' . $text);
}

if ($item->getName() == $selected_item_guid) {
	// do not show children
	return;
}

echo elgg_format_element('option', [
	'selected' => $item->getName() == $parent_item_guid,
	'value' => $item->getName(),
], $text);

$children = $item->getChildren();
if (empty($children)) {
	return;
}

$indent++;
foreach ($children as $child) {
	echo elgg_view('navigation/menu/static_edit/item', [
		'item' => $child,
		'indent' => $indent,
		'selected_item_guid' => $selected_item_guid,
		'parent_item_guid' => $parent_item_guid,
		'root_entity' => $root_entity,
	]);
}