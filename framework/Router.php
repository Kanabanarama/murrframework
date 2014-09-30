<?php

/*
 * Router
 * Murrmurr framework
 *
 * Breaks down the url to a controller/subject/action path
 * and instancing the desired Controller automatically.
 * Is called via Router::start()
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 30.01.2010
 * @version 1.0.1
 *
 * 1.0.1 fix for 404 base href
 */

class Router
{
	private $controller;
	private $subject;
	private $action;
	private static $bProhibitRouting;

	function __construct($strController, $strSubject, $strAction, $strParams) {
		self::$bProhibitRouting = false;
		$this->controller = $strController . 'Controller';
		$this->subject = $strSubject;
		$this->action = $strAction;
		$this->params = $strParams;
	}

	public static function start() {
		// URL Bestandteile in Array zerlegen
		$aUrlVars = parse_url(
			(isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . // Scheme
			(isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '') . ':' . // User
			(isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '') . '@' . // Password
			$_SERVER['HTTP_HOST'] . // Hostname
			$_SERVER['REQUEST_URI'] // Path and query string
		);

		$strCurrentSubdir = dirname($_SERVER["PHP_SELF"]);
		$strPathWithoutSubdir = substr($aUrlVars['path'], strlen($strCurrentSubdir), strlen($aUrlVars['path']));
		$aPath = explode("/", substr($strPathWithoutSubdir, 1));

		// Aus dem Array die Pfadinformationen holen
		$strController = (isset($aPath[0]) && ($aPath[0])) ? $aPath[0] : "Index"; // Index wenn kein Controller definiert
		$strSubject = (isset($aPath[1]) && ($aPath[1])) ? $aPath[1] : null;
		$strAction = (isset($aPath[2]) && ($aPath[2])) ? $aPath[2] : null;
		$strParams = (isset($aPath[3]) && ($aPath[3])) ? $aPath[3] : null;

		// Pfad nach ungültigen Zeichen durchsuchen
		if((preg_match('/^[-a-zA-Z0-9_.]*$/i', $strController)) // später [-a-zA-Z0-9.:_&%+-=] für ajax?
			&& (preg_match('/^[-a-zA-Z0-9_.%+]*$/i', $strSubject))
			&& (preg_match('/^[-a-zA-Z0-9_.%+]*$/i', $strAction))
			&& (preg_match('/^[-a-zA-Z0-9_=.%+]*$/i', $strParams))
		) { // TODO: regex für JSON serialisierte Objekte
			// wenn nötig URL-Encodierte Sonderzeichen auflösen
			$strSubject = urldecode($strSubject);
			$strAction = urldecode($strAction);
			$strParams = urldecode($strParams);
			// Wenn gültige Pfad-Syntax, Router-Objekt instanzieren			
			$routerInstance = new self($strController, $strSubject, $strAction, $strParams);
			$routerInstance->dispatch();
		} else {
			throw new Exception("URL contains illegal characters: " . $_SERVER['REQUEST_URI'], 001);
		}
	}

	public function dispatch() {
		// Versuchen Controller zu instanzieren (geschieht per autoload)
		try {
			$controller = new $this->controller($this->subject, $this->action, $this->params);
		} catch(Exception $e) {
			$templateName = str_replace('Controller', '', $this->controller);
			$controller = new DefaultTemplateDeliveryController($this->subject, $this->action, $this->params, $templateName);
		}
		if(!$controller->doNotLink || $this->isAjaxRequest()) {
			if($controller->getView() instanceof BaseView) {
				$controller->getView()->render();
			} else {
				throw new Exception("The controller must call the publish() method of a view class", 005);
			}
		} else {
			Router::_404();
		}
	}

	public static function go($strLocation) {
		if(strpos($strLocation, 'http://') === 0 || strpos($strLocation, 'https://') === 0) {
			header('Location: ' . $strLocation);
		} else if(!self::$bProhibitRouting) {
			header('Location: /' . BASE_DIR . trim($strLocation, '/'));
		}
		exit;
	}

	public static function _404() {
		if(!self::$bProhibitRouting) {
			header('Location: ' . 'framework/predef/templates/404.htm');
		}
	}

	public static function isAjaxRequest() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest");
	}

	/*public static function prohibitRouting() {
		self::$bProhibitRouting = true;
	}

	public static function allowRouting() {
		self::$bProhibitRouting = false;
	}*/
}

?>