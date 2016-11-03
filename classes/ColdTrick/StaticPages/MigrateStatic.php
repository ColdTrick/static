<?php

namespace ColdTrick\StaticPages;

use ColdTrick\EntityTools\Migrate;

class MigrateStatic extends Migrate {
	
	/**
	 * Add static to the supported types for EntityTools
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param array  $return_value current return value
	 * @param mixed  $params       supplied params
	 *
	 * @return array
	 */
	public static function supportedSubtypes($hook, $type, $return_value, $params) {
		
		$return_value[\StaticPage::SUBTYPE] = self::class;
		
		return $return_value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::setSupportedOptions()
	 */
	protected function setSupportedOptions() {
		$this->supported_options = [
			'backdate' => true,
			'change_owner' => false,
			'change_container' => true,
		];
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
	}
}
