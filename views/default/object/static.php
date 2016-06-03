<?php

$entity = elgg_extract('entity', $vars);
$full_view = elgg_extract('full_view', $vars);

if ($full_view) {
	echo elgg_view('object/static/full', $vars);
	return;
}

$icon = '';
$editor = $entity->getLastEditor();
if ($editor) {
	$icon = elgg_view_entity_icon($editor, 'tiny');
}

$metadata = elgg_view_menu('entity', [
	'entity' => $entity,
	'handler' => 'static',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
]);

$excerpt = elgg_get_excerpt($entity->description);

$params = [
	'entity' => $entity,
	'metadata' => $metadata,
	'subtitle' => elgg_view('static/by_line', $vars),
	'content' => $excerpt,
];
$params = $params + $vars;
$list_body = elgg_view('object/elements/summary', $params);

echo elgg_view_image_block($icon, $list_body);
