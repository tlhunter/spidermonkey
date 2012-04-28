<?php

set_time_limit(0);

class Spider {
	private $urlList = Array();
	private $startingUrl;
	private $filename;
	private $lastIndex;
	private $blacklistExtensions = Array('exe', 'zip', 'msi', 'xls', 'doc', 'pdf');
	
	function __construct($firstUrl, $filename) {
		array_push($this->urlList, $firstUrl);
		$this->startingUrl = $firstUrl;
		$this->filename = $filename;
	}
	
	function crawl() {
		$this->lastIndex = 0;
		while ($this->lastIndex < count($this->urlList)) {
			$currentUrl = $this->urlList[$this->lastIndex];
			$foundUrls = 0;
			$html = $this->downloadUrl($currentUrl);
			if ($this->saveData($html, $currentUrl)) {
				echo $currentUrl . "\n";
				$newUrls = $this->gatherUrls($html, $currentUrl);
				$totalFoundUrls = count($newUrls);
				foreach($newUrls AS $newUrl) {
					if (!in_array($newUrl, $this->urlList) &&			# Unique URL
						(strpos($newUrl, $this->startingUrl) === 0)) {	# URL is under starting URL
						array_push($this->urlList, $newUrl);
						$foundUrls ++;
					}
				}
				echo "New URLs Found: $foundUrls/$totalFoundUrls\n\n\n";
			}
			$this->lastIndex ++;
		}
		print_r($urlList);
		return count($this->urlIndex);
	}
	
	private function downloadUrl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$buffer = curl_exec($ch);
		curl_close($ch);
		return $buffer;
	}

	private function gatherUrls($html, $url) { #url should be set to the base href is present
		$dir = $this->getUrlDirectory($url);
		$doc = str_get_html($html);
		$urls = Array();
		foreach($doc->find('a') AS $link) {
			$myUrl = $link->attr['href'];
			$fullUrl = '';
			if (strpos($myUrl, '/') === 0) {
				$fullUrl = $dir['dom'] . $myUrl;
			} else if (strpos($myUrl, 'http://') === 0 || strpos($myUrl, 'https://') === 0) {
				$fullUrl = $myUrl;
			} else if (strpos($myUrl, 'javascript:') === 0 || strpos($myUrl, 'mailto:') === 0 || strpos($myUrl, '#') === 0) {
				
			} else {
				$fullUrl = $dir['dir'] . $myUrl;
			}
			
			if ($fullUrl) {
				foreach($this->blacklistExtensions AS $ext) {
					if (strpos($myUrl, '.' . $ext)) {
						$fullUrl = '';
					}
				}
				if ($fullUrl)
					array_push($urls, $fullUrl);
			}
		}
		return $urls;
	}
	
	private function getUrlDirectory($url) {
		$uc = parse_url($url);
		$dir = $uc['scheme'] . '://' . $uc['host'] . substr($uc['path'], 0, strrpos($uc['path'], '/'));
		$dom = $uc['scheme'] . '://' . $uc['host'];
		return Array('dir' => $dir, 'dom' => $dom);
	}
	
	private function saveData($text, $currentUrl) {
		$html = str_get_html($text);
		$content_a = $html->find('.content-A');
		if ($content_a) {
			$data = "\n\n<h1><a href='$currentUrl'>$currentUrl</a></h1>\n\n" . $content_a[0];
			$fh = fopen($this->filename, 'a');
			fwrite($fh, $data);
			fclose($fh);
			return TRUE;
		} else {
			return FALSE;
		}
	}
}