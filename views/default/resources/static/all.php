<?php

elgg_gatekeeper();

$site = elgg_get_site_entity();

if ($site->canWriteToContainer(elgg_get_logged_in_user_guid(), 'object', StaticPage::SUBTYPE)) {
	elgg_register_title_button('static', 'add', 'object', StaticPage::SUBTYPE);
}

// breadcrumb
elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE);

// build page elements
$title_text = elgg_echo('static:all');

$body = elgg_call(ELGG_IGNORE_ACCESS, function() use ($site) {
	return elgg_list_entities([
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'metadata_name_value_pairs' => [
			'parent_guid' => 0,
		],
		'container_guid' => $site->guid,
		'order_by_metadata' => ['title' => 'ASC'],
		'no_results' => elgg_echo('static:admin:empty'),
	]);
});

// build page
$body = elgg_view_layout('default', [
	'title' => $title_text,
	'content' => $body,
	'sidebar' => false,
	'filter_id' => 'static',
]);

// draw page
echo elgg_view_page($title_text, $body);
