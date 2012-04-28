<?php
class linkfinder {
	protected $pageUrl = '';
	protected $document = '';

	public function __construct($pageUrl, $document) {
		$this->pageUrl = $pageUrl;
		$this->document = $document;
	}

	public function execute() {

	}
}
