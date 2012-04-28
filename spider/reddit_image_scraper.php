#!/usr/bin/php
<?php
require_once("Classes/Autoloader.php");

$subreddit              = 'pics';
$current_url            = "http://www.reddit.com/r/{$subreddit}/over18?dest=%2Fr%2F{$subreddit}%2F";
$image_link_selector    = "#siteTable a.thumbnail";
$next_link_selector     = "p.nextprev a";
$acceptable_extensions  = array('jpg', 'jpeg', 'png', 'gif', 'jfif');
$post                   = array('uh' => '', 'over18' => 'yes');
$pages_to_download      = 100;
$download_directory     = "downloads";

for($i = 1; $i <= $pages_to_download; $i++) {
    echo "Page: $i/$pages_to_download\n";
    echo "URL: $current_url\n";

    $document = new simple_html_dom();
    $html = \SpiderMonkey\Downloader::getInstance()->execute($current_url, $post);
    $document->load($html);

    $images = $document->find($image_link_selector);
    if (!$images) {
        echo $document->find('title', 0) . "\n";
        die("Couldn't find any images.\n");
    }

    foreach($images AS $image) {
        $href = $image->href;
        $data = pathinfo($href);
        if (in_array($data['extension'], $acceptable_extensions)) {
            echo "Grab: {$data['basename']}\n";
            $picture = file_get_contents($href);
            $fp = fopen($download_directory . '/' . $data['basename'], 'w');
            fwrite($fp, $picture);
            fclose($fp);
        } else {
            echo "Skip: {$data['basename']}\n";
        }
    }

    $next_page_links = $document->find($next_link_selector, 1);
    if (!$next_page_links) {
        $next_page_links = $document->find($next_link_selector, 0);
    }
    $current_url = str_replace('&amp;', '&', $next_page_links->href);
    if (!$current_url) {
        die("Couldn't find next URL.\n");
    }
    $post = array();
}

