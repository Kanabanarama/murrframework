<?php

/**
 * StringViewhelper
 * Murrmurr framework
 *
 * viewhelper for manupilating strings
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 24.06.2014
 * @version 1.1.0
 */

class StringViewhelper extends BaseViewhelper {

	public function render($strText, $aAttributes, $mData) {
		$strManipulatedText = '';
		switch($aAttributes['function']) {
			case 'fit':
				if(strlen($strText) > $aAttributes['length']) {
					$strManipulatedText = substr($strText, 0, $aAttributes['length']);
					$strManipulatedText = substr($strText, 0, strrpos($strManipulatedText, ' '));
					if($aAttributes['tail']) {
						$strManipulatedText .= $aAttributes['tail'];
					}
				} else {
					$strManipulatedText = $strText;
				}
				break;
			case 'replace':
				if(isset($aAttributes['needle']) && isset($aAttributes['replace'])) {
					$strManipulatedText = str_replace($aAttributes['needle'], $aAttributes['replace'], $strText);
				} else {
					throw new Exception('Viewhelper '.$aAttributes['function'].' needs arguments needle and substitute.', 55);
				}
				break;
			default:
				throw new Exception('Viewhelper has no function defined for "'.$aAttributes['function'].'"', 55);
		}

		return nl2br($strManipulatedText);
	}

}

?>