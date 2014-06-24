<?php
/**
 * All plugin hooks are bundled here
 */

/**
 * Check if requested page is a static page
 *
 * @param string $hook         name of the hook
 * @param string $type         type of the hook
 * @param array  $return_value return value
 * @param array  $params       hook parameters
 *
 * @return array
 */
function static_route_hook_handler($hook, $type, $return_value, $params) {
	/**
	 * $return_value contains:
	 * $return_value['identifier'] => requested handler
	 * $return_value['segments'] => url parts ($page)
	 */

	$identifier = $return_value['identifier'];

	if (!empty($identifier)) {
		$router = _elgg_services()->router;
		$handlers = $router->getPageHandlers();

		if (!elgg_extract($identifier, $handlers)) {
			$options = array(
				"type" => "object",
				"subtype" => "static",
				"limit" => 1,
				"metadata_name_value_pairs" => array("friendly_title" => $identifier)
			);
				
			$entities = elgg_get_entities_from_metadata($options);
			if (!empty($entities)) {
				$entity_guid = $entities[0]->getGUID();

				$return_value['segments'] = array("view", $entity_guid);
				$return_value['identifier'] = "static";

				return $return_value;
			}
		}
	}
}

/**
 * Returns a url for a static content page
 *
 * @param string $hook         name of the hook
 * @param string $type         type of the hook
 * @param array  $return_value return value
 * @param array  $params       hook parameters
 *
 * @return string
 */
function static_entity_url_hook_handler($hook, $type, $return_value, $params) {
	$entity = $params["entity"];
	if (elgg_instanceof($entity, "object", "static")) {
		$friendly_title = $entity->friendly_title;
		if ($friendly_title) {
			return elgg_get_site_url() . $friendly_title;
		} else {
			$friendly_title = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($entity->title, ENT_QUOTES, 'UTF-8'));
			$friendly_title = elgg_get_friendly_title($friendly_title);
			$entity->friendly_title = $friendly_title;

			return $entity->getURL();
		}
	}
}

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
function static_permissions_check_hook_handler($hook, $type, $return_value, $params) {
	
	if (!$return_value && !empty($params) && is_array($params)) {
		$entity = elgg_extract("entity", $params);
		$user = elgg_extract("user", $params);
		
		if (!empty($entity) && elgg_instanceof($entity, "object", "static")) {
			// check if the user is a moderator of this static page
			$moderators = $entity->moderators;
			if (!empty($moderators)) {
				if (!is_array($moderators)) {
					$moderators = array($moderators);
				}
				
				$return_value = in_array($user->getGUID(), $moderators);
			}
			
			// if not moderator, check higher pages (if any)
			if (!$return_value && ($entity->getContainerGUID() != $entity->site_guid)) {
				$moderators = static_get_parent_moderators($entity, true);
				if (!empty($moderators)) {
					$return_value = in_array($user->getGUID(), $moderators);
				}
			}
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
function static_container_permissions_check_hook_handler($hook, $type, $return_value, $params) {
	
	if (!empty($type) && $type == "object") {
		
		if (!empty($params) && is_array($params)) {
			$container = elgg_extract("container", $params);
			$subtype = elgg_extract("subtype", $params);
			
			if ($subtype == "static" && elgg_instanceof($container, "site")) {
				$return_value = true;
			} elseif ($subtype == "static" && elgg_instanceof($container, "group") && !$container->canEdit()) {
				$return_value = false;
			}
		}
	}
	
	return $return_value;
}

/**
 * Add menu items to the owner block menu
 *
 * @param string         $hook         'register'
 * @param string         $type         'menu:owner_block'
 * @param ElggMenuItem[] $return_value the menu items
 * @param array          $params       supplied params
 *
 * @return ElggMenuItem[]
 */
function static_register_owner_block_menu_hook_handler($hook, $type, $return_value, $params) {
	
	if (!empty($params) && is_array($params)) {
		$owner = elgg_extract("entity", $params);
		
		if (!empty($owner) && elgg_instanceof($owner, "group")) {
			if ($owner->static_enable != "no") {
				$return_value[] = ElggMenuItem::factory(array(
					"name" => "static",
					"text" => elgg_echo("static:groups:owner_block"),
					"href" => "static/group/" . $owner->getGUID()
				));
			}
		}
	}
	
	return $return_value;
}

/**
 *
 * @param string $hook         'entity_types'
 * @param string $type         'content_subscriptions'
 * @param array  $return_value the current supported entity types
 * @param array  $params       supplied params
 *
 * @return array
 */
function static_content_subscriptions_entity_types_handler($hook, $type, $return_value, $params) {
	
	if (!is_array($return_value)) {
		$return_value = array();
	}
	
	if (!isset($return_value["object"])) {
		$return_value["object"] = array();
	}
	
	$return_value["object"][] = "static";
	
	return $return_value;
}
