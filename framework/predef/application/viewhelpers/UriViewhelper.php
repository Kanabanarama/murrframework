<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 29/06/14
 * Time: 20:02
 */

class UriViewhelper extends BaseViewhelper {

	public function render($strText, $aAttributes) {
		$strManipulatedText = '';
		switch($aAttributes['function']) {
			case 'pathify':
				$strManipulatedText = preg_replace('/\d{0,2}:\d{0,2}:\d{0,2}/i', '', $strText);
				//$search  = array (' ', 'ä', 'ö', 'ü', 'ß');
				//$replace = array ('-', 'ae', 'oe', 'ue', 'ss');
				//$strManipulatedText = str_replace($search, $replace, strtolower(trim($strManipulatedText)));
				$strManipulatedText = str_replace(' ', '-', mb_strtolower(trim($strManipulatedText)));
				//$strManipulatedText = preg_replace('/[^äöüéàè0-9a-z-]/i', '', $strManipulatedText);
				$strManipulatedText = preg_replace('/[^\w-\.]/u', '', $strManipulatedText);
				$strManipulatedText = urlencode($strManipulatedText);
				break;
			default:
				throw new Exception('UriViewhelper has no function defined for "'.$aAttributes['function'].'"', 55);
		}

		return $strManipulatedText;
	}

}

?>