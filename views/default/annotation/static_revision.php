<?php

$annotation = elgg_extract('annotation', $vars);
if (!$annotation instanceof \ElggAnnotation) {
	return;
}

$owner = $annotation->getOwnerEntity();
if (!$owner instanceof \ElggEntity) {
	return;
}

$url = elgg_generate_entity_url($annotation->getEntity(), 'edit', null, ['revision' => $annotation->id]);
$title = elgg_echo('static:revisions:view');
if (!elgg_http_url_is_identical(elgg_get_current_url(), $url)) {
	$title = elgg_view_url($url, $title);
}

$vars['title'] = $title;
$vars['icon'] = false;
$vars['content'] = false;
$vars['metadata'] = false;
$vars['byline'] = true;

echo elgg_view('annotation/default', $vars);
