<?php

/**
 * Blog
 * 
 * Klass, mis tegeleb blogide majandamisega (lisamine/laadimine jne).
 **/
class Blog{
    
    
    /**
     * Blog.blank() -> Object
     * 
     * Loob tühja blogiobjekti, vaikimisi väärtustega
     **/
    public static function blank(){
        $blog = self::deserialize();
        $blog["meta"]["inserted"] = date("Y-m-d H:i:s");
        return $blog;
    }
    
    /**
     * Blog.getById($id) -> Object
     * - $id (Number): Blogi ID
     * 
     * Laeb andmebaasist blogi andmed struktureeritud ID väärtuse alusel
     * blogiobjekti kujul. Kui ei leitud, siis tagastab false
     **/
    public static function getById($id){
        return self::getByParam("id", $id);
    }
    
    /**
     * Blog.getByURL($url) -> Object
     * - $url (String): Blogi aadress
     * 
     * Laeb andmebaasist blogi andmed struktureeritud blogi aadressi
     * alusel ja blogiobjekti kujul. Kui ei leitud, siis tagastab false
     **/
    public static function getByURL($url){
        return self::getByParam("url", urltrim($url));
    }
    
    /**
     * Blog.add($url) -> Object
     * - $url (String): Blogi veebiaadress
     * 
     * Funktsioon üritab blogi aadressi alusel tuvastada blogiga seotud
     * detailid nagu RSS jne ning kui suudab kõik leida, siis lisab andmed
     * uue blogina baasi. Juhul kui ei õnnestu, tuleks kasutada Blog::save()
     * meetodit. Tagastusväärtuseks on loodud/leitud blogi objekt või false
     **/
    public static function add($url){
        
        // Check if already exists
        $blog = self::getByURL($url);
        if($blog){
            return $blog;
        }
        
        $blog = self::blank();
        $blog["url"] = urltrim(resolve_url($url));
        $html = load_from_url($blog["url"]);
        $blog["feed"] = detectFeed($html, $blog["url"]);
        
        if(self::save($blog)){
            self::handleFeed($blog);
            return $blog;
        }else{
            return false;
        }
        
    }
    
    /**
     * Blog.save($blog) -> Boolean
     * - $blog (Object): blogi objekt, mida salvestada
     * 
     * Salvestab blogi andmed baasi. Juhul kui omadus 'id' on seatud, kirjutab
     * selle üle, kui mitte siis lisab uuena. NB! uuena lisamisel, kui sama url on
     * juba baasis olemas, tekib exception! Õnnestumise korral tagastab true, muidu false
     **/
    public static function save(&$blog){

        if(!$blog["url"]){
            throw new Exception('No blog url set');
        }
        
        include_once(dirname(__FILE__)."/event.php");
        Event::fire("blog:presave", array(
            "blog" => &$blog
        ));
        
        if(!$blog["id"]){
            $sql = "INSERT INTO blogs (url, feed, hub, title, meta, updated, checked, queued) VALUES('%s','%s','%s','%s','%s','%s',NOW(),'%s')";    
        }else{
            $sql = "UPDATE blogs SET url='%s', feed='%s', hub='%s', title='%s', meta='%s', updated=NOW(), checked='%s', queued='%s' WHERE id='{$blog["id"]}'";    
        }
        
        mysql_query(sprintf($sql, 
                mysql_real_escape_string($blog["url"]),
                mysql_real_escape_string($blog["feed"]),
                mysql_real_escape_string($blog["hub"]),
                mysql_real_escape_string($blog["title"]),
                mysql_real_escape_string($blog["meta"]?serialize($blog["meta"]):""),
                mysql_real_escape_string($blog["checked"]),
                mysql_real_escape_string($blog["queued"]?"Y":"N")
            ));
        
        // NB! kui uue kirje URL on sama mis olemasoleval, siis tekib error
        // võibolla peaks üle kirjutama hoopis
        
        if(mysql_error()){
            throw new Exception('Error while saving');
            return false;
        }else{
            if(!$blog["id"]){
                $blog["id"] = mysql_insert_id();
            }
            return true;
        }
    }
    
    /**
     * Blog.update_from_feed($feed, $blog) -> undefined
     * - $feed (Object): SimplePie RSS objekt
     * - $blog (Object): blogiobjekt
     * 
     * Kontrollib kas blogi andmetes on toimunud olulisi muutusi,
     * juhul kui muutub PubSubHub väärtus, tehakse ka vastav
     * unsubscribe/subscribe
     **/
    public static function update_from_feed(&$feed, &$blog){
        $data = self::get_data_from_feed($feed);
        $changed = false;
        $oldvalues = array();
        
        if($data["hub"] != $blog["hub"]){
            $oldvalues["hub"] = $blog["hub"];
            if($blog["hub"]){
                self::unsubscribe($blog["hub"], $blog["feed"]);
            }
            $blog["hub"] = $data["hub"];
            if($blog["hub"]){
                self::subscribe($blog["hub"], $blog["feed"]);
            }
            $changed = true;
        }
        
        // skip
        /*
        if($data["url"] && $blog["url"] != $data["url"]){
            $oldvalues["url"] = $blog["url"];
            $blog["url"] = $data["url"];
            $changed = true;
        }
        */
        
        if($blog["title"] != $data["title"]){
            $oldvalues["title"] = $blog["title"];
            $blog["title"] = $data["title"];
            $changed = true;
        }
        
        if($blog["meta"]["description"] != $data["description"]){
            $oldvalues["description"] = $blog["description"];
            $blog["meta"]["description"] = $data["description"];
            $changed = true;
        }
        
        if($changed){
        	include_once(dirname(__FILE__)."/event.php");
            Event::fire("blog:changed", array(
                "blog" => &$blog,
                "oldvalues" => $oldvalues
            ));
        }
        
        // kuna kutsutakse alati välja postituste kontrollis, siis tuleb
        // niikuinii ka 'checked' ja 'queued' väärtust muuta 
        $blog["checked"] = date("Y-m-d H:i:s");
        $blog["queued"] = false;
        $changed = true;
        
        if($changed){
            Blog::save($blog);
        }
    }
    
    /**
     * Blog.checkFeed($id) -> Boolean
     * - $id (Number): Blogi ID väärtus
     * 
     * Kontrollib blogi RSS failist uuendusi, blogi valitakse ID alusel
     **/
    public static function checkFeed($id){
        $blog = Blog::getById($id);
        return self::handleFeed($blog);
    }
    
    /**
     * Blog.handleFeed($blog) -> Boolean
     * - $blog (Object): blogiobjekt
     * 
     * Laeb blogi RSS faili ja otsib sellest üles uued postitused, samuti uuendab
     * vajadusel ka blogi enda andmed (pealkiri, hub jne). Juhul kui blogi ei leitud
     * tagastab false, muul juhul true
     **/
    public static function handleFeed(&$blog){
        if(!$blog || !$blog["feed"]){
            return false;
        }
        
        include_once(dirname(__FILE__)."/vendor/simplepie/simplepie.inc");
        $feed = new SimplePie();
        
        $feed->set_feed_url($blog["feed"]);
        $feed->set_useragent(BOT_USERAGENT);
    
        $feed->force_feed(true);
        $feed->enable_cache(false);
        $feed->set_image_handler(false);
    
        $feed->init();
        
        if($feed->error()){
            $feed->__destruct(); // Do what PHP should be doing on it's own.
            unset($feed);
            return false;
        }
    
        // lisa postitused
        Post::handlePosts($feed, $blog);
        
        // uuenda blogi enda andmeid
        Blog::update_from_feed($feed, $blog);
        
        $feed->__destruct(); // Do what PHP should be doing on it's own.
        unset($feed);
        return true;        
    }
    
    /**
     * Blog.subscribe($hub, $feed_url) -> undefined
     * - $hub (String): PubSubHubbub serveri URL
     * - $feed_url (String): Blogi RSS aadress
     * 
     * Funktsioon kutsub välja PubSubHubbub teenusesse registreerimise
     **/
    public static function subscribe($hub, $feed_url){
        include_once(dirname(__FILE__)."/vendor/subscriber/subscriber.php");
        if($hub){
            $subscriber = new Subscriber($hub, PUBSUB_CALLBACK_URL);
            $subscriber->subscribe($feed_url);
        }
    }

    /**
     * Blog.unsubscribe($hub, $feed_url) -> undefined
     * - $hub (String): PubSubHubbub serveri URL
     * - $feed_url (String): Blogi RSS aadress
     * 
     * Funktsioon kutsub välja PubSubHubbub teenusest maha registreerimise
     **/
    public static function unsubscribe($hub, $feed_url){
        include_once(dirname(__FILE__)."/vendor/subscriber/subscriber.php");
        if($hub){
            $subscriber = new Subscriber($hub, PUBSUB_CALLBACK_URL);
            $subscriber->unsubscribe($feed_url);
            return true;
        }else
            return false;
    }
    
    /**
     * Blog.get_data_from_feed($feed) -> Object
     * - $feed (Object): SimplePie RSS objekt
     * 
     * Otsib RSS failist välja erinevad blogi kohta käivad omadused
     * nagu pealkiri või PubSubHub aadress
     **/
    private static function get_data_from_feed(&$feed){
        $data = array();
        $data["title"] = text_decode($feed->get_title());
        $data["description"] = text_decode($feed->get_description());
        $data["url"] = urltrim($feed->get_permalink());
    
        $hubs = $feed->get_links("hub");
        $data["hub"] = $hubs && count($hubs)?urldecode($hubs[0]):false;
        return $data;
    }
    
    /**
     * Blog.getByParam($param, $value) -> Object
     * - $param (String): parameetri nimi mille alusel otsida
     * - $value (String): väärtus mille alusel otsida
     * 
     * Võimaldab otsida blogi kindla välja alusel, näiteks url või id. Tagastab
     * blogiobjekti, kui blogi leiti, vastasel korral tagastab false
     **/
    private static function getByParam($param, $value){
        $sql = "SELECT * FROM blogs WHERE `%s`='%s'";
        $result = mysql_query(sprintf($sql, 
            mysql_real_escape_string($param), mysql_real_escape_string($value)));
        if($row = mysql_fetch_array($result)){
            return self::deserialize($row);
        }else
            return false;
    }
    
    /**
     * Blog.deserialize([$data]) -> Object
     * - $data (Object): mysql_array objekt blogi andmetega
     * 
     * Koostab struktureeritud blogi andmete objekti.
     **/
    public static function deserialize($data=array()){
        
        $blog = array(
            "id"     => $data["id"]?intval($data["id"]):false,
            "url"    => $data["url"],
            "feed"   => $data["feed"]?$data["feed"]:false,
            "hub"    => $data["hub"]?$data["hub"]:false,
            "updated"=> $data["updated"],
            "title"  => $data["title"],
            "meta"   => $data["meta"]?unserialize($data["meta"]):array(),
            "checked"=> $data["checked"]!="0000-00-00 00:00:00"?$data["checked"]:false,
            "queued"  => $data["queued"]=="Y"?true:false
        );
        
        return $blog;
    }

}