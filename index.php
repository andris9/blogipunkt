<?php

require_once("config.php");
require_once("includes/tools.php");
require_once("includes/blog.php");
require_once("includes/post.php");

$title = "";
$page = false;
$js = array();

if(!isset($_GET["page"])){
    // esileht
    $title = "Esileht";
    $page = "front";
}else{
    // ruuter
    switch($_GET["page"]){
        case "addBlog":
            $title = "Lisa uus blogi";
            $page = "add_blog";
            $js[] = SITE_URL ."static/addblog.js";
            break;
    }
}

if(!$page){
    // näita 404 veateadet
	Header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    echo template_render("views/main.php", array(
        "title" => "Ei leitud",
        "body"=> template_render("views/error.php",array(
            "message"=>"404 Otsitud lehte ei leitud!"
        ))
    ));
}else{
    // näita soovitud lehte
    echo template_render("views/main.php", array(
        "title" => "$title",
        "js" => $js,
        "body"=> template_render("views/".$page.".php")
    ));
}