<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof StaticPage) {
	return;
}

echo elgg_view_image_block('', elgg_view_entity_url($entity));
