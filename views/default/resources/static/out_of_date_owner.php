<?php

use Elgg\Database\Clauses\OrderByClause;
use Elgg\Database\Clauses\WhereClause;
use Elgg\Values;
use Elgg\Exceptions\Http\PageNotFoundException;

if (!static_out_of_date_enabled()) {
	throw new PageNotFoundException();
}

$page_owner = elgg_get_page_owner_entity();

elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE);

$title_text = elgg_echo('static:out_of_date:owner:title', [$page_owner->getDisplayName()]);

$body = elgg_call(ELGG_IGNORE_ACCESS, function() use ($page_owner) {
	$dbprefix = elgg_get_config('dbprefix');
	$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');

	return elgg_list_entities([
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'modified_time_upper' => Values::normalizeTime("-{$days} days"),
		'wheres' => [
			new WhereClause("e.guid IN (
				SELECT revs.entity_guid
				FROM (
					SELECT *
					FROM {$dbprefix}annotations a
					JOIN (
						SELECT entity_guid AS e_guid, MAX(time_created) AS max_time
		                FROM {$dbprefix}annotations
		                WHERE name = 'static_revision'
		                GROUP BY entity_guid
					) AS b ON (
	            		a.entity_guid = b.e_guid
						AND a.time_created = b.max_time
					)
					WHERE a.owner_guid = {$page_owner->guid}
				) revs
			)"),
		],
		'order_by' => new OrderByClause('e.time_updated', 'DESC'),
		'no_results' => elgg_echo('static:out_of_date:none'),
	]);
});

// draw page
echo elgg_view_page($title_text, [
	'content' => $body,
	'sidebar' => false,
	'filter_id' => 'static',
	'filter_value' => 'out_of_date_mine',
]);
