<?php
$guid = get_input("guid");
$guids = get_input("order");

if (!empty($guid) && ($parent = get_entity($guid)) && ($parent->getSubtype() == "static") && empty($parent->parent_guid)) {
	if (!empty($guids) && $parent->canEdit()) {
		$order = 1;
		foreach ($guids as $child_guid) {
			if (($child = get_entity($child_guid)) && ($child->getSubtype() == "static")) {
				$child->container_guid = $guid;
				$child->order = $order;
				$child->save();
				$order++;
			}
		}
	}
}
