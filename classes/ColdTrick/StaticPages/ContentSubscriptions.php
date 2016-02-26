<?php

namespace ColdTrick\StaticPages;

/**
 * ContentSubscriptions
 */
class ContentSubscriptions {

	/**
	 *
	 * @param string $hook         'entity_types'
	 * @param string $type         'content_subscriptions'
	 * @param array  $return_value the current supported entity types
	 * @param array  $params       supplied params
	 *
	 * @return array
	 */
	public static function entityTypes($hook, $type, $return_value, $params) {
	
		if (!is_array($return_value)) {
			$return_value = [];
		}
	
		if (!isset($return_value['object'])) {
			$return_value['object'] = [];
		}
	
		$return_value['object'][] = 'static';
	
		return $return_value;
	}
}
