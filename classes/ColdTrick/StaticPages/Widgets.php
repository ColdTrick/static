<?php

namespace ColdTrick\StaticPages;

/**
 * Widgets
 */
class Widgets {
	
	/**
	 * Returns a url for a static widget
	 *
	 * @param \Elgg\Event $event 'entity:url', 'object'
	 *
	 * @return null|string
	 */
	public static function widgetURL(\Elgg\Event $event): ?string {
		$return_value = $event->getValue();
		if (!empty($return_value)) {
			return null;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggWidget) {
			return null;
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
	 * @param \Elgg\Event $event 'group_tool_widgets', 'widget_manager'
	 *
	 * @return null|array
	 */
	public static function groupToolWidgets(\Elgg\Event $event): ?array {
	
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return null;
		}
		
		$return_value = $event->getValue();
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
