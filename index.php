<?php

require_once("config.php");
require_once("includes/tools.php");
require_once("includes/blog.php");
require_once("includes/post.php");
require_once("includes/simplepie.inc");

Header("content-type: text/plain; charset=utf-8");

echo Blog::add("http://innojairja.blogspot.com/");
