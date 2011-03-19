<?php

require_once("config.php");
require_once("includes/tools.php");
require_once("includes/blog.php");

Header("content-type: text/plain; charset=utf-8");

$url = "http://toompark.pri.ee/aarne/valimisreklaam-minu-postkastis/?utm_source=Feedburner&utm_medium=feed&utm_campaign=Feed%3A+AarneBloog+%28Aarne+bloog%29&r=2";
echo(urltrim($url))."\n\n";

$b = Blog::blank();

print_r($b);

$b["title"]="testmie õüäö";

$c = Blog::save($b);

print_r($c);

print_r(Blog::getById($c["id"]));