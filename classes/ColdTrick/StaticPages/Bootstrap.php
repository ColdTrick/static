<?php

namespace ColdTrick\StaticPages;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
		
		// register page handler for nice URL's
		elgg_register_page_handler('static', '\ColdTrick\StaticPages\PageHandler::staticHandler');
	
		
	
		
		// groups
		if (static_group_enabled()) {
			add_group_tool_option('static', elgg_echo('static:groups:tool_option'), true);
			elgg_register_widget_type('static_groups', elgg_echo('static:widgets:static_groups:title'), elgg_echo('static:widgets:static_groups:description'), ['groups']);
		}
		
		$this->registerViews();
		$this->registerEvents();
		$this->registerHooks();
	}
	
	protected function registerViews() {
		elgg_extend_view('css/elgg', 'css/static/site.css');
		
		elgg_register_ajax_view('static/ajax/menu_static_edit');
	}
	
	protected function registerEvents() {
		elgg_register_event_handler('create', 'object', '\ColdTrick\StaticPages\Cache::resetMenuCache');
		elgg_register_event_handler('update', 'object', '\ColdTrick\StaticPages\Cache::resetMenuCache');
		elgg_register_event_handler('delete', 'object', '\ColdTrick\StaticPages\Cache::resetMenuCache');
		elgg_register_event_handler('create', 'relationship', '\ColdTrick\StaticPages\Cache::resetMenuCacheFromRelationship');
		elgg_register_event_handler('delete', 'relationship', '\ColdTrick\StaticPages\Cache::resetMenuCacheFromRelationship');
		elgg_register_event_handler('cache:flush', 'system', '\ColdTrick\StaticPages\Cache::resetAllCache');;
	}
	
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('entity:url', 'object', '\ColdTrick\StaticPages\Widgets::widgetURL');
		$hooks->registerHandler('entity:icon:file', 'object', '\ColdTrick\StaticPages\IconService::getIconFile');
		
		$hooks->registerHandler('permissions_check', 'object', '\ColdTrick\StaticPages\Permissions::objectPermissionsCheck');
		$hooks->registerHandler('container_permissions_check', 'all', '\ColdTrick\StaticPages\Permissions::containerPermissionsCheck');
			
		$hooks->registerHandler('register', 'menu:owner_block', '\ColdTrick\StaticPages\Menus::ownerBlockMenuRegister');
		$hooks->registerHandler('register', 'menu:filter', '\ColdTrick\StaticPages\Menus::filterMenuRegister');
		$hooks->registerHandler('register', 'menu:entity', '\ColdTrick\StaticPages\Menus::entityMenuRegister');
		$hooks->registerHandler('register', 'menu:page', '\ColdTrick\StaticPages\Menus::registerAdminPageMenuItems');
		$hooks->registerHandler('register', 'menu:static_edit', '\ColdTrick\StaticPages\Menus::registerStaticEditMenuItems');
		$hooks->registerHandler('response', 'all', '\ColdTrick\StaticPages\PageHandler::respondAll');
		$hooks->registerHandler('prepare', 'menu:page', '\ColdTrick\StaticPages\Menus::pageMenuPrepare');
		
		$hooks->registerHandler('entity_types', 'content_subscriptions', '\ColdTrick\StaticPages\ContentSubscriptions::entityTypes');
		$hooks->registerHandler('group_tool_widgets', 'widget_manager', '\ColdTrick\StaticPages\Widgets::groupToolWidgets');
		$hooks->registerHandler('autocomplete', 'search_advanced', '\ColdTrick\StaticPages\Search::searchAdvancedAutocomplete');
		
		$hooks->registerHandler('cron', 'daily', '\ColdTrick\StaticPages\Cron::outOfDateNotification');
		
		$hooks->registerHandler('likes:is_likable', 'object:' . \StaticPage::SUBTYPE, '\Elgg\Values::getTrue');
		$hooks->registerHandler('supported_types', 'entity_tools', '\ColdTrick\StaticPages\MigrateStatic::supportedSubtypes');
		$hooks->registerHandler('view_vars', 'forms/entity_tools/update_entities', '\ColdTrick\StaticPages\EntityTools::limitTopPages');
		
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', '\ColdTrick\StaticPages\CSVExporter::addLastEditor');
		$hooks->registerHandler('export_value', 'csv_exporter', '\ColdTrick\StaticPages\CSVExporter::exportLastEditor');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', '\ColdTrick\StaticPages\CSVExporter::addLastRevision');
		$hooks->registerHandler('export_value', 'csv_exporter', '\ColdTrick\StaticPages\CSVExporter::exportLastRevision');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', '\ColdTrick\StaticPages\CSVExporter::addOutOfDate');
		$hooks->registerHandler('export_value', 'csv_exporter', '\ColdTrick\StaticPages\CSVExporter::exportOutOfDate');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', '\ColdTrick\StaticPages\CSVExporter::addParentPages');
		$hooks->registerHandler('export_value', 'csv_exporter', '\ColdTrick\StaticPages\CSVExporter::exportParentPages');
		
		$hooks->registerHandler('get', 'subscriptions', '\ColdTrick\StaticPages\Notifications::addLastEditorOnComment');
		$hooks->registerHandler('get', 'subscriptions', '\ColdTrick\StaticPages\Notifications::removeLastEditorFromDelayedNotification', 999);
		
		
	}
}
