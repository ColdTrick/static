<?php

namespace ColdTrick\StaticPages;

/**
 * Widgets
 */
class Widgets {
	
	/**
	 * Returns a url for a static widget
	 *
	 * @param \Elgg\Hook $hook 'entity:url', 'object'
	 *
	 * @return string
	 */
	public static function widgetURL(\Elgg\Hook $hook) {
		$return_value = $hook->getValue();
		if (!empty($return_value)) {
			return;
		}
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggWidget) {
			return;
		}
		
		switch ($entity->handler) {
			case 'static_groups':
				$return_value = elgg_generate_url('collection:object:static:group', [
					'guid' => $entity->owner_guid
				]);
				break;
		}
	
		return $return_value;
	}
	
	/**
	 * Add or remove widgets based on the group tool option
	 *
	 * @param \Elgg\Hook $hook 'group_tool_widgets', 'widget_manager'
	 *
	 * @return array
	 */
	public static function groupToolWidgets(\Elgg\Hook $hook) {
	
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		$return_value = $hook->getValue();
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
