<?php
/**
 * Start file for the Static plugin
 */

require_once(dirname(__FILE__) . "/lib/functions.php");
require_once(dirname(__FILE__) . "/lib/events.php");
require_once(dirname(__FILE__) . "/lib/hooks.php");
require_once(dirname(__FILE__) . "/lib/page_handlers.php");

// register default Elgg events
elgg_register_event_handler("init", "system", "static_init");
elgg_register_event_handler('pagesetup', 'system', 'static_page_setup');

/**
 * Initializes the static plugin
 *
 * @return void
 */
function static_init() {

	// register page handler for nice URL's
	elgg_register_page_handler("static", "static_page_handler");

	elgg_extend_view("js/elgg", "js/static/site");
	elgg_extend_view("css/elgg", "css/static/site");

	// Register for search.
	elgg_register_entity_type('object', 'static');
	
	// groups
	if (static_group_enabled()) {
		add_group_tool_option("static", elgg_echo("static:groups:tool_option"), true);
		elgg_register_widget_type("static_groups", elgg_echo("static:widgets:static_groups:title"), elgg_echo("static:widgets:static_groups:description"), array("groups"));
	}
	
	// events
	elgg_register_event_handler("create", "object", "static_create_comment_handler");
	elgg_register_event_handler("delete", "object", "static_delete_object_handler");
	elgg_register_event_handler("create", "object", "static_reset_menu_cache_handler");
	elgg_register_event_handler("update", "object", "static_reset_menu_cache_handler");
	elgg_register_event_handler("delete", "object", "static_reset_menu_cache_handler");
	elgg_register_event_handler("upgrade", "system", "static_upgrade_system_handler");

	elgg_register_event_handler('cache:flush', 'system', 'static_reset_cache');;
	
	// plugin hooks
	elgg_register_plugin_hook_handler("route", "all", "static_route_hook_handler");
	elgg_register_plugin_hook_handler("entity:url", "object", "static_entity_url_hook_handler");
	elgg_register_plugin_hook_handler("entity:icon:url", "object", "static_entity_icon_url_hook_handler");
	
	elgg_register_plugin_hook_handler("permissions_check", "object", "static_permissions_check_hook_handler");
	elgg_register_plugin_hook_handler("container_permissions_check", "all", "static_container_permissions_check_hook_handler");
	elgg_register_plugin_hook_handler("permissions_check:comment", "object", "static_permissions_comment_hook_handler");
	
	elgg_register_plugin_hook_handler("register", "menu:owner_block", "static_register_owner_block_menu_hook_handler");
	elgg_register_plugin_hook_handler("register", "menu:filter", "static_register_filter_menu_hook_handler");
	elgg_register_plugin_hook_handler("register", "menu:entity", "static_register_entity_menu_hook_handler");
	elgg_register_plugin_hook_handler("prepare", "menu:page", "static_prepare_page_menu_hook_handler");
	
	elgg_register_plugin_hook_handler("entity_types", "content_subscriptions", "static_content_subscriptions_entity_types_handler");
	elgg_register_plugin_hook_handler("group_tool_widgets", "widget_manager", "static_group_tool_widgets_handler");
	elgg_register_plugin_hook_handler("autocomplete", "search_advanced", "static_search_advanced_autocomplete_handler");
	
	elgg_register_plugin_hook_handler("cron", "daily", "static_daily_cron_handler");
	
	// actions
	elgg_register_action("static/edit", dirname(__FILE__) . "/actions/edit.php");
	elgg_register_action("static/delete", dirname(__FILE__) . "/actions/delete.php");
	elgg_register_action("static/reorder", dirname(__FILE__) . "/actions/reorder.php");
	elgg_register_action("static/reorder_root_pages", dirname(__FILE__) . "/actions/reorder_root_pages.php");
}

/**
 * Registers menu items during page setup
 *
 * @return void
 */
function static_page_setup() {
	
	elgg_register_menu_item('page', array(
		'name' => "static_all",
		'href' => "static/all",
		'text' => elgg_echo("static:all"),
		'context' => 'admin',
		'parent_name' => "appearance",
		'section' => "configure"
	));
}
