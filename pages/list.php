<?php 

	global $CONFIG;

	admin_gatekeeper();
	
	set_context("admin");
	
	$title_text = elgg_echo("static:admin:manage");
	$title = elgg_view_title($title_text);
	
	$options = array(
			"type" => "object",
			"subtype" => "static",
			"limit" => false,
			"full_view" => false
		);
	
	$body = elgg_list_entities($options);
	if(empty($body)){
		$body = elgg_view("page_elements/contentwrapper", array("body" => elgg_echo("static:admin:empty")));
	}
	
	
	$create_link = elgg_view("output/url", array("href" => $CONFIG->wwwroot . "pg/static/new", "text" => elgg_echo("static:admin:create")));
	$create = elgg_view("page_elements/contentwrapper", array("body" => $create_link));
	
	$page_data = elgg_view_layout("two_column_left_sidebar", "", $title . $create . $body);
	page_draw($title_text, $page_data);

?>