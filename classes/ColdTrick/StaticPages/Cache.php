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
	 * @param \ElggObject $entity the entity about to be removed
	 *
	 * @return void
	 */
	public static function resetMenuCache($event, $type, \ElggObject $entity) {
	
		if (!($entity instanceof \StaticPage)) {
			return;
		}
	
		$root_entity = $entity->getRootPage();
		if (empty($root_entity)) {
			return;
		}
		
		$file = new \ElggFile();
		$file->owner_guid = $root_entity->guid;
		$file->setFilename('static_menu_item_cache');
		if ($file->exists()) {
			$file->delete();
		}
	}
	
	/**
	 * Resets all cache on the static pages
	 *
	 * @param string      $event  'cache:flush'
	 * @param string      $type   'system'
	 * @param \ElggObject $entity the entity about to be removed
	 * @return void
	 */
	public static function resetAllCache($event, $type, \ElggObject $entity) {
	
		// fetch all top pages
		$options = [
			'type' => 'object',
			'subtype' => 'static',
			'limit' => false,
			'relationship' => 'subpage_of',
		];
	
		// ignore access
		$ia = elgg_set_ignore_access(true);
	
		$batch = new \ElggBatch('elgg_get_entities_from_relationship', $options);
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
		
		if (!($entity instanceof \StaticPage)) {
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
			'text' => elgg_format_element('span', [], $root_entity->title),
			'priority' => $priority,
			'section' => 'static',
		];
			
		if ($root_entity->canEdit()) {
			$root_menu_options['itemClass'] = ['static-sortable'];
		}
		// add main menu items
		$static_items[$root_entity->guid] = \ElggMenuItem::factory($root_menu_options);
			
		// add all sub menu items so they are cacheable
		$ia = elgg_set_ignore_access(true);
		$submenu_entities = elgg_get_entities_from_relationship([
			'type' => 'object',
			'subtype' => 'static',
			'relationship_guid' => $root_entity->guid,
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
				
				$static_items[$submenu_item->guid] = \ElggMenuItem::factory([
					'name' => $submenu_item->guid,
					'rel' => $submenu_item->guid,
					'href' => $submenu_item->getURL(),
					'text' => elgg_format_element('span', [], $submenu_item->title),
					'priority' => $priority,
					'parent_name' => $submenu_item->getContainerGUID(),
					'section' => 'static',
				]);
			}
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
		$file->owner_guid = $root_entity->getGUID();
		$file->setFilename('static_menu_item_cache');
		if ($file->exists()) {
			$static_items = unserialize($file->grabFile());
		}
	
		return $static_items;
	}
}