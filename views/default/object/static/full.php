<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \StaticPage) {
	return;
}

$last_editor = $entity->getLastEditor();

$body = elgg_view('static/out_of_date', $vars);
$body .= elgg_view('output/longtext', ['value' => $entity->description]);

$params = [
	'show_summary' => true,
	'body' => $body,
	'icon_entity' => $last_editor,
	'byline_owner_entity' => $last_editor,
];
$params = $params + $vars;

echo elgg_view('object/elements/full', $params);
