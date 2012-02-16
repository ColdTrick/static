<?php 

	$guid = get_input("guid");
	if($guid){
		if($entity = get_entity($guid)){
			if(($entity->getSubtype() == "static") && $entity->canEdit()){
				$entity->delete();
			}
		}
	}

	forward("static/list");
