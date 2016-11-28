<?php

elgg_gatekeeper();
elgg_group_gatekeeper();

if (!static_out_of_date_enabled()) {
	forward(REFERER);
}

$page_owner = elgg_get_page_owner_entity();
if (!($page_owner instanceof ElggGroup)) {
	register_error(elgg_echo('pageownerunavailable', [elgg_get_page_owner_guid()]));
	forward(REFERER);
}

if (!static_group_enabled($page_owner)) {
	forward(REFERER);
}

if (!$page_owner->canEdit()) {
	register_error(elgg_echo('limited_access'));
	forward(REFERER);
}

$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');

$options = [
	'type' => 'object',
	'subtype' => StaticPage::SUBTYPE,
	'container_guid' => $page_owner->getGUID(),
	'modified_time_upper' => time() - ($days * 24 * 60 * 60),
	'order_by' => 'e.time_updated DESC',
	'no_results' => elgg_echo('static:out_of_date:none'),
	'item_view' => 'object/static/simple',
];

$title_text = elgg_echo('static:out_of_date:title');
$filter = elgg_view('page/layouts/elements/filter');

$body = elgg_list_entities($options);

// build page
$page_data = elgg_view_layout('content', [
	'title' => $title_text,
	'content' => $body,
	'filter' => $filter,
]);

// draw page
echo elgg_view_page($title_text, $page_data);
