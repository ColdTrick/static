<?php

class StaticPage extends \ElggObject {
	
	const SUBTYPE = 'static';
	
	/**
	 * (non-PHPdoc)
	 * @see ElggObject::initializeAttributes()
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		
		$this->attributes['subtype'] = self::SUBTYPE;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ElggEntity::getURL()
	 */
	public function getURL() {
		$friendly_title = $this->friendly_title;
		if ($friendly_title) {
			return elgg_normalize_url($friendly_title);
		}
		
		// fallback
		return elgg_normalize_url("static/view/{$this->getGUID()}");
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ElggEntity::getIconURL()
	 */
	public function getIconURL($params) {
		if (is_array($params)) {
			$size = elgg_extract('size', $params, 'medium');
		} else {
			$size = is_string($params) ? $params : 'medium';
		}
	
		if ($this->icontime) {
			return elgg_normalize_url("mod/static/pages/thumbnail.php?guid={$this->getGUID()}&size={$size}&icontime={$this->icontime}");
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ElggObject::canComment()
	 */
	public function canComment($user_guid = 0) {
		
		if ($this->enable_comments == 'yes') {
			return true;
		}
				
		return false;
	}

	/**
	 * Removes the thumbnail
	 *
	 * @return void
	 */
	public function removeThumbnail() {
		if (empty($this->icontime)) {
			return;
		}
		
		$fh = new \ElggFile();
		$fh->owner_guid = $this->getGUID();
		
		$prefix = 'thumb';
		$icon_sizes = elgg_get_config('icon_sizes');
		
		if (empty($icon_sizes)) {
			return;
		}
		
		foreach ($icon_sizes as $size => $info) {
			$fh->setFilename($prefix . $size . '.jpg');
				
			if ($fh->exists()) {
				$fh->delete();
			}
		}
	
		unset($entity->icontime);
	}
	
	/**
	 * Removes the thumbnail of the object prior to the deletion of the object
	 *
	 * @see ElggEntity::delete()
	 */
	public function delete($recursive = true) {
		$this->removeThumbnail();
		
		return parent::delete($recursive);
	}
	
	/**
	 * Returns a friendly title
	 */
	public function getFriendlyTitle() {
		$result = $this->friendly_title;
		if ($result) {
			return $result;
		}
		
		// this sometimes happens, prefill with new friendly title
		$result = static_make_friendly_title($this->title);
		
		return $result;
	}
	
	/**
	 * Returns the root page of an entity
	 *
	 * @return \StaticPage|false
	 */
	public function getRootPage() {
		
		$root_entity = false;
		$container = $this->getContainerEntity();
		
		if ($container instanceof \ElggSite) {
			// top page on site
			$root_entity = $this;
		} elseif($container instanceof \ElggGroup) {
			// top page in group
			$root_entity = $this;
		} else {
			// first created relationship is the root entity
			$relations = $this->getEntitiesFromRelationship([
				'type' => 'object',
				'subtype' => StaticPage::SUBTYPE,
				'relationship' => 'subpage_of',
				'limit' => 1,
			]);
			if (!empty($relations)) {
				$root_entity = $relations[0];
			}
		}
		
		return $root_entity;
	}
	
}
