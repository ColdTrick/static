<?php

$guid = get_input("guid");
if ($guid) {
	
	$ia = elgg_set_ignore_access(true);
	$entity = get_entity($guid);
	elgg_set_ignore_access($ia);
	
	$container = $entity->getContainerEntity();
	
	if (elgg_instanceof($entity, "object", "static") && $entity->canEdit()) {
		$entity->delete();
	}
	
	if ($container instanceof ElggGroup) {
		forward("static/group/" . $container->guid);
	}
}

forward("static/all");
