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
		
		switch (elgg_extract(0, $page)) {
			case 'view':
				echo elgg_view_resource('static/view', [
					'guid' =>  (int) elgg_extract(1, $page)
				]);
				return true;
			case 'edit':
				echo elgg_view_resource('static/edit', [
					'guid' =>  (int) elgg_extract(1, $page)
				]);
				return true;
			case 'add':
				echo elgg_view_resource('static/edit');
				return true;
			case 'group':
				$resource = 'static/group';
				if (elgg_extract(2, $page) === 'out_of_date') {
					$resource = 'static/out_of_date_group';
				}
				echo elgg_view_resource($resource, [
					'guid' =>  (int) elgg_extract(1, $page)
				]);
				return true;
			case 'out_of_date':
				
				$user = false;
				$username = elgg_extract(1, $page);
				if (!empty($username)) {
					$user = get_user_by_username($username);
				}
				
				if ($user instanceof \ElggUser) {
					elgg_set_page_owner_guid($user->getGUID());
					
					echo elgg_view_resource('static/out_of_date_owner', ['user' => $user]);
				} else {
					echo elgg_view_resource('static/out_of_date');
				}
				return true;
			case 'all':
			default:
				echo elgg_view_resource('static/all');
				return true;
		}
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
		
		if (!is_array($return_value)) {
			// someone else already routed this page
			return;
		}
		
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
			'subtype' => \StaticPage::SUBTYPE,
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