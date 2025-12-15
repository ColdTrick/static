<?php

use Elgg\Exceptions\Http\EntityNotFoundException;
use Elgg\Exceptions\Http\EntityPermissionsException;

$group = get_entity((int) get_input('container_guid'));
if (!$group instanceof \ElggGroup) {
	throw new EntityNotFoundException();
}

if (!$group->canEdit() || !$group->canWriteToContainer(0, 'object', \StaticPage::SUBTYPE)) {
	throw new EntityPermissionsException();
}

$guids = get_input('guids');
if (empty($guids)) {
	throw new EntityNotFoundException();
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
