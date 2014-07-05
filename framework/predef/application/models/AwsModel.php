<?php

/*http://www.dereleased.com/2010/01/11/arrays-of-objects-and-__get-friends-forever/*/

/*
 * Auf die Ergebnisse kann man mit objAWSModel->Item zugreifen, dabei ist Item[i] das i'te Suchergebnis.
 * objAWSModel->TotalResults gibt die Anzahl gefundener Suchergebnisse zurück.
 * 
 */

// TODO: - methode soll nur aktuelles ergebnis-array zurückgeben

class AwsModel extends BaseModel
{
	private $aVars;
	
	// extern zu holende Parameter
	private $strAccessKey;
	private $strAccessKeyId;
	private $strAssociateId;
	
	// statische Parameter
	private $strService			= 'AWSECommerceService';
	private $strVersion			= '2010-03-20';
	private $strResponseGroup	= 'Medium';
	private $strLocale;
	
	// dynamische Parameter
	private $strOperation;
	private $strSearchIndex;
	private $iItemPage;
	private $strSearchword;
	private $strAuthor;
	private $strItemId;
	private $strDatestamp;
	private $idType;

	const NO_MATCHES = 'NO_MATCHES';

	function __construct($strResponseGroup = 'Medium') {
		// Exception falls die benötigten Konstanten leer sind
		//if(empty($strAccessKeyId)) { throw new Exception("No AWS ID specified" , 005); }
		//if(empty($strAccessKey)) { throw new Exception("No AWS Secret Key specified" , 006); }
		
		// die externen Parameter werden bei der Objekterzeugung mitgegeben
		$this->strAccessKeyId	= AWS_ACCESSKEYID; //$strAccessKeyId;
		$this->strAccessKey		= AWS_ACCESSKEY; //$strAccessKey;, 
		$this->strAssociateId	= AWS_ASSICIATETAG; //$strAssociateId;
		$this->strResponseGroup	= $strResponseGroup;
		
		$this->strLocale = 'de';
		//return (array) $this;
	}
	
	public function setLocale($strLocaleKey) {
		//if($strLocaleKey == 'de') {	$this->strLocale = 'de'; }
		//if($strLocaleKey == 'uk') {	$this->strLocale = 'uk'; }
		
		switch($strLocaleKey) {
			case 'de':
				$this->strLocale = 'de';
				break;
			case 'us':
				$this->strLocale = 'com';
				break;
			case 'uk':
				$this->strLocale = 'co.uk';
				break;
			case 'fr':
				$this->strLocale = 'fr';
				break;
			case 'jp':
				$this->strLocale = 'jp';
				break;
			case 'ca':
				$this->strLocale = 'ca';
				break;
		}
	}
	
	function search($strSearchword) {
		if(is_numeric($strSearchword)) {
			if(strlen($strSearchword) == 10) { return $this->lookupISBN($strSearchword); }
			if(strlen($strSearchword) == 13) { return $this->lookupASIN($strSearchword); }
		} else {
			return $this->searchTitle($strSearchword);
		}
	}

	function getIsbnVersion($str) {
		if (preg_match('/^ISBN:(\d{9}(?:\d|X))$/', $str)) {
			return 10;
		} elseif (preg_match('/^ISBN:(\d{12}(?:\d|X))$/', $str)) {
			return 13;
		}
	}

	/*
	function isValidIsbn10($isbn) {
		$check = 0;
		for ($i = 0; $i < 10; $i++) {
			if ('x' === strtolower($isbn[$i])) {
				$check += 10 * (10 - $i);
			} elseif (is_numeric($isbn[$i])) {
				$check += (int)$isbn[$i] * (10 - $i);
			} else {
				return false;
			}
		}
		return (0 === ($check % 11)) ? 1 : false;
	}

	function isValidIsbn13($isbn) {
		$check = 0;
		for ($i = 0; $i < 13; $i += 2) {
			$check += (int)$isbn[$i];
		}
		for ($i = 1; $i < 12; $i += 2) {
			$check += 3 * $isbn[$i];
		}
		return (0 === ($check % 10)) ? 2 : false;
	}*/
	
	function searchTitle($strTitle, $iPage = 1, $iNumResults = 10) {
		$this->strOperation		= 'ItemSearch';
		$this->strSearchIndex	= 'Books';
		$this->strSearchword	= $strTitle;
		$this->iItemPage		= $iPage;

	 	$strRequestURI = $this->buildRequestURI();

	 	//als XML (verworfen da langsam und umständliche Pfade)
	 	/*
	 	$strResponseData = simplexml_load_file($strRequestURI);
	 	$this->aVars = (array) $strResponseData;

	 	return $strResponseData;
	 	*/

	 	//selbst parsen mit Regex (andere Verschachtelung, aber einfacher Zugriff)
	 	$strResponseData = $this->awsregex_load_file($strRequestURI);

	 	//var_dump($strRequestURI);
	 	//var_dump((object) array_merge($this->aVars, $strResponseData));

	 	//$this->aVars = array_merge($this->aVars, $strResponseData);

		if($strResponseData === 'NO_MATCHES') {
			$results = 'NO_MATCHES';
		} else {
			$results = (object) (array_merge($this->aVars, $strResponseData)); // Falls über das Objekt auf die Ergebnisse zugegriffen werden soll
		}

		return $results;
	}

	function searchAuthor($strAuthor, $iPage = 1, $iNumResults = 10) {
		$this->strOperation		= 'ItemSearch';
		$this->strSearchIndex	= 'Books';
		$this->strAuthor		= $strAuthor;
		$this->iItemPage		= $iPage;
	
	 	$strRequestURI = $this->buildRequestURI();
	 	
	 	//als XML (verworfen da langsam und umständliche Pfade)
	 	/*
		$oResponseData = simplexml_load_file($strRequestURI);
		$this->aVars = (array) $strResponseData;
		
		return $oResponseData;
		*/

		$strResponseData = $this->awsregex_load_file($strRequestURI);

		if($strResponseData === 'NO_MATCHES') {
			$results = 'NO_MATCHES';
		} else {
			$results = (object) (array_merge($this->aVars, $strResponseData));
		}

		return $results;
	}

	private function lookup($strCode, $idType = null) {
		$this->strOperation	= 'ItemLookup';
		$this->idType = $idType;
		$this->strItemId	= $strCode;

		$strRequestURI = $this->buildRequestURI();

		//als XML (verworfen da langsam und umständliche Pfade)
		/*
		$oResponseData = simplexml_load_file($strRequestURI);
		$this->aVars = (array) $strResponseData;

		return $oResponseData;
		*/

		$strResponseData = $this->awsregex_load_file($strRequestURI);

		if($strResponseData === 'NO_MATCHES') {
			$results = 'NO_MATCHES';
		} else {
			$results = (object) $this->aVars = $strResponseData;
		}

		return $results;
	}

	public function lookupASIN($strASIN) {
		return $this->lookup($strASIN, 'ASIN');
	}

	public function lookupISBN($iISBN) {
		$this->strSearchIndex = 'Books';
		return $this->lookup($iISBN, 'ISBN');
	}

	public function lookupISBN13($iISBN13) {
		$this->strSearchIndex = 'Books';
		return $this->lookup($iISBN13, 'EAN');
	}


	
	private function buildRequestURI() {
		// aktuellen Zeitsring erzeugen der für den Request benutzt wird
		$this->strDatestamp = urlencode(gmdate("Y-m-d\TH:i:s\Z"));
		
		//var_dump($this->strItemId);
		
		$aRequestParams = array('AWSAccessKeyId'	=> $this->strAccessKeyId,
								'AssociateTag'		=> $this->strAssociateId,
								'Service'			=> $this->strService,
								'ResponseGroup'		=> $this->strResponseGroup,
								'Timestamp'			=> $this->strDatestamp,
								'Version'			=> $this->strVersion,
		
								//'IdType' => 'ASIN',
								// Dynamische Parameter:
								'Operation'			=> $this->strOperation,
								'SearchIndex'		=> $this->strSearchIndex,
								'ItemPage'			=> $this->iItemPage,
								'Keywords'			=> rawurlencode($this->strSearchword),
								'Author'			=> rawurlencode($this->strAuthor),
								'ItemId'			=> trim($this->strItemId)
								);

		if($this->idType) {
			$aRequestParams['IdType'] = $this->idType;
		}

		ksort($aRequestParams);
		
		$strUrlParams = '';
		foreach($aRequestParams as $strKey => $strValue) {
			if(!empty($strValue)) {
				$strUrlParams .= $strKey.'='.$strValue;
				$strUrlParams .= ((end($aRequestParams) == $strValue)) ? '' : '&';
			}
		}
		
		///////////////////////////////////////////////////////////////////
		// API:
		// http://docs.amazonwebservices.com/AWSEcommerceService/4-0/
		///////////////////////////////////////////////////////////////////
		// ca
		// de
		// fr
		// jp
		// co.uk
		// com
		///////////////////////////////////////////////////////////////////

		$strMethod = 'GET';
		$strLocale = $this->strLocale;
		$strHost = "ecs.amazonaws.".$strLocale;
		$strUri = "/onca/xml";
		
		$strServiceUrl = "http://".$strHost.$strUri."?";
				
		$strSignedRequest	= $strMethod."\n".$strHost."\n".$strUri."\n".$strUrlParams;

		$strSignature		= urlencode(base64_encode(hash_hmac("sha256", $strSignedRequest, $this->strAccessKey, true)));
		$strRequestUrl		= $strServiceUrl.$strUrlParams."&Signature=".$strSignature;

		return $strRequestUrl; 
	}
	
	
	private function awsregex_load_file($strRequestURI) {
		// TODO: prüfen auf "Your request is missing required parameters. Required parameters include AssociateTag."
		$strResponse = file_get_contents($strRequestURI); // utf8_decode?

		$strRegexPatternErrors = '/<Errors><Error><Code>(.*?)<\/Code><Message>(.*?)<\/Message><\/Error><\/Errors>/s';
		$iNumErrors = preg_match_all($strRegexPatternErrors, $strResponse, $aErrors);

		if($iNumErrors > 0) {
			$strCode = current($aErrors[1]);
			$strError = current($aErrors[2]);
			if($strCode === 'AWS.ECommerceService.NoExactMatches') {
				return 'NO_MATCHES';
			}
			throw new Exception('AWS responded with error: "'.$strCode.': '.$strError.'"', 33);
		}

		$strRegexPatternResults = '/<TotalResults>(\d+)<\/TotalResults>/';
		preg_match($strRegexPatternResults, $strResponse, $iTotalResults);
		
		if($this->strOperation != 'ItemLookup') {
			if(isset($iTotalResults[1])) {
				$this->aVars['TotalResults'] = intval($iTotalResults[1]);
			}
		}
		
		$strRegexPatternItems = '/<Item>(.*?)<\/Item>/';
		$strRegexPatternItemElements = '/<(Title|ASIN|DetailPageURL|NumberOfPages|Author|PublicationDate|Binding|Label|ISBN|EANListElement|EditorialReviews|ImageSets)>*(.*?)<\/(?:Title|ASIN|DetailPageURL|NumberOfPages|Author|PublicationDate|Binding|Label|ISBN|EANListElement|EditorialReviews|ImageSets)>/';
		$strRegexPatternImages = '/<(SwatchImage|SmallImage|ThumbnailImage|TinyImage|MediumImage|LargeImage)>*(.*?)<\/(?:SwatchImage|SmallImage|ThumbnailImage|TinyImage|MediumImage|LargeImage)>/';
		$strRegexPatternImageAttributes = '/<(URL|Width Units=\"pixels\"|Height Units=\"pixels\")>*(.*?)<\/(?:URL|Width|Height)>/';
		
		$strResponseData	= array();
		$aItems				= array();
		$oItems				= array();
		
		$iNumItems = preg_match_all($strRegexPatternItems, $strResponse, $aResponseData);

		foreach($aResponseData[1] as $i => $strItem) {
			preg_match_all($strRegexPatternItemElements, $strItem, $aItems[$i]);

			$oItems['Item'][$i] = new stdClass();
			$oItems['Item'][$i]->Content = '';
			foreach($aItems[$i][1] as $strIndex => $strValue) {
			
				//var_dump($aResponseData[1]);
				//var_dump($strValue);
			
				if($strValue != 'ImageSets') {
					//$oItems['Item'][$i]->Content = '';
					if($strValue == 'EditorialReviews') {
						//$strRegexPatternReview = '/<(Content)>*(.*?)<\/(?:Content)>/'; -> $aReviewData[2]
						$strRegexPatternReview = '/<Source>Amazon.*?<\/Source><Content>(.*?)<\/Content>/';
						preg_match($strRegexPatternReview, $aItems[$i][2][$strIndex], $aReviewData);
						//var_dump($aItems[$i][2][$strIndex]);
						//var_dump($aReviewData, (count($aReviewData) && strlen($aReviewData[1])));
						//die;
						//$strContent = (count($aReviewData) && strlen($aReviewData[1])) ? $aReviewData[1] : 'No description available.';
						$strContent = (isset($aReviewData[1])) ? $aReviewData[1] : '';
						$oItems['Item'][$i]->Content = str_replace("<BR>", " ", html_entity_decode(trim($strContent)));
					}
				
					if(!isset($oItems['Item'][$i]->$strValue)) {	// wird sonst überschrieben
						$oItems['Item'][$i]->$strValue = $aItems[$i][2][$strIndex];
						//var_dump($oItems['Item'][$i]->$strValue);
					}
				}

				if(!$oItems['Item'][$i]->Content) {
					$oItems['Item'][$i]->Content = 'No description available.';
				}

				if($strValue === 'EANListElement') {
					$oItems['Item'][$i]->ISBN13 = $oItems['Item'][$i]->EANListElement;
					unset($oItems['Item'][$i]->EANListElement);
				}
			}
			
			//var_dump($oItems);
			
			$iImageSetsKey = array_search('ImageSets', array_values($aItems[$i][1]));
			
			if($iImageSetsKey) {
				$strImageSet = $aItems[$i][2][$iImageSetsKey];
				$aImages = array();
				preg_match_all($strRegexPatternImages, $strImageSet, $aImages[$i]); //$aItems[$i][2][$iImageSetsKey]);
				
				$aImage = array();
				foreach($aImages[$i][2] as $i23 => $strImage) {
					$iNumImg = preg_match_all($strRegexPatternImageAttributes, $strImage, $aImage[$aImages[$i][1][$i23]]);
					$oItems['Item'][$i]->$aImages[$i][1][$i23] = new stdClass();
					$oItems['Item'][$i]->$aImages[$i][1][$i23]->URL		= $aImage[$aImages[$i][1][$i23]][2][0];
					$oItems['Item'][$i]->$aImages[$i][1][$i23]->Height	= $aImage[$aImages[$i][1][$i23]][2][1];
					$oItems['Item'][$i]->$aImages[$i][1][$i23]->Width	= $aImage[$aImages[$i][1][$i23]][2][2];
				}
			} /*else {	// falls es keine Bilder gibt, Properties mit null füllen um Fehlermeldung zu vermeiden
				$aImageSetNames = array('SwatchImage', 'SmallImage', 'ThumbnailImage', 'TinyImage', 'MediumImage', 'LargeImage');
				foreach($aImageSetNames as $iImg => $strImgSet) {
					$oItems['Item'][$i]->$strImgSet->URL	= null;
					$oItems['Item'][$i]->$strImgSet->Height	= null;
					$oItems['Item'][$i]->$strImgSet->Width	= null;
				}
				
			}*/
			
			//$aResponseData[1][$i]['Content'] = html_entity_decode($aResponseData[1][$i]['Content']);
		}
		
		// htmlentities in der Beschreibung
		//$oItems['Item'][0]->Content = html_entity_decode($oItems['Item'][0]->Content);

		return $oItems;
	}
	
		
	function __get($strIndex)
	{
		//echo($strIndex);
		if(isset($this->aVars[$strIndex])) {
			//if(is_object($this->aVars[$strIndex])) { die(obj); }
			//var_dump(is_object($this->aVars[$strIndex][0]));
			//die();
			return $this->aVars[$strIndex];
		} else {
			return null;
		}
	}
	
	function __set($strIndex, $value)
	{
		return 'Change prohibited - Model is read-only.';
	}
}


		/*$strAmazonAWSUrl = "http://ecs.amazonaws.de/onca/xml";
		$strAWSServiceName = "Service=AWSECommerceService";
		$strAWSAccessKeyId = "AWSAccessKeyId=1244GABCVMS0GY6EBDR2";
		$strResponseGroup = "ResponseGroup=Medium";
		$strSearchIndex = "SearchIndex=Books";
		$strOperation = "Operation=ItemLookup";
		$strIdType = "IdType=EAN";
	
		$url = "http://ecs.amazonaws.de/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=1244GABCVMS0GY6EBDR2&ResponseGroup=Medium&SearchIndex=Books&Operation=ItemLookup&IdType=ISBN&ItemId=0517226952";
    	// Antwort von Amazon
    	//$response = utf8_decode(file_get_contents($request));
    	
    	
		//$url = "http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=1244GABCVMS0GY6EBDR2&Operation=ItemSearch&ResponseGroup=Medium";
    	
    	
    	
    	$secret = "/OU+1fa0QvSzpDyVdB0/QRziQ6aKcxDYDe0qCXK0";
		$host = parse_url($url,PHP_URL_HOST);
		$timestamp = gmstrftime("%Y-%m-%dT%H:%M:%S.000Z");
		$url=$url. "&Timestamp=" . $timestamp;
		$paramstart = strpos($url,"?");
		$workurl = substr($url,$paramstart+1);
		$workurl = str_replace(",","%2C",$workurl);
		$workurl = str_replace(":","%3A",$workurl);
		$params = explode("&",$workurl);
		sort($params);
		$signstr = "GET\n" . $host . "\n/onca/xml\n" . implode("&",$params);
		$signstr = base64_encode(hash_hmac('sha256', $signstr, $secret, true));
		$signstr = urlencode($signstr);
		$signedurl = $url . "&Signature=" . $signstr;
		$request = $signedurl;
    	
		//$test = "http://ecs.amazonaws.de/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=1244GABCVMS0GY6EBDR2&ResponseGroup=Medium&SearchIndex=Books&Operation=ItemLookup";

		$response = simplexml_load_file($request);
		
    	//$response = simplexml_load_file($test); // get the XML data
    	
		//var_dump(file_get_contents("http://ecs.amazonaws.de/onca/xml"));
		var_dump($response);
		die("test"); 
    	
    	//$response = utf8_decode(file_get_contents($request));
    	
    	//var_dump($response);*/

/*
$secret_access_key	= "00GEHEIM000000000000"; 
$access_key_id		= "0XYZ00XYZ0";    
$associate_id		= "ohnephantasie-21";                  

$SearchIndex		= "Books";
$Suchwort			= "Zwergkaninchen";
$ItemPage			= '1';

$aktuelle			= gmdate("Y-m-d\TH:i:s\Z");
$aktuellezeit		= urlencode($aktuelle);


$alleparameter =	"AWSAccessKeyId"	."=".	$access_key_id."&".
					"AssociateTag"		."=".	$associate_id."&".
					"ItemPage"			."=".	$ItemPage."&".
					"Keywords"			."=".	$Suchwort."&".
					"Operation"			."=".	"ItemSearch"."&".
					"ResponseGroup"		."=".	"Medium"."&".
					"SearchIndex"		."=".	$SearchIndex."&".
					"Service"			."=".	"AWSECommerceService"."&".
					"Timestamp"			."=".	$aktuellezeit."&".
					"Version"			."=".	"2009-07-30"; 

$stringsignr = "GET\n"."ecs.amazonaws.de"."\n"."/onca/xml"."\n".$alleparameter;

$signature1 = base64_encode(hash_hmac("sha256", $stringsignr, $secret_access_key, True));

$signature2  = urlencode($signature1);

$daten = file_get_contents("http://ecs.amazonaws.de/onca/xml?".$alleparameter."&Signature=".$signature2);

print_r($daten);
*/




/* aws.class.php */
/*
class AWS
{
  private $matches;
  private $data;



  function __construct($type,$title,$page)
  {
    // SQL-Request je nach Suchtyp
    if($type == 'search_title')
      $request = "http://ecs.amazonaws.de/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=1244GABCVMS0GY6EBDR2&ResponseGroup=Medium&SearchIndex=Books&Operation=ItemSearch&sort=+pmrank&ItemPage=".$page."&Title=".urlencode($title);
    else if($type == 'search_author')
      $request = "http://ecs.amazonaws.de/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=1244GABCVMS0GY6EBDR2&ResponseGroup=Medium&SearchIndex=Books&Operation=ItemSearch&sort=+pmrank&ItemPage=".$page."&Author=".urlencode($title);
    else if($type == 'search_isbn10')
      $request = "http://ecs.amazonaws.de/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=1244GABCVMS0GY6EBDR2&ResponseGroup=Medium&Operation=ItemLookup&ItemId=".urlencode($title);
    else if($type == 'search_isbn13')
      $request = "http://ecs.amazonaws.de/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=1244GABCVMS0GY6EBDR2&ResponseGroup=Medium&SearchIndex=Books&Operation=ItemLookup&IdType=EAN&ItemId=".urlencode($title);
    // Antwort von Amazon
    $response = utf8_decode(file_get_contents($request));
    // Suchmuster
    $pattern='/<(IsValid|Title|ASIN|DetailPageURL|NumberOfPages|Author|Binding|Label|ISBN|EAN|MediumImage><URL)>*(.*?)<\/(IsValid|Title|ASIN|DetailPageURL|NumberOfPages|Author|Binding|Label|ISBN|EAN|URL)>/';
    $matches=array();
    // Array f�llen durch Suchmuster
    $num = preg_match_all ($pattern, $response, $matches);
    // das von Suchmuster �brig gebliebene ><URL entfernen

    for($i=0; $i<count($matches[1]); $i++)
    {
      if ($matches[1][$i] == 'MediumImage><URL')
      {
      $matches[1][$i] = 'MediumImage';
      //$matches[2][$i] = substr($matches[2][$i],0,-12);
      }
    }

    $len = count($matches[1]);
    for($i=0; $i<$len; $i++)
    {
     // if ($matches[1][$i] == 'MediumImage><URL') $matches[1][$i] = 'MediumImage';

//      if((array_key_exists($i,$matches[1])) && (array_key_exists($i+1,$matches[1])))
//      {
        while(((array_key_exists($i,$matches[1])) && (array_key_exists($i+1,$matches[1]))) && ($matches[1][$i] == $matches[1][$i+1]))
          {
          //echo("l�schen");
            unset($matches[1][$i+1]);
            unset($matches[2][$i+1]);
            $i++;
          }
          //echo("___".$i."___");
//      }

      //if ($matches[1][$i] == $matches[1][$i+1]) unset($matches[1][$i+1]);
    }
    
    
    // das erste Title entfernen (Ist nur das Suchwort)
    unset($matches[1][1]);
    unset($matches[2][1]);
    // $matches Variable der Klasse mit Index 1 und 2 vom Ergebnis f�llen (XML Bezeichner und Wert)
    $this->matches[1] = array_values($matches[1]);
    $this->matches[2] = array_values($matches[2]);
//var_dump($this->matches);
  }
  
  
  
  final public function get($identifier, $index = -1)
  {
  unset($this->data);
    if(isset($this->matches[1][1]))
      {
        foreach($this->matches[1] as $key => $element)
          {
            if($element == $identifier)
              {
                $this->data[] = $this->matches[2][$key];
              }
          }
      }
//var_dump($this->data);
    if($index != -1)
      return $this->data[$index];
    else
      return $this->data;
  }
    
    
    
  final public function dump_response() { var_dump($this->matches); }
}*/



?>