<?php

class FluentQueryBuilder extends BaseModel
{
	private $aVars = array();

	private $strTableName = '';
	private $aQueryStack = array();
	private $iStackPos = 0;
	private $aJoinedTablesStack = array();

	private $DEBUG = false;

	private $tableConfInfoFile = 'private/config/tables.php';
	private $tableConf;

	public function __construct($strTableName) {
		$this->oDB = Registry::get('dbconnection');

		$strTableName = self::escape($strTableName);

		if($this->tableExists($strTableName)) {
			$this->strTableName = $strTableName;
		} else {
			$this->oDB = null;
			throw new Exception('Database table "' . $strTableName . '" doesn\'t exist', 27);
		}

		// For splitting query results into parent-children representing arrays
		if(is_file($this->tableConfInfoFile)) {
			require_once($this->tableConfInfoFile);

			$tables = Registry::get('tables');

			if($tables[$strTableName]) {
				$this->tableConf = $tables[$strTableName];
			} else {
				throw new Exception('table configuration array is missing information for ' . $strTableName, 40);
			}
		}
	}

	public function find($mFieldValue = null, $mFieldName = 'uid') {
		$this->aQueryStack[$this->iStackPos]['ACTION'] = 'SELECT';

		$uidChanger = $this->strTableName . '.uid AS ' . $this->strTableName . '_uid, ';
		$timeChanger = $this->strTableName . '.time AS ' . $this->strTableName . '_time, ';

		$this->aQueryStack[$this->iStackPos]['WHAT'] = $uidChanger . $timeChanger . $this->strTableName . '.*';
		$this->aQueryStack[$this->iStackPos]['TABLE'] = 'FROM ' . $this->strTableName;
		$this->aQueryStack[$this->iStackPos]['TABLENAME'] = $this->strTableName;

		if(is_array($mFieldValue)) {
			$this->aQueryStack[$this->iStackPos]['WHERE'] = 'WHERE ';
			foreach($mFieldValue as $i => $value) {
				$this->aQueryStack[$this->iStackPos]['WHERE'] .= $this->getTableName() . '.' . $mFieldName . ' = ' . $value;
				if($i < (count($mFieldValue) - 1)) {
					$this->aQueryStack[$this->iStackPos]['WHERE'] .= ' OR ';
				}
			}
		} else if($mFieldValue) {
			$this->aQueryStack[$this->iStackPos]['WHERE'] = 'WHERE ' . $this->getTableName() . '.' . $mFieldName . ' = ' . $mFieldValue;
		} else {
			$this->aQueryStack[$this->iStackPos]['WHERE'] = 'WHERE 1=1';
		}

		return $this;
	}

	public function join($mTable) {
		if(get_class($mTable) == get_class($this)) {
			$tableNameForJoin = $mTable->getTableName();

			$this->aQueryStack[$this->iStackPos]['TABLENAME'] = $mTable->strTableName;

			$uidChanger = $tableNameForJoin . '.uid AS ' . $tableNameForJoin . '_uid, ';
			$timeChanger = $tableNameForJoin . '.time AS ' . $tableNameForJoin . '_time, ';

			$this->aQueryStack[$this->iStackPos]['WHAT'] = $uidChanger . $timeChanger . $tableNameForJoin . '.*';
			$this->aQueryStack[$this->iStackPos]['JOIN'] = 'LEFT JOIN ' . $tableNameForJoin;
			$this->aQueryStack[$this->iStackPos]['JOINTABLE'] = $tableNameForJoin;

			$temp = explode('foreign_', $tableNameForJoin);
			$fieldNameForJoin = $this->getTableName() . '.foreign_' . $temp[0];

			$foreignFieldNameForJoin = $mTable->getTableName() . '.' . 'uid';

			$this->aQueryStack[$this->iStackPos]['ON'] = 'ON (' . $fieldNameForJoin . ' = ' . $foreignFieldNameForJoin . ')';
		}

		$this->iStackPos++;
		$this->aJoinedTablesStack[$this->iStackPos] = $mTable;

		return $this;
	}

	public function joinMore($mTable) {
		if(get_class($mTable) == get_class($this)) {

			$lastJoinedTable = $this->aQueryStack[$this->iStackPos]['TABLENAME'];
			$tableNameForJoin = $mTable->getTableName();
			$foreignFieldNameForJoin = 'parent_' . $lastJoinedTable;
			$foreignFieldNameWithTableForJoin = $tableNameForJoin . '.' . $foreignFieldNameForJoin;

			// Prüfen ob überhaupt das nötige Relationsfeld existiert
			if(!in_array($foreignFieldNameForJoin, $mTable->getTableConf())) {
				throw new Exception('Can\'t join, the field ' . $foreignFieldNameForJoin . ' is missing', 40);
			}

			$this->iStackPos++;
			$this->aJoinedTablesStack[$this->iStackPos] = $mTable;

			$this->aQueryStack[$this->iStackPos]['TABLENAME'] = $mTable->strTableName;

			$uidChanger = $tableNameForJoin . '.uid AS ' . $tableNameForJoin . '_uid, ';
			$timeChanger = $tableNameForJoin . '.time AS ' . $tableNameForJoin . '_time, ';

			$this->aQueryStack[$this->iStackPos]['WHAT'] = $uidChanger . $timeChanger . $tableNameForJoin . '.*';
			$this->aQueryStack[$this->iStackPos]['JOIN'] = 'LEFT JOIN ' . $tableNameForJoin;
			$this->aQueryStack[$this->iStackPos]['JOINTABLE'] = $tableNameForJoin;

			$fieldNameForJoin = $this->getTableName() . '.uid';
			$this->aQueryStack[$this->iStackPos]['ON'] = 'ON (' . $fieldNameForJoin . ' = ' . $foreignFieldNameWithTableForJoin . ')';
		}

		return $this;
	}

	public function joinMoreMM($mTable) {
		if(get_class($mTable) == get_class($this)) {
			if(count($this->aQueryStack) == 0) {
				$this->find();
			}

			$lastJoinedTable = $this->aQueryStack[$this->iStackPos]['TABLENAME'];
			$tableNameForJoin = $mTable->getTableName();
			$tableNameForJoinSingular = (substr($tableNameForJoin, -1, 1) == 's') ? substr($tableNameForJoin, 0, -1) : $tableNameForJoin;

			$mmTableName = $lastJoinedTable . '_relation_' . $tableNameForJoinSingular;

			$mmForeignBaseFieldName = 'mm_foreign_' . $lastJoinedTable;
			$mmForeignBaseFieldNameWithTable = $mmTableName . '.' . $mmForeignBaseFieldName;
			$mmForeignJoinFieldName = 'mm_foreign_' . $tableNameForJoinSingular;
			$mmForeignJoinFieldNameWithTable = $mmTableName . '.' . $mmForeignJoinFieldName;

			// Prüfen ob überhaupt das nötige Relationsfeld existiert
			$tableConstant = constant(strtoupper('tbl_' . $mmTableName));
			$mmTable = new self($tableConstant);
			if(!in_array($mmForeignBaseFieldName, $mmTable->getTableConf()) || !in_array($mmForeignJoinFieldName, $mmTable->getTableConf())) {
				throw new Exception('Can\'t join, the field ' . $mmForeignBaseFieldName . ' or ' . $mmForeignJoinFieldName . ' is missing', 52);
			}

			$this->iStackPos++;
			$this->aJoinedTablesStack[$this->iStackPos] = $mTable;

			$this->aQueryStack[$this->iStackPos]['TABLENAME'] = $mTable->strTableName;

			$uidChanger = $tableNameForJoin . '.uid AS ' . $tableNameForJoin . '_uid, ';
			$timeChanger = $tableNameForJoin . '.time AS ' . $tableNameForJoin . '_time, ';

			// foreign_xyz mit mm_foreign_xyz Inhalt der join-Tabelle überschreiben für späteres Auflösen
			// TODO: doppeltes joinen der gleichen Tabelle durch unterschiedliche join-Typen unterbinden?
			$this->aQueryStack[$this->iStackPos]['WHAT'] = $mmTableName . '.' . $mmForeignJoinFieldName . ' AS foreign_tag, ';

			$this->aQueryStack[$this->iStackPos]['WHAT'] .= $uidChanger . $timeChanger . $tableNameForJoin . '.*';

			$this->aQueryStack[$this->iStackPos]['JOIN'] = 'LEFT JOIN ' . $mmTableName;
			$this->aQueryStack[$this->iStackPos]['JOINTABLE'] = $tableNameForJoin;

			$fieldNameForJoin = $lastJoinedTable . '.uid';
			$this->aQueryStack[$this->iStackPos]['ON'] = 'ON (' . $fieldNameForJoin . ' = ' . $mmForeignBaseFieldNameWithTable . ')';

			$this->aQueryStack[$this->iStackPos]['JOIN2'] = ' LEFT JOIN ' . $tableNameForJoin;
			$this->aQueryStack[$this->iStackPos]['JOINTABLE2'] = $tableNameForJoin;

			$foreignFieldNameWithTable = $tableNameForJoin . '.' . 'uid';
			$this->aQueryStack[$this->iStackPos]['ON2'] = 'ON (' . $mmForeignJoinFieldNameWithTable . ' = ' . $foreignFieldNameWithTable . ')';
		} else {
			throw new Exception('Can\'t join, the parameter is not an object of type ', 51);
		}

		return $this;
	}

	// mit comma separated uid list andere Tabellen joinen
	// Benutzung bitte vermeiden lol (da nicht normalisiert und von DB nicht optimierbar)
	public function joinMoreByList($mTable) {
		if(get_class($mTable) == get_class($this)) {
			$tableNameForJoin = $mTable->getTableName();
			$temp = explode('foreign_', $tableNameForJoin);
			$fieldNameForJoin = 'foreign_' . $temp[0];
			$fieldNameWithTableForJoin = $this->getTableName() . '.' . $fieldNameForJoin;

			// Prüfen ob überhaupt das nötige Relationsfeld existiert
			if(!in_array($fieldNameForJoin, $this->tableConf)) {
				throw new Exception('Can\'t join, the field ' . $fieldNameWithTableForJoin . ' is missing', 050);
			}

			$this->iStackPos++;
			$this->aJoinedTablesStack[$this->iStackPos] = $mTable;

			$this->aQueryStack[$this->iStackPos]['TABLENAME'] = $mTable->strTableName;
			$this->aQueryStack[$this->iStackPos]['WHAT'] = $tableNameForJoin . '.uid AS ' . $tableNameForJoin . '_uid, ' . $tableNameForJoin . '.*';
			$this->aQueryStack[$this->iStackPos]['JOIN'] = 'JOIN ' . $tableNameForJoin;
			$this->aQueryStack[$this->iStackPos]['JOINTABLE'] = $tableNameForJoin;
			$this->aQueryStack[$this->iStackPos]['ON'] = 'ON (FIND_IN_SET(' . $mTable->getTableName() . '.' . 'uid, ' . $fieldNameWithTableForJoin . ') )';
		}

		return $this;
	}

	public function fetch() {
		$what = '';
		$where = '';
		$join = '';

		// join stack durchgehen & query zusammenbauen
		foreach($this->aQueryStack as $i => $stackRow) {
			if(isset($stackRow['JOIN'])) {
				$join .= $stackRow['JOIN'] . ' ' . $stackRow['ON'] . ' ';
				if(isset($stackRow['JOIN2'])) {
					$join .= $stackRow['JOIN2'] . ' ' . $stackRow['ON2'] . ' ';
				}
			}
			if(isset($stackRow['WHERE'])) {
				$where = $stackRow['WHERE'] . ' ';
			}
			$what .= $stackRow['WHAT'];
			if($i < count($this->aQueryStack) - 1) {
				$what .= ', ';
			} else {
				$what .= ' ';
			}
		}

		$startRow = $this->aQueryStack[0];
		$strQuery = $startRow['ACTION'] . ' ' . $what . $startRow['TABLE'] . ' ' . $join . $where;

		if($this->DEBUG) {
			$debugQuery = $this->oDB->formatStatement($strQuery) . '<br />';
			echo($debugQuery);
		}

		// Query absetzen
		$flatResult = $this->oDB->oquery($strQuery);

		// von *-Umwandlung übrige uid und time entfernen
		if(count($flatResult)) {
			foreach($flatResult as $i => $singleResult) {
				unset($flatResult[$i]->uid);
				unset($flatResult[$i]->time);
			}
		}

		// in relationales Objekt umwandeln
		$result = $this->getRelationalObject($flatResult);

		return $result;
	}

	private function getRelationalObject($result) {
		if($result && ($this->iStackPos > 0)) {
			// Objekt nur mit Keys die diese Tabelle auch besitzt erzeugen
			$slicedResults = array();
			$slicedResults[0] = $this->getResultIntersectOfThisTableConfiguration($this, $result);
			foreach($this->aJoinedTablesStack as $iJoinNum => $joinedTable) {
				// Diese Filterung auch für die gejointen Tabellen vornehmen
				$foreignTableKey = /*'foreign_'.*/
					$joinedTable->getTableName();
				$slicedResults[$foreignTableKey] = $this->getResultIntersectOfThisTableConfiguration($joinedTable, $result);
			}
			// aus den nach Tabellenfeldern zerstückelten Array die Relationen zusammenbauen
			$ormMergedResult = $this->mergeDownResults($slicedResults);
			$ormResult = (object)$ormMergedResult;
		} else {
			$ormResult = (object)$result;
		}

		return $ormResult;
	}


	private function mergeDownResults(&$aResults) {
		// awesome magic comes here.

		// search other results for parent_xyz and append them to results[0]
		foreach($aResults as $mTable => $oResultSet) {
			foreach($oResultSet as $iKey => $oSingleResult) {
				foreach($oSingleResult as $strKey => $mValue) {
					if(strpos($strKey, 'foreign_') === 0) {
						$strPoolName = str_replace('foreign_', '', $strKey);
						if($mValue) {
							$aForeignUidList = explode(',', $mValue);
							$mForeignValues = array();
							foreach($aForeignUidList as $iKey) {
								$mForeignValues[$iKey] = $aResults[$strPoolName][$iKey];
							}
							$oSingleResult->$strPoolName = $mForeignValues;
							unset($oSingleResult->$strKey);
						}
					}
				}
				// ignore first result subset; it cant have a parent
				if($mTable === 0) {
					continue;
				}
				// attach to parents if parent_xyz key is present
				$strParentKey = 'parent_' . $this->strTableName;
				if(isset($oSingleResult->$strParentKey)) {
					// get uid of parent
					$iParentUid = $oSingleResult->$strParentKey;
					// create child element on this result
					if(!isset($aResults[0][$iParentUid]->$mTable)) {
						$aResults[0][$iParentUid]->$mTable = array($oSingleResult->uid => $oSingleResult);
					} else {
						$aResults[0][$iParentUid]->{$mTable}[$oSingleResult->uid] = $oSingleResult;
					}
					// parent_xyz Eintrag entfernen, nachdem er aufgelöst wurde
					unset($aResults[0][$iParentUid]->{$mTable}[$oSingleResult->uid]->$strParentKey);
				}
			}
		}

		return ($aResults[0]);
	}


	private function getResultIntersectOfThisTableConfiguration(FluentQueryBuilder $table, $result) {
		if($table->tableConf) {
			$ormResult = array();
			foreach($result as $singleResult) {
				$intersect = array_intersect_key((array)$singleResult, array_flip($table->tableConf));
				// wenn intersect leer ist (wenn Datensatz keine Relation hatte)
				if(!array_filter($intersect)) {
					continue;
				}

				unset($intersect['uid']);
				$thisResultUidKey = $table->getTableName() . '_uid';
				$intersect['uid'] = $singleResult->$thisResultUidKey;

				$thisResultTimeKey = $table->getTableName() . '_time';
				if(isset($singleResult->$thisResultTimeKey)) {
					unset($intersect['time']);
					$intersect['time'] = $singleResult->$thisResultTimeKey;
				}

				$iDataUid = $singleResult->$thisResultUidKey;

				// Wenn Datensatz nocheinmal vorhanden ist, muss er durch einen join kommen -> Fremd-Uids zu Liste formen
				if(isset($ormResult[$iDataUid])) {
					$ormResult[$iDataUid] = $this->mergeForeignUids($ormResult[$iDataUid], ((object)$intersect));
				} else {
					$ormResult[$iDataUid] = ((object)$intersect);
				}
			}

			return $ormResult;
		}
	}

	private function mergeForeignUids(&$existingIntersect, $newIntersect) {
		$intersectDiff = array_diff_assoc((array)$existingIntersect, (array)$newIntersect);
		// falls foreign key + unterschiedliche Werte, uid list erstellen und im Original setzen
		if(count($intersectDiff)) {
			$strKeyToMerge = key($intersectDiff);
			$existingIntersect->$strKeyToMerge .= ',' . $newIntersect->$strKeyToMerge; //.current($intersectDiff);
		}

		return $existingIntersect;
	}

	public static function escape($strString) {
		return Registry::get('dbconnection')->escape($strString);
	}

	private function tableExists($tablename) {
		$strQuery = 'SHOW TABLES';
		$result = $this->oDB->query($strQuery);

		$bTableExists = false;
		if(count($result)) {
			foreach($result as $i => $aTable) {
				if(current($aTable) == $tablename) {
					$bTableExists |= true;
				}
			}
		} else {
			throw new Exception('Database has no tables', 27);
		}

		return (boolean)$bTableExists;
	}

	public function getTableName() {
		return $this->strTableName;
	}

	private function getTableConf() {
		return $this->tableConf;
	}

	public function debug($bOnOrOff = null) {
		if(is_bool($bOnOrOff)) {
			$this->DEBUG = $bOnOrOff;
		} else if($bOnOrOff == null) { // bei debug() davon ausgehen dass man aktivieren will
			$this->DEBUG = true;
		} else {
			throw new Exception('debug only accepts boolean', 034);
		}
	}

	public function __call($strFuncName, $mArgs) {
		echo($strFuncName);
	}

	public function __get($strIndex) {
		if(isset($this->aVars[$strIndex])) {
			return $this->aVars[$strIndex];

		} else {
			return null;
		}
	}

	public function __set($strIndex, $value) {
		$this->aVars[$strIndex] = $value;
	}
}

?>