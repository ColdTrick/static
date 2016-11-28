<?php

$guid = (int) get_input('guid');
$guids = get_input('order');

if (empty($guids)) {
	return;
}

$parent = get_entity($guid);
if (!($parent instanceof \StaticPage)) {
	return;
}

if (!$parent->canEdit()) {
	return;
}

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

$parent->getRootPage()->clearMenuCache();
