<?php

class Post{
	
    public static function getByUrl($url){
    	$sql = "SELECT * FROM posts WHERE url='%s'";
        $result = mysql_query(sprintf($sql, mysql_real_escape_string($url)));
        return ($row = mysql_fetch_array($result))?true:false;
    }
    
    public static function handlePosts(&$feed, $blog = false){
        
        if(!$blog){
        	$blog = Blog::getByUrl(resolve_url($feed->get_permalink()));
        }
        
    	if(!$blog){
            echo "ei saanud aru :/\n";
        }else{
            foreach ($feed->get_items() as $item){
                self::save($item, $blog);
            }
            echo "alles OK!\n";
        }
        
        Blog::update_from_feed($feed, $blog);
        
    }
    
    public static function save(&$item, &$blog){
    	
        $url = resolve_url($item->get_permalink());
        
        if(self::getByUrl($url)){
        	return false;
        }
        
        if($author = $item->get_author()){
        	$author = $author->get_name();
        }else{
        	$author = "";
        }
        
        $tags = array();
        if($categories = $item->get_categories(0, 5)){
        	foreach ($item->get_categories() as $category){
                $tags[] = $category->get_label();
            }
        }
        
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