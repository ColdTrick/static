<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof StaticPage) {
	return;
}
$newsletter = elgg_extract('newsletter', $vars);
$container = $entity->getContainerEntity();

// data for embedding
$data = [
	'data-title' => $entity->getDisplayName(),
	'data-description' => $entity->description,
	'data-url' => $entity->getURL(),
];

// excerpt support
$excerpt = $entity->excerpt;
if (empty($excerpt)) {
	$excerpt = elgg_get_excerpt($entity->description);
}
if (!empty($excerpt)) {
	$data['data-excerpt'] = $excerpt;
}

// icon support
if ($entity->hasIcon('large')) {
	$data['data-icon-url'] = $entity->getIconURL('large');
}

// subtitle
$subtitle = [elgg_echo('item:object:' . $entity->getSubtype())];

if ($container instanceof ElggGroup) {
	$subtitle[] = elgg_echo('river:ingroup', [$container->getDisplayName()]);
}

// build listing view
$params = [
	'entity' => $entity,
	'title' => $entity->getDisplayName(),
	'subtitle' => implode(' ', $subtitle),
	'tags' => false,
	'content' => $excerpt,
];

echo elgg_format_element('div', $data, elgg_view('object/elements/summary', $params));
