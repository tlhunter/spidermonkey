<?php
$config = isset($_GET['config']) ? $_GET['config'] : 'default';
$config = './config/' . $config . '.ini';
if (file_exists($config)) {
	define('BROWSER_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
	$config = parse_ini_file($config, TRUE);
} else {
	die("Bad Config File");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>SpiderMonkey by Thomas Hunter</title>
		<link type="text/css" href="css/black-tie/jquery-ui-1.8.2.custom.css" rel="stylesheet">	
		<link type="text/css" href="css/spidermonkey.css" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.2.custom.min.js"></script>
		<script type="text/javascript" src="js/spidermonkey.js"></script>
	</head>
	<body>
	<h1>SpiderMonkey <span>by Thomas Hunter</span></h1>
	<form method="post" action="execute.php">
		<div class="tabs" id="master-tabs">
			<ul>
				<li><a href="#tabs-1">URL Definition</a></li>
				<li><a href="#tabs-2">Capture Fields</a></li>
				<li><a href="#tabs-3">Output</a></li>
				<div style="float: right;">
					<li><a href="#tabs-4">Favorites</a></li>
					<li><a href="#tabs-5">Docs</a></li>
				</div>
			</ul>
			<div id="tabs-1">
				<div class="radios" id="method_radios">
					<input type="radio" id="url_method_spider" name="url_method" value="crawl" checked="checked" /><label for="url_method_spider">Crawling Spider</label>
					<input type="radio" id="url_method_increment" name="url_method" value="increment" /><label for="url_method_increment">Incremental Spider</label>
				</div>
				<div style="margin-top: 10px;">
					<table cellspacing="0">
						<tr class="handle-crawl"><th class="first label_column">&nbsp;</th><th class="last">Crawling Spider <span>(Find links like a search engine)</span></th></tr>
						<tr class="handle-crawl"><td><input name="first_url" maxlength="255" value="http://www.example.com/start.htm" class="textual" /></td><td>Starting URL<small>Spider will start here and build links</small></td></tr>
						<tr class="handle-crawl"><td><input name="limit_domain" maxlength="255" value="www.example.com" class="textual" /></td><td>Limit Domain To<small>Spider will not leave domain, leave blank for any (dangerous!)</small></td></tr>
						<tr class="handle-crawl"><td><input name="max_depth" maxlength="3" value="5" class="textual integer" /></td><td>Depth from beginning to crawl<small>0 = first page only, 1 = first linked pages, -1 = unlimited</small></td></tr>
						<tr class="handle-increment"><th class="first label_column">&nbsp;</th><th class="last">Incremental Spider <span>(Increment number in URL)</span></th></tr>
						<tr class="handle-increment"><td><input name="url_structure" maxlength="255" value="http://www.example.com/prod.php?id=#" class="textual" /></td><td>URL Structure<small>Use # symbol for integer</small></td></tr>
						<tr class="handle-increment"><td><input name="start_integer" maxlength="5" size="5" value="1" class="textual integer" /> - <input name="stop_integer" maxlength="5" size="5" value="99999" class="textual integer" /></td><td>Value Range</td></tr>
						<tr><th class="first label_column">&nbsp;</th><th class="last">Global Settings</th></tr>
						<tr><td><input name="give_up_404" value="0" maxlength="3" size="4" class="textual integer" /></td><td>Give Up 404s<small>Give up after X number of consecutive 404 pages</small></td></tr>
						<tr><td><input name="simultaneous" type="hidden" value="0" id="simultaneous-input" /><input name="delay" type="hidden" value="0" id="delay-input" /><div id="nice-slider"></div><div id="nice-visible">0</div></td><td>Niceness Slider<small>Left/Slow = ms between requests, Right/Aggressive = simultaneous requests</small></td></tr>
						<tr><td><input name="user_agent" maxlength="255" value="<?=$_SERVER['HTTP_USER_AGENT']?>" class="textual" /></td><td>User Agent<small>Disguises Spider as a Browser</small></td></tr>
						<tr><td><input name="referer" maxlength="255" value="http://www.example.com" class="textual" /></td><td>Referring URL<small>Reported to server as the page just visited</small></td></tr>
					</table>
				</div>
			</div>
			<div id="tabs-2">
				<table width="100%" cellspacing="0" id="captures">
					<tr>
						<th class="center first">Capture Method</th>
						<th class="center">Capture Expression</th>
						<th class="center">Capture Field Name</th>
						<th class="center last">Delete</th>
					</tr>
					<tr id="row-template">
						<td>
							<input type="hidden" class="finite" name="captures[]" value="" />
							<div class="radios">
								<input type="radio" name="capture" value="selector" id="capture-A" /><label for="capture-A">Selector</label>
								<input type="radio" name="capture" value="regex" id="capture-B" /><label for="capture-B">REGEX</label>
								<input type="radio" name="capture" value="asterisk" id="capture-C" /><label for="capture-C">Asterisk</label>
							</div>
						</td>
						<td>
							<input type="text" name="expression" value="" class="textual expression" />
						</td>
						<td>
							<input type="text" name="name" class="textual expname" />
						</td>
						<td class="center">
							<button class="trash">Del</button>
						</td>
					</tr>
				</table>
				<div style="margin-top: 10px; text-align: right;">
					<button class="add" id="add-row">Add Capture Field</button>
				</div>
			</div>
			<div id="tabs-3">
				<div class="radios" id="output_radios">
					<input type="radio" id="storage_mysql" name="storage" value="mysql" /><label for="storage_mysql">MySQL DB</label>
					<input type="radio" id="storage_sqlite" name="storage" value="sqlite" checked="checked" /><label for="storage_sqlite">SQLite DB</label>
					<input type="radio" id="storage_csv" name="storage" value="csv" /><label for="storage_csv">CSV File</label>
					<input type="radio" id="storage_xml" name="storage" value="xml" /><label for="storage_xml">XML File</label>
					<input type="radio" id="storage_json" name="storage" value="json" /><label for="storage_json">JSON File</label>
					<input type="radio" id="storage_display" name="storage" value="display" /><label for="storage_display">Display Only</label>
				</div>
				<div style="margin-top: 10px;">
					<table cellspacing="0">
						<tr class="handle-mysql"><th class="first label_column">&nbsp;</th><th class="last">MySQL Settings</th></tr>
						<tr class="handle-mysql"><td><input name="mysql_username" maxlength="255" value="root" class="textual" /></td><td>MySQL Username</td></tr>
						<tr class="handle-mysql"><td><input name="mysql_password" maxlength="255" value="" class="textual" /></td><td>MySQL Password</td></tr>
						<tr class="handle-mysql"><td><input name="mysql_server" maxlength="255" value="localhost" class="textual" /></td><td>MySQL Server</td></tr>
						<tr class="handle-mysql"><td><input name="mysql_database" maxlength="255" value="spidermonkey" class="textual" /></td><td>MySQL Database</td></tr>
						<tr class="handle-file"><th class="first label_column">&nbsp;</th><th class="last">File Settings</th></tr>
						<tr class="handle-file"><td><input name="filename" maxlength="255" value="results" class="textual" /></td><td>Filename<small>Include file extension</small></td></tr>
						<tr class="handle-file"><td><input name="local" type="checkbox" /></td><td>Save to Server<small>Uncheck to download to browser</small></td></tr>
					</table>
				</div>
			</div>
			<div id="tabs-4">
<h3>Load Configuration:</h3>
				<ul>
<?php
if ($handle = opendir('./config')) {
    while (false !== ($file = readdir($handle))) {
		if (!strpos($file, '.ini')) {
			continue;
		}
		$file = basename($file, ".ini");
        echo "<li><a href='?config=$file'>" . ucfirst($file) . "</a></ul>\n";
	}
} else {
	echo "No Configuration Directory";
}
?>
				</ul>
<h3>Save Current Configuration:</h3>
				<input name="save-favorite" placeholder="Favorite Name" />
				<input type="button" value="Save Current Config" />
			</div>
			<div id="tabs-5">
				<p>SpiderMonkey is a powerful yet easy to use website spidering application. SpiderMonkey makes it easy to build website scraping tools for custom projects without the need to hire a programmer for each job. Using the powerful CSS selector engine, users can simply develop selectors using a tool in their browser such as Firebug and without the need for custom Regular Expressions, which can be difficult to develop.</p>
				<p>Give this tool a try for testing CSS Selectors on the webpages you'd be crawling: <a href="http://www.westciv.com/mri/">CSS MRI</a>.</p>
				<strong>Upcoming Features:</strong>
				<ul>
					<li>Load and Save favorite queries</li>
					<li>Asynchronous Downloads</li>
					<li>Select different jQuery UI Themes</li>
					<li>Generic bugfixes and stability</li>
				</ul>
				<h3>URL Definition</h3>
				<p>This tab allows you to specify the rules for which pages will be downloaded. There are two primary ways which pages can be downloaded, Incremental and Crawling.</p>
				<h5>Crawling Spider</h5>
				<p>Crawling Spider's work the way the major search engines work. An initial page URL is provided, and is downloaded. Once this page is downloaded, all links on the page are added to a queue to be downloaded. Care is taken to prevent duplicate pages from being downloaded. The Starting URL setting specifies the first page to download, the Limit Domain To setting prevents the Spider from leaving this domain, and the Depth setting specifies how many layers deep from the first page to Spider.</p>
				<h5>Incremental Spider</h5>
				<p>Incremental Spiders do not have the ability to find new pages like Crawling Spiders. These are useful for crawling pages whose URL have a unique numerical ID representing data being retrieved. In the URL, use a # symbol to represent the number to be incremented (if you need to use a literal # symbol in the URL, replace it with %23). You can specify the starting number and the ending number for the crawl.</p>
				<h5>Global Spider URL Settings</h5>
				<p>The 404 Give Up setting specifies how many consecutive pages with a 404 error will cause the Spider to shut down. This is useful to abort a Spider which may have been fed some bad data.</p>
				<p>The Simultaneous Download option specifies how many URLs should be downloaded at the same time. By specifying a number greater than 1, the time to finish a spidering operation will be a lot faster, however the toll it takes on the spidering and content server will be greater. Keep in mind that setting this number greater than 1 will cause incremental downloads to not be in their numerical order.</p>
				<p>The Crawl Timeout option tells the server to wait X number of milliseconds between crawls. This throttles the overall download speed and is used as a courtesy feature for the content server.</p>
				<p>The User Agent and Referring URL options are used for sites which block normal Spider requests. These help cloak your Spider into acting more like a typical browser. The User Agent is automatically copied from your current browser. You may want to set the Referring URL to be a known page on the content web site.</p>
				
				<h3>Capture Fields</h3>
				<p>This tab allows you to specify the data which is being copied from the content server. You can specify CSS Selectors, which makes spidering VERY easy for non programmers, and is specifically built for interacting with HTML. You can specify the traditional Regular Expressions, which is the de-facto pattern matching language, although it is harder to understand. You can also use our custom Asterisk notation, which is a simple tool for replacing the most common of Regular Expressions.</p>
				<p>Click the Add More Captures button to define more captures for the spider. Clicking delete on the far right of a capture row will remove that entry. Make sure you specify a Capture method for each row, otherwise the spider will fail. The expression defines the pattern to search for, and the name column allows you to name it, to be later used for database/file storage.</p>
				<h5>Selector</h5>
				<p>You can use most of the standardized CSS selectors. To grab a pages Title, you can simply use the selector <em>title</em>. To grab the footer of a webpage, you could use <em>#footer div.inside</em>. Selectors will return the HTML which is INSIDE of the specified element by default (including other HTML). Or, if there is nothing inside of the element (e.g. an IMG tag), it will return the WHOLE tag.</p>
				<h5>REGEX</h5>
				<p>Here you can specify a <abbr title="Perl Compatible Regular Expression">PCRE</abbr> regular expression. Note that you do not need to specify the beginning and ending delimiters. Data in the first capture group will be returned. To grab the page's title, you could use <em>&lt;title&gt;(.*?)&lt;/title&gt;</em>.</p>
				<h5>Asterisk</h5>
				<p>The Asterisk method provides an easy way to grab content when you know the text before and after it. This provides a simpler alternative to 75% of the web spidering REGEX's. To grab the page's title, you could use <em>&lt;title&gt;*&lt;/title&gt;</em>. Only use one asterisk per expression.</p>

				<h3>Output</h3>
				<p>This screen allows you to specify what to do with the data once it's been acquired. You can store the data into a MySQL database, export the data into common data formats, or just display the information on-screen.</p>
			</div>
		</div>
		<div style="margin-top: 20px;">
			<button class="play">Execute Spider</button>
		</div>
	</form>
	</body>
</html>


