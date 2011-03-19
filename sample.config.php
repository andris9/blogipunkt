<?php

//Andmebaasi konstandid
define ("MYSQL_SERVER_NAME", "localhost"); 
define ("MYSQL_SERVER_USER", "db_username"); 
define ("MYSQL_SERVER_PASS", "db_password"); 
define ("MYSQL_SERVER_BASE", "db_base"); 

//Andmebaasiga ühendamine
$dbconnect=mysql_connect(MYSQL_SERVER_NAME,MYSQL_SERVER_USER,MYSQL_SERVER_PASS) or die("1:".mysql_error()); 
mysql_select_db(MYSQL_SERVER_BASE) or die("2:".mysql_error());

// Lokaal ja tähetabelid
mysql_set_charset('utf8');
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
setlocale(LC_ALL,'et_EE.UTF-8');

// user agent string for the bot
define("BOT_USERAGENT","MyBot");
define("PUBSUB_VERIFY_TOKEN","secret_token");

// Listid

$GLOBALS["IGNORE_QUERY_PARAMS"] = array(
    "utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content"
);

$GLOBALS["IGNORE_FILENAMES"] = array(
    "index.php", "index.htm", "index.html"
);