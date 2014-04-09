<?php

$guid = (int) get_input("guid");
$parent_guid = (int) get_input("parent_guid");
$title = get_input("title");

$friendly_title = get_input("friendly_title", $title);
$friendly_title = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($friendly_title, ENT_QUOTES, 'UTF-8'));
$friendly_title = elgg_get_friendly_title($friendly_title);

$description = get_input("description");
$access_id = get_input("access_id", ACCESS_PUBLIC);

if ($guid) {
	$content = get_entity($guid);
	if (empty($content) || ($content->getSubtype() !== "static") || !($content->canEdit())) {
		forward(REFERER);
	} else {
		if (!empty($title)) {
			$content->title = $title;
		}
		$content->description = $description;
		$content->access_id = $access_id;
		$content->save();

		if (!empty($parent_guid)) {
			if ($parent_guid !== $content->parent_guid) {
				unset($content->order);
			}
			$content->parent_guid = $parent_guid;
		} else {
			unset($content->parent_guid);
			unset($content->order);
		}

		$content->friendly_title = $friendly_title;
		forward($content->getURL());
	}

} else {
	if (!empty($title)) {
		$site = elgg_get_site_entity();
		
		$content = new ElggObject();
		$content->subtype = "static";
		$content->access_id = $access_id;
		$content->owner_guid = $site->getGUID();
		$content->container_guid = $site->getGUID();
		$content->title = $title;
		$content->description = $description;
		$content->parent_guid = $parent_guid;
		$content->friendly_title = $friendly_title;
		$content->save();

		forward($content->getURL());
	} else {
		forward(REFERER);
	}
}
