<?php


class Blog{
	
	public static function getById($id){
		return self::getByParam("id", $id);
	}
    
    public static function getByURL($url){
        return self::getByParam("url", urltrim($url));
    }
    
    public static function save($blog){
        
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
        }else{
        	if(!$blog["id"]){
        		$blog["id"] = mysql_insert_id();
        	}
            return $blog;
        }
    }
    
    public static function blank(){
        $blog = self::deserialize();
        $blog["meta"]["inserted"] = date("Y-m-d H:i:s");
        return $blog;
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