<?php

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
     	header('HTTP/1.1 404 Not Found');
	echo("File not found. ");
	exit(0);
}

$gSiteTitle = "Marcin's page ON-LINE";
$gTeaserLength = 200;
$gTitlesPerPage = 10;
$gFilesDir = "arts";
$gAllowedTags = "<a><p>";
$gSecondsForVisits = 60*60*2; // 60*60*2 = 2h

$gRedirect = array();
$gRedirect["test"]="1";

function ignoreFromTeaser($text) {
	$text2 = $text;

	while (true) {
		if (!(strpos($text2,"align=\"center\" style=\"color: rgb(0, 64, 128);\">")===false)) {
			$text2 = substr($text2,strpos($text2,"style=\"color: rgb(0, 64, 128);\">")+32);
		} else {
			break;
		}
	}

	return $text2;
}

function writeUserMenu() {
?>
<span class=title_no_click>English</span>
<hr>
<a href="?q=taxonomy/term/Windows">Windows</a><br>
<a href="?q=test">Test page</a><br>
<a href="?q=2">Test page 2</a><br>

<p>
<a class=ext href="https://github.com/marcinwiacek">GitHub</a><br>
<a class=ext href="http://www.linkedin.com/in/marcinwiacek?_l=en">LinkedIn</a><br>
<?php
}

?>
