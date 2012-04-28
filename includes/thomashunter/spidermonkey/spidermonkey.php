<?php
namespace ThomasHunter\SpiderMonkey;
define('version', '0.0.1');

set_time_limit(0);

$spider_directory = dirname(__FILE__);

include($spider_directory . "/spiderinterface.php");
include($spider_directory . "/spiderabstract.php");
include($spider_directory . "/downloader.php");

include($spider_directory . "/spidercrawl.php");
include($spider_directory . "/spiderincrement.php");