<?php

$annotation = elgg_extract('annotation', $vars);

$owner = get_entity($annotation->owner_guid);
if (!$owner) {
	return true;
}

$output = elgg_view('output/url', [
	'href' => $owner->getURL(),
	'text' => $owner->name,
]);

$output .= elgg_format_element('span', ['class' => 'elgg-subtext'], elgg_view_friendly_time($annotation->time_created));

echo elgg_format_element('div', [], $output);
