<?php

$guid = get_input("guid");
if ($guid) {
	$entity = get_entity($guid);
	
	$container = $entity->getContainerEntity();
	
	if (elgg_instanceof($entity, "object", "static") && $entity->canEdit()) {
		$entity->delete();
	}
	
	if ($container instanceof ElggGroup) {
		forward("static/group/" . $container->guid);
	}
}

forward("static/all");
