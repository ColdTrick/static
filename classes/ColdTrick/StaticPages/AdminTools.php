<?php

namespace ColdTrick\StaticPages;

class AdminTools {
	
	/**
	 * Set the correct owner for static content
	 *
	 * @param \Elgg\Hook $hook 'deadlink_owner', 'admin_tools'
	 *
	 * @return null|\ElggUser
	 */
	public static function deadLinkOwner(\Elgg\Hook $hook): ?\ElggUser {
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \StaticPage) {
			return null;
		}
		
		$owner = $entity->getLastEditor();
		return ($owner instanceof \ElggUser) ? $owner : null;
	}
}
