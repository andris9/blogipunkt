<?php

include("../includes/event.php");

$counter = 0;

Event::observe("test1","func1");
Event::observe("test1","func2");
Event::observe("test2","func1");

Event::fire("test1",$counter); // 1+10 = 11 
Event::stopObserving("test1","func2");
Event::fire("test2",$counter); // 11+1 = 12
Event::fire("test2",$counter); // 12+1 = 13

echo $counter == 13?"Event passed":"Event failed";

function func1(&$e){
    $e++;
}

function func2(&$e){
    $e += 10;
}