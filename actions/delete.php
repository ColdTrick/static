<?php

$guid = get_input("guid");
if ($guid) {
	$entity = get_entity($guid);
	
	if (elgg_instanceof($entity, "object", "static") && $entity->canEdit()) {
		$entity->delete();
	}
}

forward("static/all");
