<?php

namespace ColdTrick\StaticPages;

/**
 * Permissions
 */
class Permissions {
	
	/**
	 * Allow moderators to edit static pages and their children
	 *
	 * @param string $hook         'permissions_check'
	 * @param string $type         'object'
	 * @param bool   $return_value can the user edit this entity
	 * @param array  $params       supplied params
	 *
	 * @return bool
	 */
	public static function objectPermissionsCheck($hook, $type, $return_value, $params) {
		if ($return_value) {
			// already have access, no need to add
			return;
		}
	
		$entity = elgg_extract('entity', $params);
		$user = elgg_extract('user', $params);
	
		if (!$entity instanceof \StaticPage || !$user instanceof \ElggUser) {
			return;
		}
		
		// check if the owner is a group
		$owner = $entity->getOwnerEntity();
		if ($owner instanceof \ElggGroup) {
			// if you can edit the group, you can edit the static page
			if ($owner->canEdit($user->getGUID())) {
				return true;
			}
		}
	
		// check if the user is a moderator of this static page
		$moderators = $entity->moderators;
		$parent_guid = $entity->parent_guid;
		
		if (!empty($moderators)) {
			if (!is_array($moderators)) {
				$moderators = [$moderators];
			}
	
			if (in_array($user->getGUID(), $moderators)) {
				return true;
			}
		}
	
		// if not moderator, check higher pages (if any)
		if ($parent_guid) {
			$moderators = static_get_parent_moderators($entity, true);
	
			if (in_array($user->getGUID(), $moderators)) {
				return true;
			}
		}
	}
	
	/**
	 * Allow moderators to write static pages
	 *
	 * @param string $hook         'container_permissions_check'
	 * @param string $type         'object'
	 * @param bool   $return_value can the user write to this container
	 * @param array  $params       supplied params
	 *
	 * @return bool
	 */
	public static function containerPermissionsCheck($hook, $type, $return_value, $params) {
	
		if ($type !== 'object' || !is_array($params)) {
			return;
		}
		
		$container = elgg_extract('container', $params);
		$subtype = elgg_extract('subtype', $params);
		$user = elgg_extract('user', $params);
			
		if ($subtype !== 'static') {
			return;
		}
				
		if (elgg_instanceof($container, 'group') && !$container->canEdit()) {
			$return_value = false;
			if ($user) {
				$return_value = static_is_moderator_in_container($container, $user);
			}
		}
		return $return_value;
	}
}
