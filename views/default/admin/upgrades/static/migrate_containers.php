<?php

// Upgrade also possible hidden entities. This feature get run
// by an administrator so there's no need to ignore access.
$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

$dbprefix = elgg_get_config('dbprefix');
$name_id = elgg_get_metastring_id('parent_guid');
$count = elgg_get_entities([
	'type' => 'object',
	'subtype' => \StaticPage::SUBTYPE,
	'count' => true,
	'wheres' => [
		"e.guid NOT IN (SELECT entity_guid FROM {$dbprefix}metadata WHERE name_id = {$name_id})"
	],
]);

echo elgg_view('output/longtext', ['value' => elgg_echo('admin:upgrades:static:migrate_containers:description')]);

echo elgg_view('admin/upgrades/view', [
	'count' => $count,
	'action' => 'action/static/upgrades/migrate_containers',
]);
access_show_hidden_entities($access_status);