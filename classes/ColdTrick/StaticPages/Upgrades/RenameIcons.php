<?php

namespace ColdTrick\StaticPages\Upgrades;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

/**
 * Renames icons for static pages
 */
class RenameIcons implements AsynchronousUpgrade {
	
	/**
	 * {@inheritDoc}
	 */
	public function getVersion(): int {
		return 2022061401;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function needsIncrementOffset(): bool {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function shouldBeSkipped(): bool {
		return empty($this->countItems());
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function countItems(): int {
		return elgg_count_entities($this->getOptions());
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function run(Result $result, $offset): Result {
		
		$batch = elgg_get_entities($this->getOptions([
			'offset' => $offset,
		]));
		
		/* @var $entity \StaticPage */
		foreach ($batch as $entity) {
			$from = new \ElggFile();
			$from->owner_guid = $entity->guid;
			$from->setFilename('thumbmaster.jpg');
			
			if (!$from->exists()) {
				$result->addSuccesses();
				continue;
			}
			
			$icon = $entity->getIcon('master');
			if ($icon->exists()) {
				$result->addSuccesses();
				continue;
			}
			
			if ($from->transfer($entity->guid, $icon->getFilename())) {
				$result->addSuccesses();
			} else {
				$result->addFailures();
			}
		}
		
		return $result;
	}
	
	/**
	 * Options for elgg_get_entities()
	 *
	 * @param array $options additional options
	 *
	 * @return array
	 * @see elgg_get_entities()
	 */
	protected function getOptions(array $options = []): array {
		$defaults = [
			'type' => 'object',
			'subtype' => 'static',
			'limit' => 100,
			'batch' => true,
			'batch_inc_offset' => $this->needsIncrementOffset(),
			'metadata_names' => [
				'icontime',
			],
		];
		
		return array_merge($defaults, $options);
	}
}
