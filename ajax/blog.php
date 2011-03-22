<?php

/*
 * blog.php
 * http://example.com/ajax/blog/(action)
 *
 * Vastab Ajax päringutele ja võimaldab kontrollida URL'i kehtivust ning
 * lisada blogi andmebaasi
 */

require_once("../config.php");
require_once("../includes/tools.php");
require_once("../includes/blog.php");
require_once("../includes/post.php");

// kontrolli päringu tüüpi
switch($_GET["action"]){
    case "check":
        AjaxBlog::check($_REQUEST);
        break;
    case "add":
        AjaxBlog::add($_REQUEST);
        break;
    case "queue":
        AjaxBlog::queue($_REQUEST);
        break;
}

Header("Content-type: application/json; Charset=utf-8");
echo json_encode(AjaxBlog::$response);


/**
 * AjaxBlog
 *
 * Võtab vastu Ajax päringuid blogi staatuse kontrolliks ja lisamiseks
 */
class AjaxBlog{

    /**
     * AjaxBlog.$response -> Object
     *
     * Sisaldab päringu lõpus tagastatavaid väärtusi, mis muudetakse JSON formaati
     **/
    public static $response = array(
        "status"=>"error",
        "message"=>"Vigane päring"
    );

    /**
     * AjaxBlog.check($request) -> undefined
     * - $request (Object): Päringuobjekt GET ja POST muutujatega
     *
     * Kontrollib kas kasutatud URL ($request["url"]) on korrektne ja kas sellist
     * blogi juba ei ekistseeri. Õnnestumise korral saab brauser tagasi aadresside
     * info
     **/
    public static function check($request){
        $url = urltrim(resolve_url($request["url"]));
        if(!$url){
        	self::$response["status"] = "error";
            self::$response["message"] = "Vigane blogi URL, kontrolli aadressi kehtivust";
            return;
        }

        // vaikimisi pealkirjaks on url ilma protokollita
        list($scheme, $title) = explode("//",$url,2);
        $description = "";
        $categories = array();

        // kontrolli kas on juba olemas
        if($blog = Blog::getByURL($url)){
        	$title = $blog["title"];
            $description = $blog["meta"]["description"];
            $categories = $blog["meta"]["categories"]?$blog["meta"]["categories"]:array();
            $feed_url = $blog["feed"];
        }else{
            $html = load_from_url($url);
            $feed_url = detectFeed($html, $url);
        }

        // lae kirjeldus RSS failist, kui on
        if(!$blog && $feed_url){
        	include_once("../includes/vendor/simplepie/simplepie.inc");
            $feed = new SimplePie();

            $feed->set_feed_url($feed_url);
            $feed->set_useragent(BOT_USERAGENT);

            $feed->force_feed(true);
            $feed->enable_cache(false);
            $feed->set_image_handler(false);

            $feed->init();

            if(!$feed->error()){
                $title = text_decode($feed->get_title());
                $description = text_decode($feed->get_description());
            }
            $feed->__destruct(); // Do what PHP should be doing on it's own.
            unset($feed);
        }

        self::$response["status"] = "OK";
        unset(self::$response["message"]);
        self::$response["data"] = array(
            "url" => $url,
            "feed" => $feed_url,
            "title" => htmlspecialchars($title),
            "description" => htmlspecialchars($description),
            "categories" => $categories,
            "exists"=> !!$blog
        );
    }

    /**
     * AjaxBlog.add($request) -> undefined
     * - $request (Object): Päringuobjekt GET ja POST muutujatega
     *
     * Sisestab blogi andmed baasi.
     **/
    public static function add($request){
        $url = $request["url"];
        $feed = $request["feed"];
        $title = trim($request["title"]);
        $description = false; //trim($request["description"]);
        $lang = trim($request["lang"]);

        // kontrolli kas kehtib
        $url = urltrim(resolve_url($url));
        if(!$url){
        	self::$response["message"] = "Vigane blogi URL, kontrolli aadressi kehtivust";
            return;
        }

        $categories = $request["categories"]?$request["categories"]:false;

        if($blog = Blog::getByURL($url)){
            self::$response["status"]="exists";
            self::$response["message"]="Juba olemas";

            $blog["url"] = $url;
            $blog["feed"] = $feed;
            $blog["title"] = $title;
            $blog["meta"]["categories"] = $categories;

            self::$response["data"] = array(
                "id" => $blog["id"],
                "url" => $url,
                "feed" => $feed,
                "title" => htmlspecialchars($title),
                "description" => htmlspecialchars($blog["meta"]["description"]),
                "categories" => $categories,
                "exists"=> true
            );
            // TODO: save changes

            Blog::queueSave($blog);

            return;
        }

        if($feed){
        	$feed = urltrim(resolve_url($feed));
        }

        // kui RSS url'i ei ole määratud, siis proovi ikkagi tuvastada
        if(!$feed){
        	$html = load_from_url($url);
            $feed = detectFeed($html, $url);
        }

        if($blog = Blog::add($url, $feed, $lang, $title, $description, $categories)){
            unset(self::$response["message"]);
            self::$response["status"] = "OK";
            self::$response["data"] = array(
                "id" => $blog["id"],
                "url" => $url,
                "feed" => $feed,
                "title" => htmlspecialchars($title),
                "description" => htmlspecialchars($blog["meta"]["description"]),
                "categories" => $categories,
                "exists"=> true
            );
        }else{
        	self::$response["message"]="Salvestamine ebaõnnestus";
        }

    }

    /**
     * AjaxBlog.queue($request) -> undefined
     * - $request (Object): Päringuobjekt GET ja POST muutujatega
     *
     * Märgib valitud blog ($_REQUEST["id"]) ülevaatamise järjekorda
     **/
    public static function queue($request){
        if($blog = Blog::getById($request["id"])){
            $blog["queued"] = true;
            Blog::save($blog);

            self::$response["status"] = "OK";
            unset(self::$response["message"]);
        }else{
        	self::$response["message"] = "Blogi ei leitud";
        }
    }
}

