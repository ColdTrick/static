<?php

namespace ColdTrick\StaticPages\Plugins;

/**
 * Support for entity attachments plugin
 */
class EntityAttachments {
	
	/**
	 * Load private static page for adding an attachment
	 *
	 * @param \Elgg\Event $event 'view_vars', 'forms/entity_attachment/add'
	 *
	 * @return null|array
	 */
	public static function loadPrivateStaticPage(\Elgg\Event $event): ?array {
		$vars = $event->getValue();
		$guid = elgg_extract('guid', $vars);
		if ($guid === null) {
			// no guid requested
			return null;
		}
		
		if (isset($vars['entity'])) {
			// entity already loaded
			return null;
		}
		
		$entity = elgg_call(ELGG_IGNORE_ACCESS, function() use ($guid) {
			return get_entity($guid);
		});
		
		if (!$entity instanceof \StaticPage) {
			return null;
		}
		
		$vars['entity'] = $entity;
		
		return $vars;
	}
}
