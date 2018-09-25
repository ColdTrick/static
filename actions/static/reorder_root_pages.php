<?php

$container_guid = (int) get_input('container_guid');
$guids = get_input('ordered_guids');

if (empty($container_guid) || empty($guids)) {
	return elgg_error_response();
}

$container_entity = get_entity($container_guid);
if (empty($container_entity)) {
	return elgg_error_response();
}

if (!($container_entity instanceof ElggSite) && !($container_entity instanceof ElggGroup)) {
	return elgg_error_response();
}

if (!$container_entity->canEdit()) {
	return elgg_error_response();
}

$order = 1;
foreach ($guids as $guid) {
	$page_entity = get_entity($guid);
	
	if (!$page_entity instanceof StaticPage) {
		continue;
	}

	if ($page_entity->container_guid !== $container_guid) {
		continue;
	}
	
	$page_entity->order = $order;
	
	$order++;
}

return elgg_ok_response();
