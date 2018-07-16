<?php
/**
 * All helper functions are bundled here
 */

/**
 * Register page menu items
 *
 * @param \StaticPage $entity base entity on which the menu will be created
 *
 * @return void
 */
function static_setup_page_menu(\StaticPage $entity) {
	
	if (!elgg_instanceof($entity, 'object', 'static')) {
		return;
	}
	
	elgg_require_js('static/sidebar_menu');
	
	$page_owner = elgg_get_page_owner_entity();
	$can_write = false;
	if ($page_owner) {
		$can_write = $page_owner->canWriteToContainer(0, 'object', 'static');
	}
	
	if ($can_write) {
		$ia = elgg_set_ignore_access(true);
	}
	
	$root_entity = $entity->getRootPage();
	
	if ($can_write) {
		elgg_set_ignore_access($ia);
	}
	
	if (!$root_entity instanceof \StaticPage) {
		return;
	}
	
	// check for availability in cache
	$static_items = \ColdTrick\StaticPages\Cache::getMenuItemsCache($root_entity);
	if (empty($static_items)) {
		// no items in cache so generate menu + add them to the cache
		$static_items = \ColdTrick\StaticPages\Cache::generateMenuItemsCache($root_entity);
	}
			
	if (empty($static_items) || count($static_items) < 2) {
		return;
	}
	
	global $CONFIG;
	
	// fetch all menu items the user has access to
	if ($can_write) {
		$ia = elgg_set_ignore_access(true);
	}
	$allowed_guids = elgg_get_entities([
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'relationship_guid' => $root_entity->getGUID(),
		'relationship' => 'subpage_of',
		'limit' => false,
		'inverse_relationship' => true,
		'callback' => function($row) {
			return (int) $row->guid;
		},
	]);
	if ($can_write) {
		elgg_set_ignore_access($ia);
	}
	$allowed_guids[] = $root_entity->guid;
	
	$manages_guids = null;
	foreach ($static_items as $item) {
		if (in_array($item->rel, $allowed_guids)) {
			// if you have access to the guid, then add menu item
			elgg_register_menu_item('page', $item);
		} else {
			// is the manager of any of the pages? If so do a canEdit check to determine if we can add it to the
			if (!isset($manages_guids)) {
				$manages_guids = static_check_moderator_in_list(array_keys($static_items));
			}
			
			if ($manages_guids) {
				$ia = elgg_set_ignore_access(true);
				// need to get without access otherwise we can not check for canEdit()
				$tmp_entity = get_entity($item->rel);
				elgg_set_ignore_access($ia);
				
				if (!($tmp_entity instanceof ElggObject)) {
					continue;
				}
				
				if ($tmp_entity->canEdit()) {
					elgg_register_menu_item('page', $item);
				}
			}
		}
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
		'metadata_names' => ['moderators'],
		'limit' => false,
		'joins' => ["JOIN {$dbprefix}metastrings msv ON n_table.value_id = msv.id"],
		'wheres' => ['msv.string <> ""'],
		'callback' => function($row) {
			$value = $row->value;
			if (!empty($value)) {
				return $value;
			}
		},
	]);
	elgg_set_ignore_access($ia);
	
	return in_array($user_guid, $md);
}

/**
 * Checks if the user is a moderator of any item in the given container
 *
 * @param ElggEntity $container_entity container entity to check in
 * @param ElggUser   $user             user to check
 *
 * @return boolean
 */
function static_is_moderator_in_container(ElggEntity $container_entity, ElggUser $user) {
	if (empty($container_entity) || empty($user)) {
		return false;
	}
	
	$dbprefix = elgg_get_config('dbprefix');
	
	$ia = elgg_set_ignore_access(true);
	$md = elgg_get_metadata([
		'selects' => ['msv.string as value'],
		'metadata_names' => ['moderators'],
		'limit' => false,
		'joins' => [
			"JOIN {$dbprefix}metastrings msv ON n_table.value_id = msv.id",
			"JOIN {$dbprefix}entities e ON n_table.entity_guid = e.guid"
		],
		'wheres' => [
			'msv.string <> ""',
			'e.type = "object" AND e.subtype = ' . get_subtype_id('object', 'static'),
			'e.container_guid = ' . $container_entity->getGUID()
		],
		'callback' => function($row) {
			$value = $row->value;
			if (!empty($value)) {
				return $value;
			}
		},
	]);
	elgg_set_ignore_access($ia);
	
	return in_array($user->getGUID(), $md);
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
	$result = [];
	
	if (!($entity instanceof \StaticPage)) {
		return;
	}
	
	$ia = elgg_set_ignore_access(true);
	
	if ($entity->parent_guid) {
		$parent = $entity->getParentPage();
		if (!empty($parent)) {
			$moderators = $parent->moderators;
			if (!empty($moderators)) {
				if (!is_array($moderators)) {
					$moderators = [$moderators];
				}
				
				foreach ($moderators as $user_guid) {
					$moderator = get_user($user_guid);
					if (empty($moderator)) {
						continue;
					}
					
					if (!$guid_only) {
						$result[$user_guid] = $moderator;
					} else {
						$result[] = $user_guid;
					}
				}
			}
			
			// check further up the tree
			$result += static_get_parent_moderators($parent, $guid_only);
		}
	}
	
	elgg_set_ignore_access($ia);
	
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
function static_get_parent_options($parent_guid = null, $depth = 0) {
	$result = [];
	
	if ($parent_guid === null) {
		$parent_guid = elgg_get_site_entity()->guid;
	}
	
	$parent = get_entity($parent_guid);
	if ($parent instanceof ElggSite || $parent instanceof ElggGroup) {
		$result[0] = elgg_echo('static:new:parent:top_level');
		$parent_guid = 0;
	}

	$can_write = $parent->canWriteToContainer(0, 'object', 'static');
	if ($can_write) {
		$ia = elgg_set_ignore_access(true);
	}
		
	// more memory friendly
	$parent_entities = new ElggBatch('elgg_get_entities', [
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'metadata_name_value_pair' => [
			'parent_guid' => $parent_guid,
		],
		'limit' => false,
	]);
	foreach ($parent_entities as $parent) {
		$result[$parent->guid] = trim(str_repeat('-', $depth) . ' ' . $parent->title);
		
		$result += static_get_parent_options($parent->guid, $depth + 1);
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
	
	$friendly_title = strtolower($friendly_title);
	$friendly_title = html_entity_decode($friendly_title);
	$friendly_title = preg_replace('/[^a-z0-9-_]/', '-', $friendly_title); // only allow a-z, 0-9, - and _
	$friendly_title = preg_replace('/-{2,}/', '-', $friendly_title); // replace multiple -- with only one -
	$friendly_title = trim($friendly_title, '-'); // remove trailing -
	
	if (empty($friendly_title)) {
		// only contained replaced chars
		return false;
	}
	
	$entity_guid = sanitise_int($entity_guid, false);
	
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
	
	// check handler
	return true;
	// @todo need to update with elgg()->routes->get(params)
	$router = _elgg_services()->router;
	$handlers = $router->getPageHandlers();
	
	if (elgg_extract($friendly_title, $handlers)) {
		return false;
	}
	
	// check for duplicates
	$options = [
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'metadata_name_value_pairs' => [
			'name' => 'friendly_title',
			'value' => $friendly_title,
		],
		'metadata_case_sensitive' => false,
		'count' => true,
	];
	
	if (!empty($entity_guid)) {
		$options['wheres'] = ["(e.guid <> {$entity_guid})"];
	}
	
	$ia = elgg_set_ignore_access(true);
	$entities = elgg_get_entities($options);
	elgg_set_ignore_access($ia);
	
	if (!empty($entities)) {
		return false;
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
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	
	// check the plugin settings
	$days = (int) elgg_get_plugin_setting('out_of_date_days', 'static');
	if ($days > 0) {
		$result = true;
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
	
	if (!elgg_instanceof($entity, 'object', 'static')) {
		return false;
	}
	
	$tree_guid = sanitise_int($tree_guid, false);
	if (empty($tree_guid)) {
		$tree_guid = $entity->getGUID();
	}
			
	// ignore access for this part
	$ia = elgg_set_ignore_access(true);
	
	$batch = new ElggBatch('elgg_get_entities', [
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'owner_guid' => $entity->getOwnerGUID(),
		'metadata_name_value_pair' => [
			'parent_guid' => $entity->getGUID(),
		],
		'limit' => false,
	]);
	foreach ($batch as $static) {
		
		// remove old tree
		remove_entity_relationships($static->getGUID(), 'subpage_of');
		
		// add new tree
		add_entity_relationship($static->getGUID(), 'subpage_of', $tree_guid);
		
		// check children
		static_check_children_tree($static, $tree_guid);
	}
	
	// restore access
	elgg_set_ignore_access($ia);
	
	return true;
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

		$setting = elgg_get_plugin_setting('enable_groups', 'static');
		if ($setting === 'yes') {
			$plugin_setting = true;
		}
	}

	// shortcut
	if (!$plugin_setting) {
		return false;
	}

	if (empty($group) || !elgg_instanceof($group, 'group')) {
		return $plugin_setting;
	}

	if ($group->static_enable !== 'no') {
		return true;
	}

	return false;
}
