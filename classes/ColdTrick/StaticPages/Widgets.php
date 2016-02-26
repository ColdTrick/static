<?php

namespace ColdTrick\StaticPages;

/**
 * Widgets
 */
class Widgets {
	
	/**
	 * Returns a url for a static widget
	 *
	 * @param string $hook         name of the hook
	 * @param string $type         type of the hook
	 * @param array  $return_value return value
	 * @param array  $params       hook parameters
	 *
	 * @return string
	 */
	public static function widgetURL($hook, $type, $return_value, $params) {
	
		if (!empty($return_value)) {
			return;
		}
		
		$entity = elgg_extract('entity', $params);
		if (!elgg_instanceof($entity, 'object', 'widget')) {
			return;
		}
		
		switch ($entity->handler) {
			case 'static_groups':
				$return_value = "static/group/{$entity->getOwnerGUID()}";
				break;
		}
	
		return $return_value;
	}
	
	/**
	 * Add or remove widgets based on the group tool option
	 *
	 * @param string $hook         'group_tool_widgets'
	 * @param string $type         'widget_manager'
	 * @param array  $return_value current enable/disable widget handlers
	 * @param array  $params       supplied params
	 *
	 * @return array
	 */
	public static function groupToolWidgets($hook, $type, $return_value, $params) {
	
		$entity = elgg_extract('entity', $params);
		if (!elgg_instanceof($entity, 'group')) {
			return;
		}
		
		if (!is_array($return_value)) {
			$return_value = [];
		}
			
		if (!isset($return_value['enable'])) {
			$return_value['enable'] = [];
		}
		if (!isset($return_value['disable'])) {
			$return_value['disable'] = [];
		}

		if (static_group_enabled($entity)) {
			$return_value['enable'][] = 'static_groups';
		} else {
			$return_value['disable'][] = 'static_groups';
		}

		return $return_value;
	}
}