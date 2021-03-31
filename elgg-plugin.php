<?php

use ColdTrick\StaticPages\Bootstrap;
use Elgg\Router\Middleware\Gatekeeper;
use Elgg\Router\Middleware\GroupPageOwnerCanEditGatekeeper;
use Elgg\Router\Middleware\AdminGatekeeper;
use Elgg\Router\Middleware\UserPageOwnerCanEditGatekeeper;

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
		'static/mark_not_out_of_date' => [],
	],
	'routes' => [
		'view:object:static' => [
			'path' => '/static/view/{guid}',
			'resource' => 'static/view',
		],
		'add:object:static' => [
			'path' => '/static/add/{guid?}',
			'resource' => 'static/edit',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'edit:object:static' => [
			'path' => '/static/edit/{guid}',
			'resource' => 'static/edit',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'collection:object:static:group' => [
			'path' => '/static/group/{guid}',
			'resource' => 'static/group',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'collection:object:static:group:out_of_date' => [
			'path' => '/static/group/{guid}/out_of_date',
			'resource' => 'static/out_of_date_group',
			'middleware' => [
				Gatekeeper::class,
				GroupPageOwnerCanEditGatekeeper::class,
			],
		],
		'collection:object:static:user:out_of_date' => [
			'path' => '/static/out_of_date/{username}',
			'resource' => 'static/out_of_date_owner',
			'middleware' => [
				Gatekeeper::class,
				UserPageOwnerCanEditGatekeeper::class,
			],
		],
		'collection:object:static:out_of_date' => [
			'path' => '/static/out_of_date',
			'resource' => 'static/out_of_date',
			'middleware' => [
				AdminGatekeeper::class,
			],
		],
		'collection:object:static:all' => [
			'path' => '/static/all',
			'resource' => 'static/all',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'default:object:static' => [
			'path' => '/static',
			'resource' => 'static/all',
			'middleware' => [
				Gatekeeper::class,
			],
		],
	],
];
