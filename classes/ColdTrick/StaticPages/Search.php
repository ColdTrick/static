<?php

namespace ColdTrick\StaticPages;

/**
 * Search
 */
class Search {
	
	/**
	 * Adds static pages to the search advanced autocomplete dropdown
	 *
	 * @param \Elgg\Hook $hook 'autocomplete', 'search_advanced'
	 *
	 * @return array
	 */
	public static function searchAdvancedAutocomplete(\Elgg\Hook $hook) {
	
		$query = $hook->getParam('query');
		if (empty($query)) {
			return;
		}
		
		$params = $hook->getParams();
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

		$return_value = $hook->getValue();
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
