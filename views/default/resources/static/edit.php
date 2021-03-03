<?php

use Elgg\EntityPermissionsException;

elgg_gatekeeper();

$title_text = elgg_echo('static:edit');

$body = '';
$sidebar = '';

elgg_call(ELGG_IGNORE_ACCESS, function() use ($vars, &$body, &$sidebar) {
	
	$guid = (int) elgg_extract('guid', $vars);
	$body_vars = [];
	$page_owner = elgg_get_page_owner_entity();
	$site = elgg_get_site_entity();
	if (!$page_owner instanceof ElggGroup) {
		elgg_set_page_owner_guid($site->guid);
		$page_owner = $site;
	}
	$body_vars['owner'] = $page_owner;

	if ($guid) {
		$entity = get_entity($guid);
		if ($entity instanceof StaticPage && !$entity->canEdit()) {
			throw new EntityPermissionsException();
		}
		
		if ($entity instanceof StaticPage) {
			// edit
			$body_vars['entity'] = $entity;
			
			elgg_set_page_owner_guid($entity->owner_guid);
			$page_owner = elgg_get_page_owner_entity();
			$body_vars['owner'] = $page_owner;
			
			$sidebar = elgg_view('static/sidebar/revisions', [
				'entity' => $entity,
			]);
		} elseif ($entity instanceof ElggGroup) {
			// new in group
			elgg_set_page_owner_guid($entity->guid);
			$page_owner = elgg_get_page_owner_entity();
			$body_vars['owner'] = $page_owner;
		}
	}
	
	if ($page_owner instanceof ElggGroup) {
		elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE, $page_owner);
	} else {
		elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE);
	}
	
	if ($entity instanceof StaticPage) {
		elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());
	}
	
	$body = elgg_view_form('static/edit', [
		'class' => 'elgg-form-alt',
		'enctype' => 'multipart/form-data',
		'prevent_double_submit' => true,
	], $body_vars);
});

echo elgg_view_page($title_text, [
	'content' => $body,
	'sidebar' => $sidebar,
]);
