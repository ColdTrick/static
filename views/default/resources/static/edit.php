<?php

use Elgg\Exceptions\Http\EntityPermissionsException;

$body = '';
$sidebar = '';

elgg_call(ELGG_IGNORE_ACCESS, function() use ($vars, &$body, &$sidebar) {
	
	$guid = (int) elgg_extract('guid', $vars);
	$body_vars = [];
	$page_owner = elgg_get_page_owner_entity();
	$site = elgg_get_site_entity();
	if (!$page_owner instanceof \ElggGroup) {
		elgg_set_page_owner_guid($site->guid);
		$page_owner = $site;
	}
	
	$body_vars['owner'] = $page_owner;

	if ($guid) {
		$entity = get_entity($guid);
		elgg_call(ELGG_ENFORCE_ACCESS, function() use ($entity) {
			if ($entity instanceof \StaticPage && !$entity->canEdit()) {
				throw new EntityPermissionsException();
			}
		});
		
		if ($entity instanceof \StaticPage) {
			// edit
			$body_vars['entity'] = $entity;
			
			elgg_set_page_owner_guid($entity->owner_guid);
			$page_owner = elgg_get_page_owner_entity();
			$body_vars['owner'] = $page_owner;
			
			$sidebar = elgg_view('static/sidebar/revisions', [
				'entity' => $entity,
			]);
		} elseif ($entity instanceof \ElggGroup) {
			// new in group
			elgg_set_page_owner_guid($entity->guid);
			$page_owner = elgg_get_page_owner_entity();
			$body_vars['owner'] = $page_owner;
		}
	}
	
	if (!$entity instanceof \StaticPage && !$page_owner->canWriteToContainer(0, 'object', \StaticPage::SUBTYPE)) {
		throw new EntityPermissionsException();
	}
	
	if ($page_owner instanceof \ElggGroup) {
		elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE, $page_owner);
	} else {
		elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE);
	}
	
	if ($entity instanceof \StaticPage) {
		elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());
	}
	
	$body = elgg_view_form('static/edit', ['sticky_enabled' => true], $body_vars);
});

echo elgg_view_page(elgg_echo('static:edit'), [
	'content' => $body,
	'sidebar' => $sidebar,
	'filter_id' => 'static/edit',
]);
