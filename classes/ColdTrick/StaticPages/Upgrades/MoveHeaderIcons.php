<?php

namespace ColdTrick\StaticPages\Upgrades;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

class MoveHeaderIcons extends AsynchronousUpgrade {

	/**
	 * {@inheritdoc}
	 */
	public function getVersion(): int {
		return 2023031300;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function needsIncrementOffset(): bool {
		return false;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function shouldBeSkipped(): bool {
		return empty($this->countItems());
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function countItems(): int {
		return elgg_count_entities($this->getOptions());
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function run(Result $result, $offset): Result {
		$pages = elgg_get_entities($this->getOptions(['offset' => $offset]));
		
		/* @var $page \Event */
		foreach ($pages as $page) {
			$old_icon = $page->getIcon('master', 'icon');
			if ($old_icon->exists()) {
				$coords = [
					'x1' => $page->x1,
					'y1' => $page->y1,
					'x2' => $page->x2,
					'y2' => $page->y2,
				];
				
				$page->saveIconFromElggFile($old_icon, 'header', $coords);
			}
			
			$page->deleteIcon('icon');
			
			$result->addSuccesses();
		}
		
		return $result;
	}
	
	/**
	 * Get options for elgg_get_entities
	 *
	 * @param array $options additional options
	 *
	 * @return array
	 */
	protected function getOptions(array $options = []) {
		$defaults = [
			'type' => 'object',
			'subtype' => 'static',
			'limit' => 50,
			'batch' => true,
			'metadata_name' => 'icontime',
		];
		
		return array_merge($defaults, $options);
	}
}
