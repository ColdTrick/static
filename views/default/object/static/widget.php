<?php

$entity = elgg_extract('entity', $vars);

$body = elgg_view('output/url', [
	'text' => $entity->title,
	'href' => $entity->getURL(),
]);

echo elgg_view_image_block('', $body);