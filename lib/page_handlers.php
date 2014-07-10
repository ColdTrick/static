<?php
/**
 * All the page handlers for this plugin are bundled here
 */

/**
 * Handles the static pages
 *
 * @param array $page requested page
 *
 * @return boolean
 */
function static_page_handler($page) {
	switch ($page[0]) {
		case "view":
			set_input("guid", $page[1]);
			include(dirname(dirname(__FILE__)) . "/pages/view.php");
			break;
		case "edit":
			set_input("guid", $page[1]);
		case "add":
			include(dirname(dirname(__FILE__)) . "/pages/edit.php");
			break;
		case "group":
			set_input("guid", $page[1]);
			include(dirname(dirname(__FILE__)) . "/pages/group.php");
			break;
		case "thumbnail":
			set_input("guid", $page[1]);
			set_input("size", $page[2]);
			include(dirname(dirname(__FILE__)) . "/pages/thumbnail.php");
			break;
		case "all":
		default:
			include(dirname(dirname(__FILE__)) . "/pages/all.php");
			break;
	}
	
	return true;
}