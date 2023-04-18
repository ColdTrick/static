<?php

namespace ColdTrick\StaticPages;

use Elgg\Groups\ToolContainerLogicCheck;

/**
 * Prevent static pages from being created if the group tool option is disabled
 */
class GroupToolContainerLogicCheck extends ToolContainerLogicCheck {

	/**
	 * {@inheritDoc}
	 */
	public function getContentType(): string {
		return 'object';
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getContentSubtype(): string {
		return \StaticPage::SUBTYPE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getToolName(): string {
		return 'static';
	}
}
