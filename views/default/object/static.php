<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \StaticPage) {
	return;
}

if (elgg_extract('full_view', $vars)) {
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

	$params = [
		'show_summary' => true,
		'body' => $body,
		'icon_entity' => $entity->getLastEditor(),
		'byline' => false,
	];
	$params = $params + $vars;
	
	echo elgg_view('object/elements/full', $params);
} else {
	// brief view
	$params = [
		'icon' => true,
		'icon_entity' => $entity->getLastEditor(),
		'content' => false,
		'byline' => false,
	];
	$params = $params + $vars;
	echo elgg_view('object/elements/summary', $params);
}
