<?php

/*
 * receive.php
 * http://example.com/pubsub
 * 
 * Kutsutakse ellu PubSubHubbub serveri poolt (vt. config.php PUBSUB_CALLBACK_URL)
 * kui meetodiks on GET, siis tehakse subscribe/unsubscribe kinnitust, kui aga POST
 * siis saadetakse uuenenud postitusi. Uuenduste RSS on POST body väärtuseks.
 * 
 * CRON pole vaja, kutsutakse välja PubSubHubbub serveri poolt vajadusel
 */

require_once("../config.php");
require_once("../includes/tools.php");
require_once("../includes/blog.php");
require_once("../includes/post.php");

header("content-type: text/plain");

if($_SERVER["REQUEST_METHOD"]=="POST"){ // Uute postituste lisamine
    
    $data = file_get_contents("php://input");
    
    include_once(dirname(__FILE__)."/../includes/vendor/simplepie/simplepie.php");
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
    
    // tuvasta blogi
    if($blog = Blog::getByUrl(resolve_url($feed->get_permalink()))){
    	// uuenda postitusi
        Post::handlePosts($feed, $blog);
        // uuenda blogi andmeid
        Blog::update_from_feed($feed, $blog);
    }
    
    $feed->__destruct();
    unset($feed);
    
    echo "ready.";
        
}elseif($_SERVER["REQUEST_METHOD"]=="GET"){ // subscribe/unsubscribe kinnitused
    
	
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

    // "tehingu" aktsepteerimiseks tuleb väljastada GET parameetri hub_challenge väärtus 
    echo $_GET['hub_challenge'];
}