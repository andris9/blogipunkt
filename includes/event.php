<?php

class Event{
	
    private static $registry = array();
    
    public static function observe($event, $handler){
        if(!self::$registry[$event])self::$registry[$event] = array();
        if(!in_array($handler, self::$registry[$event])){
        	self::$registry[$event][] = $handler;
        }
    }
    
    public static function stopObserving($event, $handler){
        if(!self::$registry[$event])self::$registry[$event] = array();
        if(($key = array_search($handler, self::$registry[$event]))!==false){
            unset(self::$registry[$event][$key]);
        }
    }
    
    public static function fire($event, &$payload){
        if(self::$registry[$event]){
        	foreach(self::$registry[$event] as $handler){
        		call_user_func($handler, &$payload);
        	}
        }
    }
    
}