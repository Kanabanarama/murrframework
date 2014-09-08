<?php

/**
 * Logger
 * Murrmurr framework
 *
 * logger class that logs into files or emails
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 12.07.2014
 * @version 1.0
 */

class Logger {
	public static function log($line) {
		if(($line != '') && defined(_LOGFILE)) {
			$fh = fopen(_LOGFILE, 'a');
			fwrite($fh,date('Y-m-d H:i:s').' - '.$line."\n");
			fclose($fh);
		}
	}
}

?>