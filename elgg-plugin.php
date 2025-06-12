<?php

use ColdTrick\StaticPages\Bootstrap;
use ColdTrick\StaticPages\FieldsHandler;
use ColdTrick\StaticPages\Forms\PrepareFields;
use Elgg\Blog\GroupToolContainerLogicCheck;
use Elgg\Router\Middleware\Gatekeeper;
use Elgg\Router\Middleware\GroupPageOwnerCanEditGatekeeper;
use Elgg\Router\Middleware\AdminGatekeeper;
use Elgg\Router\Middleware\GroupPageOwnerGatekeeper;
use Elgg\Router\Middleware\UserPageOwnerCanEditGatekeeper;

require_once(dirname(__FILE__) . '/lib/functions.php');

return [
	'plugin' => [
		'version' => '13.1.2',
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
				'restorable' => true,
			],
		],
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
				GroupPageOwnerGatekeeper::class,
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
		'collection:object:static:trashed' => [
			'path' => '/static/trashed',
			'resource' => 'static/trashed',
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
		'access:collections:write' => [
			'user' => [
				'\ColdTrick\StaticPages\Access::removeFriendsAccess' => [],
				'\ColdTrick\StaticPages\Access::removeFriendsCollectionsAccess' => [],
			],
		],
		'action:validate' => [
			'entity/delete' => [
				'\ColdTrick\StaticPages\Permissions::allowActionAccessToPrivateEntity' => [],
			],
			'entity/trash' => [
				'\ColdTrick\StaticPages\Permissions::allowActionAccessToPrivateEntity' => [],
			],
			'entity_attachments/add' => [
				'\ColdTrick\StaticPages\Permissions::allowActionAccessToPrivateEntity' => [],
			],
		],
		'autocomplete' => [
			'search_advanced' => [
				'\ColdTrick\StaticPages\Plugins\SearchAdvanced::searchAdvancedAutocomplete' => [],
			],
		],
		'container_logic_check' => [
			'object' => [
				\ColdTrick\StaticPages\GroupToolContainerLogicCheck::class => [],
			],
		],
		'container_permissions_check' => [
			'all' => [
				'\ColdTrick\StaticPages\Permissions::containerPermissionsCheck' => [],
			],
		],
		'create' => [
			'relationship' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCacheFromRelationship' => [],
			],
		],
		'create:after' => [
			'object' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCache' => [],
			],
		],
		'cron' => [
			'daily' => [
				'\ColdTrick\StaticPages\Cron::outOfDateNotification' => [],
			],
		],
		'deadlink_owner' => [
			'admin_tools' => [
				'\ColdTrick\StaticPages\Plugins\AdminTools::deadLinkOwner' => [],
			],
		],
		'delete' => [
			'relationship' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCacheFromRelationship' => [],
			],
		],
		'delete:after' => [
			'object' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCache' => [],
			],
		],
		'entity:url' => [
			'object' => [
				'\ColdTrick\StaticPages\Widgets::widgetURL' => [],
			],
		],
		'export_value' => [
			'csv_exporter' => [
				'\ColdTrick\StaticPages\Plugins\CSVExporter::exportLastEditor' => [],
				'\ColdTrick\StaticPages\Plugins\CSVExporter::exportLastRevision' => [],
				'\ColdTrick\StaticPages\Plugins\CSVExporter::exportOutOfDate' => [],
				'\ColdTrick\StaticPages\Plugins\CSVExporter::exportParentPages' => [],
			],
		],
		'fields' => [
			'object:static' => [
				FieldsHandler::class => [],
			],
		],
		'form:prepare:fields' => [
			'static/edit' => [
				PrepareFields::class => [],
			],
		],
		'get_exportable_values' => [
			'csv_exporter' => [
				'\ColdTrick\StaticPages\Plugins\CSVExporter::addLastEditor' => [],
				'\ColdTrick\StaticPages\Plugins\CSVExporter::addLastRevision' => [],
				'\ColdTrick\StaticPages\Plugins\CSVExporter::addOutOfDate' => [],
				'\ColdTrick\StaticPages\Plugins\CSVExporter::addParentPages' => [],
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
		'prepare' => [
			'menu:page' => [
				'\ColdTrick\StaticPages\Menus::pageMenuPrepare' => [],
			],
		],
		'register' => [
			'menu:entity' => [
				'\ColdTrick\StaticPages\Menus::changeDeleteItem' => ['priority' => 9999],
			],
			'menu:entity:trash' => [
				'\ColdTrick\StaticPages\Menus\EntityTrash::removeRestoreItem' => [],
			],
			'menu:filter:static' => [
				'\ColdTrick\StaticPages\Menus::filterMenuRegister' => [],
			],
			'menu:owner_block' => [
				'\ColdTrick\StaticPages\Menus::ownerBlockMenuRegister' => [],
				'\ColdTrick\StaticPages\Menus::userOwnerBlockMenuRegister' => [],
			],
			'menu:admin_header' => [
				'\ColdTrick\StaticPages\Menus::registerAdminHeaderMenuItems' => [],
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
		'restore:after' => [
			'object' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCache' => [],
			],
		],
		'seeds' => [
			'database' => [
				'\ColdTrick\StaticPages\Seeder::register' => [],
			],
		],
		'supported_types' => [
			'entity_tools' => [
				'\ColdTrick\StaticPages\Plugins\EntityTools::supportedSubtypes' => [],
			],
		],
		'trash:after' => [
			'object' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCache' => [],
			],
		],
		'update:after' => [
			'object' => [
				'\ColdTrick\StaticPages\Cache::resetMenuCache' => [],
			],
		],
		'view_vars' => [
			'forms/entity_attachments/add' => [
				'\ColdTrick\StaticPages\Plugins\EntityAttachments::loadPrivateStaticPage' => [],
			],
			'forms/entity_tools/update_entities' => [
				'\ColdTrick\StaticPages\Plugins\EntityTools::limitTopPages' => [],
			],
		],
	],
	'view_extensions' => [
		'elgg.css' => [
			'static/site.css' => [],
		],
	],
	'view_options' => [
		'static/ajax/menu_static_edit' => ['ajax' => true],
	],
];
