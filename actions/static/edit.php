<?php

$guid = (int) get_input('guid');
$owner_guid = (int) get_input('owner_guid'); // site or group
$parent_guid = (int) get_input('parent_guid');

$values = [];
$fields = elgg()->fields->get('object', \StaticPage::SUBTYPE);
foreach ($fields as $field) {
	$value = null;
	
	$name = elgg_extract('name', $field);
	switch ($name) {
		case 'title':
			$value = elgg_get_title_input();
			break;
		case 'friendly_title':
			$value = get_input($name, elgg_get_title_input());
			$value = static_make_friendly_title($value, $guid);
			break;
		case 'parent_guid':
			continue(2);
		default:
			$value = get_input($name);
			break;
	}
	
	if (elgg_extract('required', $field) && elgg_is_empty($value)) {
		if (in_array($name, ['title', 'description'])) {
			return elgg_error_response(elgg_echo('static:action:edit:error:title_description'));
		} elseif ($name === 'friendly_title') {
			return elgg_error_response(elgg_echo('static:action:edit:error:friendly_title'));
		}
		
		return elgg_error_response(elgg_echo('error:missing_data'));
	}
	
	if ($field['#type'] === 'tags') {
		$value = elgg_string_to_array((string) $value);
	}
	
	$values[$name] = $value;
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

$entity = null;
if (!empty($guid)) {
	$entity = elgg_call(ELGG_IGNORE_ACCESS, function () use ($guid){
		return get_entity($guid);
	});

	if (!$entity instanceof \StaticPage || !$entity->canEdit()) {
		return elgg_error_response(elgg_echo('actionunauthorized'));
	}
}

$new_entity = false;
if (!$entity instanceof \StaticPage) {
	$entity = new \StaticPage();
	$entity->owner_guid = $owner->guid;
	$entity->container_guid = $owner->guid;
	
	// new child static pages should go on top, root pages are last
	$entity->order = $parent ? -time() : time();
	
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

elgg_call(ELGG_IGNORE_ACCESS, function() use ($entity, $values, $parent_guid) {
	$entity->parent_guid = $parent_guid;
	
	// save all the content
	foreach ($values as $name => $value) {
		if ($name === 'description') {
			$entity->annotate('static_revision', $value);
		}
		
		$entity->{$name} = $value;
	}
	
	$entity->save();
	
	if (get_input('header_remove')) {
		$entity->deleteIcon('header');
	} else {
		$entity->saveIconFromUploadedFile('header', 'header');
	}
});

// Need to subscribe for future updates as owner is not a user
$entity->addSubscription();

return elgg_ok_response('', elgg_echo('static:action:edit:success'), $entity->getURL());
