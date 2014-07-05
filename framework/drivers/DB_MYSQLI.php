<?php
/**
 * DB driver class
 */

class DB_MYSQLI
{
	static $queries_total = 0;
	private $db_time = 0;
	private static $instance = null;
	private $iDbStatus = false;
	private $rDbConnection;

	private function __construct() {
		$this->rDbConnection = new MySQLi(MYSQL_HOST, MYSQL_USER, MYSQL_PSSWD);
		if (mysqli_connect_errno()) {
			throw new Exception('Could not connect to database.', 1);
		}

		/* change character set to utf8 */
		if (!$this->rDbConnection->set_charset("utf8")) {
			throw new Exception('Could not set character set utf8. ('.$this->rDbConnection->error.')', 1);
		}

		$bSelectResult = $this->selectDb();
		/*if(!$bSelectResult) {
			throw new Exception('Database could not be selected.', 1);
		}*/

		$this->iDbStatus = ($bSelectResult == true) ? 1 : false;

		return $bSelectResult;
	}

	private function selectDb() {
		$bSelectResult = $this->rDbConnection->select_db(MYSQL_DATABASE);
		return $bSelectResult;
	}

	public function getStatus() {
		return $this->iDbStatus;
	}

	public static function getInstance() {
		if( self::$instance === null ) self::$instance = new self;
		return self::$instance;
	}

	public function query($statement) {
		if($this->iDbStatus == false) {
			$this->selectDb();
		}

		$starttime = microtime();
		$endtime = microtime();
		$resource = $this->rDbConnection->query($statement);

		self::$queries_total++;

		$this->calc_query_time($starttime,$endtime);
		if(!is_bool($resource)) {
			$result = array();
			while ($row = $resource->fetch_array()) {
				$result[] = $row;
			}
		} else {
			$result = $resource;
		}

		if($result === false) {
			echo('ERROR: '.$this->formatStatement($statement));
		}

		if(($result) && (!empty($result))) { return $result; }
		else { return null; }
	}

	public function oquery($statement) {
		$starttime = microtime();
		$resource = $this->rDbConnection->query($statement);
		if ($this->rDbConnection->errno) {
			if ($this->rDbConnection->errno == 1062) {
				// Datensatz schon vorhanden
				return false;
			} else {
				die($this->rDbConnection->error);
			}
		}

		self::$queries_total++;
		$endtime = microtime();
		$this->calc_query_time($starttime,$endtime);

		if(!is_bool($resource)) {
			$result = array();
			while ($row = $resource->fetch_object()) {
				$result[] = $row;
			}
		} else {
			$result = $resource;
		}

		if(($result) && (!empty($result))) { return $result; }
		else { return null; }
	}

	public function escape($strToEscape) {
		return $this->rDbConnection->real_escape_string($strToEscape);
	}

	public function get_last_id() {
		return $this->rDbConnection->insert_id;
	}

	public function get_last_error() {
		return $this->rDbConnection->error;
	}

	public function get_last_errno() {
		return $this->rDbConnection->errno;
	}

	public function get_query_time() {
		return $this->db_time;
	}

	private function calc_query_time($starttime,$endtime) {
		$this->db_time = '<span style="color:red;">'.sprintf("%7.6f", ($endtime - $starttime + $this->db_time)).'</span> ';
	}

	public function get_queries_total() {
		return ($this->queries_total);
	}

	public function formatStatement($strQuery) {
		preg_match_all('/([A-Z]{2,})/sm', $strQuery, $aSQLKeywords);
		foreach($aSQLKeywords[1] as $i => $strSQLKeyword) {
			$strQuery = str_replace($strSQLKeyword, '<span style="color:blue;">'.$strSQLKeyword.'</span>', $strQuery);
		}
		$strQuery .= '<br />';

		return $strQuery;
	}

}

?>
