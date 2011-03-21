<?php

require_once("config.php");
require_once("includes/tools.php");
require_once("includes/blog.php");
require_once("includes/post.php");



echo template_render("views/main.php", array("body"=>print_r($_REQUEST,1)));
