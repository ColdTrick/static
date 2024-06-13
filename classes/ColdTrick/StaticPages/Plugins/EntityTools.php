<?php

namespace ColdTrick\StaticPages\Plugins;

/**
 * EntityTools
 */
class EntityTools {

	/**
	 * Limit list of updatable entities to top pages
	 *
	 * @param \Elgg\Event $event 'view_vars', 'forms/entity_tools/update_entities'
	 *
	 * @return void|array
	 */
	public static function limitTopPages(\Elgg\Event $event): ?array {
		$vars = $event->getValue();
		$subtype = elgg_extract('subtype', $vars);
		if ($subtype !== \StaticPage::SUBTYPE) {
			return null;
		}
		
		$vars['entity_options'] = (array) elgg_extract('entity_options', $vars, []);
		
		$vars['entity_options']['metadata_name_value_pairs'] = [
			'parent_guid' => 0,
		];
	
		return $vars;
	}
	
	/**
	 * Add static to the supported types for EntityTools
	 *
	 * @param \Elgg\Event $event 'supported_types', 'entity_tools'
	 *
	 * @return array
	 */
	public static function supportedSubtypes(\Elgg\Event $event): array {
		$return_value = $event->getValue();
		$return_value[\StaticPage::SUBTYPE] = \ColdTrick\StaticPages\MigrateStatic::class;
		return $return_value;
	}
}
