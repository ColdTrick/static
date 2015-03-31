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
	$page_owner = elgg_get_page_owner_entity();
	
	if ($entity->getContainerGUID() == $entity->site_guid) {
		$root_entity = $entity;
	} elseif(!empty($page_owner) && ($entity->getContainerGUID() == $page_owner->getGUID())) {
		$root_entity = $entity;
	} else {
		$relations = $entity->getEntitiesFromRelationship(array("relationship" => "subpage_of", "limit" => 1));
		if ($relations) {
			$root_entity = $relations[0];
		}
	}

	if ($root_entity) {
		$priority = (int) $root_entity->order;
		if (empty($priority)) {
			$priority = (int) $root_entity->time_created;
		}
		
		$root_menu_options = array(
			"name" => $root_entity->getGUID(),
			"rel" => $root_entity->getGUID(),
			"href" => $root_entity->getURL(),
			"text" => '<span>' . $root_entity->title . '</span>',
			"priority" => $priority,
			"section" => "static"
		);
		
		if ($root_entity->canEdit()) {
			$root_menu_options["itemClass"] = array("static-sortable");
		}
		// add main menu items
		elgg_register_menu_item("page", $root_menu_options);

		// add sub menu items
		$ia = elgg_set_ignore_access(true);
		$submenu_options = array(
			"type" => "object",
			"subtype" => "static",
			"relationship_guid" => $root_entity->getGUID(),
			"relationship" => "subpage_of",
			"limit" => false,
			"inverse_relationship" => true
		);
		$submenu_entities = elgg_get_entities_from_relationship($submenu_options);
		elgg_set_ignore_access($ia);
		
		if ($submenu_entities) {
			foreach ($submenu_entities as $submenu_item) {
				
				if (!has_access_to_entity($submenu_item) && !$submenu_item->canEdit()) {
					continue;
				}
				
				$ia = elgg_set_ignore_access(true);
				$priority = (int) $submenu_item->order;
				if (empty($priority)) {
					$priority = (int) $submenu_item->time_created;
				}
				elgg_set_ignore_access($ia);
				
				elgg_register_menu_item("page", array(
					"name" => $submenu_item->getGUID(),
					"rel" => $submenu_item->getGUID(),
					"href" => $submenu_item->getURL(),
					"text" => '<span>' . $submenu_item->title . '</span>',
					"priority" => $priority,
					"parent_name" => $submenu_item->getContainerGUID(),
					"section" => "static"
				));
			}
		}
	}

	if ($entity->canEdit() && !elgg_instanceof($page_owner, "group")) {
		elgg_register_menu_item("page", array(
			"name" => "manage",
			"href" => "static/all",
			"text" => elgg_echo("static:all"),
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
		$ia = elgg_set_ignore_access(true);
		
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
				
				// did we reach the top page
				if (elgg_instanceof($parent, "object", "static")) {
					// not yet, so check further
					$result += static_get_parent_moderators($parent, $guid_only);
				}
			}
		}
		
		elgg_set_ignore_access($ia);
	}
	
	return $result;
}

/**
 * Get the parent select options for the edit form
 *
 * @param int $parent_guid the current parent to check the children of (default: site)
 * @param int $depth       internal depth counter
 *
 * @return array
 */
function static_get_parent_options($parent_guid = 0, $depth = 0) {
	$result = array();
	
	if (empty($parent_guid)) {
		$parent_guid = elgg_get_site_entity()->getGUID();
	}
	
	$parent = get_entity($parent_guid);
	if (elgg_instanceof($parent, "site") || elgg_instanceof($parent, "group")) {
		$result[0] = elgg_echo("static:new:parent:top_level");
	}
	
	$options = array(
		"type" => "object",
		"subtype" => "static",
		"container_guid" => $parent_guid,
		"limit" => false,
	);
	
	// more memory friendly
	$parent_entities = new ElggBatch("elgg_get_entities", $options);
	foreach ($parent_entities as $parent) {
		$result[$parent->getGUID()] = trim(str_repeat("-", $depth) . " " . $parent->title);
		
		$result += static_get_parent_options($parent->getGUID(), $depth + 1);
	}
	
	return $result;
}

/**
 * Make a unique friendly title/permalink, when editing it validates to make sure it's unique
 *
 * @param string $friendly_title the input friendly title
 * @param int    $entity_guid    when provided it validates for uniques
 *
 * @return bool|string false when not unique, string otherwise
 */
function static_make_friendly_title($friendly_title, $entity_guid = 0) {
	
	if (empty($friendly_title)) {
		return false;
	}
	
	$entity_guid = sanitise_int($entity_guid, false);
	$friendly_title = strtolower($friendly_title);
	
	// make an URL friendly title
	$friendly_title = str_replace('"', "", $friendly_title);
	$friendly_title = str_replace("'", "", $friendly_title);
	$friendly_title = str_replace("`", "", $friendly_title);
	$friendly_title = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($friendly_title, ENT_QUOTES, 'UTF-8'));
	$friendly_title = preg_replace('~&([a-z]{1,2})(quo);~i', '', $friendly_title); // rich text editor double quotes
	
	$friendly_title = elgg_get_friendly_title($friendly_title);
	
	// check for duplicates
	$options = array(
		"type" => "object",
		"subtype" => "static",
		"metadata_name_value_pairs" => array(
			"name" => "friendly_title",
			"value" => $friendly_title
		),
		"metadata_case_sensitive" => false,
		"count" => true
	);
	
	if (!empty($entity_guid)) {
		$options["wheres"] = array("(e.guid <> " . $entity_guid . ")");
	}
	
	$ia = elgg_set_ignore_access(true);
	$entities = elgg_get_entities_from_metadata($options);
	if (!empty($entities)) {
		
		if (!empty($entity_guid)) {
			elgg_set_ignore_access($ia);
			return false;
		}
		
		$counter = 1;
		$options["metadata_name_value_pairs"]["value"] = $friendly_title . $counter;
		while (elgg_get_entities_from_metadata($options)) {
			$counter++;
			$options["metadata_name_value_pairs"]["value"] = $friendly_title . $counter;
		}
		
		$friendly_title = $friendly_title . $counter;
	}
	
	elgg_set_ignore_access($ia);
	
	return $friendly_title;
}

/**
 * Recursively orders menu items
 *
 * @param array $menu_items array of menu items that need to be sorted
 *
 * @return array
 */
function static_order_menu($menu_items) {
	
	if (is_array($menu_items)) {
		$ordered = array();
		foreach($menu_items as $menu_item) {
			$children = $menu_item->getChildren();
			if ($children) {
				$ordered_children = static_order_menu($children);
				$menu_item->setChildren($ordered_children);
			}
			
			$ordered[$menu_item->getPriority()] = $menu_item;
		}
		ksort($ordered);
		
		return $ordered;
	} else {
		return $menu_items;
	}
}

/**
 * Remove the thumbnails from a static page
 *
 * @param int $entity_guid the GUID of the static page
 *
 * @return bool
 */
function static_remove_thumbnail($entity_guid) {
	
	$entity_guid = sanitize_int($entity_guid, false);
	
	if (empty($entity_guid)) {
		return false;
	}
	
	$entity = get_entity($entity_guid);
	if (empty($entity) || !elgg_instanceof($entity, "object", "static")) {
		return false;
	}
	
	if (!$entity->icontime) {
		return false;
	}
	
	$fh = new ElggFile();
	$fh->owner_guid = $entity_guid;
	
	$prefix = "thumb";
	$icon_sizes = elgg_get_config("icon_sizes");
	
	if (!empty($icon_sizes)) {
		foreach ($icon_sizes as $size => $info) {
			$fh->setFilename($prefix . $size . ".jpg");
			
			if ($fh->exists()) {
				$fh->delete();
			}
		}
		
		unset($entity->icontime);
	}
	
	return true;
}

/**
 * Check of the out of date listing/notifications is enabled
 *
 * @return bool
 */
function static_out_of_date_enabled() {
	static $result;
	
	if (!isset($result)) {
		$result = false;
		
		$setting = elgg_get_plugin_setting("enable_out_of_date", "static");
		if ($setting == "yes") {
			$days = (int) elgg_get_plugin_setting("out_of_date_days", "static");
			if ($days > 0) {
				$result = true;
			}
		}
	}
	
	return $result;
}

/**
 * Make sure all the children are in the correct tree
 *
 * @param ElggObject $entity    the entity to check the children from
 * @param int        $tree_guid the correct tree guid (will default to the given entity)
 *
 * @return bool
 */
function static_check_children_tree(ElggObject $entity, $tree_guid = 0) {
	
	if (empty($entity) || !elgg_instanceof($entity, "object", "static")) {
		return false;
	}
	
	$tree_guid = sanitise_int($tree_guid, false);
	if (empty($tree_guid)) {
		$tree_guid = $entity->getGUID();
	}
	
	$options = array(
		"type" => "object",
		"subtype" => "static",
		"owner_guid" => $entity->getOwnerGUID(),
		"container_guid" => $entity->getGUID(),
		"limit" => false
	);
	
	// ignore access for this part
	$ia = elgg_set_ignore_access(true);
	
	$batch = new ElggBatch("elgg_get_entities", $options);
	foreach ($batch as $static) {
		
		// remove old tree
		remove_entity_relationships($static->getGUID(), "subpage_of");
		
		// add new tree
		add_entity_relationship($static->getGUID(), "subpage_of", $tree_guid);
		
		// check children
		static_check_children_tree($static, $tree_guid);
	}
	
	// restore access
	elgg_set_ignore_access($ia);
	
	return true;
}

/**
 * Find the root page based on the old tree structure
 *
 * @param ElggObject $entity the static page to find the root for
 *
 * @return false|ElggObject
 */
function static_find_old_root_page(ElggObject $entity) {

	if (empty($entity) || !elgg_instanceof($entity, "object", "static")) {
		return false;
	}

	$parent_guid = (int) $entity->parent_guid;
	if (empty($parent_guid)) {
		return $entity;
	}

	$ia = elgg_set_ignore_access(true);
	$parent = get_entity($parent_guid);
	elgg_set_ignore_access($ia);

	if (empty($parent) || !elgg_instanceof($parent, "object", "static")) {
		return $entity;
	}

	$root = static_find_old_root_page($parent);
	if (empty($root)) {
		return $parent;
	}

	return $root;
}

/**
 * Check if group support is enabled
 *
 * @param ElggGroup $group (optional) check if the group has this enabled
 *
 * @return bool
 */
function static_group_enabled(ElggGroup $group = null) {
	static $plugin_setting;

	if (!isset($plugin_setting)) {
		$plugin_setting = false;

		$setting = elgg_get_plugin_setting("enable_groups", "static");
		if ($setting === "yes") {
			$plugin_setting = true;
		}
	}

	// shortcut
	if (!$plugin_setting) {
		return false;
	}

	if (empty($group) || !elgg_instanceof($group, "group")) {
		return $plugin_setting;
	}

	if ($group->static_enable !== "no") {
		return true;
	}

	return false;
}

