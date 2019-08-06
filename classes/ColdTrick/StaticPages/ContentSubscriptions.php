<?php

namespace ColdTrick\StaticPages;

/**
 * ContentSubscriptions
 */
class ContentSubscriptions {

	/**
	 * Allow content subscriptions for static pages
	 *
	 * @param \Elgg\Hook $hook 'entity_types', 'content_subscriptions'
	 *
	 * @return array
	 */
	public static function entityTypes(\Elgg\Hook $hook) {
		$return_value = $hook->getValue();
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
