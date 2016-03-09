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
	
		$limit = (int) elgg_extract('limit', $params, 5);
	
		$options = [
			'type' => 'object',
			'subtype' => \StaticPage::SUBTYPE,
			'limit' => $limit,
			'joins' => ['JOIN ' . elgg_get_config('dbprefix') . 'objects_entity oe ON e.guid = oe.guid'],
			'wheres' => ["(oe.title LIKE '%{$query}%' OR oe.description LIKE '%{$query}%')"],
		];
		$entities = elgg_get_entities($options);
	
		if (empty($entities)) {
			return;
		}
		
		if (count($entities) >= $limit) {
			$options['count'] = true;
			$static_count = elgg_get_entities($options);
		} else {
			$static_count = count($entities);
		}

		$return_value[] = [
			'type' => 'placeholder',
			'content' => elgg_format_element('label', [], elgg_echo('item:object:static') . ' (' . $static_count . ')'),
			'href' => elgg_normalize_url('search?entity_subtype=static&entity_type=object&search_type=entities&q=' . $query),
		];

		foreach ($entities as $entity) {
			$return_value[] = [
				'type' => 'object',
				'value' => $entity->title,
				'href' => $entity->getURL(),
				'content' => elgg_view('static/search_advanced/item', ['entity' => $entity]),
			];
		}

		return $return_value;
	}
}