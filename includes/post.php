<?php

/**
 * Post
 * 
 * Klass, mis tegeleb postituste majandamisega (peamiselt lisamine)
 **/
class Post{
	
    /**
     * checkPostExists($url) -> Boolean
     * - $url (String): postituse URL
     * 
     * Kontrollib kas antud aadressiga postitus on juba andmebaasis või mitte
     **/
    public static function checkPostExists($url){
    	$sql = "SELECT * FROM posts WHERE url='%s'";
        $result = mysql_query(sprintf($sql, mysql_real_escape_string($url)));
        return ($row = mysql_fetch_array($result))?true:false;
    }
    
    /**
     * Blog.handlePosts($feed, $blog) -> Number
     * - $feed (Object): SimplePie RSS objekt
     * - $blog (Object): blogiobjekt
     * 
     * Laeb RSS objektist viimased 5 postitust ja kui neid veel ei eksisteeri,
     * siis lisab andmebaasi. Tagastab arvu uute postitustega või false, kui
     * $blog polnud määratud
     **/
    public static function handlePosts(&$feed, $blog){
    	if(!$blog){
            return false;
        }else{
            // get newest 5 items
            $count = 0;
            $max = ($feed->get_item_quantity()>5?5:$feed->get_item_quantity())-1;
            for($i=$max; $i>=0;$i--){
                $item = $feed->get_item($i);
                if(self::save($item, $blog))$count++;
            }
            return $count;
        }
    }
    
    /**
     * Post.save($item, $blog) -> Boolean
     * - $item (Object): SimplePie feed item objekt
     * - $blog (Object): blogiobjekt
     * 
     * Juhul kui tegu on uue postitusega, salvestab selle baasi. Kui salvestamine
     * õnnestus, tagastab true, kõigil muudel juhtudel false
     **/
    public static function save(&$item, &$blog){
    	
        $url = resolve_url($item->get_permalink());
        
        if(self::checkPostExists($url) || !$blog){
        	return false;
        }
        
        // leia postituse autor
        if($author = $item->get_author()){
        	$author = $author->get_name();
        }else{
        	$author = "";
        }
        
        // leia postituse kategooriad (kuni 5)
        $tags = array();
        if($categories = $item->get_categories(0, 5)){
        	foreach ($item->get_categories() as $category){
                $tags[] = $category->get_label();
            }
        }
        
        // leia postituse aeg. Kui tulevikus, siis tasanda hetke aja peale
        $time = intval($item->get_date("U"));
        if($time>time())
            $time = time();
        if($time<100)
            $time = time();
        
        $post = array(
            "blog" => $blog["id"],
            "title" => text_decode($item->get_title()),
            "date" => date("Y-m-d H:i:s", $time),
            "author" => $author,
            "tags" => serialize($tags),
            "contents" => text_decode($item->get_content()),
            "url" => $url
        );
        
        include_once(dirname(__FILE__)."/event.php");
        Event::fire("post:presave", array(
            "post" => &$post,
            "blog" => &$blog,
            "item" => &$item
        ));
        
        // Koosta SQL päring
        $sqlnames = array();
        $sqlvalues = array();
        foreach($post as $name=>$value){
        	$sqlnames[] = "`".$name."`";
            $sqlvalues[] = '"'.mysql_real_escape_string($value).'"';
        }
        
        $sql = "INSERT INTO posts (".join(", ",$sqlnames).") VALUES(".join(", ",$sqlvalues).")";
        mysql_query($sql);
        if(mysql_error()){
        	return false;
        }else{
        	return true;
        }
        
    }
    
}