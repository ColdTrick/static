<?php

namespace ColdTrick\StaticPages;

/**
 * Cache
 */
class Cache {
	
	/**
	 * Resets the menu cache for static pages on update and create of an entity
	 *
	 * @param \Elgg\Event $event 'create|delete|update', 'object'
	 *
	 * @return void
	 */
	public static function resetMenuCache(\Elgg\Event $event) {
		$entity = $event->getObject();
		if (!$entity instanceof \StaticPage) {
			return;
		}
	
		$entity->getRootPage()->clearMenuCache();
	}

	/**
	 * Resets the menu cache for static pages on update and create of an entity
	 *
	 * @param \Elgg\Event $event 'create|delete', 'relationship'
	 *
	 * @return void
	 */
	public static function resetMenuCacheFromRelationship(\Elgg\Event $event) {
		$relationship = $event->getObject();
		if (!$relationship instanceof \ElggRelationship) {
			return;
		}
		
		if ($relationship->relationship !== 'subpage_of') {
			return;
		}
		
		$root_page = get_entity($relationship->guid_two);
		if (!$root_page instanceof \StaticPage) {
			return;
		}
		
		$root_page->clearMenuCache();
	}
	
	/**
	 * Resets all cache on the static pages
	 *
	 * @param \Elgg\Event $event 'cache:flush', 'system'
	 *
	 * @return void
	 */
	public static function resetAllCache(\Elgg\Event $event) {
		
		// this could take a while
		set_time_limit(0);

		elgg_call(ELGG_IGNORE_ACCESS, function() {
			// fetch all top pages
			$batch = elgg_get_entities([
				'type' => 'object',
				'subtype' => \StaticPage::SUBTYPE,
				'limit' => false,
				'relationship' => 'subpage_of',
				'batch' => true,
			]);
			foreach ($batch as $entity) {
				// reset cache for the pages
				$file = new \ElggFile();
				$file->owner_guid = $entity->guid;
				$file->setFilename('static_menu_item_cache');
				if ($file->exists()) {
					$file->delete();
				}
			}
		});
	}
	
	/**
	 * Caches menu items for a given entity and returns an array of the menu items
	 *
	 * @param \StaticPage $root_entity Root entity to fetch the menu items for
	 *
	 * @return array|false
	 */
	public static function generateMenuItemsCache(\StaticPage $root_entity) {
		
		if (!($root_entity instanceof \StaticPage)) {
			return false;
		}
		
		$static_items = [];
	
		$priority = (int) $root_entity->order;
		if (empty($priority)) {
			$priority = (int) $root_entity->time_created;
		}
			
		$root_menu_options = [
			'name' => $root_entity->guid,
			'rel' => $root_entity->guid,
			'href' => $root_entity->getURL(),
			'text' => $root_entity->title,
			'priority' => $priority,
			'section' => 'static',
		];
			
		if ($root_entity->canEdit()) {
			$root_menu_options['itemClass'] = ['static-sortable'];
		}
		// add main menu items
		$menu_item = \ElggMenuItem::factory($root_menu_options);
		$menu_item = elgg_trigger_plugin_hook('menu_item', 'static', ['entity' => $root_entity], $menu_item);
		
		$static_items[$root_entity->guid] = $menu_item;
			
		// add all sub menu items so they are cacheable
		$ia = elgg_set_ignore_access(true);
		$submenu_entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'relationship_guid' => $root_entity->guid,
			'relationship' => 'subpage_of',
			'limit' => false,
			'inverse_relationship' => true,
			'batch' => true,
		]);
		foreach ($submenu_entities as $submenu_item) {
				
			if (!has_access_to_entity($submenu_item) && !$submenu_item->canEdit()) {
				continue;
			}
				
			$priority = (int) $submenu_item->order;
			if (empty($priority)) {
				$priority = (int) $submenu_item->time_created;
			}
			
			$menu_item = \ElggMenuItem::factory([
				'name' => $submenu_item->guid,
				'rel' => $submenu_item->guid,
				'href' => $submenu_item->getURL(),
				'text' => $submenu_item->title,
				'priority' => $priority,
				'parent_name' => $submenu_item->parent_guid,
				'section' => 'static',
			]);
			$menu_item = elgg_trigger_plugin_hook('menu_item', 'static', ['entity' => $submenu_item], $menu_item);
			
			$static_items[$submenu_item->guid] = $menu_item;
		}
			
		elgg_set_ignore_access($ia);
		
		$file = new \ElggFile();
		$file->owner_guid = $root_entity->guid;
		$file->setFilename('static_menu_item_cache');
		$file->open('write');
		$file->write(serialize($static_items));
		$file->close();
	
		return $static_items;
	}

	/**
	 * Reads cached menu items from file for give root entity
	 *
	 * @param \ElggEntity $root_entity root entity to fetch the cache from
	 *
	 * @return array
	 */
	public static function getMenuItemsCache(\ElggEntity $root_entity) {
		$static_items = [];
	
		$file = new \ElggFile();
		$file->owner_guid = $root_entity->guid;
		$file->setFilename('static_menu_item_cache');
		if ($file->exists()) {
			$static_items = unserialize($file->grabFile());
		}
	
		return $static_items;
	}
}
