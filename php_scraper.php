<?php
	/**
	* The main function that does the job
	*
	* The function crawls the URL for links and then scrapes data
	*  from pages of these links and exports the data to CSV file
	*
	* @param string $url Starting URL
	* @param string $crawl_regex Regular expression that will be used
	*    for link crawling
	* @param string $scrape_regex Regular expression that will be used
	*    for data scraping
	* @param integer $level Used for recursion, use 0 when calling function
	* @param string $out_file Name of CSV file to export to
	* @param integer $max_level Maximum levels or depth to crawl into
	* @param string $domain (Optional) Used for recursion, use ""
	*    when calling function
	* @param integer $max_retries (Optional) Number of HTTP retries
	*    when timeouts or errors occur (default 3)
	* @param boolean $use_cache (Optional) True to cache web pages
	*    for fast extraction after re-running the script
	*
	* @return boolean Tue if successful, false if otherwise
	*/
	function scrape($url, $crawl_regex, $scrape_regex, $level, $out_file, $max_level = 0, $domain = "", $max_retries = 3, $use_cache = true) {
		// Stop at a certain depth defined by Maximum Level
		// Root page is level 0
		if ($max_level > 0 && $level > $max_level) {
			return false;
		}
		
		echo "\nScraping: $url\n";
		
		// Empty the output file at root page and get domain name
		if ($level == 0) {
			file_put_contents($out_file, "");
			$ret = preg_match('!https?://[^/\?]+!i', $url, $arr);
			$domain =  $arr[0];
			echo "Domain: $domain\n";
		}
		
		// Caching web pages in folder "cache" if Use Cache is set to true
		$cached = false;
		if ($use_cache) {
			@mkdir("cache", 0777);
			$cache = "cache/" . md5($url);
			if (file_exists($cache)) {
				echo "Fetching from: $cache\n";
				$html = file_get_contents($cache);
				$cached = true;
			} else {
				echo "Fetching from: $url\n";
				$retry = 0;
				$html = false;
				while ($html === false && $retry < $max_retries) {
					$retry++;
					$html = @file_get_contents($url);
				}
				if ($html === false) {
					echo "Error reading: $url\n";
					return false;
				}
				file_put_contents($cache, $html);
			}
		}
		
		// Almost all web pages have titles, so the first thing
		//  to scrape is the title
		$ret = preg_match('|<title>([^<]+)</title>|i', $html, $arr);
		$title =  $arr[1];
		echo "Title: $title\n";
		
		// --------- Data scraping ---------
		//Assuming there is no data to scrape in first page
		if ($level != 0) {
			$ret = preg_match_all("!$scrape_regex!imsU", $html, $arr);
			// Usually links need prefixing the domain name to it
			array_walk($arr[2], 'pfx', $domain);
			array_walk($arr[4], 'sfx', '.jpg');
			foreach ($arr[1] as $key => $val) {
				// Add the title of the web page at the start of each output line
				$line = $title;
				$line .= ",$val";
				$line .= ",\"{$arr[14][$key]}\"";
				$line .= ",{$arr[8][$key]}";
				$line .= ",\"{$arr[11][$key]}\"";
				$line .= ",\"{$arr[3][$key]}\"";
				$line .= ",{$arr[2][$key]}";
				$line .= ",{$arr[4][$key]}\n";
				$line .= "\n";
				file_put_contents($out_file, $line, FILE_APPEND);
			}
		}
		
		// --------- Links crawling ---------
		$ret = preg_match_all("!$crawl_regex!imsU", $html, $arr);
		if ($ret === false || $ret == 0) {
			return true;
		}
		// Usually links need prefixing the domain name to it
		array_walk($arr[1], 'dec');
		array_walk($arr[1], 'pfx', $domain);
		foreach ($arr[1] as $key => $sub_url) {
			scrape($sub_url, $crawl_regex, $scrape_regex, $level + 1,
			$out_file, $max_level, $domain, $max_retries, $use_cache);
		}
		return true;
	}
	
	function pfx(&$val, $key, $prefix)
	{
		$val = "$prefix$val";
	}

	function sfx(&$val, $key, $suffix)
	{
		$val = "$val$suffix";
	}

	function dec(&$val, $key)
	{
		$val = htmlspecialchars_decode($val);
	}
?>
