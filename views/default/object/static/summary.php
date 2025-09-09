<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \StaticPage) {
	return;
}

$last_editor = $entity->getLastEditor();

$params = [
	'icon' => true,
	'icon_entity' => $last_editor,
	'byline_owner_entity' => $last_editor,
	'content' => elgg_get_excerpt((string) $entity->description),
];
$params = $params + $vars;

echo elgg_view('object/elements/summary', $params);
