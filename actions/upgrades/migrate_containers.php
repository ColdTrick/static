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

foreach ($batch as $page) {
	$success_count++;

	if ($page->parent_guid) {
		// already converted
		continue;
	}
	
	$container_entity = $page->getContainerEntity();
	if (!($container_entity instanceof \StaticPage)) {
		// probably top page
		$page->parent_guid = 0;
	} else {
		$root = $page->getRootPage();
	
		if ($root instanceof \StaticPage) {
			$page->parent_guid = $page->container_guid;
			$page->container_guid = $root->container_guid;
		} else {
			// edge case where root page is gone... probably an orphaned page
			$page->parent_guid = 0;
		}
	}
	
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
