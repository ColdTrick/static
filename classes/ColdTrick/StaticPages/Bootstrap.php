<?php

namespace ColdTrick\StaticPages;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
		// groups
		if (static_group_enabled()) {
			$this->elgg()->group_tools->register('static', [
				'default_on' => true,
			]);
			elgg_register_widget_type([
				'id' => 'static_groups',
				'name' => elgg_echo('static:widgets:static_groups:title'),
				'description' => elgg_echo('static:widgets:static_groups:description'),
				'context' => ['groups'],
			]);
		}
		
		if (elgg_is_active_plugin('entity_tools')) {
			elgg_register_plugin_hook_handler('supported_types', 'entity_tools', '\ColdTrick\StaticPages\MigrateStatic::supportedSubtypes');
		}
	}
}
