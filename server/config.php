<?php
namespace SimpleApi{
	session_start();
	define('MYSQL_HOST', 'localhost');
	define('MYSQL_USER', 'root');
	define('MYSQL_PASSWORD', 'cfvjrbk');
	define('MYSQL_DATABASE', 'simpleapi');

	define('DEBUG', true);
	define('LOG_FILE', dirname(__FILE__).'/main.log');

	define('ROLE_ADMIN', 1);
	global $roleAllowedMethods;
	$roleAllowedMethods = array(
		ROLE_ADMIN => array('.*')
	);
}