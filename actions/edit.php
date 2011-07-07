<?php 

	global $CONFIG;

	$guid = get_input("guid");
	$title = get_input("title");
	$description = get_input("description");
	$access_id = get_input("access_id", ACCESS_PUBLIC);
	
	if($guid){
		$content = get_entity($guid);
		if(($content->getSubtype() !== "static") || !($content->canEdit())){
			forward(REFERER);
		} else {
			if(!empty($title)){
				$content->title = $title;
			}
			$content->description = $description;
			$content->access_id = $access_id;
			$content->save();
			
			forward($content->getURL());
		}
		
	} else {
		if(!empty($title)){
			$content = new ElggObject();
			$content->subtype = "static";
			$content->access_id = $access_id;
			$content->owner_guid = $CONFIG->site_guid;
			$content->container_guid = $CONFIG->site_guid;
			$content->title = $title;			
			$content->description = $description;			
			$content->save();
			
			forward($content->getURL());
		} else {
			forward(REFERER);
		}
	}
?>