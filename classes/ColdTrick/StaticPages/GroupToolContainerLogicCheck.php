<?php

namespace ColdTrick\StaticPages;

use Elgg\Groups\ToolContainerLogicCheck;

/**
 * Prevent static pages from being created if the group tool option is disabled
 */
class GroupToolContainerLogicCheck extends ToolContainerLogicCheck {

	/**
	 * {@inheritdoc}
	 */
	public function getContentType(): string {
		return 'object';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getContentSubtype(): string {
		return \StaticPage::SUBTYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getToolName(): string {
		return 'static';
	}
}
