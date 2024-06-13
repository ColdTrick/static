<?php

use Elgg\Database\Clauses\OrderByClause;
use Elgg\Values;
use Elgg\Exceptions\Http\PageNotFoundException;

if (!static_out_of_date_enabled()) {
	throw new PageNotFoundException();
}

$page_owner = elgg_get_page_owner_entity();

elgg_group_tool_gatekeeper('static', $page_owner->guid);

elgg_push_collection_breadcrumbs('object', \StaticPage::SUBTYPE, $page_owner);

$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');

echo elgg_view_page(elgg_echo('static:out_of_date:title'), [
	'content' => elgg_list_entities([
		'type' => 'object',
		'subtype' => \StaticPage::SUBTYPE,
		'container_guid' => $page_owner->guid,
		'modified_time_upper' => Values::normalizeTime("-{$days} days"),
		'order_by' => new OrderByClause('e.time_updated', 'DESC'),
		'no_results' => elgg_echo('static:out_of_date:none'),
	]),
	'filter_id' => 'static',
	'filter_value' => 'out_of_date_group',
]);
