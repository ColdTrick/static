<?php

namespace ColdTrick\StaticPages;

/**
 * EntityTools
 */
class EntityTools {

	/**
	 * Limit list of updateable entities to top pages
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param array  $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @return void|array
	 */
	public static function limitTopPages($hook, $type, $return_value, $params) {
		$subtype = elgg_extract('subtype', $return_value);
		if ($subtype !== \StaticPage::SUBTYPE) {
			return;
		}
		
		$return_value['entity_options'] = (array) elgg_extract('entity_options', $return_value, []);
		
		$return_value['entity_options']['metadata_name_value_pairs'] = [
			'parent_guid' => 0,
		];
	
		return $return_value;
	}
}
