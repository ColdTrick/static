<?php

namespace ColdTrick\StaticPages\Plugins;

/**
 * SearchAdvanced
 */
class SearchAdvanced {
	
	/**
	 * Adds static pages to the search advanced autocomplete dropdown
	 *
	 * @param \Elgg\Event $event 'autocomplete', 'search_advanced'
	 *
	 * @return array
	 */
	public static function searchAdvancedAutocomplete(\Elgg\Event $event) {
	
		$query = $event->getParam('query');
		if (empty($query)) {
			return;
		}
		
		$params = $event->getParams();
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

		$return_value = $event->getValue();
		$return_value[] = [
			'type' => 'placeholder',
			'content' => elgg_format_element('label', [], elgg_echo('item:object:static') . ' (' . $static_count . ')'),
			'href' => elgg_generate_url('default:search', [
				'entity_type' => 'object',
				'entity_subtype' => \StaticPage::SUBTYPE,
				'search_type' => 'entities',
				'q' => $query,
			]),
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
