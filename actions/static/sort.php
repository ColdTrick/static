<?php

$group = get_entity((int) get_input('container_guid'));
if (!$group instanceof \ElggGroup) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if (!$group->canEdit() || !$group->canWriteToContainer(0, 'object', \StaticPage::SUBTYPE)) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

$guids = get_input('guids');
if (empty($guids)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$order = 1;
foreach ($guids as $guid) {
	$entity = get_entity((int) $guid);
	if (!$entity instanceof \StaticPage) {
		continue;
	}

	if ($entity->container_guid !== $group->guid) {
		continue;
	}
	
	if ($entity->parent_guid !== 0) {
		continue;
	}

	$entity->order = $order;

	$order++;
}

return elgg_ok_response();
