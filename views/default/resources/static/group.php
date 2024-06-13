<?php
/**
 * List the main static pages of this group
 */

use Elgg\Exceptions\Http\PageNotFoundException;

elgg_entity_gatekeeper(elgg_get_page_owner_guid(), 'group');

$group = elgg_get_page_owner_entity();
if (!static_group_enabled($group)) {
	throw new PageNotFoundException();
}

elgg_push_collection_breadcrumbs('object', \StaticPage::SUBTYPE, $group);

$can_write = $group->canWriteToContainer(0, 'object', \StaticPage::SUBTYPE);
if ($can_write) {
	elgg_register_title_button('add', 'object', \StaticPage::SUBTYPE);
}

$ignore_access = $can_write ? ELGG_IGNORE_ACCESS : 0;

$body = elgg_call($ignore_access, function() use ($group) {
	return elgg_list_entities([
		'type' => 'object',
		'subtype' => \StaticPage::SUBTYPE,
		'metadata_name_value_pairs' => [
			'parent_guid' => 0,
		],
		'container_guid' => $group->guid,
		'sort_by' => [
			'property' => 'title',
			'direction' => 'asc',
		],
		'no_results' => elgg_echo('static:admin:empty'),
	]);
});

echo elgg_view_page(elgg_echo('static:groups:title'), [
	'content' => $body,
	'filter_id' => 'static',
	'filter_value' => 'group',
]);
