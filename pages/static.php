<?php 

	$guid = get_input("guid");
	$parent_guid = get_input("parent_guid");
	$page_title = get_input("page_title");
	$edit = get_input("edit", false);
	$new = get_input("new", false);
	
	$content = false;
	
	if($guid){
		$content = get_entity($guid);
	} elseif(!empty($page_title)){
		if(is_numeric($page_title)){
			// support old links
			$content = get_entity($page_title);
			if($content->getSubtype() != "static"){
				unset($content);
			}
		}
		
		if(!$content){
			$options = array(
					"type" => "object",
					"subtype" => "static",
					"metadata_name_value_pairs" => array("friendly_title" => $page_title),
					"limit" => 1
				);
			if($entities = elgg_get_entities_from_metadata($options)){
				$content = $entities[0];
			}
		}
	}
	
	if($content && ($content->getSubtype() == "static") && !$edit){
		
		// show content
		$title = $content->title;
		$body = elgg_view("output/longtext", array("value" => $content->description));
		
		if($content->canEdit()){
			$edit_link = elgg_view("output/url", array("href" => elgg_get_site_url() . "admin/appearance/static/new?guid=" . $content->getGUID(), "text" => elgg_echo("edit")));
			$delete_link = elgg_view("output/confirmlink", array("href" => elgg_get_site_url() . "action/static/delete?guid=" . $content->getGUID(), "text" => elgg_echo("delete")));
	
			$actions = $edit_link . " | " . $delete_link;
			if(empty($content->parent_guid)){
				$actions .= " | " . elgg_view("output/url", array("href" => elgg_get_site_url() . "admin/appearance/static/new?parent_guid=" . $content->getGUID(), "text" => elgg_echo("static:admin:create:subpage")));
			}
			
			$actions .= " | " . elgg_view("output/url", array("href" => elgg_get_site_url() . "admin/appearance/static", "text" => elgg_echo("admin:appearance:static")));
			$body .= $actions;
		}
		
		$parent_guid = $content->parent_guid;
		if(empty($parent_guid)){
			$parent_guid = $content->getGUID();
		}
		
		$options = array(
			"type" => "object",
			"subtype" => "static",
			"metadata_name_value_pairs" => array("parent_guid" => $parent_guid),
			"limit" => false,
			"order_by" => "e.time_created asc"
			);
		
		if($menu_entities = elgg_get_entities_from_metadata($options)){
			$menu_name = "static_1";
			if($parent_guid != $content->parent_guid){
				elgg_register_menu_item('page', array(
					'name' => $menu_name,
					'href' => $content->getURL(),
					'text' => $content->title,
					'context' => "static"
				));
			} elseif($parent = get_entity($parent_guid)) {
				elgg_register_menu_item('page', array(
					'name' => $menu_name,
					'href' => $parent->getURL(),
					'text' => $parent->title,
					'context' => "static"
				));
			}
			
			foreach($menu_entities as $menu_item){
				$menu_name .= "1";
				elgg_register_menu_item('page', array(
					'name' => $menu_name,
					'href' => $menu_item->getURL(),
					'text' => $menu_item->title,
					'context' => "static"
				));
			}
			$page = elgg_view_layout('content', array(
					'filter' => '',
					'content' => $body,
					'title' => $title
				));
		} else {
			$page = elgg_view_layout('one_column', array(
					'filter' => '',
					'content' => $body,
					'title' => $title
				));
		}
		
		echo elgg_view_page($title, $page);
	} else {
		forward();
	}
