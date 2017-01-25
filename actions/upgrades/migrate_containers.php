<?php
/**
 * Migrate containers of static pages
 */

$success_count = 0;
$error_count = 0;

if (get_input('upgrade_completed')) {
	// set the upgrade as completed
	$factory = new \ElggUpgrade();
	$upgrade = $factory->getUpgradeFromPath('admin/upgrades/static/migrate_containers');
	if ($upgrade instanceof \ElggUpgrade) {
		$upgrade->setCompleted();
	}
	return true;
}

$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

$dbprefix = elgg_get_config('dbprefix');
$name_id = elgg_get_metastring_id('parent_guid');

$batch = new \ElggBatch('elgg_get_entities', [
	'type' => 'object',
	'subtype' => \StaticPage::SUBTYPE,
	'wheres' => [
		"e.guid NOT IN (SELECT entity_guid FROM {$dbprefix}metadata WHERE name_id = {$name_id})"
	],
	'limit' => 50,
]);

$batch->setIncrementOffset(false);

/* @var $page \StaticPage */
foreach ($batch as $page) {
	$success_count++;

	if ($page->parent_guid !== null) {
		// already converted
		continue;
	}
	
	$container_entity = $page->getContainerEntity();
	if (($container_entity instanceof \ElggSite) || ($container_entity instanceof \ElggGroup)) {
		// this is a top page
		$page->parent_guid = 0;
		
		remove_entity_relationships($page->getGUID(), 'subpage_of');
		continue;
	}
	
	$root = $page->getRootPage();
	if (!($container_entity instanceof \StaticPage)) {
		// probably orphaned
		
		if ($root->guid !== $page->guid) {
			// link to root page exists
			$page->parent_guid = $root->guid;
			$page->container_guid = $root->container_guid;
		} else {
			// no root page found...
			$page->parent_guid = 0;
			$page->container_guid = $page->owner_guid;
			
			remove_entity_relationships($page->getGUID(), 'subpage_of');
		}
		
		$page->save();
		
		continue;
	}
	
	if ($root->guid === $page->guid) {
		// parent is known, but there is no root.. moving to top level
		$page->parent_guid = 0;
		$page->container_guid = $page->owner_guid;
		
		remove_entity_relationships($page->getGUID(), 'subpage_of');
		
		$page->save();
		
		continue;
	}

	// all is good, update metadata and attribute
	$page->parent_guid = $page->container_guid;
	$page->container_guid = $root->container_guid;
	
	$page->save();
}

access_show_hidden_entities($access_status);

// cached menus should rebuild
elgg_flush_caches();

// Give some feedback for the UI
echo json_encode([
	'numSuccess' => $success_count,
	'numErrors' => $error_count,
]);
