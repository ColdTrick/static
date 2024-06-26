<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \StaticPage) {
	return;
}

$last_editor = $entity->getLastEditor();

if (elgg_extract('full_view', $vars)) {
	// out of date message
	$body = elgg_view('static/out_of_date', $vars);
	
	// description
	$body .= elgg_view('output/longtext', ['value' => $entity->description]);

	$params = [
		'show_summary' => true,
		'body' => $body,
		'icon_entity' => $last_editor,
		'byline_owner_entity' => $last_editor,
	];
	$params = $params + $vars;
	
	echo elgg_view('object/elements/full', $params);
} else {
	// brief view
	$params = [
		'icon' => true,
		'icon_entity' => $last_editor,
		'byline_owner_entity' => $last_editor,
		'content' => elgg_get_excerpt((string) $entity->description),
	];
	$params = $params + $vars;
	
	echo elgg_view('object/elements/summary', $params);
}
