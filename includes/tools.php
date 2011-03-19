<?php


function urltrim($url){
	$urlparts = parse_url(trim($url));
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
    
    $newparts = array();
    if($urlparts["scheme"])$newparts[] = $urlparts["scheme"]."://";
    if($urlparts["user"]){
    	$newparts[] = $urlparts["user"];
        if($urlparts["pass"])$newparts[] = ":".$urlparts["pass"];
        $newparts[] = "@";
    }
    if($urlparts["host"])$newparts[] = $urlparts["host"];
    $newparts[] = $urlparts["path"]?$urlparts["path"]:"/";
    if(count($newQuery))$newparts[] = "?".http_build_query($newQuery);
    // skip fragment
    
    return trim(join("", $newparts), " \t\n\r\0\x0B/");
}