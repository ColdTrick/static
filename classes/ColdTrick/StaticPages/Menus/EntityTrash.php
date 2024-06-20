<?php

namespace ColdTrick\StaticPages\Menus;

use Elgg\Menu\MenuItems;

/**
 * Make changes to the entity:trash menu
 */
class EntityTrash {
	
	/**
	 * Remove the restore item for static pages that can't be restored
	 *
	 * @param \Elgg\Event $event 'register', 'menu:entity:trash'
	 *
	 * @return null|MenuItems
	 */
	public static function removeRestoreItem(\Elgg\Event $event): ?MenuItems {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \StaticPage || $entity->canRestore()) {
			return null;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
		$result->remove('restore');
		
		return $result;
	}
}
