<?php

include("../config.php");
include("../includes/pubsub.php");

echo test_get()?"Pubsub GET passed\n":"Pubsub GET failed\n";

function test_get(){
    $request = array(
        "hub_verify_token" => PUBSUB_VERIFY_TOKEN,
        "hub_challenge" => "saladus",
        "hub_mode" => "test",
        "hub_topic" => "topic"
    );

    ob_start();
    PubSub::handleGET($request);
    $output = ob_get_contents();
    ob_end_clean();

    return $output=="topicsaladus";
}
