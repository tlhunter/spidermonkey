<?php
namespace SpiderMonkey;
/**
 * Singleton downloading class
 * @author Thomas Hunter <tlhunter@gmail.com>
 */
class Downloader {
	static $lastHttpStatus  = '';
	static $curlHandle      = null;
    static $instance        = null;
    static $cookieFile      = '';

	private function __construct() {}
	private function __clone() {}

	public function getInstance() {
        if (!self::$instance) {
            $className = __CLASS__;
            self::$instance = new $className;
            self::$curlHandle = curl_init(); # cURL is hella faster when keeping one handle and re-using it
            self::$cookieFile = tempnam("/tmp", "CURLCOOKIE");
        }
        return self::$instance;
    }

	public function execute($url, $postValues = array(), $proxyType = NULL, $proxyAddress = NULL, $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13', $referrer = FALSE) {
		if (!$referrer) {
			$temp = parse_url($url);
            $referer = $temp['scheme'] . '://' . $temp['host'] . '/';
		}

        if ($postValues) {
            $fields_string = '';
            foreach($postValues as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');
            curl_setopt(self::$curlHandle, CURLOPT_POST,            TRUE                );
            curl_setopt(self::$curlHandle, CURLOPT_POSTFIELDS,      $fields_string      );
        }

        if ($proxyType && $proxyAddress) {
            curl_setopt(self::$curlHandle, CURLOPT_PROXYTYPE,       $proxyType          );
            curl_setopt(self::$curlHandle, CURLOPT_PROXY,           $proxyAddress       ); 
        }

		curl_setopt(self::$curlHandle, CURLOPT_URL,                 $url                );
		curl_setopt(self::$curlHandle, CURLOPT_HEADER,              FALSE               );
		curl_setopt(self::$curlHandle, CURLOPT_REFERER,             $referer            );
		curl_setopt(self::$curlHandle, CURLOPT_USERAGENT,           $user_agent         );
		curl_setopt(self::$curlHandle, CURLOPT_RETURNTRANSFER,      1                   );
        curl_setopt(self::$curlHandle, CURLOPT_FOLLOWLOCATION,      TRUE                );
        curl_setopt(self::$curlHandle, CURLOPT_MAXREDIRS,           8                   );
        curl_setopt(self::$curlHandle, CURLOPT_COOKIEJAR,           self::$cookieFile   );
        curl_setopt(self::$curlHandle, CURLOPT_COOKIEFILE,          self::$cookieFile   );
        curl_setopt(self::$curlHandle, CURLOPT_CONNECTTIMEOUT,      6                   );

		$document = curl_exec(self::$curlHandle);
		self::$lastHttpStatus = curl_getinfo(self::$curlHandle, CURLINFO_HTTP_CODE);
		return $document;
	}

	public function getStatus() {
		return self::$lastHttpStatus;
	}
}
