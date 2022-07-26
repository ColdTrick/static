<?php

$site = elgg_get_site_entity();
if ($site->canWriteToContainer(elgg_get_logged_in_user_guid(), 'object', StaticPage::SUBTYPE)) {
	elgg_register_title_button('static', 'add', 'object', StaticPage::SUBTYPE);
}

// breadcrumb
elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE);

// build page elements
$body = elgg_call(ELGG_IGNORE_ACCESS, function() use ($site) {
	return elgg_list_entities([
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'metadata_name_value_pairs' => [
			'parent_guid' => 0,
		],
		'container_guid' => $site->guid,
		'sort_by' => [
			'property' => 'title',
			'direction' => 'asc',
		],
		'no_results' => elgg_echo('static:admin:empty'),
	]);
});

// draw page
echo elgg_view_page(elgg_echo('static:all'), [
	'content' => $body,
	'sidebar' => false,
	'filter_id' => 'static',
	'filter_value' => 'all',
]);
