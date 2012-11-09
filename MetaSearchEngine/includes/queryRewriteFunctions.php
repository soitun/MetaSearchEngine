<?php
/**
 * Query Re-write Engine using global method: Query Expansion
 */
// Find sysnonyms for all the non-stopwords in an array of terms
function getSynonyms( array $searchArray )
{
	// Remove stopwords: do not want to find synonyms for these
	$searchDiff = stopwordRemoval( $searchArray ); 
	// For each word in the search array, find synonyms
	foreach ( $searchDiff as $key => $value ) 
	{			
		$result = array();	
		// Initialise variables for http request
		$apikey = "yoNJkbNlb7pQEYhSflTz"; 
		$language = "en_US"; 
		$endpoint = "http://thesaurus.altervista.org/thesaurus/v1";
		$word = "$value"; 
	
		// Invoke the remote service
		$session = curl_init(); 
		curl_setopt( $session, CURLOPT_URL, "$endpoint?word=".urlencode($word)."&language=$language&key=$apikey&output=json" ); 
		curl_setopt( $session, CURLOPT_RETURNTRANSFER, 1 ); 
		$data = curl_exec( $session ); 
		$info = curl_getinfo( $session ); 
		curl_close( $session );
		
		// Check request has been processed and parse response
		if ($info['http_code'] == 200) 
		{ 
			$result = json_decode( $data, true ); 
	  
			// Put all synonyms into a string
			foreach ( $result['response'] as $value )
			{
				$terms = strtolower ( str_replace( "(antonym)" , " " , $value["list"]["synonyms"] ));
			}

			// Explode the string into an array of synonym tokens
			$termsArray = explode( "|" , $terms );
			foreach ( $termsArray as $value )
			{	
				//$synonyms .= "<a href='http://elenamagno.host56.com//MetaSearchEngine/search.php?RadioGroup2=stemmingOff&searchText=$value&submit=+Search+&RadioGroup1=non-aggregate'>" . $value . "</a>" . "&nbsp&nbsp";
				//$synonyms .= "<a href='http://localhost/MetaSearchEngine/search.php?RadioGroup2=stemmingOff&searchText=$value
				//			  &submit=+Search+&RadioGroup1=non-aggregate'>" . $value . "</a>" . "&nbsp&nbsp";
				// same: this will search according to the last settings
				$synonyms .= "<a href='http://localhost/MetaSearchEngine/search.php?RadioGroup2=".$_GET['RadioGroup2']."&searchText=".
							  $value."&submit=+Search+&RadioGroup1=".$_GET['RadioGroup1']."'>" . $value . "</a>" . "&nbsp&nbsp";
			}
		  
		} 
	//	else 
	//		$synonyms = "Http Error: ".$info['http_code'];
	}
	
	return $synonyms;
}

/**
 * Pseudo relevance feedback
 */	

// Function to sort by tfidf
function sortTfidf ( $x , $y ) 
{
	if ( $x['tfidf'] == $y['tfidf'] )
		return 0;
	elseif ( $x['tfidf'] > $y['tfidf'] )
		return -1;
	else
		return 1;
}

// Use for easier sorting
function flattenTfidf( array $tfidf )
{
	$tfidfOneDimensional = array();
	$oneDimensionalIndex = 0;	
	foreach ( $tfidf as $key => $value )
	{
		foreach( $value as $key2 => $value2 )
		{
			// place term-docID-tfidf in one object of a new one-dimensional array
			$tfidfOneDimensional[$oneDimensionalIndex]['docID'] = $key;
			$tfidfOneDimensional[$oneDimensionalIndex]['term'] = $key2;
			$tfidfOneDimensional[$oneDimensionalIndex]['tfidf'] = $value2;
			$oneDimensionalIndex++;
		}
	}
	// Sort the array by tf-idf scores
	usort( $tfidfOneDimensional , 'sortTfidf' );

	return $tfidfOneDimensional;
}

// Function to extract the topX terms
function getTopXSet( array $tfidfOneDimensional , $topX ) // $topX = 20 , 25, 30
{	
	$topXSet = array();
	foreach ( $tfidfOneDimensional as $key => $value )
	{
	//	$tfidfOneDimensional .= $key . "=>" . $value['term'] . " in " . $value['docID'] . " has tf-idf: " . $value['tfidf'] . "<br/>";
		if  ( $key < $topX ) 
		{
			$topXSet[] = $value['term'];
		}	
	}	
	// Return an array of top terms
	return $topXSet;
}

// Function to display the terms
function displayTerms( array $topXSet, $searchString )
{
	$uniqueTerms = array_unique( $topXSet );
	foreach ( $uniqueTerms as $key => $value )
	{								  // http://elenamagno.host56.com//MetaSearchEngine/
		//$topTerms .= "<table border='0' cellpadding='0' >
		//			    <tr><td><a href='http://localhost/MetaSearchEngine/search.php?RadioGroup2=stemmingOff&searchText=".
		//				$searchString . "+" . $value."&submit=+Search+&RadioGroup1=non-aggregate'>".$value."</a></td></tr></table>";
		$topTerms .= "<table border='0' cellpadding='0' >
					  <tr><td><a href='http://localhost/MetaSearchEngine/search.php?RadioGroup2=".$_GET['RadioGroup2']."&searchText=".
					  $searchString."+".$value."&submit=+Search+&RadioGroup1=".$_GET['RadioGroup1']."'>".$value."</a></td></tr></table>";
	}				              

	return $topTerms;
}
?>