<?php
set_time_limit(220);

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
require_once("../includes/pubsub.php");

header("content-type: text/plain");

if($_SERVER["REQUEST_METHOD"]=="POST"){ // Uute postituste lisamine

    $data = file_get_contents("php://input");
    file_put_contents("log.txt", $data);

    if(PubSub::handlePOST($data)){
        echo "feed checked";
    }else{
        echo "feed error";
    }

}elseif($_SERVER["REQUEST_METHOD"]=="GET"){ // subscribe/unsubscribe kinnitused

    PubSub::handleGET($_GET);

}