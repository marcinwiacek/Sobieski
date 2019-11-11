<?php

/* Default config */
$gSiteTitle = "Site title";
$gTeaserLength = 200;
$gTitlesPerPage = 10;
$gFilesDir = "arts"; // subdirectory with article files
$gAllowedTags = "";
$gSecondsForVisits = 60*60*2; // 60*60*2 = 2h

$gRedirect = array();
$gRedirect["page"]="99";
/* End of default config */

include "config.php"; // User config + writeUserMenu() function

function CreatePageSkeletonTop($title) {
	global $gTimeArr, $gTaxonomyArr, $gTop10Arr, $gMobile;

	echo("<html><head><meta charset='utf-8'>");
	if ($gMobile) {
		echo "<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=3.0, minimum-scale=1.0;' />";
	}
	echo("<title>".$title."</title>");
    	echo("<link rel='stylesheet' type='text/css' href='styles.css'>");

	if (isset($_GET["dark"])) {
		if ($_GET["dark"]=="yes") {
			setcookie('dark', '1', 2147483647);
		    	echo("<link rel='stylesheet' type='text/css' href='dark.css'>");
		} else if ($_GET["dark"]=="no") {
			setcookie('dark', '0', 2147483647);
		}
	} else if(!isset($_COOKIE['dark'])) {
		// too slow in FP
//	    	echo("<link rel='stylesheet' type='text/css' href='autodark.css'>");
	} else if ($_COOKIE['dark']=='1') {
	    	echo("<link rel='stylesheet' type='text/css' href='dark.css'>");
	}
	echo("</head><body>");

	if ($gMobile) {
		echo("<span class=top-mobile-menu>");
	}
	echo("<table width=100%><tr><td>");
	if ($gMobile) {
		echo("<span class=menu-layers>&nbsp;<b>Menu</b><span class=mobile-menu>");
		Menu();
		echo("</span></span>");
		echo("</td><td align=right valign=top>");
		writeColorMobileLinks();
		echo("</td></tr><tr><td colspan=2>");
	}
	echo("&nbsp;<a href=\".\" class=small title='Main page'>mwiacek.com</a>");
}

function writeTopAfterMenu() {
	global $gMobile;

	if ($gMobile) {
		echo("</td></tr></table></span><p>&nbsp;<br>&nbsp;<table><tr><td class=main>");
	} else {
		echo("</td><td align=right valign=top>");
		writeColorMobileLinks();
		echo("</td></tr></table>");
		echo("<table><tr><td class=menu>");
		Menu();
		echo("</td><td class=main>");
	}
}

function writeColorMobileLinks() {
	global $gMobile;

	echo("<a href=\"?");
	if (isset($_GET['q'])) echo("q=".$_GET['q']."&");
	if (isset($_GET['page'])) echo("page=".$_GET['page']."&");
	if (isset($_GET['page'])) echo("page=".$_GET['page']."&");
	if (isset($_GET['sort'])) echo("sort=".$_GET['sort']."&");
	echo("dark=yes\" class=dark_theme>Color</a>");

	echo("<a href=\"?");
	if (isset($_GET['q'])) echo("q=".$_GET['q']."&");
	if (isset($_GET['page'])) echo("page=".$_GET['page']."&");
	if (isset($_GET['page'])) echo("page=".$_GET['page']."&");
	if (isset($_GET['sort'])) echo("sort=".$_GET['sort']."&");
	echo("dark=no\" class=light_theme>Color</a>");

	echo(" | <a href=\"?");
	if (isset($_GET['q'])) echo("q=".$_GET['q']."&");
	if (isset($_GET['page'])) echo("page=".$_GET['page']."&");
	if (isset($_GET['page'])) echo("page=".$_GET['page']."&");
	if (isset($_GET['sort'])) echo("sort=".$_GET['sort']."&");
	if ($gMobile) {
		echo("mobile=no");
	} else {
		echo("mobile=yes");
	}
	echo("\" class=small>Mobile</a> &nbsp;");
}

function Menu() {
	global $gTimeArr, $gTaxonomyArr, $gTop10Arr, $gSort, $gMobile;

	writeUserMenu();

	echo "<p><span class=title_no_click>Categories</span>";
	echo "<hr>";
    	foreach($gTaxonomyArr as $index => $entry) {
		echo("<a class=taxonomy href=\"?q=taxonomy/term/".urlencode($index)."\">".$index." (".$entry.")</a><br>");
	}

	echo "<p><span class=title_no_click>Top 10</span>";
	echo "<hr>";
    	foreach($gTop10Arr as $top10) {
		echo ("<a class=taxonomy href=\"?q=".getNodeName($top10[0])."\">".$top10[1]." (".$top10[2].")</a><p>");
	}

	echo "<p><span class=title_no_click>Timeline</span>";
	echo "<hr>";
    	foreach($gTimeArr as $index => $entry) {
		echo("<a class=taxonomy href=\"?q=time/".$index."\">".$index." (".$entry.")</a><br>");
	}
}

function CreatePageSkeletonBottom() {
	echo("</td></tr></table>");

	echo("<script>");
	echo("function setClassForExternalLinks(tree) {");
	echo("for (index = 0; index < tree.length; ++index) {");
	echo("if (tree[index].href.indexOf(window.location.protocol+'//'+window.location.hostname+':'+window.location.port)!=0");
	echo("&& tree[index].className=='') {");
	echo("tree[index].className='ext';");
	echo("}");
	echo("}");
	echo("} ");
	echo("setClassForExternalLinks(document.querySelectorAll('.teaser a'));");
	echo("setClassForExternalLinks(document.querySelectorAll('.main a'));");
	echo("</script>");

	echo("</body></html>");
}

function readSort($a, $b) {
	global $gReadsArray;

	if ($a == $b) return 0;
	// Take short names from long names
	preg_match("/^(.*)\_(.*)\.txt/", $a, $shortOne);
	preg_match("/^(.*)\_(.*)\.txt/", $b, $shortTwo);

	// Take number of reads
	$one = 0;
	$two = 0;
	if (array_key_exists($shortOne[2], $gReadsArray)) $one=$gReadsArray[$shortOne[2]];
	if (array_key_exists($shortTwo[2], $gReadsArray)) $two=$gReadsArray[$shortTwo[2]];

	// if number of reads is equal, compare full names
	if ($one == $two) return ($a < $b) ? 1 : -1;

	return ($one < $two) ? 1 : -1;
}

function ProcessAllAndGetLists($callback,$start,$end) {
	global $gTaxonomyArr,$gTimeArr,$gFilesDir,$gTitlesPerPage,$gPagesNum,$gFinalFilesArr,$gTop10Arr,$gSort,$gReadsArray;

    	$gTaxonomyArr = array();
	$gTimeArr = array();
	$gTop10Arr = array();
	$gFinalFilesArr = array();
	$gPagesNum = 0;

	$fileNames = array();
	$names = scandir("$gFilesDir",0);
	foreach ($names as $file) {
		if (is_file("$gFilesDir/$file") && preg_match("/^(.*)\_(.*)\.txt/", $file, $fileNameArray)) {
			if (!in_array($fileNameArray[2],$fileNames)) {
				$fileNames[$file] = $fileNameArray[2];
			}
		}
	}
	krsort($fileNames);

	$db = new SQLite3("$gFilesDir/test.db");
	$db->busyTimeout(5000);
	// https://www.sqlite.org/wal.html
	$db->exec('PRAGMA journal_mode = wal;');
	$db->exec("CREATE TABLE IF NOT EXISTS counters(filename TEXT, visits INT)");

	if ($gSort == "reads") {
		$gReadsArray = array();

		$statement = $db->prepare("SELECT visits,filename FROM counters ORDER by visits desc");
		$result = $statement->execute();
		$i=0;
		while ($row = $result->fetchArray()) {
			$i++;
			if ($i<=10) {
				$array2 = array();
				array_push($array2,$row['filename']);
				array_push($array2,'');
				array_push($array2,$row['visits']);
				array_push($gTop10Arr,$array2);
			}
			$gReadsArray[$row['filename']] = $row['visits'];
		}
		uksort($fileNames,"readSort");
	} else {
		$statement = $db->prepare("SELECT visits,filename FROM counters ORDER by visits desc LIMIT 10");
		$result = $statement->execute();
		while ($row = $result->fetchArray()) {
			$array2 = array();
			array_push($array2,$row['filename']);
			array_push($array2,'');
			array_push($array2,$row['visits']);
			array_push($gTop10Arr,$array2);
		}	
	}
	$db->close();

	$i = 0;
    	foreach($fileNames as $longName => $shortName) {
        	$text = readFileContent($longName);
		if ($start != $end) {
			$arr = decodeFileContent($text,true);
		} else {
			$arr = decodeFileContent($text,!$callback($shortName,null,null,null));
		}
		if (isset($arr["taxonomy"])) {
			$taxonomy = explode(",", $arr["taxonomy"]);
	  		foreach($taxonomy as $entry) {
				if (isset($gTaxonomyArr[$entry])) {
					$gTaxonomyArr[$entry] = $gTaxonomyArr[$entry]+1;
				} else {
					$gTaxonomyArr[$entry] = 1;
				}
		  	}
		} else {
			$taxonomy = NULL;
		}
		if (isset($arr["when"]) && !(($timestamp = strtotime($arr["when"])) === false)) {
			$when = date("Y-m",$timestamp);
			if (isset($gTimeArr[$when])) {
				$gTimeArr[$when] = $gTimeArr[$when]+1;
			} else {
				$gTimeArr[$when] = 1;
			}
		} else {
			$when = NULL;
		}
		if (isset($arr["title"])) {
			for ($j=0;$j<count($gTop10Arr);$j++) {
				if ($gTop10Arr[$j][0]==$shortName) {
					$gTop10Arr[$j][1] = $arr["title"];
					break;
				}
			}
		}
		if ($callback($shortName,$taxonomy,$when,isset($arr["skipmain"]))) {
			if ($gPagesNum*$gTitlesPerPage+$i+1>=$start && $gPagesNum*$gTitlesPerPage+$i+1<=$end) {
				$array2 = array();
				array_push($array2,$shortName);
				array_push($array2,$arr);
				array_push($gFinalFilesArr,$array2);
			}
			if ($start != $end) {
				$i++;
				if ($i == $gTitlesPerPage) {
					$i = 0;
					$gPagesNum++;
				}
			}
		}
	}
	if ($i!=0) $gPagesNum++;

	// alphabetical ascending or by value descending
	ksort($gTaxonomyArr,SORT_NATURAL | SORT_FLAG_CASE);
	//arsort($gTaxonomyArr);
	
	krsort($gTimeArr);
}

function strncmp_startswith($haystack, $needle) {
    	return strncmp($haystack, $needle, strlen($needle)) === 0;
}

function getNodeName($shortFileName) {
	global $gRedirect;

    	foreach($gRedirect as $index => $entry) {
		if ($entry == $shortFileName) return $index;
	}
	return "node/" . $shortFileName;
}

function readFileContent($fileName) {
	global $gFilesDir;

	$nodeFileName = "$gFilesDir/" . $fileName;

  	$handle = @fopen($nodeFileName, "r");
  	if ($handle == FALSE) {
	     	header('HTTP/1.1 404 Not Found');
     		exit(0);
  	}

  	$text = fread($handle, filesize($nodeFileName));
  	if (!$text) {
     		header('HTTP/1.1 404 Not Found');
     		exit(0);
  	}
  	fclose($handle);

	// support for UTF8 files
	if (strpos($text, '\xEF') == 0) {
		return substr($text,3);
	}
  	return $text;
}

function createTeaser($text) {
	global $gTeaserLength,$gAllowedTags;

	$teaser = "";
	$text2 = ignoreFromTeaser($text);

	// make teaser from characters outside tags - we process text until we achieve at least $gTeaserLength chars
	while (strlen(strip_tags($teaser,$gAllowedTags)) < $gTeaserLength && strlen ($text2)!=0) {
		if (strpos($text2,"<")===false || strpos($text2,"<")>$gTeaserLength-strlen(strip_tags($teaser,$gAllowedTags))) {
			$teaser = $teaser.substr($text2,0,$gTeaserLength-strlen(strip_tags($teaser,$gAllowedTags))+1)."...";
			$text2 = substr($text2,$gTeaserLength-strlen(strip_tags($teaser,$gAllowedTags))+1);
		} else {
			$pos = strpos($text2,"<");
			// for hr p br search for end of tag > or />
			// for others search for closing tag starting from </ and ending with > or />
			if (substr($text2,$pos,3)=="<hr" || substr($text2,$pos,2)=="<p" || substr($text2,$pos,3)=="<br") {
				$teaser = $teaser.substr($text2,0,strpos($text2,">")+1);
				$text2 = substr($text2,strpos($text2,">")+1);
			} else {
				$teaser = $teaser.substr($text2,0,strpos($text2,"</")+2);
				$text2 = substr($text2,strpos($text2,"</")+2);
				$teaser = $teaser.substr($text2,0,strpos($text2,">")+1);
				$text2 = substr($text2,strpos($text2,">")+1);

			}
		}
	}
		
	return $teaser;
}

function decodeFileContent($text,$headersOnly) {
	global $gTeaserLength,$gAllowedTags;

    	$arr = array();
    	$lines = preg_split("/\\r\\n|\\r|\\n/", $text);
    	$endOfHeaders = false;
    	$bodyText = "";
	$commentText = "";
	$comments = false;
    	foreach($lines as $singleLine) {
    		if ($singleLine == "") {
    			$endOfHeaders = true;
    		}

		if ($comments) {
			if ($endOfHeaders) {
	        		if ($singleLine == "<!--comment-->") {
					if (!isset($arr["comments"])) {
						$arr["comments"] = array();
					}	
					$comment["text"] = $commentText;
					array_push($arr["comments"],$comment);
				    	$comment = array();
					$commentText = "";
					$endOfHeaders=false;
				} else {
	        		       	$commentText = $commentText . $singleLine."\n";
				}
			} else {
        		    	if (strncmp_startswith($singleLine, "Title:")) {
					$comment["title"] = substr($singleLine, 6); 
		        	} else if (strncmp_startswith($singleLine, "Author:")) {
					$comment["author"] = substr($singleLine, 7);
				} else if (strncmp_startswith($singleLine, "ID:")) {
					$comment["taxonomy"] = substr($singleLine, 3);
				} else if (strncmp_startswith($singleLine, "When:")) {
					$comment["when"] = substr($singleLine, 5);
				}
			}
		} else {
			if ($endOfHeaders) {
        			if ($singleLine == "<!--comments-->") {
					$comments = true;
					$endOfHeaders=false;
				    	$comment = array();
					$commentText = "";
				} else if ($singleLine == "<!--teaser-->") {
					$arr["teaser"] = $bodyText;
					if ($headersOnly) return $arr;
					$bodyText = "";
				} else if ($singleLine == "<!--break-->") {
					$arr["teaser"] = ignoreFromTeaser($bodyText);
					if ($headersOnly) return $arr;
				} else {
        			       	$bodyText = $bodyText . $singleLine."\n";
            			}
		 	} else {
        		    	if (strncmp_startswith($singleLine, "Title:")) {
					$arr["title"] = substr($singleLine, 6); 
		        	} else if (strncmp_startswith($singleLine, "Author:")) {
					$arr["author"] = substr($singleLine, 7);
				} else if (strncmp_startswith($singleLine, "Taxonomy:")) {
					$arr["taxonomy"] = substr($singleLine, 9);
				} else if (strncmp_startswith($singleLine, "SkipMain:")) {
					$arr["skipmain"] = substr($singleLine, 9);
				} else if (strncmp_startswith($singleLine, "When:")) {
					$arr["when"] = substr($singleLine, 5);
				}
			}
		}
	}

	if (!isset($arr["teaser"]) ) {
		$arr["teaser"] = createTeaser($bodyText);
		if ($headersOnly) return $arr;
 	}
 	$arr["text"] = $bodyText;

	if ($comments) {
		if (!isset($arr["comments"])) {
			$arr["comments"] = array();
		}	
		$comment["text"] = $commentText;
		array_push($arr["comments"],$comment);
	}

	return $arr;
}

function AddSubpageToPageList($arr, $filename,$reads) {
	global $gTaxonomyArr;

	if (isset($arr["title"])) {
		echo("<p><a class=title href=\"?q=".getNodeName($filename)."\">" . $arr["title"] . "</a><br>");
	}
	if (isset($arr["author"])) {
		echo("<hr><div class=author>Submitted by " . $arr["author"]);
		if (isset($arr["when"]) && !(($timestamp = strtotime($arr["when"])) === false)) {
			echo(" on ".date("D d-M-Y",$timestamp));
		}
		echo("</div><br>");
	}

	echo "<table><tr><td>";

	if (isset($arr["teaser"])) {
		echo("<div class=bodytext>" . $arr["teaser"] . "</div><p>");
	}
	if (isset($arr["taxonomy"])) {
	  	foreach(explode(",", $arr["taxonomy"]) as $entry) {
		    	foreach($gTaxonomyArr as $index => $entry2) {
				if ($entry == $index) {
					echo("<a class=taxonomy href=\"?q=taxonomy/term/".urlencode($index)."\">".$index."</a><br>");
					break;
				}
			}
	  	}
	}
	echo("<p>» <a class=small href=\"?q=".getNodeName($filename)."\">Read more</a> | ");
	echo("<span class=reads>$reads reads</span><br>");

	echo "</td></tr></table>";
}

function PageList() {
	global $gFilesDir,$gTitlesPerPage,$gSiteTitle,$gShortName,$gTaxonomyArr,$gTimeArr,$gFinalFilesArr,$gPageNum,$gPagesNum,$gReadMoreURL,$gSort;

	$title = $gShortName;
	if ($gPageNum!=0) {
		if ($title) {
			$title = $title." (page ".($gPageNum+1).")";
		} else {
			$title = "page ".($gPageNum+1);
		}
	}
	if ($title) {
		CreatePageSkeletonTop($title." @ ".$gSiteTitle);
	} else {
		CreatePageSkeletonTop($gSiteTitle);
	}

	if ($gShortName!='') {
		echo(" | <a class=small href='?q=".$_GET['q']."'>$gShortName</a>");
	}
	if (count($gFinalFilesArr)!=1) {
		echo(" | <a class=small title='Click to change sorting' href='?");
		if (isset($_GET['q'])) echo("q=".$_GET['q']."&");
		if (isset($_GET['page'])) echo("page=".$_GET['page']."&");
		if ($gSort == "reads") {
			echo("'>Popular</a>");
		} else {
			echo("sort=reads'>Latest</a>");
		}
	}

	writeTopAfterMenu();

	$array=array();

	$query = "";
    	foreach($gFinalFilesArr as $file) {
		if ($query!="") $query=$query.",";
		$query=$query."'".$file[0]."'";
	}
	$db = new SQLite3("$gFilesDir/test.db");
	$db->busyTimeout(5000);
	// https://www.sqlite.org/wal.html
	$db->exec('PRAGMA journal_mode = wal;');
	$db->exec("CREATE TABLE IF NOT EXISTS counters(filename TEXT, visits INT)");
	$statement = $db->prepare("SELECT filename,visits FROM counters WHERE filename IN ($query)");
	$result = $statement->execute();
	while ($row = $result->fetchArray()) {
		$array2 = array();
		array_push($array2,$row['filename']);
		array_push($array2,$row['visits']);
		array_push($array,$array2);
	}	
	$db->close();

    	foreach($gFinalFilesArr as $file) {
		$i = 0;
	    	foreach($array as $reads) {
			if ($reads[0] == $file[0]) {
				$i=$reads[1];
				break;
			}
		}
		AddSubpageToPageList($file[1],$file[0],$i);
	}

	if ($gPagesNum!=1) {
		echo "<p>";
		$sortURL = "";
		if ($gSort=="reads") $sortURL="&sort=reads";
		for ($i=0;$i<$gPagesNum;$i++) {
			if ($i==$gPageNum) {
				echo "<span class=small>".($i+1)."</span>&nbsp;&nbsp; ";
			} else {
				echo "<a class=small href=\"?q=$gReadMoreURL&page=".($i+1)."$sortURL\">".($i+1)."</a>&nbsp;&nbsp; ";
			}
		}
	}

	CreatePageSkeletonBottom();
   	exit(0);
}

function SinglePage() {
	global $gFilesDir,$gSiteTitle,$gTaxonomyArr,$gAllowedTags,$gSecondsForVisits,$gFinalFilesArr;

	$increase=true;
	if ($_SERVER['REMOTE_ADDR']!="127.0.0.1") {
		$db = new SQLite3("$gFilesDir/test.db");
		$db->busyTimeout(5000);
		// https://www.sqlite.org/wal.html
		$db->exec('PRAGMA journal_mode = wal;');
		$db->exec("CREATE TABLE IF NOT EXISTS visits(ip TEXT, filename TEXT, lastvisit INT)");
		$db->exec("CREATE TABLE IF NOT EXISTS counters(filename TEXT, visits INT)");
		$db->exec("DELETE FROM visits WHERE lastvisit<".(time()-$gSecondsForVisits));

		$db->exec('BEGIN');
		$statement = $db->prepare("SELECT ip,lastvisit FROM visits WHERE filename='".$gFinalFilesArr[0][0]."' AND ip='".$_SERVER['REMOTE_ADDR']."'");
		$result = $statement->execute();
		if ($row = $result->fetchArray()) {
			$db->exec("UPDATE visits SET lastvisit='".time()."' WHERE filename='".$gFinalFilesArr[0][0]."' AND ip='".$_SERVER['REMOTE_ADDR']."'");
			if ($row['lastvisit']>=time()-$gSecondsForVisits) $increase=false;
		} else {
			$db->exec("INSERT INTO visits(ip,filename,lastvisit) VALUES('".$_SERVER['REMOTE_ADDR']."','".$gFinalFilesArr[0][0]."',".time().")");
		}
		if ($increase) {
			$statement = $db->prepare("SELECT visits FROM counters WHERE filename='".$gFinalFilesArr[0][0]."'");
			$result = $statement->execute();
			if ($row = $result->fetchArray()) {
				$db->exec("UPDATE counters SET visits='".($row['visits']+1)."' WHERE filename='".$gFinalFilesArr[0][0]."'");
			} else {
				$db->exec("INSERT INTO counters(filename,visits) VALUES('".$gFinalFilesArr[0][0]."',1)");
			}
		}
        	$db->exec('COMMIT');
		$db->close();
	}

	if (isset($gFinalFilesArr[0][1]["title"])) {
		CreatePageSkeletonTop($gFinalFilesArr[0][1]["title"]." @ ".$gSiteTitle);
	} else {
		CreatePageSkeletonTop($gSiteTitle);
	}

	writeTopAfterMenu();

	if (isset($gFinalFilesArr[0][1]["title"])) {
		echo("<span class=title_no_click>" . $gFinalFilesArr[0][1]["title"] . "</span><hr>");
	}

	if (isset($gFinalFilesArr[0][1]["author"])) {
		echo("<div class=author>Submitted by " . $gFinalFilesArr[0][1]["author"]);
		if (isset($gFinalFilesArr[0][1]["when"]) && !(($timestamp = strtotime($gFinalFilesArr[0][1]["when"])) === false)) {
			echo(" on ".date("D d-M-Y",$timestamp));
		}
		echo("</div><br>");
	}
	if (isset($gFinalFilesArr[0][1]["taxonomy"])) {
	  	foreach(explode(",", $gFinalFilesArr[0][1]["taxonomy"]) as $entry) {
		    	foreach($gTaxonomyArr as $index => $entry2) {
				if ($entry == $index) {
					echo("<a class=taxonomy href=\"?q=taxonomy/term/".urlencode($index)."\">" . $index . "</a><br>");
					break;
				}
			}
	  	}
	}
	if (isset($gFinalFilesArr[0][1]["text"])) {
		$special = "";
		if ($gFinalFilesArr[0][0] == "130") {
			$special = "<script>";
		}
		echo("<p><div class=bodytext>" . strip_tags($gFinalFilesArr[0][1]["text"], $gAllowedTags.$special) . "</div><p id=complete>");
	}
	if (isset($gFinalFilesArr[0][1]["comments"])) {
	  	foreach($gFinalFilesArr[0][1]["comments"] as $comment) {
			echo "<table border=1 width=100%><tr><td>";
			if (isset($comment["title"])) {
				echo("<span class=title>" . $comment["title"] . "</span>");
			}
			echo "<p>";
			if (isset($comment["author"])) {
				echo("<div class=author>Submitted by " . $comment["author"]);
				if (isset($comment["when"]) && !(($timestamp = strtotime($comment["when"])) === false)) {
					echo(" on ".date("D d-M-Y",$timestamp));
				}
				echo("</div><br>");
			}
			echo("<p><div class=bodytext>" . strip_tags($comment["text"], $gAllowedTags) . "</div><p id=complete>");
			echo "</td></tr></table>";
		}	
	}

	CreatePageSkeletonBottom();
   	exit(0);
}

$gSort='';
$gParamNum = 0;
$gCallBackFunc = NULL;

if (isset($_GET["dark"])) {
	if ($_GET["dark"]=="yes" || $_GET["dark"]=="no") {
		$gParamNum++;
	}
}

$gMobile = false;
if (isset($_GET["mobile"])) {
	if ($_GET["mobile"]=="yes") {
		setcookie('mobile', '1', 2147483647);
		$gMobile = true;
		$gParamNum++;
	} else if ($_GET["mobile"]=="no") {
		setcookie('mobile', '0', 2147483647);
		$gParamNum++;
	}
} else if(!isset($_COOKIE['mobile'])) {
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		if (strstr($_SERVER['HTTP_USER_AGENT'],"iPad") || strstr($_SERVER['HTTP_USER_AGENT'],"iPhone") ||
        	    strstr($_SERVER['HTTP_USER_AGENT'],"Android")) {
			setcookie('mobile', '1', 2147483647);
			$gMobile = true;
		} else {
			setcookie('mobile', '0', 2147483647);
		}
	} else {
		setcookie('mobile', '0', 2147483647);
	}
} else if ($_COOKIE['mobile']=='1') {
	$gMobile = true;
}

if (isset($_GET["q"])) {
	$gParamNum++;
	$gShortName = "";
	if (preg_match("/^node\/([0-9]+)$/", $_GET["q"], $fileNameArray)) {
		$gShortName = $fileNameArray[1];
	} else {
		foreach($gRedirect as $index => $entry) {
			if ($_GET["q"] == $index) {
				$gShortName = $entry;
				break;
			}
		}
	}
	if ($gShortName) {
		if (count($_GET)!=$gParamNum) {
			header('Location: '.$_SERVER['PHP_SELF']);
			exit(0);
		}
		$gCallBackFunc = function($fileName,$taxonomy,$when,$skipmain) { 
			global $gShortName;
			return $fileName == $gShortName;
		}; 
		ProcessAllAndGetLists($gCallBackFunc,1,1);
		if (isset($gFinalFilesArr[0][0])) {
			SinglePage();
		} else {
			// intentionally display main page instead
			header('Location: '.$_SERVER['PHP_SELF']);
			exit(0);
		}
	} else if (preg_match("/^taxonomy\/term\/(.*)$/", $_GET["q"], $taxonomyName)) {
		$gShortName = $taxonomyName[1];
		$gReadMoreURL = "taxonomy/term/".urlencode($gShortName);
		$gCallBackFunc = function($fileName,$taxonomy,$when,$skipmain) { 
			global $gShortName;
		 	foreach($taxonomy as $entry) {
				if ($entry == $gShortName || $entry == urldecode(html_entity_decode($gShortName))) return true;
			}
			return false;
		};
	} else if (preg_match("/^time\/([0-9\-]+)$/", $_GET["q"], $timeName)) {
		$gShortName = $timeName[1];
		$gReadMoreURL = "time/$gShortName";
		$gCallBackFunc = function($fileName,$taxonomy,$when,$skipmain) { 
			global $gShortName;
			return $gShortName == $when;
		};
	} else if ($_GET["q"] != "node") {
		header('Location: '.$_SERVER['PHP_SELF']);
		exit(0);
	}
}
if (is_null($gCallBackFunc)) {
	$gShortName = "";
	$gReadMoreURL = "node";
	$gCallBackFunc = function($fileName,$taxonomy,$when,$skipMain) { 
		return !$skipMain;
	};
}
if (isset($_GET["page"]) && intval($_GET["page"],10)!=0) {
	$gParamNum++;
	$gPageNum = intval($_GET["page"],10)-1;
} else {
	$gPageNum = 0;
}
$gSort='time';
if (isset($_GET["sort"])) {
	if ($_GET["sort"]=="reads") {
		$gParamNum++;
		$gSort="reads";
	}
}
if (count($_GET)!=$gParamNum) {
	header('Location: '.$_SERVER['PHP_SELF']);
	exit(0);
}
ProcessAllAndGetLists($gCallBackFunc,$gPageNum*$gTitlesPerPage+1,($gPageNum+1)*$gTitlesPerPage);
// serve main page when $_GET["q"] was wrong
if (!isset($gFinalFilesArr[0][0])) {
	header('Location: '.$_SERVER['PHP_SELF']);
	exit(0);
}
PageList();

?>
