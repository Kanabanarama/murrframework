<?php

function fwErrorHandler($iErrno, $strErr, $strErrFile, $iErrLine) {
	$oMockError = new stdClass();
	$oMockError->number = $iErrno;
	$oMockError->message = $strErr;
	$oMockError->file = $strErrFile;
	$oMockError->line = $iErrLine;

	renderError($oMockError);
}

function fwExceptionHandler($oError) {
	renderError($oError);
}

function renderError($oError) {

	$aExceptionTypes = array(
		1 => 'ERR_TYPE_DB',
		2 => '?',
		3 => 'E_AUTOLOAD',
		8 => '?',
		26 => '?',
		27 => 'E_ERR_DBCHECK',
		40 => 'E_ERR_DBCONF',
		52 => 'E_ERR_DBCONF',
		124 => 'E_ERR_TEMPLATE',
		2048 => '?'
	);

	$aErrorTypes = array(
		16384 => 'E_USER_DEPRECATED',
		8192 => 'E_DEPRECATED',
		4096 => 'E_RECOVERABLE_ERROR',
		2048 => 'E_ALL',
		1024 => 'E_USER_NOTICE',
		512 => 'E_USER_WARNING',
		256 => 'E_USER_ERROR',
		128 => 'E_COMPILE_WARNING',
		64 => 'E_COMPILE_ERROR',
		32 => 'E_CORE_WARNING',
		16 => 'E_CORE_ERROR',
		8 => 'E_NOTICE',
		4 => 'E_PARSE',
		2 => 'E_WARNING',
		1 => 'E_ERROR'
	);

	if($oError instanceof Exception) {
		$exceptionType = (isset($aExceptionTypes[$oError->getCode()])) ? $aExceptionTypes[$oError->getCode()] : '';
		$header = 'An ' . $exceptionType . ' error occured.';
		$message = $oError->getMessage();
		$location = 'File: ' . $oError->getFile() . ' Line: ' . $oError->getLine();
	} else {
		$header = 'An ' . $aErrorTypes[$oError->number] . ' error occured.';
		$message = $oError->message;
		$location = 'File: ' . $oError->file . ' Line: ' . $oError->line;
	}

	$root = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);

	require_once($root . '/predef/templates/error.htm');
	exit;
}

$oldFwErrorHandler = set_error_handler("fwErrorHandler");
$oldFwErrorHandler = set_exception_handler("fwExceptionHandler");

?>