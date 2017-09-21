<?php

/* @var $entity StaticPage */
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

// out of date message
$body = elgg_view('static/out_of_date', $vars);

// icon
if ($entity->hasIcon('large')) {
	$body .= elgg_view_entity_icon($entity, 'large', [
		'href' => false,
		'class' => 'float-alt',
		'img_attr' => [
			'data-highres-url' => $entity->getIconURL(['size' => 'master']),
		],
	]);
}
// description
$body .= elgg_view('output/longtext', ['value' => $entity->description]);

echo elgg_view('object/elements/full', [
	'entity' => $entity,
	'summary' => $summary,
	'body' => $body,
]);
