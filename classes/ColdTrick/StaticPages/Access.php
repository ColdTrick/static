<?php

namespace ColdTrick\StaticPages;

/**
 * Changes to access rights
 */
class Access {
	
	/**
	 * Remove the friends access from the input/access for a static page
	 *
	 * @param \Elgg\Event $event 'access:collections:write', 'user'
	 *
	 * @return null|array
	 */
	public static function removeFriendsAccess(\Elgg\Event $event): ?array {
		$input_params = $event->getParam('input_params');
		if (!is_array($input_params) || empty($input_params)) {
			return null;
		}
		
		if (elgg_extract('entity_type', $input_params) !== 'object' || elgg_extract('entity_subtype', $input_params) !== \StaticPage::SUBTYPE) {
			return null;
		}
		
		$user_guid = $event->getParam('user_id');
		$user = get_user($user_guid);
		if (!$user instanceof \ElggUser) {
			return null;
		}
		
		$friends = $user->getOwnedAccessCollections([
			'subtype' => 'friends',
		]);
		if (empty($friends)) {
			return null;
		}
		
		$result = $event->getValue();
		
		foreach ($friends as $acl) {
			unset($result[$acl->id]);
		}
		
		return $result;
	}
	
	/**
	 * Remove the friends collections access from the input/access for a static page
	 *
	 * @param \Elgg\Event $event 'access:collections:write', 'user'
	 *
	 * @return null|array
	 */
	public static function removeFriendsCollectionsAccess(\Elgg\Event $event): ?array {
		$input_params = $event->getParam('input_params');
		if (!is_array($input_params) || empty($input_params)) {
			return null;
		}
		
		if (elgg_extract('entity_type', $input_params) !== 'object' || elgg_extract('entity_subtype', $input_params) !== \StaticPage::SUBTYPE) {
			return null;
		}
		
		$user_guid = $event->getParam('user_id');
		$user = get_user($user_guid);
		if (!$user instanceof \ElggUser) {
			return null;
		}
		
		$friends_collections = $user->getOwnedAccessCollections([
			'subtype' => 'friends_collection',
		]);
		if (empty($friends_collections)) {
			return null;
		}
		
		$result = $event->getValue();
		
		foreach ($friends_collections as $acl) {
			unset($result[$acl->id]);
		}
		
		return $result;
	}
}
