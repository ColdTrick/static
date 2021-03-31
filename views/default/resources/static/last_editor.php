<?php
/**
 * Show a list of static pages the user is the last editor of
 */

use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\Clauses\OrderByClause;

$page_owner = elgg_get_page_owner_entity();

elgg_push_collection_breadcrumbs('object', StaticPage::SUBTYPE);

// build page elements
$title = elgg_echo('static:last_editor:title', [$page_owner->getDisplayName()]);

$body = elgg_call(ELGG_IGNORE_ACCESS, function() use ($page_owner) {
	return elgg_list_entities([
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'wheres' => [
			function (QueryBuilder $qb, $main_alias) use ($page_owner) {
				$where = new WhereClause("{$main_alias}.guid IN (
					SELECT revs.entity_guid
					FROM (
						SELECT a.*
						FROM {$qb->prefix('annotations')} a
						JOIN (
							SELECT entity_guid, max(time_created) as max_time
							FROM {$qb->prefix('annotations')}
							WHERE name = {$qb->param('static_revision', ELGG_VALUE_STRING)}
							GROUP BY entity_guid
						) AS r ON (
		            		a.entity_guid = r.entity_guid
							AND a.time_created = r.max_time
						)
						WHERE a.name = {$qb->param('static_revision', ELGG_VALUE_STRING)}
					) revs
					WHERE revs.owner_guid = {$qb->param($page_owner->guid, ELGG_VALUE_GUID)}
				)");
				
				return $where->prepare($qb, $main_alias);
			},
		],
		'order_by' => [
			new OrderByClause('e.time_updated', 'DESC'),
		],
	]);
});

// draw page
echo elgg_view_page($title, [
	'content' => $body,
	'filter_id' => 'static',
	'filter_value' => $page_owner->guid === elgg_get_logged_in_user_guid() ? 'last_editor' : null,
]);
