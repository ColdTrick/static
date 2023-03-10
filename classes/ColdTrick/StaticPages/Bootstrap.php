<?php

namespace ColdTrick\StaticPages;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap
 */
class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
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
	}
}
