<?php

namespace ColdTrick\StaticPages;

/**
 * Menus
 */
class Menus {


	/**
	 * Orders the items in the static page menu
	 *
	 * @param string         $hook         'prepare'
	 * @param string         $type         'menu:page'
	 * @param ElggMenuItem[] $return_value the menu items
	 * @param array          $params       supplied params
	 *
	 * @return ElggMenuItem[]
	 */
	public static function pageMenuPrepare($hook, $type, $return_value, $params) {
		$static = elgg_extract('static', $return_value);
	
		if (is_array($static)) {
			$return_value['static'] = self::orderMenu($static);
		}
	
		return $return_value;
	}
	
	/**
	 * Recursively orders menu items
	 *
	 * @param array $menu_items array of menu items that need to be sorted
	 *
	 * @return array
	 */
	private static function orderMenu($menu_items) {
	
		if (!is_array($menu_items)) {
			return $menu_items;
		}
		
		$ordered = [];
		foreach($menu_items as $menu_item) {
			$children = $menu_item->getChildren();
			if ($children) {
				$ordered_children = self::orderMenu($children);
				$menu_item->setChildren($ordered_children);
			}
				
			$ordered[$menu_item->getPriority()] = $menu_item;
		}
		ksort($ordered);

		return $ordered;
	}
	
	/**
	 * Registers the static menu items for use on th edit page
	 *
	 * @param string         $hook         'register'
	 * @param string         $type         'menu:static_edit'
	 * @param ElggMenuItem[] $return_value the menu items
	 * @param array          $params       supplied params
	 *
	 * @return ElggMenuItem[]
	 */
	public static function registerStaticEditMenuItems($hook, $type, $return_value, $params) {
		$root_entity = elgg_extract('root_entity', $params);
		if (empty($root_entity)) {
			return;
		}
		$return_value = Cache::getMenuItemsCache($root_entity);
		if (empty($return_value)) {
			// no items in cache so generate menu + add them to the cache
			$return_value = Cache::generateMenuItemsCache($root_entity);
		}
			
		return $return_value;
	}
	
	/**
	 * Add menu items to the admin page menu
	 *
	 * @param string         $hook         'register'
	 * @param string         $type         'menu:owner_block'
	 * @param ElggMenuItem[] $return_value the menu items
	 * @param array          $params       supplied params
	 *
	 * @return ElggMenuItem[]
	 */
	public static function registerAdminPageMenuItems($hook, $type, $return_value, $params) {
		if (!elgg_in_context('admin') || !elgg_is_admin_logged_in()) {
			return;
		}
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'static_all',
			'href' => 'static/all',
			'text' => elgg_echo('static:all'),
			'context' => 'admin',
			'parent_name' => 'appearance',
			'section' => 'configure',
		]);
	
		return $return_value;
	}
	
	/**
	 * Add menu items to the owner block menu
	 *
	 * @param string         $hook         'register'
	 * @param string         $type         'menu:owner_block'
	 * @param ElggMenuItem[] $return_value the menu items
	 * @param array          $params       supplied params
	 *
	 * @return ElggMenuItem[]
	 */
	public static function ownerBlockMenuRegister($hook, $type, $return_value, $params) {

		$owner = elgg_extract('entity', $params);
		if (empty($owner) || !elgg_instanceof($owner, 'group')) {
			return;
		}
	
		if (!static_group_enabled($owner)) {
			return;
		}
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'static',
			'text' => elgg_echo('static:groups:owner_block'),
			'href' => "static/group/{$owner->getGUID()}",
		]);
	
		return $return_value;
	}
	
	/**
	 * Add menu items to the filter menu
	 *
	 * @param string         $hook         'register'
	 * @param string         $type         'menu:filter'
	 * @param ElggMenuItem[] $return_value the menu items
	 * @param array          $params       supplied params
	 *
	 * @return ElggMenuItem[]
	 */
	public static function filterMenuRegister($hook, $type, $return_value, $params) {
		
		if (!static_out_of_date_enabled()) {
			return;
		}
		
		if (!elgg_in_context('static')) {
			return;
		}
		
		$current_page = current_page_url();
		$out_of_date_selected = false;
		
		$page_owner = elgg_get_page_owner_entity();
		if (elgg_instanceof($page_owner, 'group')) {
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'all',
				'text' => elgg_echo('all'),
				'href' => "static/group/{$page_owner->getGUID()}",
				'is_trusted' => true,
				'priority' => 100,
			]);
			
			if ($page_owner->canEdit()) {
				
				$url = "static/group/{$page_owner->getGUID()}/out_of_date";
				if (strpos($current_page, elgg_normalize_url($url)) === 0) {
					$out_of_date_selected = true;
				}
				
				$return_value[] = \ElggMenuItem::factory([
					'name' => 'out_of_date_group',
					'text' => elgg_echo('static:menu:filter:out_of_date:group'),
					'href' => $url,
					'is_trusted' => true,
					'priority' => 250,
					'selected' => $out_of_date_selected,
				]);
			}
		} else {
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'all',
				'text' => elgg_echo('all'),
				'href' => 'static/all',
				'is_trusted' => true,
				'priority' => 100,
			]);
		}
		
		$user = elgg_get_logged_in_user_entity();
		if (!empty($user)) {
			$url = "static/out_of_date/{$user->username}";
			if (strpos($current_page, elgg_normalize_url($url)) === 0) {
				$out_of_date_selected = true;
			}
			
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'out_of_date_mine',
				'text' => elgg_echo('static:menu:filter:out_of_date:mine'),
				'href' => $url,
				'is_trusted' => true,
				'priority' => 300,
				'selected' => $out_of_date_selected,
			]);
		}
		
		if (elgg_is_admin_logged_in()) {
			
			$url = 'static/out_of_date';
			if (!$out_of_date_selected && strpos($current_page, elgg_normalize_url($url)) === 0) {
				$out_of_date_selected = true;
			} else {
				$out_of_date_selected = false;
			}
			
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'out_of_date',
				'text' => elgg_echo('static:menu:filter:out_of_date'),
				'href' => $url,
				'is_trusted' => true,
				'priority' => 200,
				'selected' => $out_of_date_selected,
			]);
		}
		
		return $return_value;
	}
	
	/**
	 * Add some menu items
	 *
	 * @param string         $hook         the name of the hook
	 * @param string         $type         the type of the hook
	 * @param ElggMenuItem[] $return_value current menu items
	 * @param array          $params       supplied params
	 *
	 * @return ElggMenuItem[]
	 */
	public static function entityMenuRegister($hook, $type, $return_value, $params) {
	
		$entity = elgg_extract('entity', $params);
		if (!elgg_instanceof($entity, 'object', 'static')) {
			return;
		}
	
		if (!$entity->canComment()) {
			return $return_value;
		}
		
		// add comment link
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'comments',
			'text' => elgg_view_icon('speech-bubble'),
			'href' => "{$entity->getURL()}#comments",
			'title' => elgg_echo('comment:this'),
			'priority' => 300,
		]);
	
		return $return_value;
	}
}
