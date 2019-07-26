# PHP Scraper

Simple PHP scraper/crawler written for PHP command line

Hopefully it will help anyone out there...
Enjoy ;)

## Requirements

* PHP command line utility (comes with PHP download no need to install web server)
* Add `php` binary or `php.exe` path to PATH environment variable

## Parameters

* `string $url` Starting URL
* `string $crawl_regex` Regular expression that will be used for link crawling
* `string $scrape_regex` Regular expression that will be used for data scraping
* `integer $level` Used for recursion, use 0 when calling function
* `string $out_file` Name of CSV file to export to
* `integer $max_level` Maximum levels or depth to crawl into
* `string $domain` (Optional) Used for recursion, use "" when calling function
* `integer $max_retries` (Optional) Number of HTTP retries when timeouts or errors occur (default 3)
* `boolean $use_cache` (Optional) True to cache web pages for fast extraction after re-running the script

## Usage

1) Open terminal (cmd, PowerShell or Git Bash for Windows)
2) Change directory to script directory
3) Run `php` to start scripting mode
4) Run the scrape function using your required parameters
```
<?php include 'php_scraper.php';
scrape("https://www.google.com/", "test", "test", 0, "output.csv", 20); ?>
```
5) Press Ctrl+Z then enter to run

To know more about the regular expressions used check my [tutorial](http://www.brainyleaks.com/2017/11/basic-php-website-crawler-scraper.html)

## Contributions

I encourage you all to contribute into this simple project to make better and more usable.