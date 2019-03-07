<?php

namespace ColdTrick\StaticPages;

use Elgg\Notifications\SubscriptionNotificationEvent;

/**
 * Notifications
 */
class Notifications {
	
	/**
	 * Add the last editor to a comment nofitication
	 *
	 * @param string $hook         'get'
	 * @param string $type         'subscriptions'
	 * @param array  $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @return void|array
	 */
	public static function addLastEditorOnComment($hook, $type, $return_value, $params) {
		
		$event = elgg_extract('event', $params);
		if (!$event instanceof SubscriptionNotificationEvent) {
			// only delayed notifications
			return;
		}
		
		if ($event->getAction() !== 'create') {
			return;
		}
		
		$object = $event->getObject();
		if (!$object instanceof \ElggComment) {
			// not a comment
			return;
		}
		
		$container = $object->getContainerEntity();
		if (!$container instanceof \StaticPage) {
			// not static
			return;
		}
		
		$last_editor = $container->getLastEditor();
		if (!$last_editor instanceof \ElggUser) {
			return;
		}
		
		if (isset($return_value[$last_editor->guid])) {
			// already in the list
			return;
		}
		
		$notification_settings = $last_editor->getNotificationSettings();
		if (empty($notification_settings)) {
			return;
		}
		
		$methods = [];
		foreach ($notification_settings as $method => $enabled) {
			if (empty($enabled)) {
				continue;
			}
			
			$methods[] = $method;
		}
		if (empty($methods)) {
			return;
		}
		
		$return_value[$last_editor->guid] = $methods;
		
		return $return_value;
	}
}
