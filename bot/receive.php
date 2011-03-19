<?php

require_once("../config.php");
require_once("../includes/tools.php");
require_once("../includes/blog.php");
require_once("../includes/post.php");
require_once("../includes/simplepie.inc");

header("content-type: text/plain");

if($_SERVER["REQUEST_METHOD"]=="POST"){
    
    $data = file_get_contents("php://input");
    
    $feed = new SimplePie();
    $feed->set_raw_data($data);
    $feed->set_useragent(BOT_USERAGENT);
    
    $feed->enable_cache(false);
    $feed->set_image_handler(false);
    
    $feed->init();
    
    if($feed->error()){
    	echo "Invalid feed";
        exit;
    }
    
    Post::handlePosts($feed);
    
}elseif($_SERVER["REQUEST_METHOD"]=="GET"){
    
	
    if($_GET['hub_verify_token'] == PUBSUB_VERIFY_TOKEN){
        $lease_time = time() + $_GET['hub_lease_seconds'];
        $mode = $_GET['hub_mode'];
        $topic = $_GET['hub_topic'];

        if($mode=="subscribe"){
            $sql = "UPDATE blogs SET `lease`='%s' WHERE feed='%s'";
            mysql_query(sprintf($sql, mysql_real_escape_string($lease_time),
                                        mysql_real_escape_string($topic)));
        }
    }

    echo $_GET['hub_challenge'];
}