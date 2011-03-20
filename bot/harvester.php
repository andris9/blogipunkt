<?php

/*
 * harvester.php
 * http://example.com/robot/harvester
 * 
 * Laeb andmebaasist kuni 100 blogi, millel on märgitud 'queued=Y' ning 
 * uuendab nende sisu.
 * 
 * CRON võimalikult tihti
 */

require_once("../config.php");
require_once("../includes/tools.php");
require_once("../includes/blog.php");
require_once("../includes/post.php");

header("content-type: text/plain");

$sql = "SELECT * FROM blogs WHERE queued='Y' AND feed<>'' ORDER BY RAND() LIMIT 100";
$result = mysql_query($sql);
while($row = mysql_fetch_array($result)){
    echo "updating {$row["title"]}\n";
    flush();
    if($blog = Blog::deserialize($row)){
        Blog::handleFeed($blog);
    };
}

echo "okidoki";