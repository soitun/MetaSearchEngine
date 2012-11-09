<?php require_once( 'includes/simple_html_dom.php' ); ?> 
<?php
/**
 * REST requests to bing, yahoo, blekko, and google
 */
// bing request
function searchBing( $searchString ) 
{
	$bing = array();
	/*	// to get 100 results
	$docID= 0; // document counter
	$numDocs = count ( $jsonobj->SearchResponse->Web->Results );
	for ( $i = 0 ; $i < 100 ; $i+=51 ) 
	{	
		$bingRequest = 'http://api.search.live.net/json.aspx?Appid=1225978DBB9E59DAE44C2C1382093BB74C343920&sources=web&web.count=50&web.offset='.$i.'&query=' . urlencode( $searchString );
	*/		
	$bingRequest = 'http://api.search.live.net/json.aspx?Appid=1225978DBB9E59DAE44C2C1382093BB74C343920&sources=web&web.count=50&query=' . urlencode( $searchString );

	// Read entire file into a string
	$bingResponse = file_get_contents( $bingRequest ); 
	// Decode json into an object
	$jsonobj = json_decode( $bingResponse );
	
	// If there are results, then parse them
	if ( isset ( $jsonobj->SearchResponse->Web->Results ))
	{
		// doc counter and total doc counter for scoring system
		$docID = 0; 
		$numDocs = count ( $jsonobj->SearchResponse->Web->Results );
		
		// Parse results and extract data to display
		foreach ( $jsonobj->SearchResponse->Web->Results as $value )
		{
			// put json elements in different strings
			$click = $value->Url;
			$title = $value->Title;
			$snippet = $value->Description;
			$url = $value->Url;
	
			// Increase the doc counter
			$docID++;
			// Create a normalised document score
			$normScore = 1 - (( $docID - 1 ) / $numDocs );
	
			// two-dimensional arrays to hold the title, snippet, url, score, relative to the clickurl
			$bing[$click]['title'] = $title;
			$bing[$click]['snippet'] = $snippet;
			$bing[$click]['url'] = $url;
			$bing[$click]['score'] = $normScore;
			$bing[$click]['docID'] = $docID;
		}
	}
	// If no results were returned
	else
		return false;		
//	} // end for loop if requesting more than 50 results
	
	// Return array		
	return $bing;
}

// yahoo request
function searchYahoo( $searchString ) 
{
	$yahoo = array();
	
	$appid = "UjZtwKTV34HgOqmhqGK5MNxEPBcteEwxAGfqS_BdmEkMRzZ.n9OUFOZLRfrBPg_Uqkx5tydHh0CTA9LCs3P.Gygt0BFu_sM-";
	$BASE_URL = 'http://query.yahooapis.com/v1/public/yql';
	$yahooRequest = 'select title,clickurl,abstract,dispurl from search.web(50) where query = "' . $searchString . '" and appid = "' . $appid . '"';
	$yahooResponse = $BASE_URL . "?q=" . urlencode( $yahooRequest ); // urlencode the whole request as per yahoo instructions
		
	libxml_use_internal_errors( true );
	$xml = simplexml_load_file( $yahooResponse );
	if ( !$xml )
	{
		$yahooresults .= "Failed loading XML\n";
		foreach ( libxml_get_errors() as $error )
		{
			$yahooresults .= "\t" . $error->message;
		}
	}
	
	if ( isset( $xml->results->result ))
	{
		$docID= 0; 
		$numDocs = count ( $xml->results->result );
		
		// Parse results and extract data to display
		foreach ( $xml->results->result as $value )
		{
			$click = $value->clickurl;
			$title = strip_tags( $value->title );
			$snippet = strip_tags( $value->abstract );
			$url = strip_tags( $value->dispurl );
			
			// fix the yahoo url
			$http = "http://";
			$endslash = "/";
			$yurl = $http . $url . $endslash;

			$docID++;
			$normScore = 1 - (( $docID - 1 ) / $numDocs );

			//$yahoo[$yurl]['click'] = $click; // not used: used url instead
			$yahoo[$yurl]['title'] = $title;
			$yahoo[$yurl]['snippet'] = $snippet;
			$yahoo[$yurl]['url'] = $url;
			$yahoo[$yurl]['score'] = $normScore;
			$yahoo[$yurl]['docID'] = $docID;
		}
	}
	else
		return false;
		
	return $yahoo;
}

// entireWeb request
function searchEntireWeb( $searchString ) 
{	
	$entireWeb = array();
	$entireWebRequest = 'http://www.entireweb.com/xmlquery?pz=ac287415b154910f5c6a34f3c3a6b730&ip=1.2.3.7&of=0&sc=9&format=json&n=50&q='. urlencode( $searchString );

	$session = curl_init();
	curl_setopt( $session, CURLOPT_URL, $entireWebRequest ); 
	curl_setopt( $session, CURLOPT_RETURNTRANSFER, 1 );
	//curl_setopt( $session, CURLOPT_REFERER, $_SERVER['HTTP_REFERER'] );
	$entireWebResponse = curl_exec( $session ); 
	
	// Check for curl errors
	if( curl_error( $session ))
		$entireWebresults .= 'ERROR: ' . curl_error( $session );
		
	// Close the curl session
	curl_close( $session );
		
	// Convert JSON to PHP object
	$jsonobj = json_decode( $entireWebResponse );
		
	// If there are results, then parse them
	if ( isset ( $jsonobj->hits ))
	{
		$docID= 0; 
		$numDocs = count ( $jsonobj->hits );

		foreach ( $jsonobj->hits as $value )
		{
			$click = $value->url;
			$title = $value->title;
			$snippet = $value->snippet;
			$url = $value->displayurl;

			$docID++;
			$normScore = 1 - (( $docID - 1 ) / $numDocs ); 

			$entireWeb[$click]['title'] = $title;
			$entireWeb[$click]['snippet'] = $snippet;
			$entireWeb[$click]['url'] = $url;
			$entireWeb[$click]['score'] = $normScore;
			$entireWeb[$click]['docID'] = $docID;
		}
	}
	else
		return false;
	
	return $entireWeb;
}

// blekko request
function searchBlekko( $searchString ) 
{
	$blekko = array();
	
	$blekkoRequest = 'http://blekko.com/ws/?q=' . urlencode( $searchString ) .'/json+/ps=50&auth=b58f6ba2';

	$session = curl_init();
	curl_setopt( $session, CURLOPT_URL, $blekkoRequest ); 
	curl_setopt( $session, CURLOPT_RETURNTRANSFER, 1 );
	//curl_setopt( $session, CURLOPT_REFERER, $_SERVER['HTTP_REFERER'] );
	$blekkoResponse = curl_exec( $session ); 
	
	// Check for curl errors
	if( curl_error( $session ))
		$blekkoresults .= 'ERROR: ' . curl_error( $session );
		
	// Close the curl session
	curl_close( $session );
		
	// Convert JSON to PHP object
	$jsonobj = json_decode( $blekkoResponse );
		
	// If there are results, then parse them
	if ( isset ( $jsonobj->RESULT ))
	{
		$docID= 0; 
		$numDocs = count ( $jsonobj->RESULT );

		foreach ( $jsonobj->RESULT as $value )
		{
			$click = strip_tags( $value->url );
			$title = strip_tags( $value->url_title );
			$snippet = strip_tags( $value->snippet );
			$url = strip_tags( $value->display_url );

			$docID++;
			$normScore = 1 - (( $docID - 1 ) / $numDocs ); 

			$blekko[$click]['title'] = $title;
			$blekko[$click]['snippet'] = $snippet;
			$blekko[$click]['url'] = $url;
			$blekko[$click]['score'] = $normScore;
			$blekko[$click]['docID'] = $docID;
		}
	}
	else
		return false;

	return $blekko;
}

// google request
function searchGoogle( $searchString ) 
{
	$google = array();
	$docID= 0; 
	$numDocs = count ( $jsonobj->items );
	for ( $i = 1 ; $i < 101 ; $i+=10 ) // $i  101 - n, where n is number of pages
	{	
		$googleRequest = "https://www.googleapis.com/customsearch/v1?key=AIzaSyAD3dfrKmlvkXxJkX8iPP-LfzTxC4iFDfI&q=$searchString&alt=json&start=$i"; 

		// Initialize session and set URL
		$session = curl_init();
		curl_setopt( $session, CURLOPT_URL,  urlencode( $googleRequest )); // urlencoded version
		// Set so curl_exec returns the result instead of outputting it
		curl_setopt( $session, CURLOPT_RETURNTRANSFER, 1 );
		//curl_setopt( $session, CURLOPT_REFERER, $_SERVER['HTTP_REFERER'] );
		// Get the response
		$googleResponse = curl_exec( $session );
		
		// Check for curl errors
		if( curl_error( $session ))
			$googleresults .= 'ERROR: ' . curl_error( $session );
			
		// Close the curl session
		curl_close( $session ); 
	
		// Convert JSON to PHP object
		$jsonobj = json_decode( $googleResponse );	

		// If there are results, then parse them
		if( isset( $jsonobj->items ))
		{
			foreach( $jsonobj->items as $value )
			{
				$click = $value->link;
				$title = $value->title;
				$snippet = strip_tags( $value->snippet );
				$url = $value->displayLink;

				$docID++;

				$google[$click]['title'] = $title;
				$google[$click]['snippet'] = $snippet;
				$google[$click]['url'] = $url;
				$google[$click]['docID'] = $docID;
			}
		}
		else
			return false;
	}
	return $google;
}						

// scraping function
function scrapeGoogle( $searchString ) 
{	
	$google = array();

	$googleRequest = 'http://www.google.com/custom?start=0&num=100&q='. $searchString .'&client=google-csbe&cx=AIzaSyA7HyFroWISKY59ptutRt4Dg0OvQogPZ8g';

	$session = curl_init(); 												
	curl_setopt( $session, CURLOPT_URL, $googleRequest );  
	curl_setopt( $session, CURLOPT_RETURNTRANSFER, 1 );  
	curl_setopt( $session, CURLOPT_CONNECTTIMEOUT, 10 );  
	$googleResponse = curl_exec( $session );  
	curl_close( $session );  
	$html= str_get_html( $googleResponse ); 

	$docID= 0; 
	$numDocs = count ( $html->find('div.g') ); 

	foreach( $html->find('div.g') as $result ) 
	{
		$a_l = $result->children(0)->children(0);
		$click = $a_l->href;
		$title = $a_l->plaintext;
		$snippet = $result->children(1)->plaintext;
		$url = $result->children(1)->children(0)->plaintext; 
		
		$docID++;

		$google[$click]['title'] = $title;
		$google[$click]['snippet'] = $snippet;
		$google[$click]['url'] = $url;
		$google[$click]['docID'] = $docID;
	}	
	$html->clear(); 
	unset($html);

	return $google;
}
?>