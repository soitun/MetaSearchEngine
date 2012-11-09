<?php
/**
 * Evaluation
 */
// Find the number of relevant docs per seach engine, relative to google
function getRelevantSet( $results , $google )  // $results = bing, yahoo, blekko, aggregated, non-aggregated
{
	foreach ( $results as $key => $value ) // key = click, and value = title, url, snippet, score
	{   // match docs by $click as always
		if ( array_key_exists( $key , $google ) )
		{   // count the number of matching docs = relevant set
			$relevantSet += 1;
		}
	}	
	// Return a number
	return $relevantSet;
}

// Precision and Precision@n
function getPrecision( $relevantSet , $resultsSet ) 
{	
	$precision = $relevantSet / $resultsSet; 	
	// Return a number
	return $precision;
}

// Get top K results
function getTopKSet( $results , $topK ) // $results = google, bing, yahoo, blekko, aggregated, non-aggregated ; $topK = 10 , 25, 50
{
	$topKSet = array();
	foreach ( $results as $key => $value ) 
	{
		if  ( $results[$key]['docID'] <= $topK ) 
		{
			$topKSet[$key]['docID'] = $results[$key]['docID'];
			$topKSet[$key]['title'] = $results[$key]['title'];
			$topKSet[$key]['snippet'] = $results[$key]['snippet'];
			$topKSet[$key]['url'] = $results[$key]['url'];
			$topKSet[$key]['score'] = $results[$key]['score'];	
		}
	}	
	// Return an array of the top documents
	return $topKSet;
}

// Recall		
function getRecall( $relevantSet , $googleRelevantSet ) // $bing, $yahoo, $blekko
{	
	$recall = $relevantSet / $googleRelevantSet;	
	// Return a number
	return $recall;
}

// F-measure
function getFmeasure( $precision , $recall )
{
	$fMeasure = (( 2 * ( $precision * $recall )) / ( $precision + $recall ));	
	// Return a number
	return $fMeasure;	
} 

// Average Precision
//function getAvgPrecision( $results , $google , &$rankRelevantSet ) // the var by reference is for testing only
function getAvgPrecision( $results , $google )
{	
	$rankRelevantSet = array();	
	foreach ( $results as $key => $value ) 
	{   
		if ( array_key_exists( $key , $google ) )
		{
			$rankRelevantSet[] = $results[$key]['docID'];
		}
	}	
	// calculate precision at each rank	
	$numRelevantDocs = 0;
	foreach ( $rankRelevantSet as $value ) // value of a docID is a number that corresponds to the rank
	{
			// Increase the relevant doc counter at each rank, and divide by that rank
			$numRelevantDocs += 1;	
			$precision += $numRelevantDocs / $value;	
	}
	// AvgPrecision is the precision at each rank / tot number of documents	included
	$avgPrecision = $precision / count ( $rankRelevantSet );
	
	// Return a number
	return $avgPrecision;
}
// MAP: save query avg precisions to a file, then use Excel for statistics
?>