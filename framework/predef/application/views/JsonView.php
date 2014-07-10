<?php

/**
 * JsonView
 * Murrmurr framework
 *
 * serves content as json formatted string
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 26.06.2014
 * @version 1.0
 */

class JsonView extends BaseView
{
	/**
	 * Constructor
	 *
	 * @param $file string the file name you want to load
	 */
	public function __construct($strTemplateFile) {
		if(is_file(TEMPLATE_DIR . $strTemplateFile)) {
			$this->strTemplateFile = TEMPLATE_DIR . $strTemplateFile;
		} else if(is_file(STATIC_DIR . $strTemplateFile)) {
			$this->strTemplateFile = STATIC_DIR . $strTemplateFile;
		} else {
			return false;
		}
		self::$bBindInPage = true;
	}

	/**
	 * is the template existent?
	 */
	public function templateFileExists() {
		return isset($this->strTemplateFile);
	}

	/**
	 * Set a template variable.
	 */
	public function set($index, $value) {
		if($value instanceof BaseController) {
			if($value->bIsValid) {
				self::$aWidgets[$index] = strval($value->getView());
			}
		} else {
			$this->aVars[$index] = $value;
		}
	}

	public function setAll($aVars) {
		$this->aVars = array_merge($this->aVars, $aVars);
	}

	public function publish($objCallingController) {
		$this->controller = $objCallingController;
		if($objCallingController->ajaxify == 1) {
			self::$bBindInPage = false;
		}
		$objCallingController->setView($this);
	}

	public function render() {
		echo $this->fetch();
	}

	/**
	 * Open, parse, and return the template file.
	 *
	 * @param $file string the template file name
	 */
	public function fetch() {
		if(is_array($this->aVars)) {
			//$this->aVars['section'] = $this->controller->section;
			$this->aVars['messages'] = (count($this->aVars['messages'])) ? ('<span class="message">'.implode('</span><span class="message">', $this->aVars['messages']).'</span>') : '';
			$this->aVars['errors'] = (count($this->aVars['errors'])) ? ('<span class="error">'.implode('</span><span class="error">', $this->aVars['errors']).'</span>') : '';

			extract($this->aVars); // Extract the vars to local namespace
		}

		if(is_array(self::$aWidgets)) {
			extract(self::$aWidgets);
		}

		ob_start(); // Start output buffering

		$contents = '';

		$strTemplateFile = $this->strTemplateFile;
		if(!file_exists($strTemplateFile)) {
			throw new Exception("Template does not exist: " . $strTemplateFile, 124);
		}

		$currentErrorReportingLevel = error_reporting();
		error_reporting(0);
		include($strTemplateFile); // Include the file
		error_reporting($currentErrorReportingLevel);

		$contents = ob_get_contents();

		ob_end_clean(); // End buffering and discard

		return $contents;
	}
}

?>