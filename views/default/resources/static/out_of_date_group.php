<?php

use Elgg\PageNotFoundException;
use Elgg\EntityPermissionsException;
use Elgg\Database\Clauses\OrderByClause;

elgg_gatekeeper();
elgg_entity_gatekeeper(elgg_get_page_owner_guid(), 'group');

if (!static_out_of_date_enabled()) {
	throw new PageNotFoundException();
}

elgg_group_tool_gatekeeper('static', elgg_get_page_owner_guid());

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner->canEdit()) {
	throw new EntityPermissionsException();
}

$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');

$options = [
	'type' => 'object',
	'subtype' => StaticPage::SUBTYPE,
	'container_guid' => $page_owner->guid,
	'modified_time_upper' => time() - ($days * 24 * 60 * 60),
	'order_by' => new OrderByClause('e.time_updated', 'DESC'),
	'no_results' => elgg_echo('static:out_of_date:none'),
];

$title_text = elgg_echo('static:out_of_date:title');

$body = elgg_list_entities($options);

// build page
$page_data = elgg_view_layout('content', [
	'title' => $title_text,
	'content' => $body,
	'filter_id' => 'static',
]);

// draw page
echo elgg_view_page($title_text, $page_data);
