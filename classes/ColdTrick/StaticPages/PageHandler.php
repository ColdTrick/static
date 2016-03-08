<?php

namespace ColdTrick\StaticPages;

/**
 * PageHandler
 */
class PageHandler {
	
	/**
	 * Handles the static pages
	 *
	 * @param array $page requested page
	 *
	 * @return boolean
	 */
	public static function staticHandler($page) {
		
		$resource_loaded = false;
		$vars = [];
		
		switch ($page[0]) {
			case 'view':
				$resource_loaded = true;
				
				$vars['guid'] = (int) elgg_extract('1', $page);
				
				echo elgg_view_resource('static/view', $vars);
				break;
			case 'edit':
				$vars['guid'] = (int) elgg_extract('1', $page);
			case 'add':
				$resource_loaded = true;
				
				echo elgg_view_resource('static/edit', $vars);
				break;
			case 'group':
				$resource_loaded = true;
				
				$vars['guid'] = (int) elgg_extract('1', $page);
				
				if (elgg_extract('2', $page) === 'out_of_date') {
					echo elgg_view_resource('static/out_of_date_group', $vars);
				} else {
					echo elgg_view_resource('static/group', $vars);
				}
				break;
			case 'out_of_date':
				$resource_loaded = true;
				
				$user = false;
				if (!empty($page[1])) {
					$user = get_user_by_username($page[1]);
				}
				
				if ($user instanceof \ElggUser) {
					$vars['user'] = $user;
					elgg_set_page_owner_guid($user->getGUID());
					
					echo elgg_view_resource('static/out_of_date_owner', $vars);
				} else {
					echo elgg_view_resource('static/out_of_date', $vars);
				}
				break;
			case 'all':
			default:
				$resource_loaded = true;
				
				echo elgg_view_resource('static/all', $vars);
				break;
		}
		
		// did we handle the page
		if ($resource_loaded) {
			return true;
		}
		
		return false;
	}
	
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
	public static function routeAll($hook, $type, $return_value, $params) {
		/**
		 * $return_value contains:
		 * $return_value['identifier'] => requested handler
		 * $return_value['segments'] => url parts ($page)
		 */
	
		$identifier = elgg_extract('identifier', $return_value);
		if (empty($identifier)) {
			return;
		}
		
		$router = _elgg_services()->router;
		$handlers = $router->getPageHandlers();

		if (elgg_extract($identifier, $handlers)) {
			return;
		}
		
		$ia = elgg_set_ignore_access(true);
		$entities = elgg_get_entities_from_metadata([
			'type' => 'object',
			'subtype' => 'static',
			'limit' => 1,
			'metadata_name_value_pairs' => ['friendly_title' => $identifier],
			'metadata_case_sensitive' => false,
		]);
		elgg_set_ignore_access($ia);
			
		if (empty($entities)) {
			return;
		}
		
		$entity = $entities[0];
		if (!has_access_to_entity($entity) && !$entity->canEdit()) {
			return;
		}
		
		$return_value['segments'] = ['view', $entity->getGUID()];
		$return_value['identifier'] = 'static';

		return $return_value;
	}
}