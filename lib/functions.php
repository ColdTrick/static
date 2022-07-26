<?php
/**
 * All helper functions are bundled here
 */

use Elgg\Database\QueryBuilder;

/**
 * Register page menu items
 *
 * @param \StaticPage $entity    base entity on which the menu will be created
 * @param string      $menu_name menu name (default page)
 *
 * @return void
 */
function static_setup_page_menu(\StaticPage $entity, string $menu_name = 'page'): void {
	
	elgg_require_js('static/sidebar_menu');
	
	$page_owner = elgg_get_page_owner_entity();
	$ignore_access = 0;
	if ($page_owner) {
		$ignore_access = $page_owner->canWriteToContainer(0, 'object', StaticPage::SUBTYPE) ? ELGG_IGNORE_ACCESS : 0;
	}
	
	$root_entity = $entity->getRootPage();
	
	// check for availability in cache
	$static_items = $root_entity->getMenuCache();
	if (empty($static_items)) {
		// no items in cache so generate menu + add them to the cache
		$static_items = \ColdTrick\StaticPages\Cache::generateMenuItemsCache($root_entity);
	}
			
	if (empty($static_items) || count($static_items) < 2) {
		return;
	}
	
	// fetch all menu items the user has access to
	$allowed_guids = elgg_call($ignore_access, function() use($root_entity) {
		return elgg_get_entities([
			'type' => 'object',
			'subtype' => StaticPage::SUBTYPE,
			'relationship_guid' => $root_entity->guid,
			'relationship' => 'subpage_of',
			'limit' => false,
			'inverse_relationship' => true,
			'callback' => function($row) {
				return (int) $row->guid;
			},
		]);
	});

	$allowed_guids[] = $root_entity->guid;
	
	$manages_guids = null;
	foreach ($static_items as $item) {
		if (in_array($item->rel, $allowed_guids)) {
			// if you have access to the guid, then add menu item
			elgg_register_menu_item($menu_name, $item);
		} else {
			// is the manager of any of the pages? If so do a canEdit check to determine if we can add it to the
			if (!isset($manages_guids)) {
				$manages_guids = static_check_moderator_in_list(array_keys($static_items));
			}
			
			if ($manages_guids) {
				// need to get without access otherwise we can not check for canEdit()
				$tmp_entity = elgg_call(ELGG_IGNORE_ACCESS, function() use ($item) {
					return get_entity($item->rel);
				});
				
				if (!$tmp_entity instanceof ElggObject) {
					continue;
				}
				
				if ($tmp_entity->canEdit()) {
					elgg_register_menu_item($menu_name, $item);
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
function static_check_moderator_in_list(array $guids): bool {
	if (empty($guids)) {
		return false;
	}
	
	$user_guid = elgg_get_logged_in_user_guid();
	if (!$user_guid) {
		return false;
	}
	
	$md = elgg_get_metadata([
		'guids' => $guids,
		'metadata_names' => ['moderators'],
		'limit' => false,
	]);
	
	if (empty($md)) {
		return false;
	}
	
	$user_guids = [];
	/* @var $metadata \ElggMetadata */
	foreach ($md as $metadata) {
		if ($metadata->value === '') {
			// shouldn't happen
			$metadata->delete();
			continue;
		}
		
		$user_guids[] = $metadata->value;
	}
	
	return in_array($user_guid, $user_guids);
}

/**
 * Checks if the user is a moderator of any item in the given container
 *
 * @param ElggEntity $container_entity container entity to check in
 * @param ElggUser   $user             user to check
 *
 * @return boolean
 */
function static_is_moderator_in_container(\ElggEntity $container_entity, \ElggUser $user): bool {
		
	$md = elgg_get_metadata([
		'type' => 'object',
		'subtype' => StaticPage::SUBTYPE,
		'container_guid' => $container_entity->guid,
		'metadata_names' => ['moderators'],
		'limit' => false,
	]);
	if (empty($md)) {
		return false;
	}
	
	$user_guids = [];
	/* @var $metadata ElggMetadata */
	foreach ($md as $metadata) {
		if ($metadata->value === '') {
			// shouldn't happen
			$metadata->delete();
			continue;
		}
		
		$user_guids[] = $metadata->value;
	}
	
	return in_array($user->guid, $user_guids);
}

/**
 * Get the moderators of the parent page(s)
 *
 * @param ElggObject $entity    the static page to check
 * @param bool       $guid_only return only guids (Default: false)
 *
 * @return array in format array(guid => ElggUser)
 */
function static_get_parent_moderators(\ElggObject $entity, bool $guid_only = false): array {
	
	if (!$entity instanceof \StaticPage) {
		return [];
	}
	
	if (!$entity->parent_guid) {
		return [];
	}
	
	$result = [];
	
	elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity, $guid_only, &$result) {
		$parent = $entity->getParentPage();
		if (!$parent instanceof \StaticPage) {
			return;
		}
		
		$moderators = $parent->moderators;
		if (empty($moderators)) {
			return;
		}
		
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
		
		// check further up the tree
		$result += static_get_parent_moderators($parent, $guid_only);
	});
	
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
function static_get_parent_options($parent_guid = null, int $depth = 0): array {
	$result = [];
	
	if ($parent_guid === null) {
		$parent_guid = elgg_get_site_entity()->guid;
	}
	
	$parent = get_entity($parent_guid);
	if ($parent instanceof \ElggSite || $parent instanceof \ElggGroup) {
		$result[0] = elgg_echo('static:new:parent:top_level');
		$parent_guid = 0;
	}

	$ignore_access = $parent->canWriteToContainer(0, 'object', StaticPage::SUBTYPE) ? ELGG_IGNORE_ACCESS : 0;
	
	elgg_call($ignore_access, function () use ($parent_guid, &$result, $depth) {
		// more memory friendly
		$parent_entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => StaticPage::SUBTYPE,
			'metadata_name_value_pair' => [
				'parent_guid' => $parent_guid,
			],
			'limit' => false,
			'batch' => true,
		]);
		
		foreach ($parent_entities as $parent) {
			$result[$parent->guid] = trim(str_repeat('-', $depth) . ' ' . $parent->title);
			
			$result += static_get_parent_options($parent->guid, $depth + 1);
		}
	});
	
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
function static_make_friendly_title(string $friendly_title, int $entity_guid = 0) {
	
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
	
	$entity_guid = (int) $entity_guid;
	
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
function static_is_friendly_title_available(string $friendly_title, int $entity_guid = 0): bool {
	
	if (empty($friendly_title)) {
		return false;
	}
	
	// check handler
	$dummy_request = _elgg_services()->request->create($friendly_title);
	try {
		$match = _elgg_services()->urlMatcher->match($dummy_request->getPathInfo());
		
		return false;
	} catch (Exception $e) {
		// no route match found, can continue
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
	];
	
	if (!empty($entity_guid)) {
		$options['wheres'][] = function(QueryBuilder $qb, $main_alias) use ($entity_guid) {
			return $qb->compare("{$main_alias}.guid", '!=', $entity_guid, ELGG_VALUE_GUID);
		};
	}
		
	return elgg_call(ELGG_IGNORE_ACCESS, function() use ($options){
		return empty(elgg_count_entities($options));
	});
}

/**
 * Check of the out of date listing/notifications is enabled
 *
 * @return bool
 */
function static_out_of_date_enabled(): bool {
	return (int) elgg_get_plugin_setting('out_of_date_days', 'static') > 0;
}

/**
 * Make sure all the children are in the correct tree
 *
 * @param ElggObject $entity    the entity to check the children from
 * @param int        $tree_guid the correct tree guid (will default to the given entity)
 *
 * @return bool
 */
function static_check_children_tree(\ElggObject $entity, int $tree_guid = 0): bool {
	
	if (!$entity instanceof StaticPage) {
		return false;
	}
	
	if ($tree_guid < 1) {
		$tree_guid = $entity->guid;
	}
	
	// ignore access for this part
	elgg_call(ELGG_IGNORE_ACCESS, function() use ($entity, $tree_guid) {
		$batch = elgg_get_entities([
			'type' => 'object',
			'subtype' => StaticPage::SUBTYPE,
			'owner_guid' => $entity->owner_guid,
			'metadata_name_value_pair' => [
				'parent_guid' => $entity->guid,
			],
			'limit' => false,
			'batch' => true,
		]);
		
		/* @var $static StaticPage */
		foreach ($batch as $static) {
			
			// remove old tree
			$static->removeAllRelationships('subpage_of');
			
			// add new tree
			$static->addRelationship($tree_guid, 'subpage_of');
			
			// check children
			static_check_children_tree($static, $tree_guid);
		}
	});
	
	return true;
}

/**
 * Check if group support is enabled
 *
 * @param ElggGroup $group (optional) check if the group has this enabled
 *
 * @return bool
 */
function static_group_enabled(\ElggGroup $group = null): bool {
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

	if (!$group instanceof \ElggGroup) {
		return $plugin_setting;
	}

	return $group->isToolEnabled('static');
}
