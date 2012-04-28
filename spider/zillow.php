#!/usr/bin/php
<?php
require_once('Classes/Autoloader.php');         // Autoloader to make class loading easier
date_default_timezone_set('America/Detroit');   // Doesn't really matter what's here, it subdues date() errors
set_time_limit(0);                              // Script can execute forever without dying

define('QUICK_DEBUG_MODE',      FALSE); // Downloads less data, just useful for testing regex & selectors
define("BASE_URL",              "http://www.zillow.com");
define("STATE_LIST_URL",        "http://www.zillow.com/homes/browse/");
define("STATE_FINDER",          ".browse-homes-list a");
define("CITY_FINDER",           "#geo-region-list .hlisting .adr a");
define("NO_PROPERTY_FINDER",    "#search-results .results-msg b");
define("NO_PROPERTY_TEXT",      "No results where found.");
define("PROPERTY_FINDER",       "#list-container .property-info");
define("MAIN_LINK_FINDER",      ".adr a");
define("ZIPCODE_REGEX",         "-([0-9]{5})%2F");
define("SALE_PRICE_FINDER",     ".value-info .price");
define("DATA_TABLE_FINDER",     ".prop-cola");
define("DATA2_TABLE_FINDER",    ".prop-colb");
define("BEDROOM_REGEX",         "Beds:\ ([0-9]+|--)");
define("BATHROOM_REGEX",        "Baths:\ ([0-9.]+|--)");
define("SQUAREFOOT_REGEX",      "Sqft:\ ([0-9,]+|--)");
define("LOTSIZE_REGEX",         "Lot:\ ([0-9,]+|--)");
define("LISTED_REGEX",          "Days\ on\ Zillow:\ ([0-9]+)");
define("BUILT_REGEX",           "Built:\ ([0-9]+|--)");
define("PROP_TYPE_REGEX",       '\r\nBuilt.+\r\n\s+([a-zA-Z-_\ ]+)\r\n');
define("OUTPUT_FILENAME",       "data.jsonx"); // Each line is a valid JSON document
define("PROXY_TYPE",            CURLPROXY_SOCKS5);
define("PROXY_ADDRESS",         FALSE); // IP:Port notation, or FALSE to disable

$fileHandle = fopen(OUTPUT_FILENAME, 'a'); // We append data at the end, in case the spider dies we still have data

title('ACQUIRING LIST OF ALL STATES');

$state_page_array   = array();

$document = download(STATE_LIST_URL);

$states = $document->find(STATE_FINDER);

foreach($states AS $state_element) {
    # States have two pages worth of cities, each page holding up to 50 cities
    $state_page_array[] = BASE_URL . $state_element->href;
    $state_page_array[] = BASE_URL . $state_element->href . "p_2/";
    if (QUICK_DEBUG_MODE) break;
}

title('ACQUIRING LIST OF TOP 100 CITIES WITHIN THE STATE');

$city_page_array    = array();

foreach($state_page_array AS $state_page_url) {
    $document = download($state_page_url);

    $cities = $document->find(CITY_FINDER);
    foreach($cities AS $city_element) {
        $city_page_array[] = BASE_URL . $city_element->href;
    }
    # Example City URL: http://www.zillow.com/homes/for_sale/Bluefield-WV/
}

title('ACQUIRING LIST OF ALL PROPERTIES WITHIN THIS CITY');

foreach($city_page_array AS $city_page_url) {
    for($i = 1; $i <= 20; $i++) { # each city has up to 20 pages
        $current_url = $city_page_url;
        if ($i !== 1) { // First page doesn't use the X_p/ URL
            $current_url = $city_page_url . "{$i}_p/";
        }

        $document = download($current_url);

        $no_property = $document->find(NO_PROPERTY_FINDER, 0);
        if ($no_property && $no_property->plaintext == NO_PROPERTY_TEXT) {
            break;
        }
        parse_property($document);
        if (QUICK_DEBUG_MODE) break;
    }
    if (QUICK_DEBUG_MODE) break;
}

fclose($fileHandle);




function title($text) {
    $used_characters = strlen($text) + 1;
    $remain = 80 - $used_characters;
    echo "\n" . strtoupper($text) . ' ' . str_repeat('=', $remain) . "\n\n";
}

function download($url) {
    echo "DOWNLOAD: $url\n";
    $document = new simple_html_dom();
    $html = \SpiderMonkey\Downloader::getInstance()->execute($url, NULL, PROXY_TYPE, PROXY_ADDRESS);
    $document->load($html);
    return $document;
}

function parse_property($document) {
    $properties = $document->find(PROPERTY_FINDER);
    echo "FOUND " . count($properties) . " PROPERTIES\n";
    foreach($properties AS $property) {
        $property_data = array();

        $main_link = $property->find(MAIN_LINK_FINDER, 0);

        $property_data['zip'] = regex(ZIPCODE_REGEX, $main_link->href);

        $property_data['address'] = $main_link->plaintext;

        echo "FOUND: {$property_data['address']}\n";

        $property_data['price'] = str_replace(array('$', ','), '', $property->find(SALE_PRICE_FINDER, 0)->plaintext);

        $data_table = $property->find(DATA_TABLE_FINDER, 0)->plaintext;
        $data_table2 = $property->find(DATA2_TABLE_FINDER, 0)->plaintext;

        $value = regex(BEDROOM_REGEX, $data_table);
        $property_data['bed'] = $value == '--' ? 0 : $value+0;

        $value = regex(BATHROOM_REGEX, $data_table);
        $property_data['bath'] = $value == '--' ? 0 : $value+0;

        $value = regex(SQUAREFOOT_REGEX, $data_table);
        $property_data['sqft'] = $value == '--' ? 0 : str_replace(',', '', $value)+0;

        $value = regex(LOTSIZE_REGEX, $data_table);
        $property_data['lot'] = $value == '--' ? 0 : str_replace(',', '', $value)+0;

        $value = regex(LISTED_REGEX, $data_table2) + 0;
        $property_data['listed'] = date('Y-m-d', strtotime("$value days ago"));
        
        $value = regex(BUILT_REGEX, $data_table2);
        $property_data['built'] = $value == '--' ? '' : $value;

        $value = regex(PROP_TYPE_REGEX, $data_table2);
        $property_data['type'] = $value;

        $property_data['res'] = FALSE;
        if ($property_data['bed'] != 0 && $property_data['sqft'] != 0) {
            $property_data['res'] = TRUE;
        }

        writeData($property_data);
    }
}

function regex($pattern, $subject) {
    $matches = array();
    preg_match('#'.$pattern.'#', $subject, $matches);
    return $matches[1];
}

function writeData($data) {
    // Keeps adding data one line at a time. The whole document is invalid JSON, but each
    // line is valid. We do this in case the application dies halfway through. To print one
    // giant valid JSON document would take a lot of memory (and luck).
    global $fileHandle;
    fwrite($fileHandle, json_encode($data) . "\n");
}
