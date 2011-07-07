<?php 

	global $CONFIG;

	$guid = get_input("guid");
	$edit = get_input("edit", false);
	$new = get_input("new", false);

	if($guid){
		$content = get_entity($guid);
	}
	
	if($content && ($content->getSubtype() == "static") && !$edit){
		
		// show content
		$title_text = $content->title;
		$title = elgg_view_title($title_text);
		$body = elgg_view("page_elements/contentwrapper", array("body" => $content->description));
		
		if($content->canEdit()){
			$edit_link = elgg_view("output/url", array("href" => $CONFIG->wwwroot . "pg/static/edit/" . $content->getGUID(), "text" => elgg_echo("edit")));
			$delete_link = elgg_view("output/confirmlink", array("href" => $CONFIG->wwwroot . "action/static/delete?guid=" . $content->getGUID(), "text" => elgg_echo("delete")));
	
			$actions = $edit_link . " | " . $delete_link;
			
			$body .= elgg_view("page_elements/contentwrapper", array("body" => $actions));
		}
		
		$page = elgg_view_layout("one_column", $title . $body);
		
		page_draw($title_text, $page);
	} else {
		if(isadminloggedin() && ($new || $edit)){
			set_context("admin");
			
			// create form
			if($new){
				$title_text = elgg_echo("static:admin:create");
			} else {
				$title_text = elgg_echo("static:admin:edit");
			}
			
			$title = elgg_view_title($title_text);
			
			if($content){
				$content_guid = $content->getGUID();
				$content_title = $content->title;
				$content_description = $content->description;
				$content_access_id = $content->access_id;
			}

			$form_body .= elgg_view("input/hidden", array("internalname" => "guid", "value" => $content_guid));
			$form_body .= "<label>" . elgg_echo("title") . "</label><br />";
			$form_body .= elgg_view("input/text", array("internalname" => "title", "value" => $content_title)) . "<br />";
			$form_body .= "<label>" . elgg_echo("description") . "</label><br />";
			$form_body .= elgg_view("input/longtext", array("internalname" => "description", "value" => $content_description)) . "<br />";
			$form_body .= "<label>" . elgg_echo("access") . "</label><br />";
			$form_body .= elgg_view("input/access", array("internalname" => "access_id", "value" => $content_access_id)) . "<br />";
			$form_body .= elgg_view("input/submit", array("value" => elgg_echo("save")));
			
			$form = elgg_view("input/form", array("body" => $form_body, "action" => $CONFIG->wwwroot . "action/static/edit"));
			$form = elgg_view("page_elements/contentwrapper", array("body" => $form));
			$page = elgg_view_layout("one_column", $title . $form);
			
			page_draw($title_text, $page);
		} else {
			forward();
		}
	}

?>