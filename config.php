<?php

//Andmebaasi konstandid
define ("MYSQL_SERVER_NAME", "localhost"); 
define ("MYSQL_SERVER_USER", "test"); 
define ("MYSQL_SERVER_PASS", ""); 
define ("MYSQL_SERVER_BASE", ""); 

//Andmebaasiga ühendamine
$dbconnect=mysql_connect(MYSQL_SERVER_NAME,MYSQL_SERVER_USER,MYSQL_SERVER_PASS) or die("1:".mysql_error()); 
mysql_select_db(MYSQL_SERVER_BASE) or die("2:".mysql_error());

// Lokaal ja tähetabelid
mysql_set_charset('utf8');
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
setlocale(LC_ALL,'et_EE.UTF-8');