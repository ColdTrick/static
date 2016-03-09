<?php

namespace ColdTrick\StaticPages;

/**
 * Upgrade
 */
class Upgrade {
	
	/**
	 * Migrate old tree structure to new structure
	 *
	 * @param string $event  the name of the event
	 * @param string $type   the type of the event
	 * @param mixed  $entity supplied entity
	 *
	 * @return void
	 */
	public static function migrateTreeStructure($event, $type, $entity) {
	
		// this process could take a while
		set_time_limit(0);
	
		// set entity options
		$options = [
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'metadata_name' => 'parent_guid',
			'site_guids' => false,
			'limit' => false,
		];
	
		// set default metadata options
		$metadata_options = [
			'metadata_name' => 'parent_guid',
			'site_guids' => false,
			'limit' => false,
		];
	
		// make sure we can get all entities
		$ia = elgg_set_ignore_access(true);
	
		// create a batch for processing
		$batch = new \ElggBatch('elgg_get_entities_from_metadata', $options);
		$batch->setIncrementOffset(false);
		foreach ($batch as $entity) {
	
			$metadata_options['guid'] = $entity->getGUID();
	
			$parent_guid = (int) $entity->parent_guid;
			if (empty($parent_guid)) {
				// workaround for multi-site
				elgg_delete_metadata($metadata_options);
	
				continue;
			}
	
			$parent = get_entity($parent_guid);
			if (empty($parent)) {
				// workaround for multi-site
				elgg_delete_metadata($metadata_options);
	
				continue;
			}
	
			// set correct container
			$entity->container_guid = $parent->getGUID();
			$entity->save();
	
			// find the root page for the tree
			$root = self::findOldRootPage($entity);
			if (empty($root)) {
				// workaround for multi-site
				elgg_delete_metadata($metadata_options);
	
				continue;
			}
	
			// add relationship to the correct tree
			remove_entity_relationships($entity->getGUID(), 'subpage_of');
	
			$entity->addRelationship($root->getGUID(), 'subpage_of');
	
			// workaround for multi-site
			elgg_delete_metadata($metadata_options);
		}
	
		// restore access
		elgg_set_ignore_access($ia);
	}
	
	/**
	 * Find the root page based on the old tree structure
	 *
	 * @param ElggObject $entity the static page to find the root for
	 *
	 * @return false|ElggObject
	 */
	private static function findOldRootPage(ElggObject $entity) {
	
		if (!elgg_instanceof($entity, 'object', 'static')) {
			return false;
		}
	
		$parent_guid = (int) $entity->parent_guid;
		if (empty($parent_guid)) {
			return $entity;
		}
	
		$ia = elgg_set_ignore_access(true);
		$parent = get_entity($parent_guid);
		elgg_set_ignore_access($ia);
	
		if (!elgg_instanceof($parent, 'object', 'static')) {
			return $entity;
		}
	
		$root = self::findOldRootPage($parent);
		if (empty($root)) {
			return $parent;
		}
	
		return $root;
	}

	/**
	 * Registers the correct class for static objects
	 *
	 * @param string $event  the name of the event
	 * @param string $type   the type of the event
	 * @param mixed  $entity supplied entity
	 *
	 * @return void
	 */
	public static function registerClass($event, $type, $entity) {
	
		// set correct class handler for static pages
		if (get_subtype_id('object', \StaticPage::SUBTYPE)) {
			update_subtype('object', \StaticPage::SUBTYPE, 'StaticPage');
		} else {
			add_subtype('object', \StaticPage::SUBTYPE, 'StaticPage');
		}
	}
}