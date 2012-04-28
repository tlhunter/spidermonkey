<?php
namespace ThomasHunter\SpiderMonkey;
class Downloader {
	public $url = '';
	public $referer = '';
	public $user_agent = '';
	public $http_status = '';
	private $curl_handle = null;

	function __construct($url, $referrer = '', $user_agent = '') {
		$this->url = $url;
		$this->user_agent = $user_agent ? : 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13 SpiderMonkey/0.1';
		if (!$referrer) {
			$temp = parse_url($url);
			$this->referer = $url['scheme'] . '://' . $url['host'] . '/';
		} else {
			$this->referer = $referrer;
		}
	}

	function execute() {
		$this->curl_handle = curl_init();
		curl_setopt($this->curl_handle, CURLOPT_URL, $this->url);
		curl_setopt($this->curl_handle, CURLOPT_HEADER, FALSE);
		curl_setopt($this->curl_handle, CURLOPT_REFERER, $this->referer);
		curl_setopt($this->curl_handle, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, 1);
		$document = curl_exec($this->curl_handle);
		$this->http_status = curl_getinfo($this->curl_handle, CURLINFO_HTTP_CODE);
		curl_close($this->curl_handle);
		return $document;
	}

	function get_status() {
		return $this->http_status;
	}
}