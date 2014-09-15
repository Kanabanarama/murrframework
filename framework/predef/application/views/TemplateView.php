<?php

/**
 * TemplateView
 * Murrmurr framework
 *
 * view that builds a page from wrapping html and content html
 * and parses some markers
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 30.01.2010
 * @version 1.2.0
 */

class TemplateView extends BaseView
{
	private static $bBindInPage;
	private static $bTemplateWrapped = false;
	private $aVars = array(); /// Holds all the template variables
	private static $aWidgets; // Widgets (müssen überall erreichbar sein)
	private $strTemplateFile;
    private $missingTemplateFile;

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
            $this->missingTemplateFile = $strTemplateFile;
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
		if((self::$bBindInPage) && (!self::$bTemplateWrapped)) {
			$template = new self('standardpage.htm');
			$template->setAll($this->aVars);
			self::$bTemplateWrapped = true;
			// TODO: hier noch eine instance uid setzen?
			$template->set('content', $this->fetch());
			$template->render();
		} else {
			echo $this->fetch();
		}
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
			throw new Exception("Template does not exist: " . $this->missingTemplateFile, 124);
		}

		$currentErrorReportingLevel = error_reporting();
		error_reporting(0);
		include($strTemplateFile); // Include the file
		error_reporting($currentErrorReportingLevel);

		$contents = ob_get_contents();

		// Forms entfernen, wenn ein Widget gerendert wird
		if(isset($this->controller->iInstanceUid)) {
			if($this->controller->iInstanceUid > 0) {
				$contents = preg_replace("#<form.+>(.*)</form>#Uis", '\1', $contents);
			}
		}

		//preg_match_all('/(<!--)?<value:(.+) \/>(-->)?/u', $contents, $aValueMatches);
		/*preg_match_all('/\[(.+)\]/u', $contents, $aValueMatches);
		foreach($aValueMatches[1] as $i => $strValueMatch) {
			$templateMarkerValue = '';
			//if (preg_match("/^[\$a-zA-Z0-9]+$/", $strValueMatch)) {
			if (strpos($strValueMatch, '$') === 0) {
				$templateMarkerValue = $this->aVars[str_replace('$', '', $strValueMatch)];
			} else {
				eval('$templateMarkerValue = htmlspecialchars('.$aValueMatches[1][$i].');');
			}
			$contents = str_replace($aValueMatches[0][$i], $templateMarkerValue, $contents);
		}*/

		// nach Viewhelpern suchen
		// Normal tag:
		preg_match_all('/(<!--)?<viewhelper[ ](.+)>(.*)<\/viewhelper>(-->)?/Us', $contents, $aViewhelperMatches);
		// Short tag:
		//preg_match_all('/(<!--)?<viewhelper[ ]?(.+) \/>(-->)?/u', $contents, $aViewhelperMatches);

		//var_dump($aViewhelperMatches);die;

		$aViewhelpers = $aViewhelperMatches[0];
		$aViewhelperCommentedOut = $aViewhelperMatches[1];
		$aViewhelperAttributes = $aViewhelperMatches[2];
		$aViewhelperContents = $aViewhelperMatches[3];

		// Alle viewhelper durchgehen
		foreach($aViewhelpers as $i => $strViewhelper) {
			$aAttributesRaw = explode(' ', $aViewhelperAttributes[$i]);
			$aAttributes = array();
			foreach($aAttributesRaw as $strAttribute) {
				$aAttr = explode('="', substr($strAttribute, 0, -1));
				$aAttributes[$aAttr[0]] = $aAttr[1];
			}
			$strTagContent = $aViewhelperContents[$i];

			if(!$aViewhelperCommentedOut[$i]) {
				if(isset($aAttributes['name'])) {
					$strViewhelperClassName = $aAttributes['name'] . 'Viewhelper';
					unset($aAttributes['name']);
					$oViewhelper = new $strViewhelperClassName();
					//$oViewhelper->transferVariables($this->aVars);
					if(!isset($aAttributes['strip']) || $aAttributes['strip'] != 0) {
						$strTagContent = $oViewhelper->stripContent($strTagContent);
					}
					if(!isset($aAttributes['escape']) || $aAttributes['escape'] != 0) {
						$strTagContent = $oViewhelper->escapeContent($strTagContent);
					}
					$strReplaceContent = $oViewhelper->render($strTagContent, $aAttributes);
					$contents = str_replace($aViewhelpers[$i], $strReplaceContent, $contents);
				} else {
					throw new Exception('Viewhelper tag has no name.', 33);
				}
			}
		}

		ob_end_clean(); // End buffering and discard

		return $contents;
	}










	// TODO: fillContent Funktion in die man das HTML schreiben kann statt eine Template File zum auslesen anzugeben

	public static function makeStringFitting($strString, $iMaxLenght, $strTail = '...') {
		if(strlen($strString) > $iMaxLenght) {
			$strString = substr($strString, 0, $iMaxLenght) . $strTail;
		}
		return $strString;
	}

	/*public function parseBBCode($strText) {
		$strText = htmlspecialchars($strText);
		$strText = preg_replace("/\[b\](.*)\[\/b\]/Usi", "<b>\\1</b>", $strText);
		$strText = preg_replace("/\[i\](.*)\[\/i\]/Usi", "<i>\\1</i>", $strText);
		$strText = preg_replace("/\[u\](.*)\[\/u\]/Usi", "<u>\\1</u>", $strText);
		$strText = preg_replace("/\[color=(.*)\](.*)\[\/color\]/Usi", "<font color=\"\\1\">\\2</font>", $strText);
		$strText = preg_replace("/\[size=(.*)\](.*)\[\/size\]/Usi", "<font size=\"\\1\">\\2</font>", $strText);
		$strText = preg_replace("/\[email=(.*)\](.*)\[\/email\]/Usi", "<a href=\"mailto:\\1\">\\2</a>", $strText);
		$strText = preg_replace("/\[img\](.*)\[\/img\]/Usi", '<img src="\\1">', $strText);
		return nl2br($strText);
	}*/

	public function generateDateSelect($dateStart, $dateEnd = null, $dateSelected = null) {
		if($dateSelected == '0000-00-00') {
			$iSelectedDay = 0;
			$iSelectedMonth = 0;
			$iSelectedYear = 0;
		} else {
			$iSelectedDay = intval(date("j", strtotime($dateSelected)));
			$iSelectedMonth = intval(date("n", strtotime($dateSelected)));
			$iSelectedYear = intval(date("Y", strtotime($dateSelected)));
		}

		$strOptions = '<select name="day">';
		$strOptions .= '<option value="0">-</option>';
		for($iDay = 1; $iDay <= 31; $iDay++) {

			$strOptions .= '<option' . (($iDay == $iSelectedDay) ? ' selected="selected"' : '') . '>' . $iDay . '</option>';
		}
		$strOptions .= '</select>';

		$strOptions .= '<select name="month">';
		$strOptions .= '<option value="0">-</option>';
		for($iMonth = 1; $iMonth <= 12; $iMonth++) {
			$strOptions .= '<option' . (($iMonth == $iSelectedMonth) ? ' selected="selected"' : '') . '>' . $iMonth . '</option>';
		}
		$strOptions .= '</select>';

		$strOptions .= '<select name="year">';
		$strOptions .= '<option value="0">-</option>';
		for($iYear = 1970; $iYear <= date("Y"); $iYear++) {
			$strOptions .= '<option' . (($iYear == $iSelectedYear) ? ' selected="selected"' : '') . '>' . $iYear . '</option>';
		}
		$strOptions .= '</select>';

		return $strOptions;
	}

	public function generateSelect($strName, $strClass, $aOptClasses, $aOptValues, $aOptText, $strSelectedValue) //$dateStart, $dateEnd = null, $dateSelected = null)
	{
		$strOptions = '<select name="' . $strName . '" class="' . $strClass . '">';
		for($i = 0; $i <= count($aOptText) - 1; $i++) {
			$strOptions .= '<option value="' . $aOptValues[$i] . '" class="' . $aOptClasses[$i] . '" ' . (($aOptValues[$i] == $strSelectedValue) ? ' selected="selected"' : '') . '>' . $aOptText[$i] . '</option>';
		}
		$strOptions .= '</select>';

		return $strOptions;
	}

	public function getGenderSymbol($iGender) {
		$genderSymbol = '-';
		switch($iGender) {
			case 1:
				$genderSymbol = '♀';
				break;
			case 2:
				$genderSymbol = '♂';
				break;
			default:
				$genderSymbol = '-';
				break;
		}
		return $genderSymbol;
	}

	public function getAgeFromDate($date) {
		if($date === '0000-00-00') { return '-'; }
		$birthday = new DateTime($date);
		$now = new DateTime();
		$interval = $now->diff($birthday);
		return $interval->y;
	}

	/* country codes according to ISO 3166 */
	// TODO: laden aus xml + lang integration
	public function getCountryFromCode($countryCode) {
		if(!$countryCode || $countryCode === '') { return '-'; }
		$countries = array(
			'de' => 'Germany',
			'ch' => 'Switzerland',
			'at' => 'Austria',
			'uk' => 'United Kingdom',
			'us' => 'USA'
		);
		if(isset($countries[$countryCode])) {
			$countryCode = $countries[$countryCode];
		} else {
			throw new Exception('A country for iso code "'.$countryCode.'" is missing.', 7);
		}

		return $countryCode;
	}

	public function getDateLocalized($strDate) {
		$date = new DateTime($strDate);
		$localizedDate = $date->format('d.m.Y');

		return $localizedDate;
	}
}

?>