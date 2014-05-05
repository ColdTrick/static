<?php
/**
 * All helper functions are bundled here
 */

/**
 * Register page menu items
 *
 * @param ElggObject $entity base entity on which the menu will be created
 *
 * @return void
 */
function static_setup_page_menu($entity) {
	if ($entity->container_guid == $entity->site_guid) {
		$root_entity = $entity;
	} else {
		$relations = $entity->getEntitiesFromRelationship(array("relationship" => "subpage_of", "limit" => 1));
		if ($relations) {
			$root_entity = $relations[0];
		}
	}

	if ($root_entity) {
		// add main menu items
		elgg_register_menu_item("page", array(
		"name" => $root_entity->guid,
		"href" => $root_entity->getURL(),
		"text" => $root_entity->title,
		"section" => "static"
				));

		// add sub menu items
		$submenu_options = array("relationship_guid" => $root_entity->guid, "relationship" => "subpage_of", "limit" => false, "inverse_relationship" => true);
		$submenu_entities = elgg_get_entities_from_relationship($submenu_options);

		if ($submenu_entities) {
			foreach($submenu_entities as $submenu_item) {
				elgg_register_menu_item("page", array(
				"name" => $submenu_item->guid,
				"href" => $submenu_item->getURL(),
				"text" => $submenu_item->title,
				"parent_name" => $submenu_item->container_guid,
				"section" => "static"
						));
			}
		}
	}

	if ($entity->canEdit()) {
		elgg_register_menu_item('page', array(
		'name' => "manage",
		'href' => "static/all",
		'text' => elgg_echo("static:all"),
		"section" => "static_admin"
				));
	}
}

/**
 * Get the moderators of the parent page(s)
 * 
 * @param ElggObject $entity    the static page to check
 * @param bool       $guid_only return only guids (Default: false)
 * 
 * @return array in format array(guid => ElggUser)
 */
function static_get_parent_moderators(ElggObject $entity, $guid_only = false) {
	$result = array();
	
	if (!empty($entity) && elgg_instanceof($entity, "object", "static")) {
		if ($entity->getContainerGUID() != $entity->site_guid) {
			$parent = $entity->getContainerEntity();
			if (!empty($parent)) {
				$moderators = $parent->moderators;
				if (!empty($moderators)) {
					if(!is_array($moderators)) {
						$moderators = array($moderators);
					}
					
					foreach ($moderators as $user_guid) {
						$moderator = get_user($user_guid);
						if (!empty($moderator)) {
							if (!$guid_only) {
								$result[$user_guid] = $moderator;
							} else {
								$result[] = $user_guid;
							}
						}
					}
				}
				
				$result += static_get_parent_moderators($parent, $guid_only);
			}
		}
	}
	
	return $result;
}
