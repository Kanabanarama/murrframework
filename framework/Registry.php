<?php

/**
 * Registry
 * Murrmurr framework
 *
 * class to encapsulate globals
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @version 1.2
 *
 * 1.1: added push
 * 1.2: added has
 */

abstract class Registry
{
	private static $vars = array('controllers' => array());

	public static function set($index, $value) {
		self::$vars[$index] = $value;
	}

	public static function get($index) {
		if(!empty($index) && array_key_exists($index, self::$vars)) {
			return self::$vars[$index];
		} else {
			return null;
		}
	}

	public static function push($arrayname, $value) {
		self::$vars[$arrayname][] = $value;
	}

	public static function has($arrayname, $value) {
		if(!empty($arrayname) && array_search($value, self::$vars[$arrayname])) {
			return true;
		} else {
			return false;
		}
	}

	public static function save($index, $value) {
		$_SESSION['reg'][$index] = $value;
	}

	public static function load($index) {
		if(isset($_SESSION['reg'][$index])) {
			$mValue = $_SESSION['reg'][$index];
			//unset($_SESSION['reg'][$index]);
			return $mValue;
		} else {
			return null;
		}
	}
}

?>