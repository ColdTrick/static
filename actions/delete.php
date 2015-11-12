<?php

$guid = get_input("guid");
if ($guid) {
	
	$ia = elgg_set_ignore_access(true);
	$entity = get_entity($guid);
	elgg_set_ignore_access($ia);
	
	$container = $entity->getContainerEntity();
	$ia = elgg_set_ignore_access(can_write_to_container(0, $entity->getOwnerGUID(), 'object', 'static'));
	
	if (elgg_instanceof($entity, "object", "static") && $entity->canEdit()) {
		$entity->delete();
	}
	
	elgg_set_ignore_access($ia);
	
	if ($container instanceof ElggGroup) {
		forward("static/group/" . $container->guid);
	}
}

forward("static/all");
