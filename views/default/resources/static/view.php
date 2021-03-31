<?php

use Elgg\EntityNotFoundException;
use Elgg\EntityPermissionsException;

$guid = (int) elgg_extract('guid', $vars);

$entity = elgg_call(ELGG_IGNORE_ACCESS, function () use ($guid) {
	return get_entity($guid);
});
if (!$entity instanceof StaticPage) {
	throw new EntityNotFoundException();
}

if (!has_access_to_entity($entity) && !$entity->canEdit()) {
	throw new EntityPermissionsException();
}

// since static has 'magic' URLs make sure context is correct
elgg_set_context('static');

if ($entity->canEdit()) {
	elgg_register_menu_item('title', [
		'name' => 'edit',
		'icon' => 'edit',
		'href' => elgg_generate_entity_url($entity, 'edit'),
		'text' => elgg_echo('edit'),
		'link_class' => 'elgg-button elgg-button-action',
	]);
		
	elgg_register_menu_item('title', [
		'name' => 'create_subpage',
		'icon' => 'plus',
		'text' => elgg_echo('static:add:subpage'),
		'href' => elgg_generate_url('add:object:static', [
			'guid' => $entity->owner_guid,
			'parent_guid' => $entity->guid,
		]),
		'link_class' => 'elgg-button elgg-button-action',
	]);
}

// page owner (for groups)
$owner = $entity->getOwnerEntity();
if ($owner instanceof ElggGroup) {
	elgg_set_page_owner_guid($owner->guid);
	
	elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE, $owner);
} else {
	elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE);
}

// show breadcrumb
elgg_call(ELGG_IGNORE_ACCESS, function() use ($entity) {
	$parent_entity = $entity->getParentPage();
	if (!$parent_entity) {
		return;
	}
	
	$parents = [];
	while ($parent_entity) {
		$parents[] = $parent_entity;
		$parent_entity = $parent_entity->getParentPage();
	}
	
	// correct order
	$parents = array_reverse($parents);
	/* @var $parent StaticPage */
	foreach ($parents as $parent) {
		elgg_push_breadcrumb($parent->getDisplayName(), $parent->getURL());
	}
});

elgg_push_breadcrumb($entity->getDisplayName());

$ignore_access = $entity->canEdit() ? ELGG_IGNORE_ACCESS : 0;

$body = elgg_call($ignore_access, function() use ($entity) {
	// build sub pages menu
	static_setup_page_menu($entity);
		
	return elgg_view_entity($entity, [
		'full_view' => true,
	]);
});

// draw page
echo elgg_view_page($entity->getDisplayName(), [
	'content' => $body,
	'filter' => false,
	'entity' => $entity,
]);
