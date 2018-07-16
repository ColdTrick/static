<?php

$guid = (int) get_input('guid');
if (empty($guid)) {
	forward('static/all');
}
	
$ia = elgg_set_ignore_access(true);
$entity = get_entity($guid);
elgg_set_ignore_access($ia);

if (elgg_instanceof($entity, 'object', 'static') && $entity->canEdit()) {
	$entity->delete();
}

$container = $entity->getContainerEntity();
if ($container instanceof ElggGroup) {
	forward('static/group/' . $container->guid);
}

forward('static/all');
