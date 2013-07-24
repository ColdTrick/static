<?php

	function static_init(){

		// register page handler for nice URL's
		elgg_register_page_handler("static", "static_page_handler");

		// Register a URL handler
		elgg_register_entity_url_handler('object','static', 'static_url');

		elgg_extend_view("css/admin", "static/css/admin");
		elgg_extend_view("js/admin", "js/static/admin");

		elgg_register_plugin_hook_handler("route", "all", "static_route_handler");

		// Register for search.
		elgg_register_entity_type('object', 'static');
	}

	function static_page_handler($page){
		set_input("page_title", $page[0]);
		include_once(dirname(__FILE__) . "/pages/static.php");
		return true;
	}

	function static_page_setup(){
		elgg_register_admin_menu_item('configure', 'static', 'appearance');
	}

	function static_route_handler($hook, $type, $return_value, $params){
		/**
		 * $return_value contains:
		 * $return_value['handler'] => requested handler
		 * $return_value['segments'] => url parts ($page)
		 */
		global $CONFIG;
		$handler = $return_value['handler'];

		if(!empty($handler)){
			if (!isset($CONFIG->pagehandler) || !isset($CONFIG->pagehandler[$handler])) {
				$options = array(
						"type" => "object",
						"subtype" => "static",
						"limit" => 1,
						"metadata_name_value_pairs" => array( "friendly_title" => $handler)
					);
				$entities = elgg_get_entities_from_metadata($options);
				if(!empty($entities)){
					elgg_push_context("static"); //needed for menu items
					$return_value['segments'] = array($return_value['handler']);
					$return_value['handler'] = "static";
					return $return_value;
				}
			}

		}
	}

	function static_url($content){
		$friendly_title = $content->friendly_title;
		if($friendly_title){
			return elgg_get_site_url() . $friendly_title;
		} else {
			$friendly_title = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($content->title, ENT_QUOTES, 'UTF-8'));
			$friendly_title = elgg_get_friendly_title($friendly_title);
			$content->friendly_title = $friendly_title;

			return $content->getURL();
		}
	}

	// register default elgg events
	elgg_register_event_handler("init", "system", "static_init");
	elgg_register_event_handler('pagesetup', 'system', 'static_page_setup');

	elgg_register_action("static/edit", dirname(__FILE__). "/actions/edit.php", "admin");
	elgg_register_action("static/delete", dirname(__FILE__). "/actions/delete.php", "admin");
	elgg_register_action("static/reorder", dirname(__FILE__). "/actions/reorder.php", "admin");
