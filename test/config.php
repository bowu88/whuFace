<?php
$server='localhost';
$username='root';
$password='';
$database='whuface';
$conn=mysql_connect($server,$username,$password)
or die("Cann't open mysql");
mysql_select_db($database, $conn)
or die("Cann't open database");
mysql_query("SET NAMES 'UTF8'"); 
mysql_query("SET CHARACTER SET UTF8"); 
mysql_query("SET CHARACTER_SET_RESULTS=UTF8'");
//error_reporting(0); 
?>
