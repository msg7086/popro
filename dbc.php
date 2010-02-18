<?php
	/*
	$dbhost = 'localhost';
	$dbuser = 'wlicpsc_wlicpsc';
	$dbpass = 'admsp2938945';
	$dbname = 'wlicpsc_btm';
	*/
	$dbhost = 'ftp.xdmhy.net';
	$dbuser = 'tosho';
	$dbpass = 'tosho';
	$dbname = 'tosho';
	
	/*
	$DB = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	
	if ($DB->connect_error)
		die('Connect Error (' . $DB->connect_errno . ') ' . $DB->connect_error);
	*/
require 'adodb5/adodb.inc.php';
require 'adodb5/adodb-exceptions.inc.php';
$db = &ADONewConnection('mysqli');
$db->Connect($dbhost, $dbuser, $dbpass, $dbname);
$db->Execute('SET NAMES UTF8;');
