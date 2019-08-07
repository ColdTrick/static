<?php

namespace ColdTrick\StaticPages;

/**
 * EntityTools
 */
class EntityTools {

	/**
	 * Limit list of updateable entities to top pages
	 *
	 * @param \Elgg\Hook $hook 'view_vars', 'forms/entity_tools/update_entities'
	 *
	 * @return void|array
	 */
	public static function limitTopPages(\Elgg\Hook $hook) {
		$vars = $hook->getValue();
		$subtype = elgg_extract('subtype', $vars);
		if ($subtype !== \StaticPage::SUBTYPE) {
			return;
		}
		
		$vars['entity_options'] = (array) elgg_extract('entity_options', $vars, []);
		
		$vars['entity_options']['metadata_name_value_pairs'] = [
			'parent_guid' => 0,
		];
	
		return $vars;
	}
}
