<?php
/**
 * Start file for the Static plugin
 */

elgg_register_event_handler("init", "system", "static_init");
elgg_register_event_handler('pagesetup', 'system', 'static_page_setup');

/**
 * Initializes the static plugin
 *
 * @return void
 */
function static_init() {

	// register page handler for nice URL's
	elgg_register_page_handler("static", "static_page_handler");

	elgg_extend_view("js/elgg", "js/static/site");
	elgg_extend_view("css/elgg", "css/static/site");

	elgg_register_plugin_hook_handler("route", "all", "static_route_hook_handler");

	// Register for search.
	elgg_register_entity_type('object', 'static');
	
	elgg_register_plugin_hook_handler("entity:url", "object", "static_entity_url_hook_handler");
	
	elgg_register_action("static/edit", dirname(__FILE__) . "/actions/edit.php", "admin");
	elgg_register_action("static/delete", dirname(__FILE__) . "/actions/delete.php", "admin");
	elgg_register_action("static/reorder", dirname(__FILE__) . "/actions/reorder.php", "admin");
}

/**
 * Handles the static pages
 *
 * @param array $page requested page
 *
 * @return boolean
 */
function static_page_handler($page) {
	switch($page[0]){
		case "view":
			set_input("guid", $page[1]);
			include(dirname(__FILE__) . "/pages/view.php");
			break;
		case "edit":
			set_input("guid", $page[1]);
		case "add":
			include(dirname(__FILE__) . "/pages/edit.php");
			break;
		case "all":
		default:
			include(dirname(__FILE__) . "/pages/all.php");
			break;
	}
	return true;
}

/**
 * Registers menu items during page setup
 *
 * @return void
 */
function static_page_setup() {
	elgg_register_menu_item('page', array(
		'name' => "static",
		'href' => "static/all",
		'text' => elgg_echo("static:all"),
		'context' => 'admin',
		'parent_name' => "appearance",
		'section' => "configure"
	));
}

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
