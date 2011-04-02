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

        // discard posts from external domains
        // some blogs include flickr, del.icio.us etc. newsfeed items as posts
        $blog_host = parse_url($blog["url"], PHP_URL_HOST);
        $post_host = parse_url($url, PHP_URL_HOST);
        if($blog_host != $post_host){
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

        $contents = text_decode($item->get_content());
        $data = array(
            "blog" => $blog["id"],
            "title" => text_decode($item->get_title()),
            "date" => date("Y-m-d H:i:s", $time),
            "author" => $author,
            "tags" => serialize($tags),
            "contents" => $contents,
            "snippet" => generateSnippet($contents),
            "url" => $url,
            "comment_feed" => self::get_comments_feed(&$item)
        );

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
            "snippet" => $data["snippet"],
            "url" => $data["url"],
            "comment_feed" => $data["comment_feed"],
            "comment_data" => $data["comment_data"]?unserialize($data["comment_data"]):array(),
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
            "snippet" => $post["snippet"]?$post["snippet"]:generateSnippet($post["contents"]),
            "url" => $post["url"],
            "votes" => intval($post["votes"]),
            "points" => floatval($post["points"]),
            "comment_feed" => $post["comment_feed"]
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


    /**
     * Blog.getList([$start=0][, $limit=20][, $lang]) -> Array
     * - $start (Number): millisest kirjest alustada
     * - $limit (Number): mitu kirjet maksimaalselt lugeda
     * - $lang (String): mis keeles peab blogi olema (n: "et")
     *
     * Laeb andmebaasist $limit hulga postitusi, järjestatult uuemad eespool
     **/
    public static function getList($start=0, $limit=20, $lang=false){

        $data = array();

        $selectors = array(1); // vaikimisi tingimus on 1, mis on alati tõene
        $selectors[]= "blogs.disabled='N'";
        if($lang){
        	$selectors[]= sprintf("blogs.lang='%s'", mysql_real_escape_string($lang));
        }

    	$sql = "SELECT posts.*, blogs.title AS blogtitle, blogs.url AS blogurl FROM posts LEFT JOIN blogs ON posts.blog=blogs.id WHERE ".join(" AND ", $selectors)." ORDER BY id DESC LIMIT %s, %s";
        $result = mysql_query(sprintf($sql, intval($start), intval($limit)));
        while($row = mysql_fetch_array($result)){
            $post = self::deserialize($row);
            $post["blogtitle"] = $row["blogtitle"];
            $post["blogurl"] = $row["blogurl"];
        	$data[] = $post;
        }
        return $data;
    }

    /**
     * Posts.get_comments_feed($item) -> String
     * - $item (Object): SimplePie item
     * 
     * Funktsioon tuvastab kommentaaride RSS aadressi.
     * 
     * TODO: Lisada Disqus tugi. Selle jaoks tuleb paraku tõmmata alla ka iga postituse sisu,
     *       et selle HTML koodist Disqus markereid otsida, RSS failis neid pole.
     **/
    public static function get_comments_feed(&$item){
        $comment_feed = "";
        if($comment_tags = $item->get_item_tags("http://wellformedweb.org/CommentAPI/","commentRss")){ // RSS
            $comment_feed = $comment_tags[0]["data"];
        }elseif($link_tags = $item->get_item_tags("http://www.w3.org/2005/Atom","link")){ // ATOM
            foreach($link_tags as $link_elm){
                if($link_attribs = $link_elm["attribs"][""]){
                    if($link_attribs["rel"]=="replies" && $link_attribs["type"]=="application/atom+xml"){
                        $comment_feed = $link_attribs["href"];
                        break;
                    }
                } 
            }
        };
        return $comment_feed?$comment_feed:false;
    }

}