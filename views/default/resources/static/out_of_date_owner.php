<?php

use Elgg\EntityPermissionsException;
use Elgg\PageNotFoundException;
use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\OrderByClause;

elgg_gatekeeper();
elgg_entity_gatekeeper(elgg_get_page_owner_guid(), 'user');

if (!static_out_of_date_enabled()) {
	throw new PageNotFoundException();
}

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner->canEdit()) {
	throw new EntityPermissionsException();
}

$dbprefix = elgg_get_config('dbprefix');
$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');

$options = [
	'type' => 'object',
	'subtype' => StaticPage::SUBTYPE,
	'modified_time_upper' => time() - ($days * 24 * 60 * 60),
	'wheres' => [
		"e.guid IN (
			SELECT entity_guid
			FROM (
				SELECT *
				FROM (
					SELECT *
					FROM {$dbprefix}annotations
					WHERE name = 'static_revision'
					ORDER BY entity_guid, time_created DESC) a1
				GROUP BY a1.entity_guid) a2
			WHERE a2.owner_guid = {$page_owner->guid})
		",
	],
	'order_by' => new OrderByClause('e.time_updated', 'DESC'),
	'no_results' => elgg_echo('static:out_of_date:none'),
];

$title_text = elgg_echo('static:out_of_date:owner:title', [$page_owner->getDisplayName()]);

$body = elgg_list_entities($options);

// build page
$page_data = elgg_view_layout('one_column', [
	'title' => $title_text,
	'content' => $body,
	'filter_id' => 'static',
]);

// draw page
echo elgg_view_page($title_text, $page_data);
