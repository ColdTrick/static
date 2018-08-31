<?php

namespace ColdTrick\StaticPages;

class Elasticsearch {
	
	/**
	 * Change to owner of a static page, to allow the last editor to find it even if private
	 *
	 * @param \Elgg\Hook $hook 'to:object', 'entity'
	 *
	 * @return void|\stdClass
	 */
	public static function exportChangeOwner(\Elgg\Hook $hook) {
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \StaticPage) {
			return;
		}
		
		$last_editor = $entity->getLastEditor();
		if (!$last_editor instanceof \ElggUser) {
			return;
		}
		
		$return = $hook->getValue();
		
		$return->owner_guid = $last_editor->guid;
		
		return $return;
	}
}
