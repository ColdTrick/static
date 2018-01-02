<?php
/**
 * Delete orphaned static pages
 */

$success_count = 0;
$error_count = 0;

if (get_input('upgrade_completed')) {
	// set the upgrade as completed
	$factory = new \ElggUpgrade();
	$upgrade = $factory->getUpgradeFromPath('admin/upgrades/static/delete_orphaned_children');
	if ($upgrade instanceof \ElggUpgrade) {
		$upgrade->setCompleted();
	}
	return true;
}

$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

$dbprefix = elgg_get_config('dbprefix');
$name_id = elgg_get_metastring_id('parent_guid');
$subtype_id = get_subtype_id('object', StaticPage::SUBTYPE);

$batch = new \ElggBatch('elgg_get_entities', [
	'type' => 'object',
	'subtype' => \StaticPage::SUBTYPE,
	'limit' => 50,
	'offset' => (int) get_input('offset', 0),
	'wheres' => [
		"e.guid IN (
			SELECT md.entity_guid
			FROM {$dbprefix}metadata md
			JOIN {$dbprefix}metastrings msv ON md.value_id = msv.id
			WHERE md.name_id = {$name_id}
			AND msv.string NOT IN (
				SELECT guid
				FROM {$dbprefix}entities
				WHERE type = 'object'
				AND subtype = {$subtype_id}
			)
			AND msv.string != '0'
		)"
	],
]);

$batch->setIncrementOffset(false);

/* @var $page \StaticPage */
foreach ($batch as $page) {
	
	if ($page->delete()) {
		$success_count++;
	} else {
		$error_count++;
	}
}

access_show_hidden_entities($access_status);

// cached menus should rebuild
elgg_flush_caches();

// Give some feedback for the UI
echo json_encode([
	'numSuccess' => $success_count,
	'numErrors' => $error_count,
]);
