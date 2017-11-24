<?php
	
	
	function scrape($url, $level, $out_file, $domain = "", $max_level = 0, $max_retries = 3, $use_cache = true) {
		// Stop at a certain depth defined by Maximum Level
		// Root page is level 0
		if ($max_level > 0 && $level > $max_level) {
			return false;
		}
		
		echo "Scraping: $url\n";
		
		// Empty the output file at root page and get domain name
		if ($level = 0) {
			file_put_contents($out_file, "");
			$ret = preg_match('!https?://[^/\?]+!i', $url, $arr);
			$domain =  $arr[1];
		}
		
		// Caching web pages in folder "cache" if Use Cahce is set to true
		$cached = false;
		if ($use_cache) {
			mkdir("cache", 0777);
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
					$html = file_get_contents($url);
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
		
		// --------- Data scraping ---------
		//Assuming there is no data to scrape in first page
		if ($level != 0) {
			$ret = preg_match_all('!Here goes the regexp for scraping data!imsU', $html, $arr);
			// Usualy links need prefixing the domain name to it
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
				file_put_contents($out_file, $line, FILE_APPEND);
			}
		}
		
		// --------- Links crawling ---------
		$ret = preg_match_all('!Here goes the regexp for crawling links!imsU', $html, $arr);
		// Usualy links need prefixing the domain name to it
		array_walk($arr[1], 'pfx', $domain);
		foreach ($arr[1] as $key => $sub_url) {
			scrape($sub_url, $level + 1, $out_file, $max_level, $max_retries, $use_cache);
		}
	}
	
	function pfx(&$val, $key, $prefix)
	{
		$val = "$prefix$val";
	}

	function sfx(&$val, $key, $suffix)
	{
		$val = "$val$suffix";
	}

	scrape("https://www.amazon.com/Best-Sellers/zgbs", 0, "output.csv");
?>
