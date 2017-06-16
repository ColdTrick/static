<?php

$guid = (int) get_input('guid');
$guids = get_input('order');
if (empty($guids)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// fetch the entity with access ignored (for moderators)
$ia = elgg_set_ignore_access(true);
$parent = get_entity($guid);
elgg_set_ignore_access($ia);

if (!($parent instanceof \StaticPage) || !$parent->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

// ignore access (for moderators)
$ia = elgg_set_ignore_access(true);

$order = 1;
foreach ($guids as $child_guid) {
	$child = get_entity($child_guid);
	if (!($child instanceof \StaticPage)) {
		continue;
	}
	
	$child->parent_guid = $guid;
	$child->order = $order;

	$child->save();
	$order++;
}

// restore access
elgg_set_ignore_access($ia);

// clear menu cache
$parent->getRootPage()->clearMenuCache();

return elgg_ok_response();
