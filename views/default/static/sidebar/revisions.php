<?php

use Elgg\Database\Clauses\OrderByClause;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof StaticPage) {
	return;
}

$annotations = elgg_list_annotations([
	'guid' => $entity->guid,
	'annotation_name' => 'static_revision',
	'limit' => false,
	'order_by' => new OrderByClause('time_created', 'desc'),
]);
if (empty($annotations)) {
	return;
}

echo elgg_view_module('aside', elgg_echo('static:revisions'), $annotations);
