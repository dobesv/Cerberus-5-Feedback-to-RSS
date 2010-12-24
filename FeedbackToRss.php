<?php
//
// By Dobes Vandermeer, 2010
//
// Usage:
//   http://your-server/path/to/FeedbackToRss.php?base_url=...&access_key=...&secret_key=...
//
// Copy to your PHP server and point your RSS reader at it.  An SSL server is recommended.
//
require_once("Cerb5Api.php");
 
$base_url = $_REQUEST["base_url"]; // e.g. https://whatever.cerb5.com/admin/rest/
$access_key = $_REQUEST["access_key"]; // Worker"s Email Address
$secret_key = $_REQUEST["secret_key"];  // Worker"s Password
 
$cerb5 = new Cerb5_WebAPI($access_key, $secret_key);

$postfields = array(
	array("sortBy","id"),
	array("sortAsc","0"),
	array("page","1"),
);
$out = $cerb5->post($base_url . "feedback/search.json", $postfields);

header("Content-Type: text/xml; charset=utf-8");

echo "<?xml version='1.0' encoding='UTF-8' ?>";
echo "<rss version='2.0'>";
echo "<channel>";
echo "<title>Feedback Stream</title>";
 
if("text/javascript; charset=utf-8" == $cerb5->getContentType()) {
	$obj = json_decode($out);
	if($obj->__status == "success") {
		foreach($obj->results as $result) {
                    echo "\n<item>";
                    echo "\n<!-- ".json_encode($result)."-->";
		    echo "\n<title>".ucwords($result->quote_mood)." feedback from "
                               .($result->author_address?$result->author_address:"someone")
                               ."</title>";
                    echo "\n<guid>".$base_url."feedback/".$result->id."</guid>";
                    if($result->url) {
                        echo "\n<link>".$result->url."</link>";
                    } else {
                        echo "\n<link>".str_replace("/rest/", "/activity/feedback</link>", $base_url);
                    }
                    echo "\n<description>".$result->quote_text."</description>";
                    echo "\n<pubDate>".date(DATE_RSS, $result->created)."</pubDate>";
		    echo "\n</item>";
                }
        }
}

echo "</channel>";
echo "</rss>\n";
?>
