<?php
	require_once(dirname(__FILE__).'/SimpleApiServer.class.php');
	$server = new SimpleApi\SimpleApiServer();

	$result = false;
	if(isset($_GET['auth'])){
		$result = $server->auth($_POST);
	}elseif(isset($_GET['getClients'])){
		$result = $server->getClients($_POST);
	}elseif(isset($_GET['addClient'])){
		$result = $server->addClient($_POST);
	}elseif(isset($_GET['updateClient'])){
		$result = $server->updateClient($_POST);
	}

	die(json_encode($result));