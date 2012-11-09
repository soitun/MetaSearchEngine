<?php
/**
 * Clustering
 */
// Normalise input data
//function normaliseTfidf( array $tfidf , &$total ) // test
function normaliseTfidf( array $tfidf )
{	
	$normalisedTfidf = array();
	$total = 0;
	// Calculate the sum of squares of each term tfidf
    foreach( $tfidf as $docID => $value )
    {
    	foreach( $value as $term => $tfidfValue )
    	{
 		   	$total += $tfidfValue * $tfidfValue;
    	}
    }
    // Take the square root of the total of squared tf-idf values
    $total = sqrt( $total );
    // Divide each term tfidf by the sqrt of the total
    foreach( $tfidf as $docID => $value )
    {
    	foreach( $value as $term => $tfidfValue )
       	{
    		$normalisedTfidf[$docID][$term] = $tfidfValue / $total;
    	}
    }
    return $normalisedTfidf;
}

// Initialise the centres of the k clusters to a random number in the range min-max of normalised tf-idf 
//function initialiseCentroids( $normalisedTfidf , $numClusters , &$tfidfmax , &$tfidfmin ) // for test
function initialiseCentroids( $normalisedTfidf , $numClusters )
{
	$centroids = array();
	$tfidfmax = array();
	$tfidfmin = array(); 

	// Min and Max arrays will contain all dimentions (all terms)
    foreach( $normalisedTfidf as $docID => $value )
    {	
    	foreach( $value as $term =>  $tfidfValue )	
    	{	
    		if( !isset( $tfidfmax[$term] ) || $tfidfValue > $tfidfmax[$term] ) 
			{
				$tfidfmax[$term] = $tfidfValue;
			}
			if( !isset( $tfidfmin[$term] ) || $tfidfValue < $tfidfmin[$term] ) 
			{
				$tfidfmin[$term] = $tfidfValue;
			}
		}
	}
	// Randomly generate a centroid, do it 5 times
	for ( $i = 0; $i < $numClusters ; $i++ ) 
	{
		$centroids[$i] = initialiseCentroid( $tfidfmax, $tfidfmin );
	}
	// Return the centroids (array with index = cluster number -> tf-idf values)
	return $centroids;
}

// Function to randomly generate a centroid
function initialiseCentroid( $tfidfmax, $tfidfmin )
{
	$lengthSquared = 0;
	$centroid = array();
	
	// Get terms from either of the max or min array
	foreach( $tfidfmax as $term => $value )
	{	
		// Centroid has a term associated with a pseudo-random tfidf value (multiply x 1000 for precision, or numbers are too small)
		$centroid[$term] = ( rand($tfidfmin[$term] * 1000, $value * 1000 ));
		// Calulate the squared vector length
		$lengthSquared += $centroid[$term] * $centroid[$term];
	}
	// Euclidian lentgh of vector
	$length = sqrt( $lengthSquared );
	
	// Normalise centroid vector length (if not values are totally out of range)
	foreach( $centroid as $key => &$value ) // must pass by reference to change value
	{
		$value = $value/$length;
	}
		
	// Return the centroid vector/array
	return $centroid;
}

// k-means: places k points in the data space then progressively averages them with the items around them
function kMeans( $normalisedTfidf , $numClusters ) 
{
	// Initialise centroids ( with key = term and value = tf-idf )
	$centroids = initialiseCentroids( $normalisedTfidf, $numClusters );
	$mapDocToCentroid = array();

	$maxIterations = 10;
	while( true ) // do this forever ( until break ) // Actually gets stuck in infinite loop and times out!
	{	
		// Assign documents to centroids
		$newMapDocToCentroid = assignCentroids( $normalisedTfidf , $centroids );
		
		// Stopping condition: when the centroid stops changing
		$changed = false;
		
		// Resulting array has docID for key and centroidID for values
		foreach( $newMapDocToCentroid as $docID => $centroidID ) 
		{	
			// If centroid for a doc in new mapping is not same as that of old mapping, use the new mapping
			if( !isset( $mapDocToCentroid[$docID] ) || $centroidID != $mapDocToCentroid[$docID] ) 
			{	
				$mapDocToCentroid = $newMapDocToCentroid;
				$changed = true; 
				break;
			}
		}
		// Decrease iterations (which were set as alternative stopping condition)
		$maxIterations--;
		// Check status of assignment and return the array with docs mapped to centroids
		if( !$changed || $maxIterations == 0 )
		{
			return $mapDocToCentroid;
		}
		// Update the centroids
		$centroids  = updateCentroids( $mapDocToCentroid , $normalisedTfidf , $numClusters ); 	
	}
}

// Funciton to assign to centroids by cosine similarity
function assignCentroids( $normalisedTfidf , $centroids ) 
{
	$mapDocToCentroid = array();
	// loop over docs of the data array
	foreach( $normalisedTfidf as $docID => $termValue ) 
	{
		$minSimilarity = null; //no Similarity set yet
		$minCentroid = null; // no centroid assgined yet
		// loop over each centroid
		foreach( $centroids as $centroidID => $centroidValue )
		{
			$similarity = 0;
			// loop over the terms in the centroid array
			foreach( $centroidValue as $term => $tfidfValue )
			{	
				// calculate similarity between doc and centroid by dot product of tfidfs vectors from data and centroid arrays
				if( isset( $termValue[$term] )) // since not all docs have same terms
				{
					$similarity += $tfidfValue * $termValue[$term];
				}
			}
			// find the smallest similarity value, and use it to assign a centroidID to a doc
			if( !isset( $minCentroid ) || $similarity < $minSimilarity )
			{	
				// find the smallest cosine of the angle between 2 vectors
				$minSimilarity = $similarity;   
				// find the id of the most similar centroid
				$minCentroid = $centroidID;
			}
		}
		// Set and return the map docs to centroid array
		$mapDocToCentroid[$docID] = $minCentroid;		
	}	
	return $mapDocToCentroid;
}

// Funciton to update centroids: resets the centroid coordinates based on the average of all the points that are assigned to it
function updateCentroids( $mapDocToCentroid , $normalisedTfidf , $numClusters ) 
{
	$centroids = array();
	$counts = array_count_values( $mapDocToCentroid ); 
	// for each doc in the mapping
	foreach( $mapDocToCentroid as $docID => $centroidID ) 
	{	
		// For the corresponding doc in the data array, loop over each term
		foreach( $normalisedTfidf[$docID] as $term => $tfidfValue ) 
		{	
			// Update the centroid by calculating average of all tfidf in each centroid
			if( isset( $centroids[$centroidID][$term] ))
			{
				$centroids[$centroidID][$term] += ( $tfidfValue / $counts[$centroidID] ); 
			}
		}
	}
	// Continue to generate new centroids until the set number of clusters is reached
	if( count( $centroids ) < $numClusters ) 
	{	
		$centroids = array_merge( $centroids , initialiseCentroids( $normalisedTfidf , $numClusters - count( $centroids )));
	}
	return $centroids;
}

?>