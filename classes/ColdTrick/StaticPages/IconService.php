<?php

namespace ColdTrick\StaticPages;

class IconService {
	
	/**
	 * Set the correct filename for an icon
	 *
	 * @param string    $hook         the name of the hook
	 * @param string    $type         the type of the hook
	 * @param \ElggIcon $return_value the current supported entity types
	 * @param array     $params       supplied params
	 *
	 * @return void|\ElggIcon
	 */
	public static function getIconFile($hook, $type, $return_value, $params) {
		
		$entity = elgg_extract('entity', $params);
		if (!($entity instanceof \StaticPage)) {
			return;
		}
		
		$size = elgg_extract('size', $params);
		$prefix = 'thumb';
		
		$return_value->setFilename("{$prefix}{$size}.jpg");
		
		return $return_value;
	}
}
