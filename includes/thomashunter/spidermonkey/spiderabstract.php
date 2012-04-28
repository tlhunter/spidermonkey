<?php

namespace ThomasHunter\SpiderMonkey;

abstract class SpiderAbstract implements SpiderInterface {
	public $url_queue;

	function __construct() {
		
	}

	function queue_get() {
		return $this->url_queue;
	}

	function queue_add($url) {
		$this->url_queue[] = $url;
	}

	function queue_empty() {
		$this->url_queue = array();
	}

	function queue_size() {
		return count($this->url_queue);
	}

}