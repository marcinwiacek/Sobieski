<?php

// Script is opening $url pages and creating text files/SQLite DB with data inside $path
// There are open $numberOfFiles and $numberOfMainPages main page subpages, later
// script is going into all pages for taxonomies

// known bugs: day in comments migrated without leading digit

$numberOfFiles = 371;
$numberOfMainPages = 23;
$url = "http://www.x";
$path = "d:\\d\\";
$pageName = "Marcin&#039;s page ON-LINE";
set_time_limit(600); // 10 minutes

function decode_chars($input) {
	return preg_replace_callback("/(&#[0-9]+;)/", 
		function($match) { return mb_convert_encoding($match[1], "UTF-8", "HTML-ENTITIES"); },
		$input);
}

function processPage($f,$main) {
	global $path;

	$one = $f;
	while (true) {
		if (($one = strstr($one, "<div id=\"node-"))=== false) break;

		$one = substr($one, 14);
		$id = strstr($one, "\"", true);

		// hardcoded
		$one = strstr($one, "<span class=\"username\">marcin</span> on ");
		$one = substr($one, 39+5);
		$date = strstr($one, "    </span>", true);
		$timestamp = strtotime($date);

		$one = strstr($one, "field-type-text-with-summary field-label-hidden\"><div class=\"field-items\"><div class=\"field-item even\">");
		$one = substr($one, 54+49);
		$teaserText = strstr($one, "</div></div></div>",true);

		echo "Processing $id $timestamp ".strlen($teaserText)."<br>";

		$text=file_get_contents("$path".date("Ymd_",$timestamp)."$id.txt");
	    	$lines = preg_split("/\\r\\n|\\r|\\n/", $text);
		$output="";
		$teaser=false;
		$teaserWithSplit=false;
		if ($main) {
		    	foreach($lines as $singleLine) {
				if ($singleLine != "SkipMain:true") $output=$output.$singleLine."\n";
			}
			file_put_contents("$path".date("Ymd_",$timestamp).$id.".txt", rtrim($output));
			$text=file_get_contents("$path".date("Ymd_",$timestamp)."$id.txt");
		    	$lines = preg_split("/\\r\\n|\\r|\\n/", $text);
		}
		$process=true;
	    	foreach($lines as $singleLine) {
			if ($singleLine == "<!--teaser-->" || $singleLine == "<!--break-->") $process=false;
		}

		if ($process) {
			// try to create new break
		    	$teaserLines = preg_split("/\\r\\n|\\r|\\n/", trim(decode_chars($teaserText)));
			$output="";
			$i=0;
			$endOfHeaders=false;
	    		foreach($lines as $singleLine) {
				if ($endOfHeaders) {
					if ($i==0 && $teaserText!="") {
						// ignore lines with this
						if (!(strpos($singleLine,"align=\"center\" style=\"color: rgb(0, 64, 128);\">")===false)) {
							$output=$output.$singleLine."\n";
							continue;
						}
						if ($singleLine == "") {
							$output=$output.$singleLine."\n";
							continue;
						}
					}
					if ($i==-1) {
						$output=$output.$singleLine."\n";
					} else if ($singleLine == $teaserLines[$i]) {
						$output=$output.$singleLine."\n";
						$i++;
						if ($i==count($teaserLines)) {
							$output=$output."<!--break-->\n";
							$i=-1;
						}
					} else {
						if ($i==0) {
							$output=$output.trim(decode_chars($teaserText))."\n<!--teaser-->\n".$singleLine."\n";
						} else {
							$output=$output."<!--break-->\n".$singleLine."\n";
						}
						$i=-1;
					}
				} else {
					$output=$output.$singleLine."\n";
				}
				if ($singleLine == "") $endOfHeaders=true;
			}

			file_put_contents("$path".date("Ymd_",$timestamp).$id.".txt", rtrim($output));
		}
	}
}

$db = new SQLite3($path."test.db");
$db->exec("CREATE TABLE IF NOT EXISTS counters(filename TEXT, visits INT)");

// Processing all pages with content
for ($i=1;$i<$numberOfFiles;$i++) {
	$f=file_get_contents("$url?q=node/".$i);

	$f2 = strstr($f, "<title>");
	$f2 = substr($f2, 7);
	$f2 = strstr($f2, " |  $pageName</title>", true);
	
	$f3 = strstr($f, "<div class=\"field field-name-body field-type-text-with-summary field-label-hidden\"><div class=\"field-items\"><div class=\"field-item even\">");
	$f3 = substr($f3, 29+25+83);
	$f3 = strstr($f3, "</div></div></div>", true);
	$f3 = str_replace("<!--break-->","<!--break-->\n",$f3);
	$f3 = trim(decode_chars($f3));

	// hardcoded
	$f4 = strstr($f, "Submitted by <span class=\"username\">marcin</span> ");
	$f4 = substr($f4, 57);
	$f4 = strstr($f4, "    </span>", true);

	$f5 = $f;
	$taxonomy="";
	while (true) {
		if (($one = strstr($f5, "<div class=\"field-item even\"><a href=\"/www/?q=taxonomy/term/"))=== false) break;

		$one = strstr($one, ">");
		$one = substr($one, 1);
		$one = strstr($one, ">");
		$one = substr($one, 1);
		$f5 = $one;
		$one = strstr($one, "</a></div>", true);
		
		if ($taxonomy!="") $taxonomy=$taxonomy.",";
		$taxonomy=$taxonomy.$one;

		if (($one = strstr($f5, "<div class=\"field-item odd\"><a href=\"/www/?q=taxonomy/term/"))=== false) break;

		$one = strstr($one, ">");
		$one = substr($one, 1);
		$one = strstr($one, ">");
		$one = substr($one, 1);
		$f5 = $one;
		$one = strstr($one, "</a></div>", true);
		
		if ($taxonomy!="") $taxonomy=$taxonomy.",";
		$taxonomy=$taxonomy.$one;
	}

	$f6 = $f;
	$comments="";
	while (true) {
//		if (($one = strstr($f5, "<div class=\"field field-name-comment-body field-type-text-long field-label-hidden\"><div class=\"field-items\"><div class=\"field-item even\">"))=== false) break;
		if (($one = strstr($f5, "<div class=\"comment "))=== false) break;

		$one = strstr($one, "<h3><a href=\"/www/?q=comment/");
		$one = strstr($one, "\" class=\"permalink\" rel=\"bookmark\">");
		$one = substr($one, 35);
		$title = strstr($one, "</a></h3>", true);

		$one = strstr($one, "<a href=\"/www/?q=comment/");
		$one = substr($one, 25);
		$id = strstr($one, "#", true);

		$one = strstr($one, "<span class=\"username\">");
		$one = substr($one, 23);
		$author = strstr($one, "</span>", true);

		$one = strstr($one, " on ");
		$one = substr($one, 9);
		$when = strstr($one, ".", true);

		$one = strstr($one, "field-item even\">");
		$one = substr($one, 17);
		$f5 = $one;
		$one = strstr($one, "</div></div></div>", true);
		$one = str_replace("<!--break-->","<!--break-->\n",$one);
		$one = decode_chars($one);

		if ($comments == "") {
			$comments = $comments."\n<!--comments-->\n";
		} else {
			$comments=$comments."<!--comment-->\n";
		}
		$comments=$comments."Title:$title\nID:$id\nAuthor:$author\nWhen:$when\n\n".trim($one);
	}

	if ($f2!="") {
		$counter = strstr($f, "<li class=\"statistics_counter last\"><span>");
		$counter = substr($counter, 42);
		$counter = strstr($counter, " ", true);

		$db->exec("INSERT INTO counters(filename,visits) VALUES('$i',$counter)");

		$timestamp = strtotime($f4);
		file_put_contents("$path".date("Ymd_",$timestamp).$i.".txt", "\xEF\xBB\xBFTitle:$f2\nAuthor:marcin\nWhen:$f4\nTaxonomy:$taxonomy\nSkipMain:true\n\n$f3$comments");
	}
}

// Processing all subpages for main page
for ($i=0;$i<$numberOfMainPages;$i++) {
	$f=file_get_contents("$url?q=node&page=".$i);

	processPage($f,true);
}

// Loop for going over taxonomies subpages
$f=file_get_contents("$url");
while (true) {
	if (($f = strstr($f, "<li class=\"leaf first\"><a href=\"?q=taxonomy/term/"))=== false) break;
	$f = substr($f, 49);
	$id = strstr($f, "\"", true);

	$f = strstr($f, "(");
	$f = substr($f, 1);
	$number = strstr($f, ")", true);

	echo $id ."->".$number."<br>";

	$num=0;
	while ($num*10 <intval($number)) {
		echo "taxonomy $id page $num<br>";
		$f2=file_get_contents("$url?q=taxonomy/term/".$id."&page=$num");
		processPage($f2,false);
		$num++;
	}
}

$db->close();

?>