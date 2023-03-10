<?php

elgg_make_sticky_form('static');

$guid = (int) get_input('guid');
$owner_guid = (int) get_input('owner_guid'); // site or group
$parent_guid = (int) get_input('parent_guid');
$title = get_input('title', '');

$friendly_title = get_input('friendly_title', $title);
$friendly_title = static_make_friendly_title($friendly_title, $guid);

$description = get_input('description');
$access_id = (int) get_input('access_id', ACCESS_PUBLIC);

$enable_comments = get_input('enable_comments');
$moderators = get_input('moderators');

if (empty($title) || empty($description)) {
	return elgg_error_response(elgg_echo('static:action:edit:error:title_description'));
}

if (empty($friendly_title)) {
	return elgg_error_response(elgg_echo('static:action:edit:error:friendly_title'));
}

$owner = get_entity($owner_guid);
if (!$owner instanceof \ElggGroup) {
	$owner = elgg_get_site_entity();
}

if ($guid === $parent_guid) {
	// can't link to self
	$parent_guid = 0;
}

$parent = elgg_call(ELGG_IGNORE_ACCESS, function () use ($parent_guid){
	return get_entity($parent_guid);
});

if (!$parent instanceof \StaticPage) {
	$parent_guid = 0;
	$parent = false;
}

$entity = false;
if ($guid) {
	$entity = elgg_call(ELGG_IGNORE_ACCESS, function () use ($guid){
		return get_entity($guid);
	});

	if (!$entity instanceof \StaticPage || !$entity->canEdit()) {
		return elgg_error_response();
	}
}

$new_entity = false;
if (!$entity) {
	$entity = new \StaticPage();
	$entity->owner_guid = $owner->guid;
	$entity->container_guid = $owner->guid;
	$entity->access_id = $access_id;
	
	// new static pages should go on top
	$entity->order = -time();
	
	$saved = elgg_call(ELGG_IGNORE_ACCESS, function () use (&$entity) {
		return $entity->save();
	});
	
	if (!$saved) {
		return elgg_error_response(elgg_echo('actionunauthorized'));
	}
	
	$entity->parent_guid = $parent_guid;
	
	$new_entity = true;
}

$parent_changed = false;
if ($parent_guid !== $entity->parent_guid) {
	// reset order if moved to another parent
	unset($entity->order);
	$parent_changed = true;
	
	// remove old tree relationships
	$entity->removeAllRelationships('subpage_of');
}
	
if (($new_entity || $parent_changed) && $parent instanceof \StaticPage) {
	// add new tree relationship
	$entity->addRelationship($parent->getRootPage()->guid, 'subpage_of');
}

// check the children for the correct tree
if ($parent_changed) {
	if ($parent) {
		static_check_children_tree($entity, $parent->getRootPage()->guid);
	} else {
		static_check_children_tree($entity);
	}
}

// validate friendly title for existing entities if changed
if (!$new_entity && ($entity->friendly_title !== $friendly_title)) {
	$friendly_title = static_make_friendly_title($friendly_title, $guid);
	if (empty($friendly_title)) {
		return elgg_error_response(elgg_echo('static:action:edit:error:friendly_title'));
	}
}

elgg_call(ELGG_IGNORE_ACCESS, function() use ($entity, $title, $description, $access_id, $parent_guid, $friendly_title, $enable_comments, $moderators) {
	// save all the content
	$entity->title = $title;
	$entity->description = $description;
	$entity->access_id = $access_id;
	
	$entity->parent_guid = $parent_guid;
	$entity->friendly_title = $friendly_title;
	$entity->enable_comments = $enable_comments;
	$entity->moderators = $moderators;
	
	$entity->save();
	
	if (get_input('header_remove')) {
		$entity->deleteIcon('header');
	} else {
		$entity->saveIconFromUploadedFile('header', 'header');
	}
	
	$entity->annotate('static_revision', $description);
});

elgg_clear_sticky_form('static');

// Need to subscribe for future updates as owner is not a user
$entity->addSubscription();

return elgg_ok_response('', elgg_echo('static:action:edit:success'), $entity->getURL());
