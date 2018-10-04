<?php

elgg_gatekeeper();

$site = elgg_get_site_entity();

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

if ($site->canWriteToContainer(elgg_get_logged_in_user_guid(), 'object', StaticPage::SUBTYPE)) {
	elgg_register_title_button('static', 'add', 'object', StaticPage::SUBTYPE);
}

$title_text = elgg_echo('static:all');

// build page
$body = elgg_view_layout('one_column', [
	'title' => $title_text,
	'content' => $body,
	'filter_id' => 'static',
]);

// draw page
echo elgg_view_page($title_text, $body);
