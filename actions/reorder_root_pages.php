<?php
$container_guid = (int) get_input('container_guid');
$guids = get_input('ordered_guids');

if (empty($container_guid) || empty($guids)) {
	return;
}

$container_entity = get_entity($container_guid);
if (empty($container_entity)) {
	return;
}

if (!($container_entity instanceof ElggSite) && !($container_entity instanceof ElggGroup)) {
	return;
}

if (!$container_entity->canEdit()) {
	return;
}

$order = 1;
foreach ($guids as $guid) {
	$page_entity = get_entity($guid);
	
	if (!elgg_instanceof($page_entity, 'object', 'static')) {
		continue;
	}

	if ($page_entity->container_guid !== $container_guid) {
		continue;
	}
	
	$page_entity->order = $order;
	
	$order++;
}
