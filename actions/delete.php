<?php 

	$guid = get_input("guid");
	if($guid){
		if($entity = get_entity($guid)){
			if(($entity->getSubtype() == "static") && $entity->canEdit()){
				$parent_guid = $entity->getGUID();
				
				if($entity->delete()){
					$options = array(
							"type" => "object",
							"subtype" => "static",
							"metadata_name_value_pairs" => array("parent_guid" => $parent_guid),
							"limit" => false,
							"order_by" => "e.time_created asc"
					);
					
					if ($childs = elgg_get_entities_from_metadata($options)) {
						
						foreach ($childs as $key => $child) {
							if ($key == 0) {
								// promote first child
								$parent_guid = $child->getGUID();
								unset($child->parent_guid);
							} else {
								// link other childs to new parent
								$child->parent_guid = $parent_guid;
							}
						}
					}
				}
			}
		}
	}

	forward("admin/appearance/static");
