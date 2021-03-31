<?php

use Elgg\PageNotFoundException;
use Elgg\Database\Clauses\OrderByClause;
use Elgg\Values;

if (!static_out_of_date_enabled()) {
	throw new PageNotFoundException();
}

elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE);

$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');
$include_groups = (int) get_input('include_groups', 0);

// group filter
$checkbox = elgg_view('input/checkbox', [
	'name' => 'include_groups',
	'value' => '1',
	'checked' => $include_groups ? true : false,
	'default' => false,
	'label' => elgg_echo('static:out_of_date:include_groups'),
	'label_class' => 'float-alt',
	'onchange' => '$("#static_out_of_date").submit();',
]);

$body = elgg_view('input/form', [
	'id' => 'static_out_of_date',
	'method' => 'GET',
	'disable_security' => true,
	'body' => $checkbox,
	'action' => 'static/out_of_date',
]);

$body .= elgg_list_entities([
	'type' => 'object',
	'subtype' => StaticPage::SUBTYPE,
	'container_guid' => $include_groups ? ELGG_ENTITIES_ANY_VALUE : elgg_get_site_entity()->guid,
	'modified_time_upper' => Values::normalizeTime("-{$days} days"),
	'order_by' => new OrderByClause('e.time_updated', 'DESC'),
	'no_results' => elgg_echo('static:out_of_date:none'),
]);

// draw page
echo elgg_view_page(elgg_echo('static:out_of_date:title'), [
	'content' => $body,
	'sidebar' => false,
	'filter_id' => 'static',
	'filter_value' => 'out_of_date',
]);
