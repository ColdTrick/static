<?php

$guid = (int) get_input('guid');
$guids = get_input('order');
if (empty($guids)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// fetch the entity with access ignored (for moderators)
$parent = elgg_call(ELGG_IGNORE_ACCESS, function () use ($guid) {
	return get_entity($guid);
});

if (!$parent instanceof \StaticPage || !$parent->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

// ignore access (for moderators)
elgg_call(ELGG_IGNORE_ACCESS, function () use ($guids, $guid) {
	$order = 1;
	foreach ($guids as $child_guid) {
		$child = get_entity($child_guid);
		if (!$child instanceof \StaticPage) {
			continue;
		}
		
		$child->parent_guid = $guid;
		$child->order = $order;
	
		$order++;
	}
});

// clear menu cache
$parent->getRootPage()->clearMenuCache();

return elgg_ok_response();
