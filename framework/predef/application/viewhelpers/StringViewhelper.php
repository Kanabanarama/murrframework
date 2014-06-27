<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 24/06/14
 * Time: 20:47
 */

class StringViewhelper extends BaseViewhelper {

	public function render($strText, $aAttributes) {
		$strManipulatedText = '';
		switch($aAttributes['function']) {
			case 'fit':
				if(strlen($strText) > $aAttributes['length']) {
					$strManipulatedText = substr($strText, 0, $aAttributes['length']);
					$strManipulatedText = substr($strText, 0, strrpos($strManipulatedText, ' '));
					$strManipulatedText .= '...';
				} else {
					$strManipulatedText = $strText;
				}
				break;
			default:
				throw new Exception('Viewhelper has no function defined for "'.$aAttributes['function'].'"', 55);
		}

		return nl2br($strManipulatedText);
	}
}

?>