<?php

// Upgrade also possible hidden entities. This feature get run
// by an administrator so there's no need to ignore access.
$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

$dbprefix = elgg_get_config('dbprefix');
$name_id = elgg_get_metastring_id('parent_guid');
$subtype_id = get_subtype_id('object', StaticPage::SUBTYPE);

$count = elgg_get_entities([
	'type' => 'object',
	'subtype' => \StaticPage::SUBTYPE,
	'count' => true,
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

echo elgg_view('output/longtext', [
	'value' => elgg_echo('admin:upgrades:static:delete_orphaned_children:description'),
]);

echo elgg_view('admin/upgrades/view', [
	'count' => $count,
	'action' => 'action/static/upgrades/delete_orphaned_children',
]);
access_show_hidden_entities($access_status);
