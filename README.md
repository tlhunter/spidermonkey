Spider Monkey
===

PHP Web Scraping Engine

I started this project in January 2011. It was going to be an easy to use web scraper that anyone could
configure. It has an attractive GUI interface using jQuery UI elements.

![Capture Screen](https://github.com/tlhunter/spidermonkey/raw/master/screenshots/url.png)

The selectors can be entered using three different methods; the first is the tried and true regular
expression method. The second, which is the easiest and most powerful to use, is CSS selectors. Someone
could visit the pages they want to scrape, use their developer console to debug some selectors, and
then toss them into the engine. The third method is a simplified regex syntax I call asterisk, where
the user enteres the start string and the end string and an asterisk in the middle.

![Capture Screen](https://github.com/tlhunter/spidermonkey/raw/master/screenshots/capture.png)

There is also an easy to use configuration screen for data storage. The engine was going to be smart
enough to build different mysql tables and even build the relationshipts between the data. Or, data
could be stored in XML or JSON documents, and a hierarchy would be maintained.

![Capture Screen](https://github.com/tlhunter/spidermonkey/raw/master/screenshots/output.png)

But, I never finished the project.

This repo is a hodge podge of code, rather unorganized. I haven't been through it in a while, there
might actually be two distinct code bases in here.

It is now released under the BSD license.
