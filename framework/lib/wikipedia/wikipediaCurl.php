<?php

/**
 * Wikipedia Curl
 * Murrmurr framework
 *
 * retrieving wikpiedia information through curl requests
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 04.09.2014
 * @version 1.0
 */

class WikipediaCurl
{
    public function __construct() {
        $url = 'http://en.wikipedia.org/w/api.php?action=query&titles=Your_Highness&prop=revisions&rvprop=content&rvsection=0';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MyBot/1.0 (http://www.mysite.com/)');

        $result = curl_exec($ch);

        if (!$result) {
            exit('cURL Error: '.curl_error($ch));
        }

        var_dump($result);
    }
}

?>