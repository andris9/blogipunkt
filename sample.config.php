<?php

// muuta on vaja kirjeid, mille taga on märkus MUUDA SEDA, muude muutmine
// ei ole otseselt vajalik

//Andmebaasi konstandid
define ("MYSQL_SERVER_NAME", "localhost");    // <-- MUUDA SEDA
define ("MYSQL_SERVER_USER", "db_username");  // <-- MUUDA SEDA
define ("MYSQL_SERVER_PASS", "db_password");  // <-- MUUDA SEDA
define ("MYSQL_SERVER_BASE", "db_base");      // <-- MUUDA SEDA

//Andmebaasiga ühendamine
$dbconnect=mysql_connect(MYSQL_SERVER_NAME,MYSQL_SERVER_USER,MYSQL_SERVER_PASS) or die("1:".mysql_error()); 
mysql_select_db(MYSQL_SERVER_BASE) or die("2:".mysql_error());

// Lokaal ja tähetabelid
mysql_set_charset('utf8');
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
setlocale(LC_ALL,'et_EE.UTF-8');

// Saidi pealkiri
define("SITE_TITLE", "Blogipunkt"); // <-- MUUDA SEDA

// Google API Key
// genereeri aadressilt http://code.google.com/intl/et-EE/apis/loader/signup.html
// vajalik blogide keele määramiseks
define("GOOGLE_API_KEY", ""); // <-- MUUDA SEDA

// Google Analytics identifikaator kujul UA-XXXXX-XX külastuste loendamiseks
define("GOOGLE_ANALYTICS_ID", "");

// Roboti identifikaator
define("BOT_USERAGENT","MyBlogBot/1.0 (+{$_SERVER["HTTP_HOST"]})");

// PubSubHubbub
// verify token on suvaline unikaalne string
define("PUBSUB_VERIFY_TOKEN","secret_token"); // <-- MUUDA SEDA
define("PUBSUB_CALLBACK_URL","http://".$_SERVER["HTTP_HOST"]."/pubsub/client");

// Listid

// GET parameetrid veebiaadresside lõpus, mille võib rahlikult ära jätta
// http://example.com/blog.php?utm_medium=1 -> http://example.com/blog.php
$GLOBALS["IGNORE_QUERY_PARAMS"] = array(
    "utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content"
);

// failinimed veebiaadressis, mida võib ära jätta
// http://example.com/index.php -> http://www.example.com
$GLOBALS["IGNORE_FILENAMES"] = array(
    "index.php", "index.htm", "index.html"
);

// Muu

// Mitu kategooriat võib korraga blogil olla
define("BLOG_MAX_CATEGORIES", 4);