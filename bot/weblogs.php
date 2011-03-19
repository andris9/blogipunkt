<?php

require_once("../config.php");
require_once("../includes/tools.php");

header("content-type: text/plain");

$updated = array();
$blogs = array();

$sql = "SELECT id, url FROM blogs WHERE queued='N'";
$result = mysql_query($sql);
while($row = mysql_fetch_array($result)){
    $blogs[$row["url"]] = $row["id"];
}

check_weblogs_updates($blogs, $updated, "http://rpc.weblogs.com/shortChanges.xml");
check_weblogs_updates($blogs, $updated, "http://blogsearch.google.com/changes.xml");

$updated = array_unique($updated);

for($i=0; $i<count($updated); $i++){
	$sql = "UPDATE blogs SET queued='Y' WHERE id='%s'";
    mysql_query(sprintf($sql, mysql_real_escape_string($updated[$i])));
}

if(count($updated)){
	echo "Queued ".count($updated)." blogs";
}else{
	echo "Nothig to queue :/";
}

function check_weblogs_updates(&$blogs, &$updated, $url){
    $handle = fopen($url, "r");
    if ($handle) {
        while (!feof($handle)) {
            $buffer = fgets($handle);
            $regexp = "url=(\"??)([^\" >]*?)\\1[^>]*";
            preg_match("/$regexp/siU", $buffer, $match);
            if(strlen($match[2])){
                if($blogs[urltrim($match[2])]){
                    $updated[] = $blogs[urltrim($match[2])];
                }
            }
        }
        fclose($handle);
    }
}
