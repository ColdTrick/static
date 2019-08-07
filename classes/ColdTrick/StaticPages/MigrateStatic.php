<?php

namespace ColdTrick\StaticPages;

use ColdTrick\EntityTools\Migrate;

class MigrateStatic extends Migrate {
	
	/**
	 * Add static to the supported types for EntityTools
	 *
	 * @param \Elgg\Hook $hook 'supported_types', 'entity_tools'
	 *
	 * @return array
	 */
	public static function supportedSubtypes(\Elgg\Hook $hook) {
		$return_value = $hook->getValue();
		$return_value[\StaticPage::SUBTYPE] = self::class;
		return $return_value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::canBackDate()
	 */
	public function canBackDate() {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::canChangeOwner()
	 */
	public function canChangeOwner() {
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::canChangeContainer()
	 */
	public function canChangeContainer() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::changeContainer()
	 */
	public function changeContainer($new_container_guid) {
		// do all the default stuff
		parent::changeContainer($new_container_guid);
		
		// also move owner
		$this->object->owner_guid = $new_container_guid;
		
		$this->object->save();

		if ($this->object->parent_guid !== 0) {
			return;
		}
		
		// update all children (assuming only top level pages can be moved)
		$ia = elgg_set_ignore_access(true);
		$batch = new \ElggBatch('elgg_get_entities', [
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'relationship_guid' => $this->object->getGUID(),
			'relationship' => 'subpage_of',
			'limit' => false,
			'inverse_relationship' => true,
		]);
		
		foreach ($batch as $entity) {
			$migrate = new MigrateStatic($entity);
			$migrate->changeContainer($new_container_guid);
		}
						
		elgg_set_ignore_access($ia);
	}
}
