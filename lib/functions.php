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
	$static_items = array();
	
	$page_owner = elgg_get_page_owner_entity();
	$can_write = false;
	if ($page_owner) {
		$can_write = $page_owner->canWriteToContainer(0, "object", "static");
	}
	
	if ($can_write) {
		$ia = elgg_set_ignore_access(true);
	}
	
	$root_entity = static_get_root_entity($entity);
	
	if ($can_write) {
		elgg_set_ignore_access($ia);
	}
	
	if ($root_entity) {
		// check for availability in cache
		$static_items = static_get_menu_items_from_cache($root_entity);
		if (empty($static_items)) {
			// no items in cache so generate menu + add them to the cache
			$static_items = static_cache_menu_items($root_entity);
		}
		
		if (!empty($static_items)) {
			global $CONFIG;
			
			// fetch all menu items the user has access to
			$menu_options = array(
				"type" => "object",
				"subtype" => "static",
				"relationship_guid" => $root_entity->getGUID(),
				"relationship" => "subpage_of",
				"limit" => false,
				"inverse_relationship" => true,
				"callback" => function($row) {
					return (int) $row->guid;
				}
			);
			if ($can_write) {
				$ia = elgg_set_ignore_access(true);
			}
			$allowed_guids = elgg_get_entities_from_relationship($menu_options);
			if ($can_write) {
				elgg_set_ignore_access($ia);
			}
			$allowed_guids[] = $root_entity->guid;
			
			$manages_guids = null;
			foreach ($static_items as $item) {
				if (in_array($item->rel, $allowed_guids)) {
					// if you have access to the guid, then add menu item
					$CONFIG->menus['page'][] = $item;
				} else {
					// is the manager of any of the pages? If so do a canEdit check to determine if we can add it to the
					if (!isset($manages_guids)) {
						$manages_guids = static_check_moderator_in_list(array_keys($static_items));
					}
					
					if ($manages_guids) {
						$ia = elgg_set_ignore_access(true);
						// need to get without access otherwise we can not check for canEdit()
						$entity = get_entity($item->rel);
						elgg_set_ignore_access($ia);
						
						if ($entity->canEdit()) {
							$CONFIG->menus['page'][] = $item;
						}
					}
				}
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
 * Checks if the user is a moderator of any item in the given list of guids
 *
 * @param array $guids
 *
 * @return bool
 */
function static_check_moderator_in_list(array $guids) {
	if (empty($guids)) {
		return false;
	}
	
	$user_guid = elgg_get_logged_in_user_guid();
	if (!$user_guid) {
		return false;
	}
	
	$dbprefix = elgg_get_config('dbprefix');
	
	$ia = elgg_set_ignore_access(true);
	$md = elgg_get_metadata([
		'selects' => ['msv.string as value'],
		'guids' => $guids,
		'metadata_names' => array('moderators'),
		'limit' => false,
		'joins' => [
			"JOIN {$dbprefix}metastrings msv ON n_table.value_id = msv.id"
		],
		'wheres' => [
			'msv.string <> ""'
		],
		'callback' => function($row) {
			$value = $row->value;
			if (!empty($value)) {
				return $value;
			}
		}
	]);
	elgg_set_ignore_access($ia);
	
	return in_array($user_guid, $md);
}

/**
 * Returns the root entity for a given entity
 *
 * @param ElggEntity $entity the entity to look up the root entity of
 *
 * @return array|false
 */
function static_get_root_entity(ElggEntity $entity) {
	$root_entity = false;
	$page_owner = elgg_get_page_owner_entity();
	
	if ($entity->getContainerGUID() == $entity->site_guid) {
		// top page on site
		$root_entity = $entity;
	} elseif(!empty($page_owner) && ($entity->getContainerGUID() == $page_owner->getGUID())) {
		// top page in group
		$root_entity = $entity;
	} else {
		// subpage
		$relations = $entity->getEntitiesFromRelationship(array("relationship" => "subpage_of", "limit" => 1));
		if ($relations) {
			$root_entity = $relations[0];
		}
	}
	
	return $root_entity;
}

/**
 * Reads cached menu items from file for give root entity
 *
 * @param ElggEntity $root_entity root entity to fetch the cache from
 *
 * @return array
 */
function static_get_menu_items_from_cache(ElggEntity $root_entity) {
	$static_items = array();
	
	$file = new ElggFile();
	$file->owner_guid = $root_entity->guid;
	$file->setFilename('static_menu_item_cache');
	if ($file->exists()) {
		$static_items = unserialize($file->grabFile());
	}
	
	return $static_items;
}

/**
 * Caches menu items for a given entity and returns an array of the menu items
 *
 * @param ElggEntity $root_entity Root entity to fetch the menu items for
 *
 * @return array
 */
function static_cache_menu_items(ElggEntity $root_entity) {
	$static_items = array();
	
	if ($root_entity) {
		$priority = (int) $root_entity->order;
		if (empty($priority)) {
			$priority = (int) $root_entity->time_created;
		}
			
		$root_menu_options = array(
			"name" => $root_entity->guid,
			"rel" => $root_entity->guid,
			"href" => $root_entity->getURL(),
			"text" => '<span>' . $root_entity->title . '</span>',
			"priority" => $priority,
			"section" => "static"
		);
			
		if ($root_entity->canEdit()) {
			$root_menu_options["itemClass"] = array("static-sortable");
		}
		// add main menu items
		$static_items[$root_entity->guid] = \ElggMenuItem::factory($root_menu_options);
			
		// add all sub menu items so they are cacheable
		$ia = elgg_set_ignore_access(true);
		$submenu_options = array(
			"type" => "object",
			"subtype" => "static",
			"relationship_guid" => $root_entity->guid,
			"relationship" => "subpage_of",
			"limit" => false,
			"inverse_relationship" => true
		);
		$submenu_entities = elgg_get_entities_from_relationship($submenu_options);
			
		if ($submenu_entities) {
			foreach ($submenu_entities as $submenu_item) {
					
				if (!has_access_to_entity($submenu_item) && !$submenu_item->canEdit()) {
					continue;
				}
					
				$priority = (int) $submenu_item->order;
				if (empty($priority)) {
					$priority = (int) $submenu_item->time_created;
				}
					
				$options = array(
					"name" => $submenu_item->guid,
					"rel" => $submenu_item->guid,
					"href" => $submenu_item->getURL(),
					"text" => '<span>' . $submenu_item->title . '</span>',
					"priority" => $priority,
					"parent_name" => $submenu_item->getContainerGUID(),
					"section" => "static"
				);
				$static_items[$submenu_item->guid] = \ElggMenuItem::factory($options);
			}
		}
			
		elgg_set_ignore_access($ia);
	}
	
	$file = new ElggFile();
	$file->owner_guid = $root_entity->guid;
	$file->setFilename('static_menu_item_cache');
	$file->open('write');
	$file->write(serialize($static_items));
	$file->close();

	return $static_items;
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

	$can_write = $parent->canWriteToContainer(0, "object", "static");
	if ($can_write) {
		$ia = elgg_set_ignore_access(true);
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

	if ($can_write) {
		elgg_set_ignore_access($ia);
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
	
	$available = static_is_friendly_title_available($friendly_title, $entity_guid);
	
	if (!empty($entity_guid) && !$available) {
		// when editing an existing entity we will not generate a new name
		return false;
	}
	
	if (!$available) {
		// generate a new name
		$counter = 1;
		while (!static_is_friendly_title_available($friendly_title . $counter, $entity_guid)) {
			$counter++;
		}
		
		$friendly_title = $friendly_title . $counter;
	}
		
	return $friendly_title;
}

/**
 * Checks if a friendly title/permalink is available for use
 *
 * @param string $friendly_title the input friendly title
 * @param int    $entity_guid    when provided it validates for uniques
 *
 * @return bool true if available, false otherwise
 */
function static_is_friendly_title_available($friendly_title, $entity_guid) {
	if (empty($friendly_title)) {
		return false;
	}
	
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
	
	$router = _elgg_services()->router;
	$handlers = $router->getPageHandlers();
	
	elgg_set_ignore_access($ia);
	
	if (!empty($entities) || elgg_extract($friendly_title, $handlers)) {
		return false;
	}
	
	return true;
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

