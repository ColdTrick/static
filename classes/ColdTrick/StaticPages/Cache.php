<?php

namespace ColdTrick\StaticPages;

/**
 * Cache
 */
class Cache {
	
	/**
	 * Resets the menu cache for static pages on update and create of an entity
	 *
	 * @param string      $event  'create|delete|update'
	 * @param string      $type   'object'
	 * @param \ElggObject $entity the entity about to be changed
	 *
	 * @return void
	 */
	public static function resetMenuCache($event, $type, \ElggObject $entity) {
	
		if (!($entity instanceof \StaticPage)) {
			return;
		}
	
		$root_entity = $entity->getRootPage()->clearMenuCache();
	}

	/**
	 * Resets the menu cache for static pages on update and create of an entity
	 *
	 * @param string            $event        'create|delete'
	 * @param string            $type         'relationship'
	 * @param \ElggRelationship $relationship the relationship
	 *
	 * @return void
	 */
	public static function resetMenuCacheFromRelationship($event, $type, \ElggRelationship $relationship) {
	
		if (!($relationship instanceof \ElggRelationship)) {
			return;
		}
		
		if ($relationship->relationship !== 'subpage_of') {
			return;
		}
		
		$root_page = get_entity($relationship->guid_two);
		if (!($root_page instanceof \StaticPage)) {
			return;
		}
		
		$root_page->clearMenuCache();
	}
	
	/**
	 * Resets all cache on the static pages
	 *
	 * @param string $event  'cache:flush'
	 * @param string $type   'system'
	 * @param mixed  $entity the entity about to be removed
	 *
	 * @return void
	 */
	public static function resetAllCache($event, $type, $entity) {
	
		// fetch all top pages
		$options = [
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'limit' => false,
			'relationship' => 'subpage_of',
		];
	
		// ignore access
		$ia = elgg_set_ignore_access(true);
	
		$batch = new \ElggBatch('elgg_get_entities', $options);
		foreach ($batch as $entity) {
			// reset cache for the pages
			$file = new \ElggFile();
			$file->owner_guid = $entity->guid;
			$file->setFilename('static_menu_item_cache');
			if ($file->exists()) {
				$file->delete();
			}
		}
	
		elgg_set_ignore_access($ia);
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
			'name' => $root_entity->getGUID(),
			'rel' => $root_entity->getGUID(),
			'href' => $root_entity->getURL(),
			'text' => elgg_format_element('span', [], $root_entity->title),
			'priority' => $priority,
			'section' => 'static',
		];
			
		if ($root_entity->canEdit()) {
			$root_menu_options['itemClass'] = ['static-sortable'];
		}
		// add main menu items
		$static_items[$root_entity->getGUID()] = \ElggMenuItem::factory($root_menu_options);
			
		// add all sub menu items so they are cacheable
		$ia = elgg_set_ignore_access(true);
		$submenu_entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'relationship_guid' => $root_entity->getGUID(),
			'relationship' => 'subpage_of',
			'limit' => false,
			'inverse_relationship' => true,
		]);
			
		if ($submenu_entities) {
			foreach ($submenu_entities as $submenu_item) {
					
				if (!has_access_to_entity($submenu_item) && !$submenu_item->canEdit()) {
					continue;
				}
					
				$priority = (int) $submenu_item->order;
				if (empty($priority)) {
					$priority = (int) $submenu_item->time_created;
				}
				
				$static_items[$submenu_item->getGUID()] = \ElggMenuItem::factory([
					'name' => $submenu_item->getGUID(),
					'rel' => $submenu_item->getGUID(),
					'href' => $submenu_item->getURL(),
					'text' => elgg_format_element('span', [], $submenu_item->title),
					'priority' => $priority,
					'parent_name' => $submenu_item->parent_guid,
					'section' => 'static',
				]);
			}
		}
			
		elgg_set_ignore_access($ia);
		
		$file = new \ElggFile();
		$file->owner_guid = $root_entity->getGUID();
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
		$file->owner_guid = $root_entity->getGUID();
		$file->setFilename('static_menu_item_cache');
		if ($file->exists()) {
			$static_items = unserialize($file->grabFile());
		}
	
		return $static_items;
	}
}