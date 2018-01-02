<?php

namespace ColdTrick\StaticPages;

/**
 * Upgrade
 */
class Upgrade {
	
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
		
	/**
	 * Migrates static pages to be using 'normal' containers and move parent relationship to parent_guid metadata
	 * The previous migration using static pages as containers backfired in places like search, (write) access, listings
	 *
	 * @param string $event  the name of the event
	 * @param string $type   the type of the event
	 * @param mixed  $entity supplied entity
	 *
	 * @return void
	 * @since 5.0
	 */
	public static function migrateContainers($event, $type, $object) {
		$ia = elgg_set_ignore_access(true);
		
		$path = 'admin/upgrades/static/migrate_containers';
		$upgrade = new \ElggUpgrade();
		if (!$upgrade->getUpgradeFromPath($path)) {
			$upgrade->setPath($path);
			$upgrade->title = elgg_echo('admin:upgrades:static:migrate_containers');
			$upgrade->description = elgg_echo('admin:upgrades:static:migrate_containers:description');
				
			$upgrade->save();
		}
		
		elgg_set_ignore_access($ia);
	}
	
	/**
	 * Delete orphaned static pages, during the migration to correct containers the deletion of child pages was forgotten
	 * when a parent page was deleted, the children remained.
	 * This upgrade will remove all orphaned child pages
	 *
	 * @param string $event  the name of the event
	 * @param string $type   the type of the event
	 * @param mixed  $entity supplied entity
	 *
	 * @return void
	 * @since 5.2
	 */
	public static function deleteOrphanedChildren($event, $type, $object) {
		
		$ia = elgg_set_ignore_access(true);
		
		$path = 'admin/upgrades/static/delete_orphaned_children';
		$upgrade = new \ElggUpgrade();
		if (!$upgrade->getUpgradeFromPath($path)) {
			$upgrade->setPath($path);
			$upgrade->title = elgg_echo('admin:upgrades:static:delete_orphaned_children');
			$upgrade->description = elgg_echo('admin:upgrades:static:delete_orphaned_children:description');
				
			$upgrade->save();
		}
		
		elgg_set_ignore_access($ia);
	}
}
