<?php

/**
 * BaseController
 *
 * Defines the abstract base class for every Controller
 *
 * @author René Lantzsch <renelantsch@web.de>
 * @param string $strSubject  Subject that is to be handled by the Controller
 * @param string $strAction  Other parameters for the Controller
 */

abstract class BaseController
{
	/**
	 * Defines if the Controller is reachable per Route (otherwise it's a mere Widget)
	 * @access private
	 * @var string
	 */
	public $executeOnly = false; // Controller nur ausführen aber nichts anzeigen
	public $doNotLink = false; // Controller nicht durch Router zugreifbar (nur als Subcontroller verwendbar)
	public $isSingleton = false; // Controller darf nur einmal existieren
	public $ajaxify = false; // Controller wird über einen ajax Request aufgerufen

	public $bIsValid;

	protected $strSubject;
	protected $strAction;
	protected $aParams;

	protected $oView;
	protected $oModel;

	protected $section;
	protected $messages;
	protected $errors;

	private static $iInstanceCounter = 0;
	public $iInstanceUid;

	public $POST;
	public $GET;
	public $FILES;

	public function __construct($strSubject = null, $strAction = null, $aParams = null)	{
		//var_dump(get_class($this), $this->isSingleton);
		// Wenn die Klasse schon existiert aber Singleton-Flag hat (z.B. Menü), nicht nochmal instanzieren!
		if ($this->isSingleton && Registry::has('controllers', get_class($this))) {
			$this->bIsValid = false; // TODO: do things with this
			return;
		}

		// Ansonsten Controller in der Registry registrieren
		// TODO: Conroller als Objekt ablegen? => benchmark
		Registry::push('controllers', get_class($this));

		$this->strSubject = $strSubject;
		$this->strAction = $strAction;
		$this->aParams = $aParams;

		$this->section = 'section1';    // start with template section 1 by default

		// TODO: prüfen ob mehrere gleichartige Controller angelegt werden
		$this->iInstanceUid = self::$iInstanceCounter++;

		// $_GET-Daten die zu einem Controller gehören zu dessen private Variablen machen
		foreach ($_GET as $strKey => $mValue) {
			if ($strKey === 'ajaxify') {
				if (Router::isAjaxRequest()) {
					$this->ajaxify = true;
				}
			} else {
				$this->GET[$strKey] = $mValue;
			}
		}

		// ebenso für $_POST-Daten
		foreach ($_POST as $strKey => $mValue) {
			if (intval(substr($strKey, 0, strlen($this->iInstanceUid))) == $this->iInstanceUid) {
				$strCleanKey = $strKey;
				if(strpos($strKey, '_') !== false) {
					$strCleanKey = substr($strKey, (strpos($strKey, '_') + 1));
				}
				if ($strCleanKey === 'jsondata') {
					$aFields = explode('&', $mValue);
					foreach ($aFields as $strField) {
						$strFieldPairs = explode("=", $strField);
						$this->POST[urldecode($strFieldPairs[0])] = urldecode($strFieldPairs[1]);
					}
				} else {
					$this->POST[$strCleanKey] = $mValue;
				}
			}
		}

		// und für hochgeladene Dateien
		foreach($_FILES as $strKey => $mValue) {
			if(substr($strKey, 0, strlen($this->iInstanceUid)) === $this->iInstanceUid) {
				$strCleanKey = substr($strKey, strpos($strKey, '_ ' )+1);
				$this->FILES[$strCleanKey] = $mValue;
			} else {
				$this->FILES[$strKey] = $mValue;
			}
		}

		$this->bIsValid = true;



		try {
			//$this->setPreProcessor();
			$this->process();
			// Post processor:
			if(is_file(CONTROLLER_DIR.'/PostProcessor.php')) {
				$postProcessor = new PostProcessor();
				$postProcessor->process();
				$this->getView()->set('section', $this->section);
				$this->getView()->set('messages', $this->messages);
				$this->getView()->set('errors', $this->errors);
				$postVars = $postProcessor->getViewVars();
				if(count($postVars)) {
					$this->getView()->setAll($postVars);
					//var_dump('set post');
				}
			}
			//$this->setPostProcessor();
		} catch(Exception $e) {
			renderError($e);
		}
	}

	/**
	 * process()
	 *
	 * Define this class in your controller and let it do everything regarding your page
	 * @return void
	 */
	abstract public function process();

	/**
	 * getView()
	 *
	 * Returns the View assigned to your Controller
	 * @return object $oView
	 */
	public function getView() {
		return $this->oView;
	}

	/**
	 * setView()
	 *
	 * Assigns a View to your Controller
	 * @param object $oView
	 * @return void
	 */
	public function setView($oView) {
		$this->oView = $oView;
		// if predefined view vars exist, merge them with the views controller defined vars
		/*if(count($this->predefinedViewVars) > 0) {
			$this->oView->setAll($this->predefinedViewVars);
		}*/
	}

	/*public function setPredefinedViewVars($predefinedVars) {
		$this->predefinedViewVars = $predefinedVars;
	}*/

	public function setInstanceCounter($iCount) {
		$this->iInstanceCounter = $iCount;
	}
}

?>