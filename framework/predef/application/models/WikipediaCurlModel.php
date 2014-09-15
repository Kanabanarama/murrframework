<?php

/**
 * Wikipedia Curl Model
 * Murrmurr framework
 *
 * retrieving wikpiedia information through curl requests
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 04.09.2014
 * @version 1.0
 */

class WikipediaCurlModel extends BaseModel
{
	public function __construct($searchTopic)
	{
		$endpoint = 'http://en.wikipedia.org/w/api.php';
		$url = $endpoint . '?action=parse&page=' . urlencode($searchTopic) . '&format=json&prop=text&section=0';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'BookpileScript/1.0 (http://www.bookpile.net/)');

		$result = curl_exec($ch);

		if (!$result) {
			throw new Exception('curl Error: ' . curl_error($ch), 25);
		}

		$json = json_decode($result);

		if(isset($json->error)) {
			if($json->error->code === 'missingtitle') {
				$this->description = 'No information about this author found.';
			}
		} else {
			$content = $json->{'parse'}->{'text'}->{'*'};
			$pattern = '#<p>(.*)</p>#Us'; // first match of a paragraph
			if (preg_match($pattern, $content, $matches)) {
				$this->description = strip_tags($matches[1]);
				//$this->description = $matches[1];
			}
		}
	}
}

?>