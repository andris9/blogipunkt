<?php

class Post{
	
    public static function getByUrl($url){
    	$sql = "SELECT * FROM posts WHERE url='%s'";
        $result = mysql_query(sprintf($sql, mysql_real_escape_string($url)));
        return ($row = mysql_fetch_array($result))?true:false;
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
        
        $time = intval($item->get_browser("U"));
        if($time>time())
            $time = time();
        if($time<100)
            $time = time();
        
        $post = array(
            "blog" => $blog["id"],
            "title" => text_decode($item->get_title()),
            "date" => $time,
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
        file_put_contents("log.txt",$sql);
        mysql_query($sql);
        if(mysql_error()){
        	return false;
        }else{
        	return true;
        }
        
    }
    
}