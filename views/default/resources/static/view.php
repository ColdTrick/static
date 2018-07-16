<?php

$guid = (int) elgg_extract('guid', $vars);

$ia = elgg_set_ignore_access(true);
$entity = get_entity($guid);
elgg_set_ignore_access($ia);

if (!($entity instanceof StaticPage)) {
	forward(REFERER);
}

if (!has_access_to_entity($entity) && !$entity->canEdit()) {
	register_error(elgg_echo('limited_access'));
	forward(REFERER);
}

// since static has 'magic' URLs make sure context is correct
elgg_set_context('static');

if ($entity->canEdit()) {
	elgg_register_menu_item('title', [
		'name' => 'edit',
		'href' => "static/edit/{$entity->getGUID()}",
		'text' => elgg_echo('edit'),
		'link_class' => 'elgg-button elgg-button-action',
	]);
		
	elgg_register_menu_item('title', [
		'name' => 'create_subpage',
		'href' => "static/add/{$entity->getOwnerGUID()}?parent_guid={$entity->getGUID()}",
		'text' => elgg_echo('static:add:subpage'),
		'link_class' => 'elgg-button elgg-button-action',
	]);
}

// page owner (for groups)
$owner = $entity->getOwnerEntity();
if ($owner instanceof ElggGroup) {
	elgg_set_page_owner_guid($owner->getGUID());
}

// show breadcrumb
$ia = elgg_set_ignore_access(true);

$parent_entity = $entity->getParentPage();
if ($parent_entity) {
	while ($parent_entity) {
		elgg_push_breadcrumb($parent_entity->title, $parent_entity->getURL());
		$parent_entity = $parent_entity->getParentPage();
	}
	
	elgg_set_config('breadcrumbs', array_reverse(elgg_get_config('breadcrumbs')));
}
elgg_set_ignore_access($ia);

$ia = elgg_set_ignore_access($entity->canEdit());

// build content
$title = $entity->title;

$body = elgg_view_entity($entity, [
	'full_view' => true,
]);

// build sub pages menu
static_setup_page_menu($entity);

// build page
$page = elgg_view_layout('content', [
	'title' => $title,
	'content' => $body,
	'filter' => false,
	'entity' => $entity,
]);

elgg_set_ignore_access($ia);

// draw page
echo elgg_view_page($title, $page);
