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
	 * @param \Elgg\Hook $hook 'get', 'subscriptions'
	 *
	 * @return void|array
	 */
	public static function addLastEditorOnComment(\Elgg\Hook $hook) {
		
		$event = $hook->getParam('event');
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
		
		$return_value = $hook->getValue();
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
