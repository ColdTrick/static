<?php

elgg_make_sticky_form("static");

$guid = (int) get_input("guid");
$owner_guid = (int) get_input("owner_guid");
$parent_guid = (int) get_input("parent_guid");
$title = get_input("title");

$friendly_title = get_input("friendly_title", $title);
$friendly_title = static_make_friendly_title($friendly_title, $guid);

$description = get_input("description");
$access_id = (int) get_input("access_id", ACCESS_PUBLIC);

$enable_comments = get_input("enable_comments");
$moderators = get_input("moderators");

$remove_icon = (int) get_input("remove_thumbnail");

if (empty($title) || empty($description)) {
	register_error(elgg_echo("static:action:edit:error:title_description"));
	forward(REFERER);
}

if (empty($friendly_title)) {
	register_error(elgg_echo("static:action:edit:error:friendly_title"));
	forward(REFERER);
}

$owner = get_entity($owner_guid);
if (!elgg_instanceof($owner, "group")) {
	$owner = elgg_get_site_entity();
}

if ($parent_guid) {
	$parent = get_entity($parent_guid);
	if (!elgg_instanceof($parent, "object", "static")) {
		$parent_guid = $owner->getGUID();
	}
} else {
	$parent_guid = $owner->getGUID();
}

if ($guid) {
	$entity = get_entity($guid);

	if (!elgg_instanceof($entity, "object", "static") || !($entity->canEdit())) {
		forward(REFERER);
	}
}

if (!$entity) {
	$entity = new ElggObject();
	$entity->subtype = "static";
	$entity->owner_guid = $owner->getGUID();
	$entity->container_guid = $parent_guid;
	$entity->access_id = $access_id;
	
	if (!$entity->save()) {
		register_error(elgg_echo("actionunauthorized"));
		forward(REFERER);
	}
}

if ($parent_guid !== $entity->container_guid) {
	// reset order if moved to another parent
	unset($entity->order);
}

if ($parent_guid !== $owner->getGUID()) {
	$parent = get_entity($parent_guid);
	if (elgg_instanceof($parent, "object", "static")) {
		if ($parent->container_guid == $owner->getGUID()) {
			$subpage_relationship_guid = $parent_guid;
		} else {
			$relations = $parent->getEntitiesFromRelationship(array("relation" => "subpage_of", "limit" => 1));
			if ($relations) {
				$subpage_relationship_guid = $relations[0]->getGUID();
			}
		}
		if ($subpage_relationship_guid) {
			$entity->addRelationship($subpage_relationship_guid, "subpage_of");
		}
	}
}

$entity->title = $title;
$entity->description = $description;
$entity->access_id = $access_id;
$entity->container_guid = $parent_guid;

$entity->friendly_title = $friendly_title;
$entity->enable_comments = $enable_comments;
$entity->moderators = $moderators;
$entity->save();

// icon
if ($remove_icon) {
	static_remove_thumbnail($entity->getGUID());
} elseif (get_resized_image_from_uploaded_file("thumbnail", 200, 200)) {
	$fh = new ElggFile();
	$fh->owner_guid = $entity->getGUID();
	
	$prefix = "thumb";
	$icon_sizes = elgg_get_config("icon_sizes");
	
	if (!empty($icon_sizes)) {
		foreach ($icon_sizes as $size => $info) {
			$fh->setFilename($prefix . $size . ".jpg");
			
			$contents = get_resized_image_from_uploaded_file("thumbnail", $info["w"], $info["h"], $info["square"], $info["upscale"]);
			if (!empty($contents)) {
				$fh->open("write");
				$fh->write($contents);
				$fh->close();
			}
		}
		
		$entity->icontime = time();
		$entity->save();
	}
}

$entity->annotate("static_revision", $description);

elgg_clear_sticky_form("static");
system_message(elgg_echo("static:action:edit:success"));

forward($entity->getURL());
