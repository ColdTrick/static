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
			
			if (!empty($page[2]) && ($page[2] == "out_of_date")) {
				include(dirname(dirname(__FILE__)) . "/pages/out_of_date_group.php");
			} else {
				include(dirname(dirname(__FILE__)) . "/pages/group.php");
			}
			break;
		case "out_of_date":
			$user = false;
			if (!empty($page[1])) {
				$user = get_user_by_username($page[1]);
			}
			
			if ($user) {
				elgg_set_page_owner_guid($user->getGUID());
				
				include(dirname(dirname(__FILE__)) . "/pages/out_of_date_owner.php");
			} else {
				include(dirname(dirname(__FILE__)) . "/pages/out_of_date.php");
			}
			break;
		case "thumbnail":
			set_input("guid", $page[1]);
			
			if (!empty($page[2])) {
				set_input("size", $page[2]);
			}
			
			if (!empty($page[3])) {
				set_input("icontime", $page[3]);
			}
			
			include(dirname(dirname(__FILE__)) . "/pages/thumbnail.php");
		case "all":
		default:
			include(dirname(dirname(__FILE__)) . "/pages/all.php");
			break;
	}
	
	return true;
}
