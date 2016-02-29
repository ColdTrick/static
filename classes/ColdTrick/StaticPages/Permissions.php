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
			return $return_value;
		}
	
		$entity = elgg_extract('entity', $params);
		$user = elgg_extract('user', $params);
	
		if (!elgg_instanceof($entity, 'object', 'static') || !elgg_instanceof($user, 'user')) {
			return $return_value;
		}
		
		// check if the owner is a group
		$owner = $entity->getOwnerEntity();
		if (!empty($owner) && elgg_instanceof($owner, 'group')) {
			// if you can edit the group, you can edit the static page
			if ($owner->canEdit($user->getGUID())) {
				return true;
			}
		}
	
		// check if the user is a moderator of this static page
		$ia = elgg_set_ignore_access(true);
		$moderators = $entity->moderators;
		elgg_set_ignore_access($ia);

		if (!empty($moderators)) {
			if (!is_array($moderators)) {
				$moderators = [$moderators];
			}
	
			if (in_array($user->getGUID(), $moderators)) {
				return true;
			}
		}
	
		// if not moderator, check higher pages (if any)
		if ($entity->getContainerGUID() !== $entity->site_guid) {
			$moderators = static_get_parent_moderators($entity, true);
	
			if (in_array($user->getGUID(), $moderators)) {
				return true;
			}
		}
	
		return $return_value;
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
		
		if (elgg_instanceof($container, 'site')) {
			return true;
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