<?php
	class SimpleApiBase{
		private $ch;
		private $cookieFile;
		protected $token;
		protected $host = 'http://127.0.0.1/simple_api/server/simpleapi.php';

		function __construct(){
			$this->initCurl();
		}
		function __destruct(){
			$this->releaseCurl();
		}

		private function initCurl(){
			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->ch, CURLOPT_HEADER, 0);

			$this->cookieFile = '/tmp/'.md5(time());
			curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookieFile);
			curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
		}

		private function releaseCurl(){
			curl_close($this->ch);
			@unlink($this->cookieFile);
		}

		protected function perform($method, $params = array()){
			$params['token'] = $this->token;
			curl_setopt($this->ch, CURLOPT_URL, $this->host.'?'.urlencode($method));
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);

			$data = curl_exec($this->ch);

			return json_decode($data, true);
		}

		protected function hashPassword($password){
			return md5($password.md5($password.$password));
		}
	}

	class SimpleApiClient extends SimpleApiBase{
		public function __construct($options = array()){
			parent::__construct();
			if(@$options['host'])
				$this->host = $options['host'];
		}

		/*
		 * Authenticates user
		 * This method should be called before any other methods.
		 */
		public function authenticate($login, $password){
			$authInfo = $this->perform('auth', array(
				'login' => $login,
				'password' => $this->hashPassword($password)
			));

			if(@$authInfo['error'])
				return false;
			else{
				$this->token = $authInfo['token'];
				return true;
			}
		}

		/*
		 * Retrieves all clients
		 * @return array of clients
		 */
		public function getClients(){
			$clients = $this->perform('getClients');

			if(@$clients['error'])
				return false;
			else{
				return $clients;
			}
		}

		/*
		 * Add a client
		 * @param array of new client's parameters (name, phone, etc.)
		 * @return true or false
		 */
		public function addClient($fields){
			$result = $this->perform('addClient', $fields);

			if(@$result['error'])
				return false;
			else{
				return true;
			}
		}

		/*
		 * Update specified client's information
		 * @param array of client's new parameters
		 * @return true or false
		 */
		public function updateClient($fields){
			$result = $this->perform('updateClient', $fields);

			if(@$result['error'])
				return false;
			else{
				return true;
			}
		}
	}
