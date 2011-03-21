<?php

function text_decode($str){
    return trim(html_entity_decode($str, ENT_QUOTES, 'UTF-8'));
}

function urltrim($url){
    $url = trim($url);

    // replace feed: with http:
    if(substr(strtolower($url),0,5)=="feed:"){
        $url = "http:".substr($url, 5);
        if(substr(strtolower($url),0,10)=="http:http:"){
            $url = substr($url,5);
        }elseif(substr(strtolower($url),0,12)=="http://http:"){
            $url = substr($url,7);
        }elseif(substr(strtolower($url),0,11)=="http:https:"){
            $url = substr($url,5);
        }elseif(substr(strtolower($url),0,13)=="http://https:"){
            $url = substr($url,7);
        }
    }

    $urlparts = parse_url($url);
    $oldQuery = false;
    parse_str($urlparts["query"], $oldQuery);
    $newQuery = array();

    if($oldQuery)foreach($oldQuery as $key=>$value){
        if(!in_array($key, $GLOBALS["IGNORE_QUERY_PARAMS"])){
            $newQuery[$key] = $value;
        }
    }

    $pathparts = split("/", $urlparts["path"]);
    if(in_array($pathparts[count($pathparts)-1], $GLOBALS["IGNORE_FILENAMES"])){
        array_pop($pathparts);
    }

    $urlparts["path"] = "/".join("/",array_filter($pathparts));
    $urlparts["query"] = count($newQuery)?http_build_query($newQuery):"";

    return build_url($urlparts);
}

function resolve_url($url){

    $sourceurl = urltrim($url);
    $sql = "SELECT dest FROM urls WHERE source=MD5('%s')";
    $result = mysql_query(sprintf($sql, mysql_real_escape_string($sourceurl)));
    if($row = mysql_fetch_array($result)){
        return $row["dest"];
    }

    return urlexists($url);

}

function urlexists($url){
    $ch = curl_init();
    $options = array(CURLOPT_URL        => $url,
                CURLOPT_USERAGENT       => BOT_USERAGENT,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_AUTOREFERER     => true,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_NOBODY          => true,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_HEADER          => false
                );

    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch);
    $status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // miskipärast suunab curl mitteeksisteerivad .com aadressid
    // domeenile com.org, mille staatus on 200
    if($err || substr($status,0,1) != "2" || ($pos = strpos($header["url"], "com.org/?not_found"))!==false){
        return false;
    }

    $sourceurl = urltrim($url);
    $desturl = urltrim($header["url"]?$header["url"]:$url);

    $sql = 'INSERT INTO urls (source, dest) VALUES(MD5("%1$s"),"%2$s") ON DUPLICATE KEY UPDATE source=MD5("%1$s"), dest="%2$s"';
    mysql_query(sprintf($sql, mysql_real_escape_string($sourceurl),
                                    mysql_real_escape_string($desturl)));

    return $desturl;
}

function load_from_url($url){
    $ch = curl_init();
    $options = array(CURLOPT_URL        => $url,
                CURLOPT_USERAGENT       => BOT_USERAGENT,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_AUTOREFERER     => true,
                CURLOPT_NOBODY          => false,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_HEADER          => false
                );
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch);
    $status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($err || substr($status,0,1) != "2"){
        return false;
    }

    return $content;
}


function build_url($urlparts){
    $newparts = array();
    if($urlparts["scheme"])$newparts[] = $urlparts["scheme"]."://";
    if($urlparts["user"]){
        $newparts[] = $urlparts["user"];
        if($urlparts["pass"])$newparts[] = ":".$urlparts["pass"];
        $newparts[] = "@";
    }
    if($urlparts["host"])$newparts[] = $urlparts["host"];
    $newparts[] = $urlparts["path"]?$urlparts["path"]:"/";
    if($urlparts["query"])$newparts[] = "?".$urlparts["query"];
    // skip fragment

    return trim(join("", $newparts), " \t\n\r\0\x0B/");
}

function baseUrl($url, $base){

    $baseparts = parse_url($base);

    // already full url
    if(substr(strtolower($url),0,7)=="http://" || substr(strtolower($url),0,8)=="https://"){
        return $url;
    }

    if(!$url){
        return $base;
    }

    if(substr($url,0,1)=="/"){
        list($baseparts["path"], $baseparts["query"]) = explode("?",$url,2);
        return build_url($baseparts);
    }

    $baseparts["pathtree"] = array_values(array_filter(split("/",$baseparts["path"])));
    // remove last element, if has a file extension
    if(count($baseparts["pathtree"])){
        $lastelm = $baseparts["pathtree"][count($baseparts["pathtree"])-1];
        $pos = strrpos($lastelm,".");
        if($pos && strlen($lastelm)-$pos<=5){
            array_pop($baseparts["pathtree"]);
        }
    }

    list($urlparts, $baseparts["query"]) = explode("?",$url,2);
    $urlparts = array_values(array_filter(split("/",$urlparts)));

    $build = $baseparts["pathtree"];

    for($i=0;$i<count($urlparts);$i++){
        if(!$urlparts[$i] || $urlparts[$i]==".")continue;
        if($urlparts[$i]==".."){
            array_pop($build);
            continue;
        }
        $build[] = $urlparts[$i];
    }

    $baseparts["path"] = "/".join("/",$build);
    return build_url($baseparts);
}


function detectFeed($html, $url){

    // kui tegu livejournal või diaryland blogiga
    $rss_default = Array(
        'livejournal.com'=>'/data/rss',
        'diaryland.com'=>'/index.rss'
        );

    $url_parts = parse_url($url);
    foreach($rss_default as $key=>$feed)
        if(($pos = strpos(strtolower($url_parts['host']), $key))!==false)
            return $url_parts['scheme']."://".$url_parts['host'].$feed;

    $baseUrl = $url;
    $feeds = Array();

    // keep only the first 32kB of the HTML
    if(strlen($html)>32*1024)
        $html = substr($html, 0, 32*1024);

    $document = new DOMDocument();
    @$document->loadHTML($html);
    $params = $document->getElementsByTagName('*');

    // * on sellepärast, et osades blogides on RSS parameetrid muude elementide küljes (n: A)
    foreach ($params as $param) {

        // find new base if set
        if($param->nodeName=="base"){
            $baseUrl = $param->getAttribute('href');
            if(!$baseUrl)$baseUrl = $url;
            continue;
        }

        // check if has RSS properties
        $type = trim(strtolower($param -> getAttribute('type')));
        if(($type == "application/atom+xml" || $type == "application/rss+xml" || $type == "text/xml") &&
            ($pos = strpos(mb_strtolower($param->getAttribute('href')),'comment'))===false &&
            ($param->getAttribute('rel')=='alternate' || !$param->getAttribute('rel'))){
                if(strlen(trim($param->getAttribute('href')))){
                    $feeds[] = trim($param->getAttribute('href'));
                }
            }
    }
    $feeds = array_unique($feeds);

    // Kui midagi pole leitud, proovime üldlevinud aadresse
    if(!count($feeds)){
        $rss_detect = Array('feed','feeds/posts/default','rss','xml-rss2.php','newsfeed.php','rss.php','atom.xml','rss.xml');

        foreach($rss_detect as $rss){
            if($rssUrl = urlexists(baseUrl($rss, $baseUrl)))
                return $rssUrl;
        }

        return false;
    }

    // otsi lühim aadress (pikem võib olla näiteks kommentaar vms.)
    $short = 0;
    $final = false;
    foreach($feeds as $feed){
        if((!$final || mb_strlen($feed)<$short) && strlen($feed))
            $final = $feed;
        $short = mb_strlen($feed);
    }
    return baseUrl($final, $baseUrl);
}

function template_render($filename, $context = array()){
    extract($context, EXTR_SKIP);
    ob_start();
    @include($filename);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

