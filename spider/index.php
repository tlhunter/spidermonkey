#!/usr/bin/php
<?php
require_once('Classes/Autoloader.php');
$queue = array();

array_push($queue, $argv[1]);

$no_parse_extensions = array (
    "zip",  "jpg",  "png",  "gif",  "jpeg", "jfif",
    "dmg",  "exe",  "rar",  "7z",   "tar",  "gz",
    "bzip", "swf",  "cab",  "mp3",  "wav",  "midi",
    "tgz",  "reg",  "msi",  "zip",  "pkg",  "ogg",
    "ogm",  "mpg",  "avi",  "divx", "m4a",  "aiff",
    "lzw",  "psd",  "rm",   "wmv",  "wma",  
);

$base_url_host = parse_url($queue[0], PHP_URL_HOST);

$i = 0;
while($url = $queue[$i]) {
    echo "Downloading: $url\n";

    $document = new simple_html_dom();

    $html = \SpiderMonkey\Downloader::getInstance()->execute($url);
    if (!$html) {
        echo "Error downloading URL: " . \SpiderMonkey\Downloader::getInstance()->getStatus();
        continue;
    }

    $document->load($html);
    $base = $document->find('head > base', 0);

    $base_url = $base ? $base->href : $url;

    echo "Base URL: $base_url\n";

    $links = $document->find('a');
    $unique_links = 0;

    echo "Found Links: " . count($links) . "\n";

    foreach($links AS $link) {
        $href = $link->href;
        if (strpos($href, '#') === 0) {
            # Link is something like '#item', skip it entirely, we already have the guts of this page
            continue;
        }
        if (strpos($href, '#') !== FALSE) {
            # Link is something like '/page#item' (want to keep part of it though)
            $href = substr($href, 0, strpos($href, '#'));
        }
        $full_url = UrlToAbsolute::load($base_url, $href);
        if ($base_url_host != parse_url($full_url, PHP_URL_HOST)) {
            #outside current domain
        } else if (in_array(pathinfo($full_url, PATHINFO_EXTENSION), $no_parse_extensions)) {
            #this is a binary file, ignore it (possibly download and keep though?)
        } else if (array_search($full_url, $queue) !== FALSE) {
            # link is already in our queue
        } else {
            $unique_links++;
            array_push($queue, $full_url);
        }
    }
    echo "New Links: $unique_links\n";
    $i++;
    echo "Progress: $i / " . count($queue) . "\n\n";
    unset($document);
}

sort($queue);
print_r($queue);

