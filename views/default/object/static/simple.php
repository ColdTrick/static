<?php
$entity = elgg_extract('entity', $vars);

$list_body = elgg_view_menu('entity', [
	'entity' => $entity,
	'handler' => 'static',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
]);

$list_body .= elgg_view('output/url', [
	'text' => $entity->title,
	'href' => $entity->getURL(),
]);

echo elgg_view_image_block('', $list_body);