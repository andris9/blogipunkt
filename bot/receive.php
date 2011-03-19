<?php

require_once("../config.php");
require_once("../includes/tools.php");
require_once("../includes/blog.php");
require_once("../includes/post.php");
require_once("../includes/simplepie.inc");

header("content-type: text/plain");

if($_SERVER["REQUEST_METHOD"]=="POST"){
    
    $data = file_get_contents("php://input");
    file_put_contents("post.txt",$data);
    
    $feed = new SimplePie();
    $feed->set_raw_data($data);
    $feed->set_useragent(BOT_USERAGENT);
    
    $feed->enable_cache(false);
    $feed->set_image_handler(false);
    
    $feed->init();
    
    if($feed->error()){
        file_put_contents("log.txt","error ");
    	echo "Invalid feed";
        exit;
    }
    
    $blog = Blog::getByUrl(resolve_url($feed->get_permalink()));

    if(!$blog){
        echo "ei saanud aru :/";
        file_put_contents("log.txt","ei saanud aru :/");
    }else{
        foreach ($feed->get_items() as $item){
            Post::save($item, $blog);
        }
        echo "alles OK!";
        //file_put_contents("log.txt","Alles OK :/");
    }

    
}elseif($_SERVER["REQUEST_METHOD"]=="GET"){
    
	
    if($_GET['hub_verify_token']==$GLOBALS['_verify_token']){
        $lease_time = time()+$_GET['hub_lease_seconds'];
        $mode = $_GET['hub_mode'];
        $topic = $_GET['hub_topic'];
    }

    echo $_GET['hub_challenge'];

    file_put_contents("get.txt",print_r($_REQUEST,1));
    
}