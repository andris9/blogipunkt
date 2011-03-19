<?php


class Blog{
	
	public static function getById($id){
		return self::getByParam("id", $id);
	}
    
    public static function getByURL($url){
        return self::getByParam("url", urltrim($url));
    }
    
    public static function add($url){
        $blog = self::getByURL($url);
        if($blog){
        	return $blog["id"];
        }
        $blog = self::blank();
        $blog["url"] = urltrim(resolve_url($url));
        $html = load_from_url($blog["url"]);
        $blog["feed"] = detectFeed($html, $blog["url"]);
        
        if(self::save($blog)){
        	self::checkFeed($blog["id"]);
        };

        return $blog["id"];
    }
    
    public static function save(&$blog){
        
        if(!$blog["url"]){
        	throw new Exception('No blog url set');
        }
        
        if(!$blog["id"]){
            $sql = "INSERT INTO blogs (url, feed, hub, title, meta, updated, queued) VALUES('%s','%s','%s','%s','%s',NOW(),'%s')";	
        }else{
            $sql = "UPDATE blogs SET url='%s', feed='%s', hub='%s', title='%s', meta='%s', updated=NOW(), queued='%s' WHERE id='{$blog["id"]}'";	
        }
        
        mysql_query(sprintf($sql, 
                mysql_real_escape_string($blog["url"]),
                mysql_real_escape_string($blog["feed"]),
                mysql_real_escape_string($blog["hub"]),
                mysql_real_escape_string($blog["title"]),
                mysql_real_escape_string($blog["meta"]?serialize($blog["meta"]):""),
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
    
    public static function blank(){
        $blog = self::deserialize();
        $blog["meta"]["inserted"] = date("Y-m-d H:i:s");
        return $blog;
    }
    
    public static function update_from_feed(&$feed, &$blog){
        $data = self::get_data_from_feed($feed);
        $changed = false;
        
        if($data["hub"] != $blog["hub"]){
            
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
        
        if($blog["url"] != $data["url"]){
            $blog["url"] = $data["url"];
            $changed = true;
        }
        
        if($blog["title"] != $data["title"]){
            $blog["title"] = $data["title"];
            $changed = true;
        }
        
        if($blog["meta"]["description"] != $data["description"]){
            $blog["meta"]["description"] = $data["description"];
            $changed = true;
        }
        
        $blog["checked"] = date("Y-m-d H:i:s");
        $blog["queued"] = "N";
        $changed = true;
        
        if($changed){
            Blog::save($blog);
        }
    }
    
    public static function checkFeed($id){

        $blog = Blog::getById($id);
        if(!$blog || !$blog["feed"]){
            return false;
        }
    
        $feed = new SimplePie();
        $feed->set_feed_url($blog["feed"]);
        $feed->set_useragent(BOT_USERAGENT);
    
        $feed->enable_cache(false);
        $feed->set_image_handler(false);
    
        $feed->init();
    
        // lisa postitused
        Post::handlePosts($feed, $blog);
        
        $feed->__destruct(); // Do what PHP should be doing on it's own.
        unset($feed);
        unset($blog);
        return true;
    }
    
    public static function subscribe($hub, $feed){
        include_once(dirname(__FILE__)."/subscriber.php");
        if($hub){
            $subscriber = new Subscriber($hub, PUBSUB_CALLBACK_URL);
            $subscriber->subscribe($feed);
        }
    }

    public static function unsubscribe($hub, $feed){
        include_once(dirname(__FILE__)."/subscriber.php");
        if($hub){
            $subscriber = new Subscriber($hub, PUBSUB_CALLBACK_URL);
            $subscriber->unsubscribe($feed);
            return true;
        }else
            return false;
    }
    
    private static function get_data_from_feed(&$feed){
        $data = array();
        $data["title"] = text_decode($feed->get_title());
        $data["description"] = text_decode($feed->get_description());
        $data["url"] = urltrim($feed->get_permalink());
    
        $hubs = $feed->get_links("hub");
        $data["hub"] = $hubs && count($hubs)?urldecode($hubs[0]):false;
        return $data;
    }
    
    private static function getByParam($param, $value){
        $sql = "SELECT * FROM blogs WHERE `%s`='%s'";
        $result = mysql_query(sprintf($sql, 
            mysql_real_escape_string($param), mysql_real_escape_string($value)));
        if($row = mysql_fetch_array($result)){
            return self::deserialize($row);
        }else
            return false;
    }
    
	private static function deserialize($data=array()){
		
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