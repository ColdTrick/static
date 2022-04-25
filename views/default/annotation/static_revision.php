<?php

/* @var $annotation ElggAnnotation */
$annotation = elgg_extract('annotation', $vars);

$owner = $annotation->getOwnerEntity();
if (!$owner instanceof \ElggEntity) {
	return true;
}

$output = elgg_view_entity_url($owner);

$output .= elgg_format_element('span', ['class' => 'mls'], elgg_view_friendly_time($annotation->time_created));

echo elgg_format_element('div', [], $output);
