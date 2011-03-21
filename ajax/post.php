<?php

/*
 * post.php
 * http://example.com/ajax/post/(action)
 *
 * Vastab Ajax päringutele mis on seotud postitustega
 */

require_once("../config.php");
require_once("../includes/tools.php");
require_once("../includes/blog.php");
require_once("../includes/post.php");

// kontrolli päringu tüüpi
switch($_GET["action"]){
    case "get":
        AjaxPost::get($_REQUEST);
        break;
    case "upvote":
        ignore_user_abort(true); // oluline on lõpuni teha
        AjaxPost::upvote($_REQUEST);
        break;
}

Header("Content-type: application/json; Charset=utf-8");
echo json_encode(AjaxPost::$response);


/**
 * AjaxPost
 *
 * Võtab vastu Ajax päringuid postitustega tegelemiseks
 */
class AjaxPost{

    /**
     * AjaxPost.$response -> Object
     *
     * Sisaldab päringu lõpus tagastatavaid väärtusi, mis muudetakse JSON formaati
     **/
    public static $response = array(
        "status"=>"error",
        "message"=>"Invalid request"
    );

    /**
     * AjaxPost.upvote($request) -> undefined
     * - $request (Object): Päringuobjekt GET ja POST muutujatega
     *
     * Lisab postitusele hääle ja arvutab ümber selle punktisumma
     **/
    public static function upvote($request){
        // TODO: kontrolli kas pole juba hääletanud
        $blog = false;
        if($post = Post::getPost($request["id"], $blog)){
            $post["votes"]++;
            Post::calculate_points($post);
            Post::serialize($post, array("votes","points"));

            self::$response["status"] = "OK";
            unset(self::$response["message"]);
        }
    }

    /**
     * AjaxPost.get($request) -> undefined
     * - $request (Object): Päringuobjekt GET ja POST muutujatega
     *
     * Tagastab postituse ja blogi andmed
     **/
    public static function get($request){
        $blog = false;
        if($post = Post::getPost($request["id"], $blog)){
        	self::$response["status"] = "OK";
            unset(self::$response["message"]);

            // peida ebaoluline osa
            unset($post["points"]);
            unset($post["votes"]);

            self::$response["data"] = array(
                "post"=>$post,
                "blog"=>$blog
            );
        }
    }

}