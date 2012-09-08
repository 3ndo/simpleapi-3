<?php
namespace SimpleApi{
	require_once(dirname(__FILE__).'/config.php');
	require_once(dirname(__FILE__).'/Logger.class.php');

	class SimpleApiServerDB{
		private $link;
		private $lastQueryResult;

		public function __construct(){
			$this->link = new \mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
			$this->link->set_charset('utf8');
		}

		public function query($q, $replace = array()){
			foreach($replace as $k=>$i){
				$escaped = $this->link->escape_string($i);
				if(is_string($i))
					$escaped = "'$escaped'";

				$q = str_replace($k, $escaped, $q);
			}

			Logger::Log("QUERY:\n$q");

			$this->lastQueryResult = $this->link->query($q);
			return $this->lastQueryResult === true ? true : $this;
		}

		public function fetchAll(){
			for ($res = array(); $tmp = $this->lastQueryResult->fetch_assoc();)
				$res[] = $tmp;
			return $res;
		}
	}

	class SimpleApiServerBase{
		protected function generateError($message){
			return array(
				'error' => $message
			);
		}

		protected function checkAuth($token){
			if($token && $token === $_SESSION['auth']['token'])
				return true;
			else
				return $this->generateError('authenticate token is wrong');
		}

		protected function checkRoleAccess($method){
			global $roleAllowedMethods;
			foreach($roleAllowedMethods[$_SESSION['auth']['role']] as $i)
				if(preg_match("#$i#", $method))
					return true;
			return false;
		}

		protected function checkSpecification($fields, $requiredKeys){
			foreach($requiredKeys as $i)
				if(!isset($fields[$i]))
					return $this->generateError('specification test failed');
			return true;
		}
	}

	class SimpleApiServer extends SimpleApiServerBase{
		private $db;
		private $clientTableFields;

		public function __construct(){
			$this->db = new SimpleApiServerDB();
			$this->clientTableFields = array('name', 'phone');
		}

		public function auth($fields){
			if(($specificationResult = $this->checkSpecification($fields, array('login', 'password'))) !== true)
				return $specificationResult;

			$users = $this->db->query(
				"SELECT role FROM users WHERE login = :login AND password = :password LIMIT 1",
				array(
					':login' => $fields['login'],
					':password' => $fields['password']
				)
			)->fetchAll();
			if(count($users)){
				$token = md5(time().mt_rand(0, 100000));
				$role = $users[0]['role'];

				$_SESSION['auth']['token'] = $token;
				$_SESSION['auth']['role'] = $role;

				return array(
					'token' => $token,
					'role' => $role
				);
			}else{
				return $this->generateError('incorrect login/password');
			}
		}

		public function getClients($fields){
			if(($authResult = $this->checkAuth($fields['token'])) !== true)
				return $authResult;
			if(($roleAccessResult = $this->checkRoleAccess(__FUNCTION__)) !== true)
				return $roleAccessResult;

			$clients = $this->db->query("SELECT * FROM clients")->fetchAll();
			return $clients;
		}

		public function addClient($fields){
			if(($authResult = $this->checkAuth($fields['token'])) !== true)
				return $authResult;
			if(($roleAccessResult = $this->checkRoleAccess(__FUNCTION__)) !== true)
				return $roleAccessResult;
			if(($specificationResult = $this->checkSpecification($fields, array('name'))) !== true)
				return $specificationResult;

			$tableFields = $this->clientTableFields;
			$rawValues = array();
			foreach($tableFields as $k=>$i)
				if(isset($fields[$i]))
					$rawValues[':'.$i] = $fields[$i];
				else
					unset($tableFields[$k]);

			$columns = implode(',', $tableFields);
			$values = ':'.implode(',:', $tableFields);
			$result = $this->db->query(
				"INSERT INTO clients ($columns, add_time) VALUES ($values, UNIX_TIMESTAMP(NOW()))",
				$rawValues
			);
			return $result === true;
		}

		public function updateClient($fields){
			if(($authResult = $this->checkAuth($fields['token'])) !== true)
				return $authResult;
			if(($roleAccessResult = $this->checkRoleAccess(__FUNCTION__)) !== true)
				return $roleAccessResult;
			if(($specificationResult = $this->checkSpecification($fields, array('id'))) !== true)
				return $specificationResult;

			$tableFields = $this->clientTableFields;
			$rawValues = array();
			foreach($tableFields as $k=>$i)
				if(isset($fields[$i]))
					$rawValues[':'.$i] = $fields[$i];
				else
					unset($tableFields[$k]);

			$set = array();
			foreach($tableFields as $i)
				$set[] = "$i = :$i";
			$set = implode(',', $set);

			$rawValues[':id'] = $fields['id'];
			$result = $this->db->query(
				"UPDATE clients SET $set WHERE id = :id",
				$rawValues
			);
			return $result === true;
		}
	}
}