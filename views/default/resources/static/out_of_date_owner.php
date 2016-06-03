<?php

elgg_gatekeeper();

if (!static_out_of_date_enabled()) {
	forward(REFERER);
}

$page_owner = elgg_get_page_owner_entity();
if (!($page_owner instanceof ElggUser)) {
	register_error(elgg_echo('pageownerunavailable', [elgg_get_page_owner_guid()]));
	forward(REFERER);
}

if (!$page_owner->canEdit()) {
	register_error(elgg_echo('limited_access'));
	forward(REFERER);
}

$dbprefix = elgg_get_config('dbprefix');
$static_revision_id = elgg_get_metastring_id('static_revision');
$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');

$options = [
	'type' => 'object',
	'subtype' => StaticPage::SUBTYPE,
	'limit' => false,
	'modified_time_upper' => time() - ($days * 24 * 60 * 60),
	'wheres' => [
		"e.guid IN (
			SELECT entity_guid
			FROM (
				SELECT *
				FROM (
					SELECT *
					FROM {$dbprefix}annotations
					WHERE name_id = {$static_revision_id}
					ORDER BY entity_guid, time_created DESC) a1
				GROUP BY a1.entity_guid) a2
			WHERE a2.owner_guid = {$page_owner->getGUID()})
		",
	],
	'order_by' => 'e.time_updated DESC',
];

$batch = new \ElggBatch('elgg_get_entities', $options);
$rows = [];
foreach ($batch as $entity) {
	$rows[] = $entity;
}

if (!empty($rows)) {
	$body = elgg_view_entity_list($rows, [
		'item_view' => 'object/static/simple',
	]);
} else {
	$body = elgg_view('output/longtext', ['value' => elgg_echo('static:out_of_date:none')]);
}

$title_text = elgg_echo('static:out_of_date:owner:title', [$page_owner->name]);
$filter = elgg_view('page/layouts/elements/filter');

// build page
$page_data = elgg_view_layout('one_column', [
	'title' => $title_text,
	'content' => $filter . $body,
]);

// draw page
echo elgg_view_page($title_text, $page_data);
