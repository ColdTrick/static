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
				'label' => elgg_echo('static:groups:tool_option'),
				'default_on' => true,
			]);
			elgg_register_widget_type('static_groups', elgg_echo('static:widgets:static_groups:title'), elgg_echo('static:widgets:static_groups:description'), ['groups']);
		}
		
		if (elgg_is_active_plugin('entity_tools')) {
			elgg_register_plugin_hook_handler('supported_types', 'entity_tools', '\ColdTrick\StaticPages\MigrateStatic::supportedSubtypes');
		}
	}
}
