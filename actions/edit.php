<?php

elgg_make_sticky_form("static");

$guid = (int) get_input("guid");
$owner_guid = (int) get_input("owner_guid");
$parent_guid = (int) get_input("parent_guid");
$title = get_input("title");

$friendly_title = get_input("friendly_title", $title);
$friendly_title = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($friendly_title, ENT_QUOTES, 'UTF-8'));
$friendly_title = elgg_get_friendly_title($friendly_title);

$description = get_input("description");
$access_id = (int) get_input("access_id", ACCESS_PUBLIC);

$enable_comments = get_input("enable_comments");
$moderators = get_input("moderators");

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

if (empty($title) || empty($description)) {
	register_error(elgg_echo("static:action:edit:error:title_description"));
	forward(REFERER);
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

$entity->annotate("static_revision", $description);

elgg_clear_sticky_form("static");
system_message(elgg_echo("static:action:edit:success"));

forward($entity->getURL());
