<?php

/*
 * tools.php
 *
 * Pakub erinevaid kasulikke funktsioone, nagu näiteks RSS aadressi tuvastamine jms.
 */

/**
 * text_decode($str) -> String
 * $str (String): tekst, mida muuta
 *
 * Tekstis asendatakse kõik html entity'd nende UTF-8 vastava sümboliga
 **/
function text_decode($str){
    return trim(html_entity_decode($str, ENT_QUOTES, 'UTF-8'));
}

/**
 * urltrim($url) -> String
 * - $url (String): veebiaadress, mida muuta
 *
 * Funktsioon parandab veebiaadressi, viies selle ühtsele kujule,
 * eemaldatakse algusest "feed:", lõpust korjatakse erinevad statistika
 * parameetrid (google analytics), eemaldatakse viimane slash, loobutakse ka
 * index.html jms lõppudest
 **/
function urltrim($url){
    $url = trim($url);

    if(!$url || preg_match("/^https?:\/\/$/",$url)){
    	return false;
    }

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

    $pathparts = explode("/", $urlparts["path"]);
    if(in_array($pathparts[count($pathparts)-1], $GLOBALS["IGNORE_FILENAMES"])){
        array_pop($pathparts);
    }

    $urlparts["path"] = "/".join("/",array_filter($pathparts));
    $urlparts["query"] = count($newQuery)?http_build_query($newQuery):"";

    return build_url($urlparts);
}

/**
 * resolve_url($url) -> String
 * - $url (String): veebiaadress, mida kontrollida
 *
 * Funktsioon kontrollib ümbersuunamisi, võttes sisendiks suvalise URL'i ja
 * tagastades lõpliku sihtaadressi või false, kui ilmnes viga. Vajalik tuvastamaks
 * unikaalseid aadresse, kui blogi kasutab erinevaid ümbersuunamise statistika URL'e
 * või aadressi lühendajaid
 **/
function resolve_url($url){

    $sourceurl = urltrim($url);
    $sql = "SELECT dest FROM urls WHERE source=MD5('%s')";
    $result = mysql_query(sprintf($sql, mysql_real_escape_string($sourceurl)));
    if($row = mysql_fetch_array($result)){
        return $row["dest"];
    }

    return urlexists($url);
}

/**
 * urlexists($url) -> String
 * - $url (String): veebiaadress, mida kontrollida
 *
 * Funktsioon kontrollib, kas veebiaadress eksisteerib või mitte. Juhul kui
 * ekistseerib, tagastatakse lõppsihi aadress, vastasel korral false
 **/
function urlexists($url){
    $ch = curl_init();
    $options = array(CURLOPT_URL        => $url,
                CURLOPT_USERAGENT       => BOT_USERAGENT,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_AUTOREFERER     => true,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_NOBODY          => true,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_HEADER          => false,
                CURLOPT_CONNECTTIMEOUT  => 10
                );

    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch);
    $status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    //print_r(array($content, $err, $errmsg, $header, $status));

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

/**
 * load_from_url($url) -> String
 * - $url (String): veebiaadress, mille sisu laadida
 *
 * Funktsioon laeb etteantud aadressi sisu. Sama mis file_get_contents($url),
 * kui kasutab korrektset user agent väärtust
 **/
function load_from_url($url){
    $ch = curl_init();
    $options = array(CURLOPT_URL        => $url,
                CURLOPT_USERAGENT       => BOT_USERAGENT,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_AUTOREFERER     => true,
                CURLOPT_NOBODY          => false,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_HEADER          => false,
                CURLOPT_CONNECTTIMEOUT  => 10
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

/**
 * build_url($urlparts) -> String
 * - $urlparts (Object): parse_url funktsiooniga saadud massiiv
 *
 * Funktsioon genereerib veebiaadressi, kasutades sisendina parse_url
 * funktsiooni abil saadud massiivi. Vajalik kuna seda esialgset massiivi
 * on teinekord vaja toimetada
 **/
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

/**
 * baseUrl($url, $base) -> String
 * - $url (String): veebiaadress, mida kontrollida
 * - $base (String): aadressi algus
 *
 * Funktsioon võtab sisendiks relatiivse veebiaadressi ja selle aluse ning
 * genereerib absoluutse aadressi.
 * Näiteks
 *     baseUrl("../icon.gif", "http://www.example.com/scripts/"); // -> http://www.example.com/icon.gif
 *
 * Oluline, kui näiteks RSS aadress HTML failis on relatiivne.
 **/
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

    $baseparts["pathtree"] = array_values(array_filter(explode("/",$baseparts["path"])));
    // remove last element, if has a file extension
    if(count($baseparts["pathtree"])){
        $lastelm = $baseparts["pathtree"][count($baseparts["pathtree"])-1];
        $pos = strrpos($lastelm,".");
        if($pos && strlen($lastelm)-$pos<=5){
            array_pop($baseparts["pathtree"]);
        }
    }

    list($urlparts, $baseparts["query"]) = explode("?",$url,2);
    $urlparts = array_values(array_filter(explode("/",$urlparts)));

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

/**
 * detectFeed($html, $url) -> String
 * - $html (String): lehe HTML sisu
 * - $url (String): lehe aadress
 *
 * Funktsioon tuvastab lehe sisu järgi RSS aadressi.
 **/
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

/**
 * template_render($filename [, $context = array()]) -> String
 * - $filename (String): faili asukoht kettal
 * - $context (Array): massiiv, mille elementidest luuakse kohalikud muutujad
 *
 * Genereerib templiidifailist (PHP fail) valmis stringi. Templiidifaili sees
 * on võimalik kasutada lokaalseid muutujaid, mis pärinevad $context massiivist
 *
 * Kontekst:
 *     $context = array("muutuja1"=>"väärtus1");
 *
 * Templiidifail template.inc
 *     <p><?php echo $muutuja1; ?></p>
 *
 * Tulemus:
 *     echo template_render("template.inc", $context); // -> <p>väärtus1</p>
 **/
function template_render($filename, $context = array()){
    extract($context, EXTR_SKIP);
    ob_start();
    @include($filename);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

/**
 * detectLanguage($text) -> Array
 * - $text(String): tekst mille keelt kontrollida, max ~350 tm.
 *
 * Funktsioon tuvastab Google Language API abil teksti keele. Vajalik on seada
 * config.php failis GOOGLE_API_KEY väärtus.
 *
 * Automaatsed päringud ei ole lubatud, päringu peab algatama inimene, seetõttu
 * on parameetrina kaasas ka päringu algatanud inimese IP
 **/
function detectLanguage($text){
    if(!GOOGLE_API_KEY)return false;
    // Automated querys are not allowed, so any request made needs to be "backed up"
    // by a real user (userip)
    $url = "https://ajax.googleapis.com/ajax/services/language/detect?".
            "v=1.0&q=".urlencode($text)."&key=". GOOGLE_API_KEY ."&userip=".$_SERVER["REMOTE_ADDR"];
    $response = load_from_url($url);
    return $response?@json_decode($response, true):false;
}

/**
 * generateSnippet($text) -> String
 * - $text (String): põhitekst HTML vormingus
 *
 * Funktsioon genereerib sissejuhatava lõigu võttes aluseks HTML teksti
 **/
function generateSnippet($text){
	$text = str_replace(">","> ", $text);
    $text = strip_tags($text,"<p><br>");
    $text = preg_replace("/\s\s*/"," ",$text);
    $text = preg_replace("/<.*?>/","\n",$text);
    $text = preg_replace("/\.\s/",".\n",$text);
    $lines = explode("\n",$text);
    $text = "";
    while(count($lines)){
    	$text .= array_shift($lines)." ";
        if(strlen($text)>300)break;
    }
    $text = trim(preg_replace("/\s\s*/"," ",$text));
    $text = trim(preg_replace("/\s*([\.,”:‘)])\s+/","$1 ",$text));
    $text = trim(preg_replace("/\s+([(“])\s*/"," $1",$text));

    return $text;
}

