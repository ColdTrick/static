<?php

namespace ColdTrick\StaticPages;

use Elgg\Menu\MenuItems;

/**
 * Menus
 */
class Menus {

	/**
	 * Orders the items in the static page menu
	 *
	 * @param \Elgg\Event $event 'prepare', 'menu:page'
	 *
	 * @return \ElggMenuItem[]
	 */
	public static function pageMenuPrepare(\Elgg\Event $event) {
		
		$return_value = $event->getValue();
		
		$static = elgg_extract('static', $return_value);
		if (!$static instanceof \Elgg\Menu\MenuSection) {
			return;
		}
		
		$ordered = self::orderMenu($static->getItems());
		$return_value['static'] = $static->fill($ordered);
	
		return $return_value;
	}
	
	/**
	 * Recursively orders menu items
	 *
	 * @param array $menu_items array of menu items that need to be sorted
	 *
	 * @return array
	 */
	protected static function orderMenu($menu_items) {
		
		if (!is_array($menu_items)) {
			return $menu_items;
		}
		
		$ordered = [];
		foreach ($menu_items as $menu_item) {
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
	 * Registers the static menu items for use on the edit page
	 *
	 * @param \Elgg\Event $event 'register', 'menu:static_edit'
	 *
	 * @return \ElggMenuItem[]
	 */
	public static function registerStaticEditMenuItems(\Elgg\Event $event) {
		$root_entity = $event->getParam('root_entity');
		if (!$root_entity instanceof \StaticPage) {
			return;
		}
		
		$return_value = $root_entity->getMenuCache();
		if (empty($return_value)) {
			// no items in cache so generate menu + add them to the cache
			$return_value = Cache::generateMenuItemsCache($root_entity);
		}
			
		return $return_value;
	}
	
	/**
	 * Add menu items to the admin header menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:admin_header'
	 *
	 * @return \ElggMenuItem[]
	 */
	public static function registerAdminHeaderMenuItems(\Elgg\Event $event) {
		if (!elgg_is_admin_logged_in()) {
			return;
		}
		
		$return_value = $event->getValue();
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'static_all',
			'href' => elgg_generate_url('collection:object:static:all'),
			'text' => elgg_echo('static:all'),
			'parent_name' => 'utilities',
		]);
	
		return $return_value;
	}
	
	/**
	 * Add menu items to the owner block menu of groups
	 *
	 * @param \Elgg\Event $event 'register', 'menu:owner_block'
	 *
	 * @return void|MenuItems
	 */
	public static function ownerBlockMenuRegister(\Elgg\Event $event) {

		$owner = $event->getEntityParam();
		if (!$owner instanceof \ElggGroup || !static_group_enabled($owner)) {
			return;
		}
		
		$return_value = $event->getValue();
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'static',
			'text' => elgg_echo('static:groups:owner_block'),
			'href' => elgg_generate_url('collection:object:static:group', [
				'guid' => $owner->guid,
			]),
		]);
	
		return $return_value;
	}
	
	/**
	 * Add menu items to the owner block menu of users
	 *
	 * @param \Elgg\Event $event 'register', 'menu:owner_block'
	 *
	 * @return void|MenuItems
	 */
	public static function userOwnerBlockMenuRegister(\Elgg\Event $event) {

		$owner = $event->getEntityParam();
		if (!$owner instanceof \ElggUser || !$owner->canEdit()) {
			return;
		}
		
		$return_value = $event->getValue();
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'last_editor',
			'text' => elgg_echo('static:menu:owner_block:last_editor'),
			'href' => elgg_generate_url('collection:object:static:user:last_editor', [
				'username' => $owner->username,
			]),
			'is_trusted' => true,
		]);
	
		return $return_value;
	}
	
	/**
	 * Add menu items to the filter menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:filter:static'
	 *
	 * @return void|MenuItems
	 */
	public static function filterMenuRegister(\Elgg\Event $event) {
		
		if (!elgg_is_logged_in()) {
			return;
		}
		
		/* @var $return_value MenuItems */
		$return_value = $event->getValue();
		$page_owner = elgg_get_page_owner_entity();
		
		if (!$page_owner instanceof \ElggGroup) {
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'all',
				'text' => elgg_echo('all'),
				'href' => elgg_generate_url('collection:object:static:all'),
				'is_trusted' => true,
				'priority' => 100,
			]);
			
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'last_editor',
				'text' => elgg_echo('mine'),
				'href' => elgg_generate_url('collection:object:static:user:last_editor', [
					'username' => elgg_get_logged_in_user_entity()->username,
				]),
				'is_trusted' => true,
				'priority' => 150,
			]);
		}
		
		if (!static_out_of_date_enabled()) {
			return;
		}
		
		if ($page_owner instanceof \ElggGroup) {
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'all',
				'text' => elgg_echo('all'),
				'href' => elgg_generate_url('collection:object:static:group', [
					'guid' => $page_owner->guid,
				]),
				'is_trusted' => true,
				'priority' => 100,
			]);
			
			if ($page_owner->canEdit()) {
				$return_value[] = \ElggMenuItem::factory([
					'name' => 'out_of_date_group',
					'text' => elgg_echo('static:menu:filter:out_of_date:group'),
					'href' => elgg_generate_url('collection:object:static:group:out_of_date', [
						'guid' => $page_owner->guid,
					]),
					'is_trusted' => true,
					'priority' => 250,
				]);
			}
		}
		
		$user = elgg_get_logged_in_user_entity();
		if (!empty($user)) {
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'out_of_date_mine',
				'text' => elgg_echo('static:menu:filter:out_of_date:mine'),
				'href' => elgg_generate_url('collection:object:static:user:out_of_date', [
					'username' => $user->username,
				]),
				'is_trusted' => true,
				'priority' => 300,
			]);
		}
		
		if (elgg_is_admin_logged_in()) {
			$return_value[] = \ElggMenuItem::factory([
				'name' => 'out_of_date',
				'text' => elgg_echo('static:menu:filter:out_of_date'),
				'href' => elgg_generate_url('collection:object:static:out_of_date'),
				'is_trusted' => true,
				'priority' => 200,
			]);
		}
		
		return $return_value;
	}
	
	/**
	 * Change some information in the delete link of static pages
	 *
	 * @param \Elgg\Event $event 'register', 'menu:entity'
	 *
	 * @return void|MenuItems
	 */
	public static function changeDeleteItem(\Elgg\Event $event) {
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \StaticPage) {
			return;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
		$delete = $result->get('delete');
		if (!$delete instanceof \ElggMenuItem) {
			return;
		}
		
		$parent = $entity->getParentPage();
		$forward_url = null;
		if (empty($parent) || $parent->guid === $entity->guid) {
			$container = $entity->getContainerEntity();
			if ($container instanceof \ElggGroup) {
				$forward_url = elgg_generate_url('collection:object:static:group', [
					'guid' => $container->guid,
				]);
			} else {
				$forward_url = elgg_generate_url('collection:object:static:all');
			}
		} else {
			$forward_url = $parent->getURL();
		}
		
		$delete->setHref(elgg_http_add_url_query_elements($delete->getHref(), [
			'forward_url' => $forward_url,
		]));
		
		return $result;
	}
}
