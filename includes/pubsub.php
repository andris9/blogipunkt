<?php

/**
 * PubSub
 * 
 * Klass tegeleb PubSubHubbub GET ja POST päringute majandamisega
 **/
class PubSub{
	
    /**
     * handlePOST($postBody [,$blog]) -> Boolean
     * - $postBody (String): RSS fail, mille saatis PubSubHubbub server
     * - $blog (Object): byref muutuja, millele omistatakse blogi objekt
     * 
     * Funktsioon võtab vastu tundmatu RSS faili ning üritab sellest tuvastada,
     * mis blogiga on tegu - juhul kui tuvastatakse, siis lisatakse uued
     * postitused baasi. Kui ei tuvastata blogi, siis tagastab false, muidu true
     **/
    public static function handlePOST($postBody, &$blog=false){
    	include_once(dirname(__FILE__)."/vendor/simplepie/simplepie.inc");
        $feed = new SimplePie();
        $feed->set_raw_data($postBody);
        $feed->set_useragent(BOT_USERAGENT);
    
        $feed->enable_cache(false);
        $feed->set_image_handler(false);
    
        $feed->init();
    
        if($feed->error()){
            return false;
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
        
        return true;
    }
    
    /**
     * handleGET($request) -> undefined
     * - $request (Array): $_GET massiiv, ei testimise jaoks ei kasuta seda otse
     * 
     * Funktsioon võtab vastu $_GET massiivi ning kontrollib kas tegu on kehtiva
     * päringuga ning kui on, siis märgib blogi lease aja. PubSubHubbub serverile
     * kinnituseks tuleb tagastada hub_challenge väärtus.
     **/
    public static function handleGET($request){
        if($request['hub_verify_token'] == PUBSUB_VERIFY_TOKEN){
            $lease_time = time() + $request['hub_lease_seconds'];
            $mode = $request['hub_mode'];
            $topic = $request['hub_topic'];

            if($mode=="subscribe"){
                $sql = "UPDATE blogs SET `lease`='%s' WHERE feed='%s'";
                mysql_query(sprintf($sql, mysql_real_escape_string($lease_time),
                                        mysql_real_escape_string($topic)));
            }
        }

        // "tehingu" aktsepteerimiseks tuleb väljastada GET parameetri hub_challenge väärtus 
        echo $request['hub_challenge'];
    }
    
}