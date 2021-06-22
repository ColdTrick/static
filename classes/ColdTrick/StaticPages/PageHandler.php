<?php

namespace ColdTrick\StaticPages;

use Elgg\Http\ErrorResponse;
use Elgg\Exceptions\HttpException;

/**
 * PageHandler
 */
class PageHandler {
		
	/**
	 * Check if requested page is a static page
	 *
	 * @param \Elgg\Hook $hook 'response', 'all'
	 *
	 * @return array
	 */
	public static function respondAll(\Elgg\Hook $hook) {
	
		if ($hook->getValue()->getStatusCode() !== 404) {
			return;
		}
		
		list($path_type, $identifier) = explode(':', $hook->getType());
		if ($path_type !== 'path') {
			return;
		}
		
		$entities = elgg_call(ELGG_IGNORE_ACCESS, function() use ($identifier) {
			return elgg_get_entities([
				'type' => 'object',
				'subtype' => \StaticPage::SUBTYPE,
				'limit' => 1,
				'metadata_name_value_pairs' => ['friendly_title' => $identifier],
				'metadata_case_sensitive' => false,
			]);
		});
		if (empty($entities)) {
			return;
		}
		
		$entity = $entities[0];
		
		try {
			$content = elgg_view_resource('static/view', [
				'guid' => $entity->guid
			]);
			
			return elgg_ok_response($content);
		} catch (HttpException $exception) {
			return new ErrorResponse($exception->getMessage(), $exception->getCode());
		}
	}
}
