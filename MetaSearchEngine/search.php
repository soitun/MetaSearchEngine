<?php 
require_once 'includes/restRequestFunctions.php'; 
require_once 'includes/queryPreprocessingFunctions.php';
require_once 'includes/porterStemming.php';
require_once 'includes/indexTfidfFunctions.php'; 
require_once 'includes/queryRewriteFunctions.php'; 
require_once 'includes/clusteringFunctions.php';
require_once 'includes/evaluationFunctions.php';
require_once 'includes/complexSearchFunctions.php'; 
require_once 'includes/displayModuleFunctions.php'; 
?>
<?php
// Initialize the session
if (!isset( $_SESSION )) 
	session_start();
?>
<?php
// Get data
if ( isset( $_GET['searchText'] ))
{
	$search = $_GET['searchText'];

	if (isset( $_GET['submit'])) 
	{
		// Tokenise search string and remove punctuation
		$searchExploded = tokeniseQuery( $search );

		// Query Re-write Engine using global method: Query Expansion
		$synonyms = getSynonyms( $searchExploded ); 
		
		// Query Pre-processing Module and Complex Search Ability			
		if (isset( $_GET['RadioGroup2'] )) 
		{
			$radioGroup2 = $_GET['RadioGroup2'];

			// If stemming is set to off
			if ( $radioGroup2 == 'stemmingOff' )
			{
				// Do boolean search if there are operators
				$operators = array( "AND", "and" , "OR" , "or", "NOT" , "not" );
			/*	foreach ( $searchExploded as $value )
				{
					if ( in_array( $value , $operators ))
					{
						$searchExploded = booleanQuery( $searchExploded ); 	
					}
					$search = implode( " " , $searchExploded );
				}
			*/
				foreach ( $searchExploded as $value )
				{
					if ( in_array( $value , $operators ))
					{
						$searchBing = booleanBing( $searchExploded ); 
						$searchEntireWeb = booleanEntireWeb( $searchExploded ); 
						$searchBlekko = booleanBlekko( $searchExploded ); 
						$searchGoogle = booleanGoogle( $searchExploded ); 
					
						$bing = searchBing( $searchBing );	
						if ( $bing == false )
							$bingresults = "Sorry, no results matching $search";
	
						$entireWeb = searchEntireWeb( $searchEntireWeb );		
						if ( $entireWeb == false )
							$entireWebresults = "Sorry, no results matching $search";
							
						$blekko = searchBlekko( $searchBlekko );		
						if ( $blekko == false )
							$blekkoresults = "Sorry, no results matching $search";
		
						$google = scrapeGoogle( urlencode( $searchGoogle )); 
						if ( $google == false )
							$googleresults = "Sorry, no results matching $search";		
					}
					else
					{
						$bing = searchBing( $search );	
						if ( $bing == false )
							$bingresults = "Sorry, no results matching $search";
				
						$entireWeb = searchEntireWeb( $search );		
						if ( $entireWeb == false )
							$entireWebresults = "Sorry, no results matching $search";
				
						$blekko = searchBlekko( $search );		
						if ( $blekko == false )
							$blekkoresults = "Sorry, no results matching $search";
		
						$google = scrapeGoogle( urlencode( $search ));
						if ( $google == false )
							$googleresults = "Sorry, no results matching $search";		
					}														
				}								
			} 
			// If stemming is set to on
			else if ( $radioGroup2 == 'stemmingOn' )
			{	
				// Remove stopwords and apply stemming
				$stemsUnique = applyPorterStemming( $searchExploded );
				$search = implode( " " , $stemsUnique ); 
				
				$bing = searchBing( $search );	
				if ( $bing == false )
					$bingresults = "Sorry, no results matching $search";
		
				$entireWeb = searchEntireWeb( $search );		
				if ( $entireWeb == false )
					$entireWebresults = "Sorry, no results matching $search";
		
				$blekko = searchBlekko( $search );		
				if ( $blekko == false )
					$blekkoresults = "Sorry, no results matching $search";

				$google = scrapeGoogle( urlencode( $search ));
				if ( $google == false )
					$googleresults = "Sorry, no results matching $search";			
			} 
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/** 
		* For evaluation only: to get MAP!
		 */ 
		/*
		// Read queries from file
		$queries = file ( 'lists/queries.txt' ); 
		
		// Send a REST request to the search engines for each query
		foreach ( $queries as $queryValue )
		{		
			$bing = searchBing( $queryValue );	
			if ( $bing == false )
				$bingresults = "Sorry, no results matching $queryValue";
			
			$entireWeb = searchEntireWeb( $queryValue );		
			if ( $entireWeb == false )
				$entireWebresults = "Sorry, no results matching $queryValue";
			
			$blekko = searchBlekko( $queryValue );		
			if ( $blekko == false )
				$blekkoresults = "Sorry, no results matching $queryValue";
			
			$google = scrapeGoogle( urlencode( $queryValue )); // needs to be urlencoded here
			if ( $google == false )
				$googleresults = "Sorry, no results matching $queryValue";	
				
			// comment out the REST requests below and run this loop including all up to line 320 (before writing results to file)
		*/		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/*
		// Send REST requests to the search engines
		$bing = searchBing( $search );	
		if ( $bing == false )
			$bingresults = "Sorry, no results matching $search";

		//$yahoo = searchYahoo( $search );		
		//if ( $yahoo == false )
		//	$yahooresults = "Sorry, no results matching $search";	

		$entireWeb = searchEntireWeb( $search );		
		if ( $entireWeb == false )
			$entireWebresults = "Sorry, no results matching $search";

		$blekko = searchBlekko( $search );		
		if ( $blekko == false )
			$blekkoresults = "Sorry, no results matching $search";

		//$google = searchGoogle( urlencode( $search ) );	
		//if ( $google == false )
		//	$googleresults = "Sorry, no results matching $search";

		// Scraping Google results
		$google = scrapeGoogle( urlencode( $search ));
		if ( $google == false )
			$googleresults = "Sorry, no results matching $search";
		*/
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/** 
		* For testing only: display results by search engine!
		 */ 
		/*	
		$bingresults .= '<div class="searchresult"><h3>Bing</h3><ul>';
		foreach ( $bing as $key => $value )
		{
			$bingresults .= '<br /><li>' . $value['docID'] . ', Score: ' . round( $value['score'],2, PHP_ROUND_HALF_UP ) . 
			'<br/><a href=' . $key . '>' . $value['title'] . '</a><br />' . $value['snippet'] . 
			'<br/><cite class="resulturl">' . $value['url'] . '</cite></li><br />';
		}
		$bingresults .= '</ul></div>';
		
		
		$yahooresults .= '<div class="searchresult"><h3>Yahoo</h3><ul>';
		foreach ( $yahoo as $key => $value )
		{
			$yahooresults .= '<br /><li>' . $value['docID'] . ', Score: ' . round( $value['score'],2, PHP_ROUND_HALF_UP ) . 
			'<br/><a href=' . $key . '>' . $value['title'] . '</a><br />' . $value['snippet'] . 
			'<br/><cite class="resulturl">' . $value['url'] . '</cite></li><br />';
		}
		$yahooresults .= '</ul></div>';	
		
		
		$entireWebresults .= '<a href="http://www.entireweb.com/"><img src="http://media.entireweb.com/images/pages/searchapi/logos/ew_poweredby_badge.png" width="155" height="30" alt="Powered by Entireweb" /></a>';
		$entireWebresults .= '<div class="searchresult"><h3>Entire Web</h3><ul>';
		foreach ( $entireWeb as $key => $value )
		{
			$entireWebresults .= '<br /><li>' . $value['docID'] . ', Score: ' . round( $value['score'],2, PHP_ROUND_HALF_UP ) . 
			'<br/><a href=' . $key . '>' . $value['title'] . '</a><br />' . $value['snippet'] . 
			'<br/><cite class="resulturl">' . $value['url'] . '</cite></li><br />';
		}
		$entireWebresults .= '</ul></div>';	
		
		
		$blekkoresults .= '<div class="searchresult"><h3>Blekko</h3><ul>';
		foreach ( $blekko as $key => $value )
		{
			$blekkoresults .= '<br /><li>' . $value['docID'] . ', Score: ' . round( $value['score'],2, PHP_ROUND_HALF_UP ) . 
			'<br/><a href=' . $key . '>' . $value['title'] . '</a><br />' . $value['snippet'] . 
			'<br/><cite class="resulturl">' . $value['url'] . '</cite></li><br />';
		}
		$blekkoresults .= '</ul></div>';	


		$googleresults .= '<div class="searchresult"><h3>Google</h3><ul>';
		foreach ( $google as $key => $value )
		{
			$googleresults .= '<br /><li>' . $value['docID'] . '<br/><a href=' . $key . '>' 
			. $value['title'] . '</a><br />' . $value['snippet'] .  '</li><br />';
		}	
		$googleresults .= '</ul></div>';	
		*/	
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
		// Find evaluation metrics for each search engine
		
		// Find each search engine relevant set relative to google
		$bingRelevantSet = getRelevantSet( $bing , $google );
		//$yahooRelevantSet = getRelevantSet( $yahoo , $google );
		$entireWebRelevantSet = getRelevantSet( $entireWeb , $google );
		$blekkoRelevantSet = getRelevantSet( $blekko , $google );
		
		// Find google relevant set
		$googleRelevantSet = count ( $google );
		
		// Find total results in each list
		$bingTotalSet = count ( $bing );
		//$yahooTotalSet = count ( $yahoo );
		$entireWebTotalSet = count ( $entireWeb );
		$blekkoTotalSet = count ( $blekko );
		
		// Precision: uses the relevant set and the total results set from a search engine
		$bingPrecision = getPrecision( $bingRelevantSet , $bingTotalSet ); 
		//$yahooPrecision = getPrecision( $yahooRelevantSet , $yahooTotalSet );
		$entireWebPrecision = getPrecision( $entireWebRelevantSet , $entireWebTotalSet );
		$blekkoPrecision = getPrecision( $blekkoRelevantSet , $blekkoTotalSet ); 
		
		// Recall: uses the relevant set form an SE and the google tot relevant set	
		$bingRecall = getRecall( $bingRelevantSet , $googleRelevantSet ); 
		//$yahooRecall = getRecall( $yahooRelevantSet , $googleRelevantSet );
		$entireWebRecall = getRecall( $entireWebRelevantSet , $googleRelevantSet );
		$blekkoRecall = getRecall( $blekkoRelevantSet , $googleRelevantSet ); 

		// Get top 10 results from all search engines
		$bingTop10Set = getTopKSet( $bing , 10 );
		//$yahooTop10Set = getTopKSet( $yahoo , 10 );	
		$entireWebTop10Set = getTopKSet( $entireWeb , 10 );	
		$blekkoTop10Set = getTopKSet( $blekko , 10 );	
		
		// Find relevant top10set vs google
		$bingTop10RelevantSet = getRelevantSet( $bingTop10Set , $google );
		//$yahooTop10RelevantSet = getRelevantSet( $yahooTop10Set , $google );
		$entireWebTop10RelevantSet = getRelevantSet( $entireWebTop10Set , $google );
		$blekkoTop10RelevantSet = getRelevantSet( $blekkoTop10Set , $google );

		// Precision @ n = 10                                                         
		$bingPrecisionAt10 = getPrecision( $bingTop10RelevantSet , 10 );
		//$yahooPrecisionAt10 = getPrecision( $yahooTop10RelevantSet , 10 );
		$entireWebPrecisionAt10 = getPrecision( $entireWebTop10RelevantSet , 10 );
		$blekkoPrecisionAt10 = getPrecision( $blekkoTop10RelevantSet , 10 );	
		
		// F-measure
		$bingFmeasure = getFmeasure( $bingPrecision , $bingRecall );
		//$yahooFmeasure = getFmeasure( $yahooPrecision , $yahooRecall );
		$entireWebFmeasure = getFmeasure( $entireWebPrecision , $entireWebRecall );
		$blekkoFmeasure = getFmeasure( $blekkoPrecision , $blekkoRecall );
		
		// Average Precision: sum of precision of relevant docs at each rank / tot docs used
		$bingAvgPrecision = getAvgPrecision( $bing , $google );
		//$yahooAvgPrecision = getAvgPrecision( $yahoo , $google );
		$entireWebAvgPrecision = getAvgPrecision( $entireWeb , $google );
		$blekkoAvgPrecision = getAvgPrecision( $blekko , $google );

		// Display Module: Aggregated and Non Aggregated Results
	
		// Create a unique list of results
		//$uniqueResults = getUniqueResults( $bing, $yahoo, $blekko, $bingAvgPrecision, $yahooAvgPrecision, $blekkoAvgPrecision );	
		$uniqueResults = getUniqueResults( $bing, $entireWeb, $blekko, $bingAvgPrecision, $entireWebAvgPrecision, $blekkoAvgPrecision );
			
		// Create a list of results aggregated  by score (at equal score, by avg precision of search engine)
		$aggregated = getAggregatedResults( $uniqueResults );
		
		// Display the unique list of docs, associated with title, snippet and url
		$aggregatedList .= '<div class="searchresult"><h3>Aggregated Results</h3><ul>';
		foreach ( $aggregated as $key => $value )
		{
			$aggregatedList .= 	'<br /><li>' . $value['docID'] . ', Score: ' . round( $value['score'],2, PHP_ROUND_HALF_UP ) . 
			'<br/><a href=' . $key . '>' . $value['title'] .			 
			'</a><br />' . $value['snippet'] . '<br/><cite class="resulturl">' . $value['url'] . '</cite></li><br />';
		}	
		$aggregatedList .= '</ul></div>';
			
		// Evaluation metrics for aggregated results list
		$aggregatedRelevantSet = getRelevantSet( $aggregated , $google );
		$aggregatedTotalSet = count ( $aggregated );
		$aggregatedPrecision = getPrecision( $aggregatedRelevantSet , $aggregatedTotalSet); 
		$aggregatedRecall = getRecall( $aggregatedRelevantSet , $googleRelevantSet );
		$aggregatedFmeasure = getFmeasure( $aggregatedPrecision , $aggregatedRecall );
		$aggregatedAvgPrecision = getAvgPrecision( $aggregated , $google );
		$aggregatedTop10Set = getTopKSet( $aggregated , 10 );
		$aggregatedTop10RelevantSet = getRelevantSet( $aggregatedTop10Set , $google );
		$aggregatedPrecisionAt10 = getPrecision( $aggregatedTop10RelevantSet , 10 );
		
		// Create a list of non aggregated results (avg precision of search engine determines order of engines lists)
		$nonAggregated = getNonAggregatedResults( $uniqueResults );
		
		// Display the unique list of docs, associated with title, snippet and url
		$nonAggregatedList .= '<div class="searchresult"><h3>Non Aggregated Results</h3><ul>';
		foreach ( $nonAggregated as $key => $value )
		{
			$nonAggregatedList .= 	'<br /><li>' . $value['docID'] . ', Score: ' . round( $value['score'],2, PHP_ROUND_HALF_UP ) .
			', Engine: ' . $value['engineName'] . '<br/><a href=' . $key . '>' . $value['title'] .			 
			'</a><br />' . $value['snippet'] . '<br/><cite class="resulturl">' . $value['url'] . '</cite></li><br />';
		}	
		$nonAggregatedList .= '</ul></div>';
			
		// Evaluation metrics for non-aggregated results list
		$nonAggregatedRelevantSet = getRelevantSet( $nonAggregated , $google );
		$nonAggregatedTotalSet = count ( $nonAggregated );
		$nonAggregatedPrecision = getPrecision( $nonAggregatedRelevantSet , $nonAggregatedTotalSet); 
		$nonAggregatedRecall = getRecall( $nonAggregatedRelevantSet , $googleRelevantSet );
		$nonAggregatedFmeasure = getFmeasure( $nonAggregatedPrecision , $nonAggregatedRecall );
		$nonAggregatedAvgPrecision = getAvgPrecision( $nonAggregated , $google );
		$nonAggregatedTop10Set = getTopKSet( $nonAggregated , 10 );
		$nonAggregatedTop10RelevantSet = getRelevantSet( $nonAggregatedTop10Set , $google );
		$nonAggregatedPrecisionAt10 = getPrecision( $nonAggregatedTop10RelevantSet , 10 );		

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/** 
		* For evaluation only continued: to get MAP!
		 */ 
		
		// If evaluation are created manually, ignore previous section on MAPs and start here
		$queries = file ( 'lists/queries.txt' );

		// Display queries as links to search manually
		foreach ( $queries as $value )
		{	
			$queriesString .= "<table border='0' cellpadding='0' ><tr><td>
			<a href='http://localhost/MetaSearchEngine/search.php?RadioGroup2=".$_GET['RadioGroup2']."&searchText=$value&submit=+Search+&RadioGroup1=".$_GET['RadioGroup1']."'>" . $value . "</a></tr></table>";
		}	
		/*	
		// Create a string of all scores and copy them to a file for offline post-processing
		$allAvgPrecision = "\nQuery | ". $search . 
		"\nBing | ". $bingPrecision ." | ". $bingRecall ." | ". $bingFmeasure ." | ". $bingPrecisionAt10 ." | ". $bingAvgPrecision .
		"\nEntireWeb | ". $entireWebPrecision  ." | ". $entireWebRecall ." | ". $entireWebFmeasure ." | ". $entireWebPrecisionAt10 ." | ". $entireWebAvgPrecision .
		"\nBlekko | ". $blekkoPrecision ." | ". $blekkoRecall ." | ". $blekkoFmeasure ." | ". $blekkoPrecisionAt10 ." | ". $blekkoAvgPrecision . 
		"\nNon-Aggregated | ". $nonAggregatedPrecision ." | ". $nonAggregatedRecall ." | ". $nonAggregatedFmeasure ." | ". $nonAggregatedPrecisionAt10 ." | ". $nonAggregatedAvgPrecision .
		"\nAggregated | ". $aggregatedPrecision ." | ". $aggregatedRecall ." | ". $aggregatedFmeasure ." | ". $aggregatedPrecisionAt10 ." | ". $aggregatedAvgPrecision;		
		*/
		
		/*	
		// If evaluation is to be run automatically, using loop form line 68, ignore the above and pic up from here	
 		$allAvgPrecision .= "\nQuery | ". $queryValue . 
		"\nBing | ". $bingPrecision ." | ". $bingRecall ." | ". $bingFmeasure ." | ". $bingPrecisionAt10 ." | ". $bingAvgPrecision .
		"\nEntireWeb | ". $entireWebPrecision  ." | ". $entireWebRecall ." | ". $entireWebFmeasure ." | ". $entireWebPrecisionAt10 ." | ". $entireWebAvgPrecision .
		"\nBlekko | ". $blekkoPrecision ." | ". $blekkoRecall ." | ". $blekkoFmeasure ." | ". $blekkoPrecisionAt10 ." | ". $blekkoAvgPrecision . 
		"\nNon-Aggregated | ". $nonAggregatedPrecision ." | ". $nonAggregatedRecall ." | ". $nonAggregatedFmeasure ." | ". $nonAggregatedPrecisionAt10 ." | ". $nonAggregatedAvgPrecision .
		"\nAggregated | ". $aggregatedPrecision ." | ". $aggregatedRecall ." | ". $aggregatedFmeasure ." | ". $aggregatedPrecisionAt10 ." | ". $aggregatedAvgPrecision;		
		
		} // Close the loop here
		*/		
		/*
		// For each query, append  Average Precision measures into a txt file			    
		$file = 'lists/maps.txt';	
		file_put_contents( $file, $allAvgPrecision, FILE_APPEND );
		*/		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		// Display Evaluation metrics (server does not handle the round() function though
		$statistics .= 	'<table border="1" cellpadding="5" >
						<tr>
							<th>Measure</th>
							<th>Bing (' . $bingTotalSet .' )</th>
							<th>EntireWeb (' . $entireWebTotalSet .' )</th>
							<th>Blekko (' . $blekkoTotalSet .' )</th>
							<th>Non-Aggregated (' . $nonAggregatedTotalSet .' )</th>
							<th>Aggregated (' . $aggregatedTotalSet .' )</th>
						</tr>
						<tr>
							<th>Precision</th>
							<td>'. round( $bingPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $entireWebPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $blekkoPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $nonAggregatedPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $aggregatedPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
						</tr>
							<th>Recall</th>
							<td>'. round( $bingRecall, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $entireWebRecall, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $blekkoRecall, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $nonAggregatedRecall, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $aggregatedRecall, 2, PHP_ROUND_HALF_UP ) .'</td>
						</tr>
						<tr>
							<th>F-measure</th>
							<td>'. round( $bingFmeasure, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $entireWebFmeasure, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $blekkoFmeasure, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $nonAggregatedFmeasure, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $aggregatedFmeasure, 2, PHP_ROUND_HALF_UP ) .'</td>
						</tr>
						<tr>
							<th>Precision@n=10</th>
							<td>'. round( $bingPrecisionAt10, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $entireWebPrecisionAt10, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $blekkoPrecisionAt10, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $nonAggregatedPrecisionAt10, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $aggregatedPrecisionAt10, 2, PHP_ROUND_HALF_UP ) .'</td>
						</tr>
							<th>Average Precision</th>
							<td>'. round( $bingAvgPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $entireWebAvgPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $blekkoAvgPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $nonAggregatedAvgPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
							<td>'. round( $aggregatedAvgPrecision, 2, PHP_ROUND_HALF_UP ) .'</td>
						</tr>
						</tr></table>';
		

		// Query Re-write Engine using local method to apply after query is sent to search engines: Pseudo relevance feedback
	
		// Create tf-idfs for the aggregated list (if using independent search engines, use count instead of fix $topK)	
		$top10 = 10;
		$top20 = 20;
		$top25 = 25;
		$top30 = 30;
		$top50 = 50;

		// Get a range of top K results sets
		$aggregatedTop10 = getTopKSet( $aggregated , $top10 );
		//$aggregatedTop25 = getTopKSet( $aggregated , $top25 );
		//$aggregatedTop50 = getTopKSet( $aggregated , $top50 );
		
		/*
		// test aggregated top K list
		$aggregatedTop50List .= '<div class="searchresult"><h3>Yahoo Top50 Results</h3><ul>';
		foreach ( $aggregatedTop50 as $key => $value )
		{
			$aggregatedTop50List .= 	'<br /><li>' . $value['docID'] . ', Score: ' . round( $value['score'],2, PHP_ROUND_HALF_UP ) . 
			' , Engine: ' . $value['engineName'] . '<br/><a href=' . $key . '>' . $value['title'] .			 
			'</a><br />' . $value['snippet'] . '<br/><cite class="resulturl">' . $value['url'] . '</cite></li><br />';
		}	
		$aggregatedTop50List .= '</ul></div>';	
		*/	
			
		// Create the index for varying top K
		$aggregatedTop10Index = getIndex( $aggregatedTop10 ); 
		//$aggregatedTop25Index = getIndex( $aggregatedTop25 );	
		//$aggregatedTop50Index = getIndex( $aggregatedTop50 );
		
		/*		
		// test aggregated index
		foreach ( $aggregatedTop50Index['dictionary'] as $termAsKey => $value ) // key = $term
		{
			foreach ( $value['termFrequency'] as $docIDAsKey => $value2 ) // key2 = docID , value2 = value of termFrequenecy
			{	
				$aggregatedTop50Dictionary .= $termAsKey . "=> docID: " . $docIDAsKey . "=> tf: " . $value2 . "=> df: " . $value['docFrequency'] . "<br/>";				
			}
		}
		*/
		
		// Get tfidfs
		$aggregatedTop10Tfidf = getTfidf( $aggregatedTop10Index , $top10 ); 
		//$aggregatedTop25Tfidf = getTfidf( $aggregatedTop25Index , $top25 ); 	
		//$aggregatedTop50Tfidf = getTfidf( $aggregatedTop50Index , $top50 ); 
		
		// Flatten the array for sorting 
		$aggregatedTop10TfidfFlat = flattenTfidf( $aggregatedTop10Tfidf );
		//$aggregatedTop25TfidfFlat = flattenTfidf( $aggregatedTop25Tfidf );
		//$aggregatedTop50TfidfFlat = flattenTfidf( $aggregatedTop50Tfidf );

		/*		
		// test aggregated tfidf scores
		foreach ( $aggregatedTop10TfidfFlat as $key => $value )
		{
			$Top10Tfidf .= $key . "=>" . $value['term'] . " in " . $value['docID'] . " has tf-idf: " . $value['tfidf'] . "<br/>";
		}	
		*/		
		
		// Select the top 20, 25, 30 terms according to tfidf, from each of the top 10, 25, 50 documents
		$aggregatedtop20TermsTop10Set = getTopXSet( $aggregatedTop10TfidfFlat , $top20 ); 
		//$aggregatedtop25TermsTop10Set = getTopXSet( $aggregatedTop10TfidfFlat , $top25 );
		//$aggregatedtop30TermsTop10Set = getTopXSet( $aggregatedTop10TfidfFlat , $top30 );
		
		//$aggregatedtop20TermsTop25Set = getTopXSet( $aggregatedTop25TfidfFlat , $top20 );
		//$aggregatedtop25TermsTop25Set = getTopXSet( $aggregatedTop25TfidfFlat , $top25 );
		//$aggregatedtop30TermsTop25Set = getTopXSet( $aggregatedTop25TfidfFlat , $top30 );
		
		//$aggregatedtop20TermsTop50Set = getTopXSet( $aggregatedTop50TfidfFlat , $top20 );
		//$aggregatedtop25TermsTop50Set = getTopXSet( $aggregatedTop50TfidfFlat , $top25 );
		//$aggregatedtop30TermsTop50Set = getTopXSet( $aggregatedTop50TfidfFlat , $top30 );

		// Display terms from array
		$top20Terms = displayTerms( $aggregatedtop20TermsTop10Set , $search );
		

		// Display Modules	
		if (isset($_GET['RadioGroup1'] )) 
		{
			$radioGroup1 = $_GET['RadioGroup1']; 
	
			// if aggregate option is chosen, display results sorted by score
			if ( $radioGroup1 == 'aggregate' )
			{	
				$_SESSION['metasearch'] = $aggregatedList; 
				header ( 'Location: ' . $_SERVER['HTTP_REFERER'] );

				//$_SESSION['googleresults'] = $googleresults; 
				//header ( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			}
			// if non-aggregate option is chosen, display results ordered by high-low avg precision of search engine
			else if ( $radioGroup1 == 'non-aggregate' ) // always use the name from the form
			{			
				//$_SESSION['metasearch'] = $bingresults . '<br/>' . $entireWebresults . '<br/>' . $blekkoresults;
				$_SESSION['metasearch'] = $nonAggregatedList; 
				header ( 'Location: ' . $_SERVER['HTTP_REFERER'] );

				//$_SESSION['googleresults'] = $googleresults; 
				//header ( 'Location: ' . $_SERVER['HTTP_REFERER'] );
				
			} 
			// if cluster option is chosen
			else if ( $radioGroup1 == 'cluster' )
			{	
				// Create index, tfidf, etc for unique results list
				$resultsIndex = getIndex( $nonAggregated ); 
				$resultsTfidf = getTfidf( $resultsIndex , $nonAggregatedTotalSet ); 
				
				// Normalise input data for centroids
				$normalisedTfidf = normaliseTfidf( $resultsTfidf );
				
				/*	
			 	// test
				foreach ( $normalisedTfidf as $docID => $value )
				{
					foreach ( $value as $term => $tfidfValue )
					{
						$normal .= "docId: " . $docID . " , term: " . $term . " , tf-idf: " . $tfidfValue . "<br />";
					}
				}
				*/	
				
				// Calculate centroids, map docs to them
				$numClusters = 5; 
				$mapDocToClusterID = kMeans( $normalisedTfidf , $numClusters );	
				
				// Get the clustered results list
				$clusteredList = getClusteredResults( $nonAggregated, $mapDocToClusterID ); 		
				
				/*				
				// test	
				$tfidfmax = array();
				$tfidfmin = array();
				$centroidsCoord = initialiseCentroids( $normalisedTfidf , $numClusters , $tfidfmax, $tfidfmin );				
				foreach ( $tfidfmax as $term => $tfidfMax )
				{
					$maxmin .= $term .  " => Max:" . $tfidfMax . " Min:" . $tfidfmin[$term] . "<br />";
					if( $tfidfMax != $tfidfmin[$term] )
						$maxmin .= "HOLY SHIT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! <br/>";
				}
				foreach ( $centroidsCoord as $clusterID => $value )
				{
					foreach ( $value as $term => $tfidf )
					{
						$centre .= $clusterID .  " => " . $term .  " => " .$tfidf . "<br />";
					}
				}				
				$maxVal = max($tfidfmax);
				$minVal = min($tfidfmin);		
				*/				
								
				$_SESSION['metasearch'] = $clusteredList;
				header ( 'Location: ' . $_SERVER['HTTP_REFERER'] ); 

				//$_SESSION['googleresults'] = $nonAggregatedList;
				//header ( 'Location: ' . $_SERVER['HTTP_REFERER'] );
			}	
			
		} // end radio groups
	} // end if submit
} // end if searchText

// create a session that stores the previous search
$_SESSION['prev_search'] = $search;
header ( 'Location: ' . $_SERVER['HTTP_REFERER'] );
// stats
$_SESSION['stats'] =  $statistics;
header('Location: ' . $_SERVER['HTTP_REFERER']);
// error
$_SESSION['error'] = $error;
header('Location: ' . $_SERVER['HTTP_REFERER']);
// synonyms
$_SESSION['synonyms'] =  $synonyms;
header('Location: ' . $_SERVER['HTTP_REFERER']);
// pseudo relevance and 50 queries
$_SESSION['pseudoRelevance'] = $top20Terms . "<br />List of 50 Queries:<br />" . $queriesString;
header ( 'Location: ' . $_SERVER['HTTP_REFERER'] );
?>