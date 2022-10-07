<?php

use ColdTrick\StaticPages\Bootstrap;
use Elgg\Router\Middleware\Gatekeeper;
use Elgg\Router\Middleware\GroupPageOwnerCanEditGatekeeper;
use Elgg\Router\Middleware\AdminGatekeeper;
use Elgg\Router\Middleware\UserPageOwnerCanEditGatekeeper;

require_once(dirname(__FILE__) . '/lib/functions.php');

return [
	'plugin' => [
		'version' => '10.1',
	],
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'enable_groups' => 'no',
	],
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'static',
			'class' => StaticPage::class,
			'capabilities' => [
				'commentable' => true,
				'searchable' => true,
				'likable' => true,
			],
		],
	],
	'upgrades' => [
		'ColdTrick\StaticPages\Upgrades\RenameIcons',
	],
	'actions' => [
		'static/edit' => [],
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
		'collection:object:static:user:last_editor' => [
			'path' => '/static/last_editor/{username}',
			'resource' => 'static/last_editor',
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
	'events' => [
		'create' => [
			'object' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCache' => [],
			],
			'relationship' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCacheFromRelationship' => [],
			],
		],
		'delete' => [
			'object' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCache' => [],
			],
			'relationship' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCacheFromRelationship' => [],
			],
		],
		'update' => [
			'object' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCache' => [],
			],
		],
	],
	'hooks' => [
		'autocomplete' => [
			'search_advanced' => [
				'\ColdTrick\StaticPages\Search::searchAdvancedAutocomplete' => [],
			],
		],
		'container_permissions_check' => [
			'all' => [
				'\ColdTrick\StaticPages\Permissions::containerPermissionsCheck' => [],
			],
		],
		'cron' => [
			'daily' => [
				'\ColdTrick\StaticPages\Cron::outOfDateNotification' => [],
			],
		],
		'deadlink_owner' => [
			'admin_tools' => [
				'\ColdTrick\StaticPages\AdminTools::deadLinkOwner' => [],
			],
		],
		'entity:url' => [
			'object' => [
				'\ColdTrick\StaticPages\Widgets::widgetURL' => [],
			],
		],
		'export_value' => [
			'csv_exporter' => [
				'\ColdTrick\StaticPages\CSVExporter::exportLastEditor' => [],
				'\ColdTrick\StaticPages\CSVExporter::exportLastRevision' => [],
				'\ColdTrick\StaticPages\CSVExporter::exportOutOfDate' => [],
				'\ColdTrick\StaticPages\CSVExporter::exportParentPages' => [],
			],
		],
		'get_exportable_values' => [
			'csv_exporter' => [
				'\ColdTrick\StaticPages\CSVExporter::addLastEditor' => [],
				'\ColdTrick\StaticPages\CSVExporter::addLastRevision' => [],
				'\ColdTrick\StaticPages\CSVExporter::addOutOfDate' => [],
				'\ColdTrick\StaticPages\CSVExporter::addParentPages' => [],
			],
		],
		'group_tool_widgets' => [
			'widget_manager' => [
				'\ColdTrick\StaticPages\Widgets::groupToolWidgets' => [],
			],
		],
		'permissions_check' => [
			'object' => [
				'\ColdTrick\StaticPages\Permissions::objectPermissionsCheck' => [],
			],
		],
		'register' => [
			'menu:entity' => [
				'\ColdTrick\StaticPages\Menus::changeDeleteItem' => [],
			],
			'menu:filter:static' => [
				'\ColdTrick\StaticPages\Menus::filterMenuRegister' => [],
			],
			'menu:owner_block' => [
				'\ColdTrick\StaticPages\Menus::ownerBlockMenuRegister' => [],
				'\ColdTrick\StaticPages\Menus::userOwnerBlockMenuRegister' => [],
			],
			'menu:page' => [
				'\ColdTrick\StaticPages\Menus::registerAdminPageMenuItems' => [],
			],
			'menu:static_edit' => [
				'\ColdTrick\StaticPages\Menus::registerStaticEditMenuItems' => [],
			],
			'menu:title:object:static' => [
				\Elgg\Notifications\RegisterSubscriptionMenuItemsHandler::class => [],
			],
		],
		'response' => [
			'all' => [
				'\ColdTrick\StaticPages\PageHandler::respondAll' => [],
			],
		],
		'supported_types' => [
			'entity_tools' => [
				'\ColdTrick\StaticPages\MigrateStatic::supportedSubtypes' => [],
			],
		],
		'view_vars' => [
			'forms/entity_tools/update_entities' => [
				'\ColdTrick\StaticPages\EntityTools::limitTopPages' => [],
			],
		],
	],
	'view_extensions' => [
		'css/elgg' => [
			'css/static/site.css' => [],
		],
	],
	'view_options' => [
		'static/ajax/menu_static_edit' => ['ajax' => true],
	],
];
