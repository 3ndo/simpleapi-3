#!/usr/bin/php
<?php
	require_once(dirname(__FILE__).'/SimpleApiClient.class.php');

	$simpleApiClient = new SimpleApiClient();
	$simpleApiClient->authenticate('username', 'password');
	var_dump($simpleApiClient->getClients());/*
	$simpleApiClient->addClient(array(
		'name' => 'Петя',
		'phone' => '123'
	));*/
	$simpleApiClient->updateClient(array(
		'id' => 1,
		'name' => 'Петя',
		'phone' => '1234'
	));
	var_dump($simpleApiClient->getClients());
