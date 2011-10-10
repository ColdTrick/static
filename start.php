<?php 

	function static_init(){
		
		// register page handler for nice URL's
		elgg_register_page_handler("static", "static_page_handler");
		
		// Register a URL handler 
		elgg_register_entity_url_handler('object', 'static', 'static_url');
	}
	
	function static_page_handler($page){	
		set_input("guid", $page[0]);
		include(dirname(__FILE__) . "/pages/static.php");
	}
	
	function static_page_setup(){
		global $CONFIG;
		if(elgg_get_context() == "admin" && elgg_is_admin_logged_in()) {

            $item = array(
                'name' => elgg_echo("static:admin:manage"),
                'text' => elgg_echo("static:admin:manage"),
                'href' => $CONFIG->wwwroot . "admin/static/list",
                'context' => "admin",
                'section' => 'administer',
            );
            elgg_register_menu_item('page', $item);
		}
	}
	
	function static_url($content){
		global $CONFIG;
		$title = $content->title;
		$title = elgg_get_friendly_title($title);
		return $CONFIG->url . "pg/static/" . $content->getGUID() . "/" . $title;
	}
	
	// register default elgg events
	elgg_register_event_handler('init', 'system', "static_init");

	elgg_register_event_handler('pagesetup', 'system', 'static_page_setup');
	
	elgg_register_action("static/edit", dirname(__FILE__). "/actions/edit.php", 'admin');

	elgg_register_action("static/delete", dirname(__FILE__). "/actions/delete.php", 'admin');
	