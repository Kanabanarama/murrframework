<?php
/**
 * index.php
 * Murrmurr framework
 *
 * the install script
 *
 * @author René Lantzsch <kana@bookpile.net>
 *
 * @author René Lantzsch <renelantzsch@web.de>
 * @copyright Copyright (c) René Lantzsch 23.01.2009
 * @since 23.01.2009
 * @version 0.4a
 */

define('INSTALL', true);
require_once '../framework/Bootstrap.php';

class Installer
{
	private $oDB;

	function __construct() {
		$bDbCheck = $this->checkDB();
		$bDirectories = $this->createUploadFolders();

		$aChecks = array(
			'db' => $bDbCheck,
			'dir' => $bDirectories
		);

		$aMessages = array(
			'db' => 'Datenbankstruktur wurde angelegt.',
			'dir' => 'Upload folders wurden erstellt.'
		);

		$aErrors = array(
			'db' => 'Datenbankstruktur konnte nicht erstellt werden.',
			'dir' => 'Upload folder konnten nicht erstellt werden. Bitte weise den Ordnern application und application/uploads die Rechte 777 zu, rufe install erneut auf und gebe dem Ordner danach wieder die Rechte 755!'
		);

		$progress = '';
		foreach($aChecks as $key => $check) {
			if($check == true) {
				$progress .= '<img src="gfx/icon_success.png" /> <span>' . $aMessages[$key] . '</span><br />';
			} else {
				$progress .= '<img src="gfx/icon_error.png" /> <span>' . $aErrors[$key] . '</span><br />';
			}
		}

		$staticdir = '/' . BASE_DIR . 'framework/predef/templates/';

		require_once('install.htm');
	}

	private function checkDB() {
		$bResult = false;
		$this->oDB = Registry::get('dbconnection');

		if($this->oDB->getStatus() != 1) {
			$bCreateDbResult = $this->oDB->query('CREATE DATABASE '.MYSQL_DATABASE);
            $bSelectDbResult = $this->oDB->query('USE '.MYSQL_DATABASE);
			$this->oDB = Registry::get('dbconnection');
		}

		if($this->oDB->getStatus() == 1 || $bCreateDbResult) {
			$result = NULL;

			if($result == NULL) {
				$import = file_get_contents("DB.sql");

				$import = preg_replace("%/\*(.*)\*/%Us", '', $import);  // \*
				$import = preg_replace("%^--(.*)\n%mU", '', $import);   // comments
				//$import = preg_replace("%^$\n%mU", '', $import);
				$import = preg_replace("/[\s][\s]*/"," ", $import);
				$import = explode(";", $import);

				$aInstructions = array();

				foreach($import as $imp) {
					$strInstruction = trim($imp);
					if($strInstruction != '') {
						$aInstructions[] = $strInstruction;
					}
				}

				foreach($aInstructions as $strQuery) {
					$result = $this->oDB->query($strQuery);
                    if($result !== true) {
                        $errno = $this->oDB->get_last_errno();
                        $error = $this->oDB->get_last_error();
                        echo($errno.': '.$error);
                    }
				}

				$bResult = true;
			} else {
				if(current($result[0]) == 'users') {
					$bResult = true;
				}
			}
		}

		return $bResult;
	}

	private function createUploadFolders() {
		$aFolders = array(
			'logs' => ROOT_DIR.'application/logs/errors',
			'uploads' => ROOT_DIR.'application/uploads',
			'images' => ROOT_DIR.'application/uploads/images',
			'profileimages' => ROOT_DIR.'application/uploads/images/avatars',
			'newsimages' => ROOT_DIR.'application/uploads/images/news'
		);

		$bResult = true;
		foreach($aFolders as $folder) {
			if(!is_dir($folder) && @mkdir($folder, 0755) !== true) {
				$bResult = $bResult && false;
			}
		}

		return $bResult;
	}

}

$oInst = new Installer();

?>