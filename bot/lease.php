<?php
set_time_limit(220);

/*
 * lease.php
 * http://example.com/pubsub/lease
 *
 * Laeb andmebaasist kuni 100 blogi, mille PubSubHubbub "liisinguaeg" hakkab täis
 * saama ning uuendab seda
 *
 * CRON kord tunnis
 */

include("../config.php");
include("../includes/blog.php");
include("../includes/tools.php");

Header("Content-type: text/plain");

$count = 0;
$sql = "SELECT * FROM blogs WHERE disabled='N' AND hub<>'' AND lease<'%s' ORDER BY RAND() LIMIT 100";
$result = mysql_query(sprintf($sql, time()+3600));
while($row = mysql_fetch_array($result)){
	Blog::subscribe($row["hub"], $row["feed"]);
    echo "subscribed for {$row["feed"]}\n";
    $count++;
}

if(!$count){
	echo "Nothing to renew :/";
}