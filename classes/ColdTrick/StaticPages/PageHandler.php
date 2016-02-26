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
		$root = elgg_get_plugins_path() . 'static';
		switch ($page[0]) {
			case 'view':
				set_input('guid', $page[1]);
				include($root . '/pages/view.php');
				break;
			case 'edit':
				set_input('guid', $page[1]);
			case 'add':
				include($root . '/pages/edit.php');
				break;
			case 'group':
				set_input('guid', $page[1]);
				
				if (!empty($page[2]) && ($page[2] == 'out_of_date')) {
					include($root . '/pages/out_of_date_group.php');
				} else {
					include($root . '/pages/group.php');
				}
				break;
			case 'out_of_date':
				$user = false;
				if (!empty($page[1])) {
					$user = get_user_by_username($page[1]);
				}
				
				if ($user) {
					elgg_set_page_owner_guid($user->getGUID());
					
					include($root . '/pages/out_of_date_owner.php');
				} else {
					include($root . '/pages/out_of_date.php');
				}
				break;
			case 'all':
			default:
				include($root . '/pages/all.php');
				break;
		}
		
		return true;
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