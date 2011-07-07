<?php 

	function static_init(){
		
		// register page handler for nice URL's
		register_page_handler("static", "static_page_handler");
		
		// Register a URL handler 
		register_entity_url_handler('static_url','object','static');
	}
	
	function static_page_handler($page){	
		switch($page[0]){			
			case "list":
				include(dirname(__FILE__) . "/pages/list.php");
				break;
			case "edit":
				set_input("edit", true);
				set_input("guid", $page[1]);
				include(dirname(__FILE__) . "/pages/static.php");
				break;
			case "new":
				set_input("new", true);
			default:
				set_input("guid", $page[0]);
				include(dirname(__FILE__) . "/pages/static.php");
				break;
		}		
	}
	
	function static_page_setup(){
		global $CONFIG;
		if(get_context() == "admin" && isadminloggedin()){
			add_submenu_item(elgg_echo("static:admin:manage"), $CONFIG->wwwroot . "pg/static/list");
		}
	}
	
	function static_url($content){
		global $CONFIG;
		$title = $content->title;
		$title = elgg_get_friendly_title($title);
		return $CONFIG->url . "pg/static/" . $content->getGUID() . "/" . $title;
	}
	
	// register default elgg events
	register_elgg_event_handler("init", "system", "static_init");
	register_elgg_event_handler('pagesetup', 'system', 'static_page_setup');
	
	register_action("static/edit",false, dirname(__FILE__). "/actions/edit.php", true);
	register_action("static/delete",false, dirname(__FILE__). "/actions/delete.php", true);
	
	
?>