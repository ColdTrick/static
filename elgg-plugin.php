<?php

use ColdTrick\StaticPages\Bootstrap;

require_once(dirname(__FILE__) . '/lib/functions.php');

return [
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'enable_groups' => 'no',
	],
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'static',
			'class' => StaticPage::class,
			'searchable' => true,
		],
	],
	'actions' => [
		'static/edit' => [],
		'static/delete' => [],
		'static/reorder' => [],
		'static/reorder_root_pages' => [],
		'static/mark_not_out_of_date' => [],
	],
	'routes' => [
		'view:object:static' => [
			'path' => '/static/view/{guid}',
			'resource' => 'static/view',
		],
		'add:object:static' => [
			'path' => '/static/add/{container_guid?}',
			'resource' => 'static/edit',
		],
		'edit:object:static' => [
			'path' => '/static/edit/{guid}',
			'resource' => 'static/edit',
		],
		'collection:object:static:group' => [
			'path' => '/static/group/{guid}',
			'resource' => 'static/group',
		],
		'collection:object:static:group:out_of_date' => [
			'path' => '/static/group/{guid}/out_of_date',
			'resource' => 'static/out_of_date',
		],
		'collection:object:static:user:out_of_date' => [
			'path' => '/static/out_of_date/{username}',
			'resource' => 'static/out_of_date_owner',
		],
		'collection:object:static:out_of_date' => [
			'path' => '/static/out_of_date',
			'resource' => 'static/out_of_date',
		],
		'collection:object:static:all' => [
			'path' => '/static/all',
			'resource' => 'static/all',
		],
		'default:object:static' => [
			'path' => '/static',
			'resource' => 'static/all',
		],
	],
];
