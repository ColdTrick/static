<?php

elgg_admin_gatekeeper();

if (!static_out_of_date_enabled()) {
	forward(REFERER);
}

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
	'container_guid' => $include_groups ? ELGG_ENTITIES_ANY_VALUE : elgg_get_site_entity()->getGUID(),
	'modified_time_upper' => time() - ($days * 24 * 60 * 60),
	'order_by' => 'e.time_updated DESC',
	'no_results' => elgg_echo('static:out_of_date:none'),
]);

$title_text = elgg_echo('static:out_of_date:title');

// build page
$page_data = elgg_view_layout('one_column', [
	'title' => $title_text,
	'content' => $body,
	'filter_id' => 'static',
]);

// draw page
echo elgg_view_page($title_text, $page_data);
