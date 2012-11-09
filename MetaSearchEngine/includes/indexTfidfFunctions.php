<?php
/**
 * Index and tf-idf scores to use with Pseudo relevance feedback and Clustering
 */	
// Get the inverted index: pass top K snippets for results list with values of 10, 25, 50, return array index
function getIndex( array $arrayTopK ) 
{	
	$snippetCollection = array();
	// Create a new array with docID pointing to the snippet
	foreach ( $arrayTopK as $value )
	{	
		$docID = $value['docID']; 
		$snippetCollection[$docID] = $value['snippet'];
		// Count tot documents (to use in tf-idf)
		$docCount = count( $snippetCollection );
	}
	
	foreach ( $snippetCollection as $docID => $snippet ) 
	{
		$snippetString .= $docID . "=>" . $snippet . "<br/>"; // for test 
		$terms = explode( " " , strtolower( preg_replace( "/[^a-zA-Z]+/" , " " , $snippet ))); 
			
		// Count the term occurences within each document (before removing duplicates)
		$termFrequency[$docID] = array_count_values( $terms );	
		
		// Remove stopwords: do not want to include these in dictionary
		$termsDiff = stopwordRemoval( $terms ); 
	
		foreach ( $termsDiff as $term )
		{						
			// Remove strings shorter than 3 letters (take care of us, ie, au, etc)
			if ( strlen( $term ) >= 3 )
			{	
				if( !isset($dictionary[$term]['docFrequency'] ))
				{	// Initialise member docFrequency count at zero
					$dictionary[$term]['docFrequency'] = 0;	
				}
				// Increase member docFrequency count each time the term is found
				$dictionary[$term]['docFrequency']++;
				
				// per term, and per docID, add the termFrequency (postings are docID + termFrequency)
				$dictionary[$term]['termFrequency'][$docID] = $termFrequency[$docID][$term];
			}
		}	
	}
	// Sort by key
	ksort( $dictionary );
	
	// Return the inverstedIndex which contains dicitonary and termFrequency
	$topKIndex = array('docCount' => $docCount, 'dictionary' => $dictionary);	

	return $topKIndex;
}

// Function to calculate the tf-idf scores for each term
function getTfidf( array $topKIndex , $topKCount ) // the index for topK snippet and the num of docs used
{
	$tfidf = array();
	$maxTermFrequency = 0;
	foreach ( $topKIndex['dictionary'] as $termAsKey => $value ) 
	{
		foreach ( $value['termFrequency'] as $docIDAsKey => $value2 ) //value2 = value of termFrequenecy
		{	// this is for test only
			//$TopKDictionary .= $termAsKey . "=> docID: " . $docIDAsKey . "=> tf: " . $value2 . "=> df: " . $value['docFrequency'] . "<br/>";				
			
			//frequency of most frequent term in whole dictionary:
			// if the term frequency encountered is larger than the currently set $maxTermFreqeuncy, reset $maxTermFreqeuncy 			
			if( $value2 > $maxTermFrequency ) 
			{	
				$maxTermFrequency = $value2; 
			}
		}
	}
	// Use $maxTermFrequency to normalise the tf (acording to lecture by Martina Naughton)
	foreach ( $topKIndex['dictionary'] as $termAsKey => $value ) 
	{		
		// calculate tf-idf:  relevancy of a document to a term	
		foreach ( $value['termFrequency'] as $docIDAsKey => $value2 ) 
		{
			// normalise tf = tf / max (tf)			
			$tf = $value2 / $maxTermFrequency;
			$idf = log( $topKCount / $value['docFrequency'], 2 );	

			// Store the score in an array with docid as key
			$tfidf[$docIDAsKey][$termAsKey] = $tf * $idf;
		}		
	}
	// Return the array with tf-idf scores
	return $tfidf;
}
?>