<?php

namespace ColdTrick\StaticPages;

/**
 * PageHandler
 */
class PageHandler {
		
	/**
	 * Check if requested page is a static page
	 *
	 * @param string $hook         name of the hook
	 * @param string $type         type of the hook
	 * @param array  $return_value return value
	 * @param array  $params       hook parameters
	 *
	 * @return array
	 */
	public static function respondAll($hook, $type, $return_value, $params) {
	
		if ($return_value->getStatusCode() !== 404) {
			return;
		}
		
		list($path_type, $identifier) = explode(':', $type);
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
		} catch (\Elgg\HttpException $exception) {
			return elgg_error_response($exception->getMessage(), REFERER, $exception->getCode());
		}
	}
}