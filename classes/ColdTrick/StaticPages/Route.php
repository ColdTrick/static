<?php

namespace ColdTrick\StaticPages;

use Elgg\Exceptions\HttpException;
use Elgg\Http\ErrorResponse;

/**
 * Route related events
 */
class Route {
	
	/**
	 * Check if we can serve to a static page
	 *
	 * @param \Elgg\Event $event 'route:match', 'system'
	 *
	 * @return ?array
	 */
	public static function serveStatic(\Elgg\Event $event): ?array {
		$url = trim($event->getParam('pathinfo'), '/ ');
		if (empty($url)) {
			return null;
		}
		
		$entities = elgg_call(ELGG_IGNORE_ACCESS, function() use ($url) {
			return elgg_get_entities([
				'type' => 'object',
				'subtype' => \StaticPage::SUBTYPE,
				'limit' => 1,
				'metadata_name_value_pairs' => ['friendly_title' => $url],
				'metadata_case_sensitive' => false,
			]);
		});
		
		if (empty($entities)) {
			return null;
		}
		
		return [
			'route' => 'view:object:static',
			'resource' => 'static/view',
			'guid' => $entities[0]->guid,
		];
	}
}
