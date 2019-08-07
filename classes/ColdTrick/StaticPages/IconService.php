<?php

namespace ColdTrick\StaticPages;

class IconService {
	
	/**
	 * Set the correct filename for an icon
	 *
	 * @param \Elgg\Hook $hook 'entity:icon:file', 'object'
	 *
	 * @return void|\ElggIcon
	 */
	public static function getIconFile(\Elgg\Hook $hook) {
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \StaticPage) {
			return;
		}
		
		$size = $hook->getParam('size');
		$prefix = 'thumb';
		
		$return_value = $hook->getValue();
		$return_value->setFilename("{$prefix}{$size}.jpg");
		return $return_value;
	}
}
