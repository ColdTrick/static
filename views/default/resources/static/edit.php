<?php

elgg_gatekeeper();

$guid = (int) elgg_extract('guid', $vars);
$body_vars = [];
$sidebar = '';
$page_owner = elgg_get_page_owner_entity();
$site = elgg_get_site_entity();

if (!($page_owner instanceof ElggGroup)) {
	elgg_set_page_owner_guid($site->getGUID());
	$page_owner = $site;
}
$body_vars['owner'] = $page_owner;

elgg_push_breadcrumb(elgg_echo('static:all'), 'static/all');

$ia = elgg_set_ignore_access(true);
if ($guid) {
	
	elgg_entity_gatekeeper($guid, 'object', 'static');
	
	$entity = get_entity($guid);
	
	if (!$entity->canEdit()) {
		elgg_set_ignore_access($ia);
		forward(REFERER);
	}
	
	$body_vars['entity'] = $entity;
	
	elgg_set_page_owner_guid($entity->getOwnerGUID());
	$page_owner = elgg_get_page_owner_entity();
	$body_vars['owner'] = $page_owner;
	
	$sidebar = elgg_view('static/sidebar/revisions', [
		'entity' => $entity,
	]);
}

if ($page_owner instanceof ElggGroup) {
	elgg_push_breadcrumb(elgg_echo('static:groups:title'), "static/group/{$page_owner->getGUID()}");
}

if (!empty($entity)) {
	elgg_push_breadcrumb($entity->title, $entity->getURL());
}

$body = elgg_view_form('static/edit', [
	'class' => 'elgg-form-alt',
	'enctype' => 'multipart/form-data',
], $body_vars);

$title_text = elgg_echo('static:edit');

// build page
$body = elgg_view_layout('one_sidebar', [
	'title' => $title_text,
	'content' => $body,
	'sidebar' => $sidebar,
]);

elgg_set_ignore_access($ia);

// draw page
echo elgg_view_page($title_text, $body);
