<?php

namespace ColdTrick\StaticPages\Plugins;

/**
 * Support for admin tools plugin
 */
class AdminTools {
	
	/**
	 * Set the correct owner for static content
	 *
	 * @param \Elgg\Event $event 'deadlink_owner', 'admin_tools'
	 *
	 * @return null|\ElggUser
	 */
	public static function deadLinkOwner(\Elgg\Event $event): ?\ElggUser {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \StaticPage) {
			return null;
		}
		
		$owner = $entity->getLastEditor();
		return ($owner instanceof \ElggUser) ? $owner : null;
	}
}
