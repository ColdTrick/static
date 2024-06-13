<?php

namespace ColdTrick\StaticPages;

use ColdTrick\EntityTools\Migrate;

/**
 * Entity tools migration class
 */
class MigrateStatic extends Migrate {
	
	/**
	 * {@inheritdoc}
	 */
	public function canBackDate(): bool {
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canChangeOwner(): bool {
		return false;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canChangeContainer(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function changeContainer($new_container_guid): void {
		// do all the default stuff
		parent::changeContainer($new_container_guid);
		
		// also move owner
		$this->object->owner_guid = $new_container_guid;
		
		$this->object->save();

		if ($this->object->parent_guid !== 0) {
			return;
		}
		
		// update all children (assuming only top level pages can be moved)
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($new_container_guid) {
			$batch = new \ElggBatch('elgg_get_entities', [
				'type' => 'object',
				'subtype' => \StaticPage::SUBTYPE,
				'relationship_guid' => $this->object->guid,
				'relationship' => 'subpage_of',
				'limit' => false,
				'inverse_relationship' => true,
			]);
			
			foreach ($batch as $entity) {
				$migrate = new MigrateStatic($entity);
				$migrate->changeContainer($new_container_guid);
			}
		});
	}
}
