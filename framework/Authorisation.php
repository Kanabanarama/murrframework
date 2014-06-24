<?php

/*
 * Authorisation.class.php
 *
 * <René Lantzsch 30.01.2010> Erstversion
 *
 * @author René Lantzsch <renelantzsch@web.de>
 * @since 30.01.2010
 * @version 1.1.0
 */

class Authorisation
{
	private static $instance = null;
	private $oDB;
	private $aVars;

	function __construct() {
		$this->oDB = Registry::get('dbconnection');
		//if(!isset($_SESSION)){
			session_start();
		//	var_dump('session started');
		//}

	}

	public static function getInstance() {
		if(self::$instance === null) self::$instance = new self;
		return self::$instance;
	}

	function login($name, $password) {
		$strQuery = sprintf("SELECT uid, username, email
    						FROM %s
    						WHERE (username='%s' OR email='%s') AND password=MD5('booksalt_%s') AND active = 1
    						LIMIT 1",
			TBL_USER,
			$this->oDB->escape($name),
			$this->oDB->escape($name),
			$this->oDB->escape($password));

		$result = $this->oDB->query($strQuery);

		if(count($result) == 1) {
			$this->uid = $result[0]['uid'];
			$this->email = $result[0]['email'];
			$this->updateSession();
			return true;
		} else {
			return false;
		}
	}

	function is_logged_in() {
		$strQuery = sprintf("SELECT uid, email
							FROM %s
							WHERE session = '%s'
							LIMIT 1",
			TBL_USER,
			$this->oDB->escape(session_id()));

		$result = $this->oDB->query($strQuery);

		$this->uid = $result[0]['uid'];
		$this->email = $result[0]['email'];

		return (count($result) == 1);
	}

	function get_rights() {
		$strQuery = sprintf("SELECT privileges
							FROM %s
							WHERE session = '%s'
							LIMIT 1",
			TBL_USER,
			$this->oDB->escape(session_id()));

		list($result) = $this->oDB->query($strQuery);
		$privileges = Array('admin' => 0);
		if(count($result)) {
			$privileges['admin'] = (($result['privileges'] & 2) == true);
		}
		return $privileges;
	}

	public function logout()
	{
		//$this->set_offline_status($this->uid);
		$strQuery = sprintf("UPDATE %s
 							SET session=NULL
 							WHERE session='%s'",
			TBL_USER,
			$this->oDB->escape(session_id()));

		$this->oDB->query($strQuery);

		// Session Cookie löschen
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}

		// Session löschen.
		session_destroy();
	}

	private function updatesession() {
		$strQuery = sprintf("UPDATE %s
							SET session = '%s', lastlogin = NOW(), ip_addr = '%s'
							WHERE uid = '%s'",
			TBL_USER,
			$this->oDB->escape(session_id()),
			$_SERVER['REMOTE_ADDR'],
			$this->oDB->escape($this->uid));

		$this->oDB->query($strQuery);
	}

	public function set_online_status() {
		$strQuery = sprintf("INSERT INTO %s
							(ip,uid,timestamp,session)
							VALUES ('%s','%s','%s','%s')
							ON DUPLICATE KEY UPDATE
							uid = '%s', timestamp = '%s'",
			TBL_USERONLINE,

			$this->oDB->escape($_SERVER['REMOTE_ADDR']),
			intval($this->uid),
			$this->oDB->escape(date('Y-m-d H:i:s')),
			$this->oDB->escape(session_id()),

			intval($this->uid),
			$this->oDB->escape(date('Y-m-d H:i:s')));

		$this->oDB->query($strQuery);

		$strQuery = sprintf("DELETE FROM %s 
							WHERE UNIX_TIMESTAMP(timestamp) < (UNIX_TIMESTAMP(NOW())-300)",
			TBL_USERONLINE);

		$this->oDB->query($strQuery);
	}

	public function set_offline_status($iUserID) {
		$strQuery = sprintf("DELETE FROM %s 
							WHERE uid = %s",
			TBL_USERONLINE,
			intval($iUserID));

		$this->oDB->query($strQuery);
	}

	public function __get($strIndex) {
		if($this->aVars[$strIndex]) {
			return $this->aVars[$strIndex];
		} else {
			$this->is_logged_in();
			return $this->aVars[$strIndex];
		}
	}

	public function __set($strIndex, $value) {
		$this->aVars[$strIndex] = $value;
	}
}

?>