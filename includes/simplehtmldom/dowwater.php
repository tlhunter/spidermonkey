<?php
include_once('simple_html_dom.php');
include_once('spider.php');

$website_url = 'http://preview-www.dowwaterandprocess.com/';
$file = 'test.htm';

$spider = New Spider($website_url, $file);
$spider->crawl();