<?php

$entity = elgg_extract('entity', $vars);

$metadata = elgg_view_menu('entity', [
	'entity' => $entity,
	'handler' => 'static',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz alliander-theme-menu-static',
]);

$summary = elgg_view('object/elements/summary', [
	'entity' => $entity,
	'title' => false,
	'metadata' => $metadata,
	'tags' => false,
]);

$body = '';
if ($entity->icontime) {
	$body .= elgg_view_entity_icon($entity, 'large', [
		'href' => false,
		'class' => 'float-alt',
	]);
}
$body .= elgg_view('output/longtext', ['value' => $entity->description]);

echo elgg_view('object/elements/full', [
	'summary' => $summary,
	'body' => $body,
]);