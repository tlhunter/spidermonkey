<?php

namespace ThomasHunter\SpiderMonkey;

interface SpiderInterface {
	public function queue_get();
	public function queue_add($str_url);
	#public function queue_rem($str_url);
	public function queue_empty();
	public function queue_size();
	public function execute();
}