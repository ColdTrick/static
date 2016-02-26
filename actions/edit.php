<?php

elgg_make_sticky_form('static');

$guid = (int) get_input('guid');
$owner_guid = (int) get_input('owner_guid');
$parent_guid = (int) get_input('parent_guid');
$title = get_input('title');

$friendly_title = get_input('friendly_title', $title);
$friendly_title = static_make_friendly_title($friendly_title, $guid);

$description = get_input('description');
$access_id = (int) get_input('access_id', ACCESS_PUBLIC);

$enable_comments = get_input('enable_comments');
$moderators = get_input('moderators');

$remove_icon = (int) get_input('remove_thumbnail');

if (empty($title) || empty($description)) {
	register_error(elgg_echo('static:action:edit:error:title_description'));
	forward(REFERER);
}

if (empty($friendly_title)) {
	register_error(elgg_echo('static:action:edit:error:friendly_title'));
	forward(REFERER);
}

$owner = get_entity($owner_guid);
if (!elgg_instanceof($owner, 'group')) {
	$owner = elgg_get_site_entity();
}

$can_write = $owner->canWriteToContainer(0, 'object', 'static');
if ($can_write) {
	$ia = elgg_set_ignore_access(true);
}

if ($parent_guid) {
	$parent = get_entity($parent_guid);
	if (!elgg_instanceof($parent, 'object', 'static')) {
		$parent_guid = $owner->getGUID();
	}
} else {
	$parent_guid = $owner->getGUID();
}

if ($can_write) {
	elgg_set_ignore_access($ia);
}

if ($guid) {
	$ia = elgg_set_ignore_access(true);
	$entity = get_entity($guid);
	elgg_set_ignore_access($ia);

	if (!elgg_instanceof($entity, 'object', 'static') || !$entity->canEdit()) {
		forward(REFERER);
	}
}

$new_entity = false;
if (!$entity) {
	$entity = new \StaticPage();
	$entity->owner_guid = $owner->getGUID();
	$entity->container_guid = $parent_guid;
	$entity->access_id = $access_id;
	
	$ia = elgg_set_ignore_access(true);
	if (!$entity->save()) {
		elgg_set_ignore_access($ia);
		
		register_error(elgg_echo('actionunauthorized'));
		forward(REFERER);
	}
	
	elgg_set_ignore_access($ia);
	$new_entity = true;
}

if ($parent_guid !== $entity->getContainerGUID()) {
	// reset order if moved to another parent
	unset($entity->order);
}

// place in the correct tree
$subpage_relationship_guid = false;
if ($parent_guid !== $owner->getGUID()) {
	
	$ia = elgg_set_ignore_access(true);
	$parent = get_entity($parent_guid);
	elgg_set_ignore_access($ia);
	
	if (elgg_instanceof($parent, 'object', 'static')) {
		
		if ($parent->container_guid == $owner->getGUID()) {
			// parent is a top page
			$subpage_relationship_guid = $parent_guid;
		} else {
			// further in the tree, so find out which tree
			$relations = $parent->getEntitiesFromRelationship([
				'type' => 'object',
				'subtype' => 'static',
				'relationship' => 'subpage_of',
				'limit' => 1,
			]);
			
			if ($relations) {
				$subpage_relationship_guid = $relations[0]->getGUID();
			}
		}
		
		if ($subpage_relationship_guid) {
			// remove old tree relationships
			remove_entity_relationships($entity->getGUID(), 'subpage_of');
			
			// add new tree relationship
			$entity->addRelationship($subpage_relationship_guid, 'subpage_of');
		}
	}
}

// check the children for the correct tree
if (!$new_entity) {
	static_check_children_tree($entity, $subpage_relationship_guid);
}

$ia = elgg_set_ignore_access(true);

// save all the content
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
	$entity->removeThumbnail();
} elseif (get_resized_image_from_uploaded_file('thumbnail', 200, 200)) {
	$fh = new \ElggFile();
	$fh->owner_guid = $entity->getGUID();
	
	$prefix = 'thumb';
	$icon_sizes = elgg_get_config('icon_sizes');
	
	if (!empty($icon_sizes)) {
		foreach ($icon_sizes as $size => $info) {
			$fh->setFilename($prefix . $size . '.jpg');
			
			$contents = get_resized_image_from_uploaded_file('thumbnail', $info['w'], $info['h'], $info['square'], $info['upscale']);
			if (!empty($contents)) {
				$fh->open('write');
				$fh->write($contents);
				$fh->close();
			}
		}
		
		$entity->icontime = time();
		$entity->save();
	}
}

$entity->annotate('static_revision', $description);

elgg_set_ignore_access($ia);
elgg_clear_sticky_form('static');
system_message(elgg_echo('static:action:edit:success'));

forward($entity->getURL());
