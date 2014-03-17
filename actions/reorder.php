<?php
$guid = get_input("guid");
$order = get_input("order");
if (!empty($guid) && ($parent = get_entity($guid)) && ($parent->getSubtype() == "static") && empty($parent->parent_guid)) {
	if (!empty($order)) {
		$guids = explode(",", $order);
		$order = 1;
		if ($guids) {
			foreach ($guids as $child_guid) {
				if (($child = get_entity($child_guid)) && ($child->getSubtype() == "static")) {
					$child->parent_guid = $guid;
					$child->order = $order;
					$order++;
				}
			}
		}
	}
}
