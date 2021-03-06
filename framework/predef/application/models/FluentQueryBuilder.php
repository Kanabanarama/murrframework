<?php

/**
 * FluentQueryBuilder
 * Murrmurr framework
 *
 * builds mysql queries by method chaining
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @version 3.1.4
 */

class FluentQueryBuilder extends BaseModel
{
	private $aVars = array();

	private $strTableName = '';
	private $aQueryStack = array();
	private $iStackPos = 0;
	private $aJoinedTablesStack = array();

	private $DEBUG = false;

	private $tableConfInfoFile = 'application/config/tables.php';
	private $tableConf;

	private $parentRelation = array();

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

			if(isset($tables[$strTableName])) {
				$this->tableConf = $tables[$strTableName];
			} else {
				throw new Exception('table configuration array is missing information for ' . $strTableName, 40);
			}
		}
	}

	public function save($oData, $bDistinct = false) {
		$aObjVars = get_object_vars($oData);

		// Falls vorher in der Kette eine Relation für den zu speichernden Datensatz definiert wurde
		if(isset($this->parentRelation[$this->iStackPos])) {
			$parentKey = 'parent_'.$this->parentRelation[$this->iStackPos]['table'];
			$parentUid = $this->parentRelation[$this->iStackPos]['uid'];
			$aObjVars[$parentKey] = $parentUid;
			empty($this->parentRelation[$this->iStackPos]);
		}

		if(empty($aObjVars)) {
			$strRows = 'uid';
			$strValues = 'null';
		} else {
			$strRows = implode(', ', array_keys($aObjVars));
			$strValues = '"'.implode('", "', array_values($aObjVars)).'"';
		}

		if($bDistinct) {
			$strAction = 'INSERT IGNORE INTO ';
		} else {
			$strAction = 'INSERT INTO ';
		}

		$strQuery = $strAction
			.$this->strTableName
			.' ('.$strRows.') '
			.'VALUES'
			.' ('.$strValues.')';
			//.' ON DUPLICATE KEY UPDATE '
			//.'VALUES'
			//.' ('.$strValues.')';

		if($this->DEBUG) {
			$debugQuery = $this->oDB->formatStatement($strQuery) . '<br />';
			echo($debugQuery);
		}

		$result = $this->oDB->oquery($strQuery);

		return $result;
	}

	public function update($oUpdate) {
		$aObjVars = get_object_vars($oUpdate);

		// Falls vorher in der Kette eine Relation für den zu speichernden Datensatz definiert wurde
		if(isset($this->parentRelation[$this->iStackPos])) {
			$parentKey = 'parent_'.$this->parentRelation[$this->iStackPos]['table'];
			$parentUid = $this->parentRelation[$this->iStackPos]['uid'];
			$aObjVars[$parentKey] = $parentUid;
			empty($this->parentRelation[$this->iStackPos]);
		}

		$strUpdateFields = '';
		//$aKeys = array_keys($aObjVars);
		//$aValues = array_values($aObjVars);

		$i = 0;
		foreach($aObjVars as $strKey => $strValue) {
			$i++;
			$strUpdateFields .= $this->escape($strKey).' = "'.$this->escape($strValue).'"';
			if($i < count($aObjVars)) { $strUpdateFields .= ', '; }
		}

		$strWhere = 'WHERE uid = '. $oUpdate->uid;
		$this->strQuery = 'UPDATE '
			.$this->strTableName.' '
			.'SET '
			.$strUpdateFields.' '
			.$strWhere;

		if($this->DEBUG) {
			$debugQuery = $this->oDB->formatStatement($this->strQuery) . '<br />';
			echo($debugQuery);
		}

		$result = $this->oDB->query($this->strQuery);

		return $result;
	}

	public function delete($oDeletion) {
		$strWhere = 'WHERE uid = '. $oDeletion->uid;
		$this->strQuery = 'DELETE FROM '
			.$this->strTableName.' '
			.$strWhere;

		if($this->DEBUG) {
			$debugQuery = $this->oDB->formatStatement($this->strQuery) . '<br />';
			echo($debugQuery);
		}

		$result = $this->oDB->query($this->strQuery);

		return $result;
	}

	public function relate($targetUid = null, $targetTableObject) {
		$this->parentRelation[$this->iStackPos]['uid'] = intval($targetUid);
		if(!$targetTableObject instanceof self) {
			throw new Exception('2nd parameter of relate() must be a '.get_class_name(self).' object.', 034);
		} else {
			$this->parentRelation[$this->iStackPos]['table'] = $targetTableObject->getTableName();
		}

		return $this;
	}

	public function relateMM($originUid, $targetTableObject, $targetUid = null) {
		if(!$targetTableObject instanceof self) {
			throw new Exception('2nd parameter of relateMM() must be a '.get_class_name(self).' object.', 034);
		} else {
			if(!$targetUid) {
				$targetUid = $targetTableObject->getLastId();
			}

			$strRows = 'mm_foreign_'.$this->getTableName().','.'mm_foreign_'.$targetTableObject->getTableName();
			$strValues = '"'.$originUid.'", "'.$targetUid.'"';
			$strQuery = 'INSERT INTO '
				.$this->strTableName.'_relation_'.$targetTableObject->getTableName()
				.' ('.$strRows.') '
				.'VALUES'
				.' ('.$strValues.')';

			if($this->DEBUG) {
				$debugQuery = $this->oDB->formatStatement($strQuery) . '<br />';
				echo($debugQuery);
			}

			$result = $this->oDB->oquery($strQuery);
		}

		return $result;
	}

	public function find($mFieldValue = null, $mFieldName = 'uid') {
		$this->aQueryStack[$this->iStackPos]['ACTION'] = 'SELECT';

		$uidChanger = $this->strTableName . '.uid AS ' . $this->strTableName . '_uid, ';
		$updatedChanger = $this->strTableName . '.updated AS ' . $this->strTableName . '_updated, ';
		$createdChanger = $this->strTableName . '.created AS ' . $this->strTableName . '_created, ';

		$this->aQueryStack[$this->iStackPos]['WHAT'] = $uidChanger . $updatedChanger . $createdChanger . $this->strTableName . '.*';
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
			$this->aQueryStack[$this->iStackPos]['WHERE'] = 'WHERE ' . $this->getTableName() . '.' . $mFieldName . ' = \'' . $mFieldValue . '\'';
		} else {
			$this->aQueryStack[$this->iStackPos]['WHERE'] = 'WHERE 1=1';
		}

		return $this;
	}

	/*public function join($mTable) {
		if(get_class($mTable) == get_class($this)) {
			$tableNameForJoin = $mTable->getTableName();

			$this->aQueryStack[$this->iStackPos]['TABLENAME'] = $mTable->strTableName;

			// TODO: Hinüberretten von Feldern auslagern
			$uidChanger = $tableNameForJoin . '.uid AS ' . $tableNameForJoin . '_uid, ';
			$updatedChanger = $tableNameForJoin . '.updated AS ' . $tableNameForJoin . '_updated, ';
			$createdChanger = $tableNameForJoin . '.created AS ' . $tableNameForJoin . '_created, ';

			$this->aQueryStack[$this->iStackPos]['WHAT'] = $uidChanger . $updatedChanger . $createdChanger . $tableNameForJoin . '.*';
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
	}*/

	public function joinOne($mTable) {
		//$this->iStackPos++;

		$this->joinMore($mTable);
		$this->aQueryStack[$this->iStackPos]['LIMIT'] = 'LIMIT 1';

		return $this;
	}

	public function joinMore($mTable) {
		if(get_class($mTable) == get_class($this)) {
			//var_dump($mTable->getTableName());
			/*if($mTable->getTableName() === 'bookedition') {
				var_dump($this->aQueryStack,$this->iStackPos);
			}*/

			//var_dump($this->aQueryStack, $this->iStackPos);
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
			$updatedChanger = $tableNameForJoin . '.updated AS ' . $tableNameForJoin . '_updated, ';
			$createdChanger = $tableNameForJoin . '.created AS ' . $tableNameForJoin . '_created, ';

			$this->aQueryStack[$this->iStackPos]['WHAT'] = $uidChanger . $updatedChanger . $createdChanger . $tableNameForJoin . '.*';
			$this->aQueryStack[$this->iStackPos]['JOIN'] = 'LEFT JOIN ' . $tableNameForJoin;
			$this->aQueryStack[$this->iStackPos]['JOINTABLE'] = $tableNameForJoin;

			///$fieldNameForJoin = $this->getTableName() . '.uid';
			$fieldNameForJoin = $lastJoinedTable . '.uid';
			$this->aQueryStack[$this->iStackPos]['ON'] = 'ON (' . $fieldNameForJoin . ' = ' . $foreignFieldNameWithTableForJoin . ')';

			/*if($mTable->getTableName() === 'bookedition') {
				var_dump($this->aQueryStack[$this->iStackPos]);die;
			}*/
		}

		return $this;
	}

	public function joinBy($fieldName, $mTable) {
		if(get_class($mTable) == get_class($this)) {
			if(strpos($fieldName,'.') === false) {
				$lastJoinedTable = $this->aQueryStack[$this->iStackPos]['TABLENAME'];
				$tableNameForJoin = $mTable->getTableName();
				//$tableNameForAlias = $tableNameForJoin;
				$ownFieldNameWithTableForJoin = $lastJoinedTable . '.' . $fieldName;
			} else {
				$tempField = explode('.', $fieldName);
				//$tableNameForJoin = $tempField[0];
				$tableNameForJoin = $mTable->getTableName();
				$ownFieldNameWithTableForJoin = $fieldName;
			}

			// Prüfen ob überhaupt das nötige Relationsfeld existiert
			/*if(!in_array($ownFieldNameWithTableForJoin, $this->getTableConf())) {
				throw new Exception('Can\'t join, the field ' . $ownFieldNameWithTableForJoin . ' is missing', 40);
			}*/

			$this->iStackPos++;
			$this->aJoinedTablesStack[$this->iStackPos] = $mTable;
			$this->aQueryStack[$this->iStackPos]['JOINBYFIELD'] = $ownFieldNameWithTableForJoin;
			$this->aQueryStack[$this->iStackPos]['TABLENAME'] = $mTable->strTableName;

			$uidChanger = $tableNameForJoin . '.uid AS ' . $tableNameForJoin . '_uid, ';
			$updatedChanger = $tableNameForJoin . '.updated AS ' . $tableNameForJoin . '_updated, ';
			$createdChanger = $tableNameForJoin . '.created AS ' . $tableNameForJoin . '_created, ';

			$this->aQueryStack[$this->iStackPos]['WHAT'] = $uidChanger . $updatedChanger . $createdChanger . $tableNameForJoin . '.*';
			$this->aQueryStack[$this->iStackPos]['JOIN'] = 'LEFT JOIN ' . $tableNameForJoin;
			$this->aQueryStack[$this->iStackPos]['JOINTABLE'] = $tableNameForJoin;

			$fieldNameForJoin = $mTable->getTableName() . '.uid';
			$this->aQueryStack[$this->iStackPos]['ON'] = 'ON (' . $ownFieldNameWithTableForJoin . ' = ' . $fieldNameForJoin . ')';
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

			// mm Tabelle ermitteln (Prüfung ob Konstante vorhanden, ansonsten die zusammengesetzten Tabellennnamen)
			$tableConstantName = strtoupper('tbl_' . $mmTableName);
			$tableName = (defined($tableConstantName)) ? constant($tableConstantName) : $mmTableName;
			$mmTable = new self($tableName);
			// Prüfen ob überhaupt das nötige Relationsfeld existiert
			if(!in_array($mmForeignBaseFieldName, $mmTable->getTableConf()) || !in_array($mmForeignJoinFieldName, $mmTable->getTableConf())) {
				throw new Exception('Can\'t join, the field ' . $mmForeignBaseFieldName . ' or ' . $mmForeignJoinFieldName . ' is missing', 52);
			}

			$this->iStackPos++;
			$this->aJoinedTablesStack[$this->iStackPos] = $mTable;

			$this->aQueryStack[$this->iStackPos]['TABLENAME'] = $mTable->strTableName;

			$uidChanger = $tableNameForJoin . '.uid AS ' . $tableNameForJoin . '_uid, ';
			$updatedChanger = $tableNameForJoin . '.updated AS ' . $tableNameForJoin . '_updated, ';
			$createdChanger = $tableNameForJoin . '.created AS ' . $tableNameForJoin . '_created, ';

			// foreign_xyz mit mm_foreign_xyz Inhalt der join-Tabelle überschreiben für späteres Auflösen
			// TODO: doppeltes joinen der gleichen Tabelle durch unterschiedliche join-Typen unterbinden?
			$this->aQueryStack[$this->iStackPos]['WHAT'] = $mmTableName . '.' . $mmForeignJoinFieldName . ' AS foreign_tag, ';

			$this->aQueryStack[$this->iStackPos]['WHAT'] .= $uidChanger . $updatedChanger . $createdChanger . $tableNameForJoin . '.*';

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

	public function returnFlat() {
		$this->returnFlat = true;

		return $this;
	}

	public function group($fieldToGroupBy) {
		$currentTable = $this->aQueryStack[$this->iStackPos]['TABLENAME'];
		$this->aQueryStack[0]['GROUPBY'] = $currentTable.'.'.$fieldToGroupBy;

		return $this;
	}

	public function limit($iLimitNum) {
		$this->aQueryStack[0]['LIMIT'] = 'LIMIT '.intval($iLimitNum);

		return $this;
	}

	public function fetch() {
		$what = '';
		$where = '';
		$join = '';
		$limit = '';
		$group = '';

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
			if(isset($stackRow['GROUPBY'])) {
				$groupby = $stackRow['GROUPBY'] . ' ';
			}
			$limit = isset($stackRow['LIMIT']) ? $stackRow['LIMIT'] : '';
		}

		$startRow = $this->aQueryStack[0];
		$strQuery = $startRow['ACTION'] . ' ' . $what . $startRow['TABLE'] . ' ' . $join . $where . ' ' . $group . $limit;
		//var_dump($strQuery);

		if($this->DEBUG) {
			$debugQuery = $this->oDB->formatStatement($strQuery) . '<br />';
			echo($debugQuery);
		}

		// Query absetzen
		$flatResult = $this->oDB->oquery($strQuery);

		// von *-Umwandlung übrige uid, updated und created entfernen
		if(count($flatResult)) {
			foreach($flatResult as $i => $singleResult) {
				unset($flatResult[$i]->uid);
				unset($flatResult[$i]->updated);
				unset($flatResult[$i]->created);
			}
		}
		// in relationales Objekt umwandeln
		if(!$this->returnFlat) {
			$result = $this->getRelationalObject($flatResult);
		} else {
			$result = $flatResult;
		}

		return $result;
	}

	private function getRelationalObject($result) {
		if($result) {
			if($this->iStackPos > 0) {
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
				$ormResult = $ormMergedResult;
			} else {
				$uidNameForJoin = $this->getTableName().'_uid';
				$result[0]->uid = $result[0]->$uidNameForJoin;
				unset($result[0]->$uidNameForJoin);
				$updatedNameForJoin = $this->getTableName().'_updated';
				$result[0]->updated = $result[0]->$updatedNameForJoin;
				unset($result[0]->$updatedNameForJoin);
				$createdNameForJoin = $this->getTableName().'_created';
				$result[0]->created = $result[0]->$createdNameForJoin;
				unset($result[0]->$createdNameForJoin);
				$ormResult = $result;
			}
		} else {
			$ormResult = $result;
		}


		return $ormResult;
	}


	private function mergeDownResults(&$aResults) {
		// awesome magic comes here.
		//var_dump($aResults);
		// search other results for parent_xyz and append them to results[0]
		$iProcessedStacks = 0;
		//var_dump($aResults);die;
		$iCurrentResultSetUid = 0;
		foreach($aResults as $mTable => $oResultSet) {
			foreach($oResultSet as $iKey => $oSingleResult) {

				//var_dump($oSingleResult);
				//$iCurrentResultSetUid = intval($oSingleResult->uid);
				$iCurrentResultSetUid = $iKey;

				$bRelationWasMerged = false;
				/*foreach($oSingleResult as $strKey => $mValue) {
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
						$bRelationWasMerged = true;
					}
				}*/
				// ignore first result subset; it cant have a parent
				if($mTable === 0) {
					continue;
				}



				//var_dump($oSingleResult);


				if(isset($this->aQueryStack[$iProcessedStacks]['JOINBYFIELD'])) {
					//var_dump($this->aQueryStack[$iProcessedStacks+1]['JOINBYFIELD']);
					//var_dump($this->aQueryStack[$iProcessedStacks],$oSingleResult);
					//var_dump(':::::::', $aResults[0][$iKey], $this->aQueryStack[$iProcessedStacks]);
					$joinByField = explode('.', $this->aQueryStack[$iProcessedStacks]['JOINBYFIELD']);
					$parentJoinKey = $joinByField[0];
					$parentJoinField = $joinByField[1];

					//var_dump($iCurrentResultSetUid, $iProcessedStacks-1, $iKey, $mTable);
					//var_dump($this->aQueryStack[$iProcessedStacks]['JOINBYFIELD'], $aResults[0][$iProcessedStacks-1], $iProcessedStacks, $oSingleResult, '_______________');

					var_dump($this->tempLastJoinedStackData);

					if(!isset($aResults[0][$iKey]->$parentJoinKey)) {
						$joinUid = $this->tempLastJoinedStackData->$parentJoinField;
						$joinIndex = $this->findResultSetWithUid($oResultSet, $joinUid);
						var_dump($parentJoinField, $joinUid, $joinIndex);
						$this->tempLastJoinedStackData->$parentJoinField = $oResultSet[$joinIndex];
						//var_dump($this->tempLastJoinedStackData->$parentJoinField);
					} else {

					foreach($aResults[0][$iKey]->$parentJoinKey as $i => $parentNode) {
						if($parentNode->$parentJoinField == $oSingleResult->uid) {
							//$parentNode->$parentJoinField = $oSingleResult->uid;
							$aResults[0][$iKey]->{$parentJoinKey}[$i]->$parentJoinField = $oSingleResult;
							unset($oResultSet[$iKey]);

							//var_dump($aResults[0][$iProcessedStacks-1]->{$parentJoinKey}[$i]->$parentJoinField);
						}
					}

					}

					if(isset($aResults[0][$iKey]->{$parentJoinKey}[$i]->$parentJoinField)) {
						$this->tempLastJoinedStackData = $aResults[0][$iKey]->{$parentJoinKey}[$i]->$parentJoinField;
					}

				}
				if(!count($oResultSet)) {
					$bRelationWasMerged = true;
				}


				// attach to parents if parent_xyz key is present
				$strParentKey = 'parent_' . $this->strTableName;
				if(isset($oSingleResult->$strParentKey)) {
					//if($this->aQueryStack[$this->iStackPos]['LIMIT'])
					// get uid of parent
					$iParentUid = $oSingleResult->$strParentKey;
					// create child element on this result
					//var_dump($oSingleResult, $strParentKey, $iParentUid, $aResults[0][$iCurrentResultSetUid], $mTable);
					if(!isset($aResults[0][$iKey]->$mTable)) {
						// relations-key nicht vorhanden -> erzeugen
						///$aResults[0][$iParentUid]->$mTable = array($oSingleResult->uid => $oSingleResult);
						$aResults[0][$iKey]->{$mTable}[] = $oSingleResult;
					} else {
						// ansonten weitere gefundene Datensätze anfügen
						$aResults[0][$iKey]->{$mTable}[$oSingleResult->uid] = $oSingleResult;
					}
					// parent_xyz Eintrag entfernen, nachdem er aufgelöst wurde
					unset($aResults[0][$iKey]->{$mTable}[$oSingleResult->uid]->$strParentKey);
					$this->lastJoinedStackData = $aResults[0][$iKey]->{$mTable};
					$bRelationWasMerged = true;
				}

				//var_dump($aResults[0]);
			}

			$iProcessedStacks++;

			// Falls hier noch kein merging stattfand, muss die relation durch mm-Tabelle zustandegekommen sein..
			///if($mTable !== 0 && !$bRelationWasMerged) {
			//	current($aResults[0])->$mTable[$oResultSet->uid] = $oResultSet;
			//  $this->tempLastJoinedStackData = $aResults[0])->$mTable[$oResultSet->uid]
			///}
		}
		//var_dump($aResults[0]);
		//die;
		return ($aResults[0]);
	}

	private function findResultSetWithUid($resultSet, $uid) {
		foreach($resultSet as $keyOfResult => $singleResult) {
			if($singleResult->uid == $uid) {
				return $keyOfResult;
			}
		}

		return false;
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

				$thisResultUpdatedKey = $table->getTableName() . '_updated';
				if(isset($singleResult->$thisResultUpdatedKey)) {
					unset($intersect['updated']);
					$intersect['updated'] = $singleResult->$thisResultUpdatedKey;
				}

				$thisResultCreatedKey = $table->getTableName() . '_created';
				if(isset($singleResult->$thisResultCreatedKey)) {
					unset($intersect['created']);
					$intersect['created'] = $singleResult->$thisResultCreatedKey;
				}

				// Wenn Datensatz nocheinmal vorhanden ist, muss er durch einen join kommen -> Fremd-Uids zu Liste formen
				$iDataUid = $singleResult->$thisResultUidKey;
				/*if(isset($ormResult[$iDataUid])) {
					$ormResult[$iDataUid] = $this->mergeForeignUids($ormResult[$iDataUid], ((object)$intersect));
				} else {
					$ormResult[$iDataUid] = ((object)$intersect);
				}*/

				$uidOfAlreadyExistingResult = $this->findResultSetWithUid($ormResult, $iDataUid);
				if($uidOfAlreadyExistingResult !== false) {
					$ormResult[$uidOfAlreadyExistingResult] = $this->mergeForeignUids($ormResult[$uidOfAlreadyExistingResult], ((object)$intersect));
				} else {
					$ormResult[] = ((object)$intersect);
				}
			}

			// wieder kontinuierlich anfügen, weil merging sonst unmöglich
			//$tempOrmResult =  $ormResult[$iDataUid];
			//unset($ormResult[$iDataUid]);
			//$ormResult[] = $tempOrmResult;
			//var_dump($ormResult);

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

	public function getLastId() {
		return $this->oDB->get_last_id();
	}

	public $errno = array(
		'DUPLICATE' => 1062
	);

	public function lastErrorWas($strErrorKey) {
		$errNo = $this->oDB->get_last_errno();
		if(isset($this->errno[$strErrorKey]) && $this->errno[$strErrorKey] === $errNo) {
			return true;
		}
		return false;
	}

	public function debug($bOnOrOff = null) {
		if(is_bool($bOnOrOff)) {
			$this->DEBUG = $bOnOrOff;
		} else if($bOnOrOff == null) { // bei debug() davon ausgehen dass man aktivieren will
			$this->DEBUG = true;
		} else {
			throw new Exception('debug only accepts boolean', 034);
		}

		return $this;
	}

	public function __call($strFuncName, $mArgs) {
		throw new Exception('The method "'.$strFuncName.'" is not implemented', 034);
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