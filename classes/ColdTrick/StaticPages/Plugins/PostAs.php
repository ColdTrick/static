<?php

namespace ColdTrick\StaticPages\Plugins;

/**
 * Support for the post_as plugin
 */
class PostAs {
	
	/**
	 * Register post_as support for static
	 *
	 * @param \Elgg\Event $event 'config', 'post_as'
	 *
	 * @return array
	 */
	public static function addConfig(\Elgg\Event $event): array {
		$result = $event->getValue();
		
		$result['static/edit'] = [
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
		];
		
		return $result;
	}
}
