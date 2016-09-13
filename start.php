<?php
use Alliander\Theme\StaticPage;
/**
 * Start file for the Static plugin
 */

require_once(dirname(__FILE__) . '/lib/functions.php');

// register default Elgg events
elgg_register_event_handler('init', 'system', 'static_init');

/**
 * Initializes the static plugin
 *
 * @return void
 */
function static_init() {

	// register page handler for nice URL's
	elgg_register_page_handler('static', '\ColdTrick\StaticPages\PageHandler::staticHandler');

	elgg_extend_view('css/elgg', 'css/static/site.css');
	
	elgg_register_ajax_view('static/ajax/menu_static_edit');

	// Register for search
	elgg_register_entity_type('object', 'static');
	
	// groups
	if (static_group_enabled()) {
		add_group_tool_option('static', elgg_echo('static:groups:tool_option'), true);
		elgg_register_widget_type('static_groups', elgg_echo('static:widgets:static_groups:title'), elgg_echo('static:widgets:static_groups:description'), ['groups']);
	}
	
	// events
	elgg_register_event_handler('create', 'object', '\ColdTrick\StaticPages\Notifications::notifyLastEditor');
	elgg_register_event_handler('create', 'object', '\ColdTrick\StaticPages\Cache::resetMenuCache');
	elgg_register_event_handler('update', 'object', '\ColdTrick\StaticPages\Cache::resetMenuCache');
	elgg_register_event_handler('delete', 'object', '\ColdTrick\StaticPages\Cache::resetMenuCache');
	elgg_register_event_handler('upgrade', 'system', '\ColdTrick\StaticPages\Upgrade::migrateTreeStructure');
	elgg_register_event_handler('upgrade', 'system', '\ColdTrick\StaticPages\Upgrade::registerClass');

	elgg_register_event_handler('cache:flush', 'system', '\ColdTrick\StaticPages\Cache::resetAllCache');;
	
	// plugin hooks
	elgg_register_plugin_hook_handler('route', 'all', '\ColdTrick\StaticPages\PageHandler::routeAll');
	elgg_register_plugin_hook_handler('entity:url', 'object', '\ColdTrick\StaticPages\Widgets::widgetURL');
	
	elgg_register_plugin_hook_handler('permissions_check', 'object', '\ColdTrick\StaticPages\Permissions::objectPermissionsCheck');
	elgg_register_plugin_hook_handler('container_permissions_check', 'all', '\ColdTrick\StaticPages\Permissions::containerPermissionsCheck');
	
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', '\ColdTrick\StaticPages\Menus::ownerBlockMenuRegister');
	elgg_register_plugin_hook_handler('register', 'menu:filter', '\ColdTrick\StaticPages\Menus::filterMenuRegister');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\StaticPages\Menus::entityMenuRegister');
	elgg_register_plugin_hook_handler('register', 'menu:page', '\ColdTrick\StaticPages\Menus::registerAdminPageMenuItems');
	elgg_register_plugin_hook_handler('register', 'menu:static_edit', '\ColdTrick\StaticPages\Menus::registerStaticEditMenuItems');
	elgg_register_plugin_hook_handler('prepare', 'menu:page', '\ColdTrick\StaticPages\Menus::pageMenuPrepare');
	
	elgg_register_plugin_hook_handler('entity_types', 'content_subscriptions', '\ColdTrick\StaticPages\ContentSubscriptions::entityTypes');
	elgg_register_plugin_hook_handler('group_tool_widgets', 'widget_manager', '\ColdTrick\StaticPages\Widgets::groupToolWidgets');
	elgg_register_plugin_hook_handler('autocomplete', 'search_advanced', '\ColdTrick\StaticPages\Search::searchAdvancedAutocomplete');
	
	elgg_register_plugin_hook_handler('cron', 'daily', '\ColdTrick\StaticPages\Cron::daily');
	
	elgg_register_plugin_hook_handler('likes:is_likable', 'object:' . \StaticPage::SUBTYPE, '\Elgg\Values::getTrue');
	
	// actions
	elgg_register_action('static/edit', dirname(__FILE__) . '/actions/edit.php');
	elgg_register_action('static/delete', dirname(__FILE__) . '/actions/delete.php');
	elgg_register_action('static/reorder', dirname(__FILE__) . '/actions/reorder.php');
	elgg_register_action('static/reorder_root_pages', dirname(__FILE__) . '/actions/reorder_root_pages.php');
}
