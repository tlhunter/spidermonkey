<?php
namespace ThomasHunter\SpiderMonkey;
include_once("includes/thomashunter/spidermonkey/spidermonkey.php");
include_once('includes/simple_html_dom.php');

$spider = null;
$parsed_object = array();

# Global Configuration
$config = array();
$config['url_method'] =				$_POST['url_method'];
$config['give_up_404'] =			$_POST['give_up_404'];
$config['simultaneous'] =			$_POST['simultaneous'];
$config['delay'] =					$_POST['delay'];
$config['timeout'] =				isset($_POST['timeout']) ? $_POST['timeout'] : 0;
$config['user_agent'] =				$_POST['user_agent'];
$config['referer'] =				$_POST['referer'];
$config['storage'] =				$_POST['storage'];

# Output Configuration
if ($config['storage'] == 'database') {
	$config['mysql']['username'] =	$_POST['mysql_username'];
	$config['mysql']['password'] =	$_POST['mysql_password'];
	$config['mysql']['server'] =	$_POST['mysql_server'];
	$config['mysql']['database'] =	$_POST['mysql_database'];
} else if ($config['storage'] != 'display') {
	$config['filename'] =			$_POST['filename'];
	$config['local'] =				isset($_POST['local']) ? TRUE : FALSE;
}

# Spider Method Configuration
if ($config['url_method'] == 'increment') {
	$config['url_structure'] =		$_POST['url_structure'];
	$config['start_integer'] =		$_POST['start_integer'];
	$config['stop_integer'] =		$_POST['stop_integer'];
	$spider = New SpiderIncrement($config);
	for($i = $config['start_integer']; $i <= $config['stop_integer']; $i++) {
		$spider->queue_add(str_replace('#', $i, $config['url_structure']));
	}
} else if ($config['url_method'] == 'crawl') {
	$config['first_url'] =			$_POST['first_url'];
	$config['limit_domain'] =		$_POST['limit_domain'];
	$config['max_depth'] =			$_POST['max_depth'];
	$spider = New SpiderCrawl($config);
	$spider->queue_add($config['first_url']);
} else {
	die("Invalid URL Spidering method supplied.");
}

$capture_object = array();
if (isset($_POST['captures'])) {
	$i = 0;
	foreach($_POST['captures'] AS $capture) {
		$capture_object[$i]['capture'] = $_POST['capture-'.$capture];
		$capture_object[$i]['expression'] = $_POST['expression-'.$capture];
		$capture_object[$i]['name'] = $_POST['name-'.$capture];
		$i++;
	}
	#print_r($capture_object);
}

# Spider Execution
$documents = $spider->execute();

if (!$documents) {
	die($spider->last_error);
} else {
	$i = 0;
	foreach($documents AS $doc) {						# Loop through all the documents we downloaded
		$parsed_object[$i]['url'] = $doc['url'];
		$html = str_get_html($doc['doc']);					# Builds the HTML document once for each page
		foreach($capture_object AS $capture) {				# Loop through all the captures we specified
			if ($capture['capture'] == 'selector') {
				$find = $html->find($capture['expression'], 0);
				if ($find) {
					$content = $find->innertext ? : $find->outertext;
					$parsed_object[$i][$capture['name']] = $content;
				}
			} else if ($capture['capture'] == 'regex') {
				$matches = array();
				$find = preg_match('~'.$capture['expression'].'~', $doc['doc'], $matches);
				if ($find) {
					$parsed_object[$i][$capture['name']] = $matches[1];
				}
			} else if ($capture['capture'] == 'asterisk') {
				$items = explode('*', $capture['expression']);
				$expression = '~' . preg_quote($items[0]) . '(.*?)' . preg_quote($items[1]) . '~';
				$find = preg_match($expression, $doc['doc'], $matches);
				if ($find) {
					$parsed_object[$i][$capture['name']] = $matches[1];
				}
			} else {
				die("Invalid Capture Method Specified");
			}
		}
		$html->clear();
		unset($html);
		$i++;
	}
}

echo "<pre>", json_encode($parsed_object), "</pre>\n";;