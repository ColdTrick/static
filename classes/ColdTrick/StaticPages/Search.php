<?php

namespace ColdTrick\StaticPages;

/**
 * Search
 */
class Search {
	
	/**
	 * Adds static pages to the search advanced autocomplete dropdown
	 *
	 * @param string $hook         'autocomplete'
	 * @param string $type         'search_advanced'
	 * @param array  $return_value current search results
	 * @param array  $params       supplied params
	 *
	 * @return array
	 */
	public static function searchAdvancedAutocomplete($hook, $type, $return_value, $params) {
	
		$query = elgg_extract('query', $params);
		if (empty($query)) {
			return;
		}
		
		$params['type'] = 'object';
		$params['subtype'] = \StaticPage::SUBTYPE;
	
		$entities = elgg_search($params);
	
		if (empty($entities)) {
			return;
		}
		
		$static_count = count($entities);
		
		if ($static_count >= elgg_extract('limit', $params)) {
			$params['count'] = true;
			$static_count = elgg_search($params);
		}

		$return_value[] = [
			'type' => 'placeholder',
			'content' => elgg_format_element('label', [], elgg_echo('item:object:static') . ' (' . $static_count . ')'),
			'href' => elgg_normalize_url('search?entity_subtype=static&entity_type=object&search_type=entities&q=' . $query),
		];

		foreach ($entities as $entity) {
			$return_value[] = [
				'type' => 'object',
				'value' => $entity->getDisplayName(),
				'href' => $entity->getURL(),
				'content' => elgg_view('static/search_advanced/item', ['entity' => $entity]),
			];
		}

		return $return_value;
	}
}