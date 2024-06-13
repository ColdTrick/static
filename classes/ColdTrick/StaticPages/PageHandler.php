<?php

namespace ColdTrick\StaticPages;

use Elgg\Http\ErrorResponse;
use Elgg\Exceptions\HttpException;
use Elgg\Http\Response;

/**
 * PageHandler
 */
class PageHandler {
		
	/**
	 * Check if requested page is a static page
	 *
	 * @param \Elgg\Event $event 'response', 'all'
	 *
	 * @return null|Response
	 */
	public static function respondAll(\Elgg\Event $event): ?Response {
	
		if ($event->getValue()->getStatusCode() !== 404) {
			return null;
		}
		
		list($path_type, $identifier) = explode(':', $event->getType());
		if ($path_type !== 'path') {
			return null;
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
			return null;
		}
		
		try {
			$content = elgg_view_resource('static/view', [
				'guid' => $entities[0]->guid
			]);
			
			return elgg_ok_response($content);
		} catch (HttpException $exception) {
			$response = new ErrorResponse($exception->getMessage(), $exception->getCode());
			$response->setException($exception);
			return $response;
		}
	}
}
