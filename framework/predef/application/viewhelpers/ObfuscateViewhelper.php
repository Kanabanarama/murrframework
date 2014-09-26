<?php

/**
 * ObfuscateViewhelper
 * Murrmurr framework
 *
 * obfuscates text and tags to be invisible for crawlers
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 02.07.2014
 * @version 1.0
 */

class ObfuscateViewhelper extends BaseViewhelper {

	public function render($strText, $aAttributes, $mData) {
		if(!isset($aAttributes['type'])) {
			throw new Exception('Obfuscate viewhelper has no type defined', 55);
		} else {
			$methodName = $aAttributes['type'];
			unset($aAttributes['type']);
			if(method_exists($this, $methodName)) {
				$content = $this->{$methodName}($strText, $aAttributes);
			} else {
				throw new Exception('Form viewhelper method '.$methodName.' is missing.', 55);
			}
		}

		return $content;
	}

	private function mailto($mailAddress, $aAttributes) {
		$linkText = isset($aAttributes['linktext']) ? $aAttributes['linktext'] : $mailAddress;
		$cssClass = isset($aAttributes['class']) ? 'class=\"'.$aAttributes['class'].'\"' : '';
		$subjectParam = isset($aAttributes['subject']) ? '?subject='.$aAttributes['subject'] : '';
		$mailtoTag = '<a href=\"mailto:'.$mailAddress.$subjectParam.'\" '.$cssClass.'>'.$linkText.'</a>';
		$obfuscatedMailto = str_rot13($mailtoTag);

		$obfuscator = '<script>document.write("'.$obfuscatedMailto.'".replace(/[a-zA-Z]/g,function(c){return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);}));</script>';
		//"<n uers=\"znvygb:grfg@grfgvat.pbz\" ery=\"absbyybj\">Fraq n zrffntr</n>"

		if(isset($aAttributes['fallback']) && $aAttributes['fallback'] == true) {
			$obfuscator .= '<noscript>'.str_replace(array('@', '.'), array('&#64;', '&#46;'), $mailtoTag).'</noscript>';
		}

		return $obfuscator;
	}

	private function text($strText, $aAttributes) {
		$obfuscatedText = str_rot13($strText);
		$obfuscator = '<script>document.write("'.$obfuscatedText.'".replace(/[a-zA-Z]/g,function(c){return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);}));</script>';

		return $obfuscator;
	}

}

?>