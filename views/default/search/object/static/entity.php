<?php

$entity = elgg_extract('entity', $vars);

$editor = $entity->getLastEditor();
if ($editor) {
	$entity->setVolatileData('search_icon', elgg_view_entity_icon($editor, 'small'));
}

echo elgg_view('search/object/entity', $vars);
