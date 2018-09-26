<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof StaticPage) {
	return;
}

$body = elgg_view('output/url', [
	'text' => $entity->getDisplayName(),
	'href' => $entity->getURL(),
]);

echo elgg_view_image_block('', $body);
