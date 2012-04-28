<?php
namespace ThomasHunter\SpiderMonkey;

class SpiderIncrement extends SpiderAbstract {
	private $config;
	private $count_404 = 0;
	public $last_error = '';

	function __construct($config) {
		parent::__construct();
		$this->config = $config;
	}

	public function execute() {
		$documents = array();
		foreach($this->url_queue AS $url) {
			if ($this->config['timeout']) {
				usleep($this->config['timeout']);
			}
			$download = new Downloader($url, $this->config['referer'], $this->config['user_agent']);
			$documents[] = array('url' => $url, 'doc' => $download->execute());
			if ($download->get_status() == '404') {
				$this->count_404++;
			} else {
				$this->count_404 = 0;
			}
			if ($this->config['give_up_404'] && $this->count_404 >= $this->config['give_up_404']) {
				$this->last_error = 'Maximum 404s hit';
				return false;
			}
		}
		return $documents;
	}

}