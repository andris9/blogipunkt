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

        $data = array(
            "blog" => $blog["id"],
            "title" => text_decode($item->get_title()),
            "date" => date("Y-m-d H:i:s", $time),
            "author" => $author,
            "tags" => serialize($tags),
            "contents" => text_decode($item->get_content()),
            "url" => $url
        );

        include_once(dirname(__FILE__)."/event.php");
        $ref = array(
            "post" => &$data,
            "blog" => &$blog,
            "item" => &$item
        );
        Event::fire("post:presave", $ref);

        return self::serialize($data);

    }


    /**
     * Post::getPost($id, &$blog) -> Object
     * - $id (Number): Postituse ID
     * - $blog (Object): tühi muutuja, millele omistatakse blogiobjekt
     *
     * Funktsioon otsib andmebaasist üles postituse ja tagastab postituse objekti
     **/
    public static function getPost($id, &$blog){
        $sql = "SELECT * FROM posts WHERE id='%s'";
        $result = mysql_query(sprintf($sql, mysql_real_escape_string($id)));

        if($row = mysql_fetch_array($result)){
        	$blog = Blog::getById($row["blog"]);
            return self::deserialize($row);
        }else{
        	return false;
        }
    }

    /**
     * Post::deserialize([$data]) -> Object
     * - $data (Object): postituse objekt andmebaasist (mysql_fetch_array)
     *
     * Funktsioon genereerib postituse objekti andmebaasiobjektist või loob tühja
     * objekti, kui parameeter $data on määramata
     **/
    public static function deserialize($data=array()){
        $post = array(
            "id" => intval($data["id"]),
            "blog" => intval($data["blog"]),
            "title" => $data["title"],
            "date" => $data["date"],
            "author" => $data["author"],
            "tags" => $data["tags"]?unserialize($data["tags"]):false,
            "contents" => $data["contents"],
            "url" => $data["url"],
            "votes" => intval($data["votes"]),
            "points" => floatval($data["points"])
        );
        return $post;
    }

    /**
     * Post::serialize($post [,$updated]) -> Object
     * - $post (Object): postituse objekt
     * - $updated (Array): väljad, mida on muudetud
     *
     * Funktsioon uuendab postitust andmebaasis või lisab selle. Juhul kui $updated
     * väärtus on seatud, uuendatakse ainult määratud välju.
     **/
    public static function serialize(&$post, $updated=false){
        $data = array(
            "blog" => $post["blog"],
            "title" => $post["title"],
            "date" => $post["date"],
            "author" => $post["author"],
            "tags" => $post["tags"]?serialize($post["tags"]):"",
            "contents" => $post["contents"],
            "url" => $post["url"],
            "votes" => intval($post["votes"]),
            "points" => floatval($post["points"])
        );

        if($post["id"] && $updated){
        	foreach($data as $key=>$value){
                if(!in_array($key, $updated)){
                	unset($data[$key]);
                }
            }
        }

        // Koosta SQL päring
        if($post["id"]){ // uuenda

            $sqlvalues = array();
            foreach($data as $name=>$value){
                $sqlvalues[] = '`'.$name.'`="'.mysql_real_escape_string($value).'"';
            }
            $sql = sprintf("UPDATE posts SET ".join(", ",$sqlvalues)." WHERE id='%s'",
                mysql_real_escape_string($post["id"]));

        }else{ // lisa
            $sqlnames = array();
            $sqlvalues = array();
            foreach($data as $name=>$value){
                $sqlnames[] = "`".$name."`";
                $sqlvalues[] = '"'.mysql_real_escape_string($value).'"';
            }
            $sql = "INSERT INTO posts (".join(", ",$sqlnames).") VALUES(".join(", ",$sqlvalues).")";
        }

        mysql_query($sql);
        if(mysql_error()){
            return false;
        }else{
            if(!$post["id"])$post["id"] = mysql_insert_id();
            return true;
        }
    }


    /**
     * Post.calculate_points(&$post) -> undefined
     * - $post (Object): postituse objekt
     *
     * Arvutab modifitseeritud Redditi algoritmi järgi postituse populaarsuse
     * Uuem postitus on automaatselt populaarsem, kui vana - täiendavalt loeb
     * ka iga üleshääl.
     *
     * Allikas http://www.teamrubber.com/blog/the-reddit-algorithm/
     **/
    public static function calculate_points(&$post){
        $startTime = 1134020803; //08.12.2005 07:46:43
        $timeDiff = strtotime($post["date"]) - $startTime;
        if($timeDiff<0) $timeDiff=0;
        $y = $post["votes"]>0?1:0;
        $post["points"] = 45000 * log10(max($post["votes"],1)) + $y * $timeDiff;
    }

}