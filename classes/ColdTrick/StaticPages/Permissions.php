<?php

namespace ColdTrick\StaticPages;

/**
 * Permissions
 */
class Permissions {
	
	/**
	 * Allow moderators to edit static pages and their children
	 *
	 * @param \Elgg\Event $event 'permissions_check', 'object'
	 *
	 * @return bool
	 */
	public static function objectPermissionsCheck(\Elgg\Event $event) {
		if ($event->getValue()) {
			// already have access, no need to add
			return;
		}
	
		$entity = $event->getEntityParam();
		$user = $event->getUserParam();
	
		if (!$entity instanceof \StaticPage || !$user instanceof \ElggUser) {
			return;
		}
		
		// allowed if you are the last editor
		$last_editor = $entity->getLastEditor();
		if ($last_editor instanceof \ElggUser && $last_editor->guid === $user->guid) {
			return true;
		}
		
		// check if the owner is a group
		$owner = $entity->getOwnerEntity();
		if ($owner instanceof \ElggGroup) {
			// if you can edit the group, you can edit the static page
			if ($owner->canEdit($user->guid)) {
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
	
			if (in_array($user->guid, $moderators)) {
				return true;
			}
		}
	
		// if not moderator, check higher pages (if any)
		if ($parent_guid) {
			$moderators = static_get_parent_moderators($entity, true);
	
			if (in_array($user->guid, $moderators)) {
				return true;
			}
		}
	}
	
	/**
	 * Allow moderators to write static pages
	 *
	 * @param \Elgg\Event $event 'container_permissions_check', 'object'
	 *
	 * @return bool
	 */
	public static function containerPermissionsCheck(\Elgg\Event $event) {
	
		if ($event->getType() !== 'object') {
			return;
		}

		if ($event->getParam('subtype') !== 'static') {
			return;
		}
		
		$container = $event->getParam('container');
		$return_value = $event->getValue();
		if ($container instanceof \ElggGroup && !$container->canEdit()) {
			$return_value = false;
			
			$user = $event->getUserParam();
			if ($user) {
				$return_value = static_is_moderator_in_container($container, $user);
			}
		}
		
		return $return_value;
	}
	
	/**
	 * Allow access to (private) static pages during certain actions
	 *
	 * @param \Elgg\Event $event 'action:validate', 'entity/delete'|'entity_attachments/add'
	 *
	 * @return void
	 */
	public static function allowActionAccessToPrivateEntity(\Elgg\Event $event) {
		$entity_guid = (int) get_input('guid');
		if (empty($entity_guid)) {
			return;
		}
		
		$entity = elgg_call(ELGG_IGNORE_ACCESS, function() use ($entity_guid) {
			return get_entity($entity_guid);
		});
			
		if (!$entity instanceof \StaticPage) {
			return;
		}
		
		if (!$entity->canEdit()) {
			return;
		}
		
		// the entity delete action might not be able to get privately owned static pages
		elgg_register_event_handler('get_sql', 'access', function(\Elgg\Event $event) use ($entity_guid) {
			if ($event->getParam('ignore_access')) {
				// access is ignored, no need for additional query parts
				return;
			}
			
			$result = $event->getValue();
			
			/* @var $qb QueryBuilder */
			$qb = $event->getParam('query_builder');
			$table_alias = $event->getParam('table_alias');
			$guid_column = $event->getParam('guid_column');
			
			$alias = function ($column) use ($table_alias) {
				return $table_alias ? "{$table_alias}.{$column}" : $column;
			};
			
			$result['ors']['special_static_access'] = $qb->compare($alias($guid_column), '=', $entity_guid);
			
			return $result;
		});
	}
}
