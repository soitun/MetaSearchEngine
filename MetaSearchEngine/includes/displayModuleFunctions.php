<?php
/**
* Display Module
*/
// Function to sort search engines by average precision  (to use with non-aggregated results )
function sortAvgPrecision( $x , $y ) 
{
	if ( $x['precision'] == $y['precision'] )
		return 0;
	elseif ( $x['precision'] > $y['precision'] )
		return -1;
	else
		return 1;
}

// Function to sort the CombMNZ score (to use with aggregated results )
function sortScore( $x , $y ) // x and y are two instances, two docs of uniquedocs
{
	if ( $x['score'] == $y['score'] )
		return 0;
	elseif ( $x['score'] > $y['score'] )
		return -1;
	else
		return 1;
}

// Function to create a merged results list
function getUniqueResults( $bing, $entireWeb, $blekko, $bingAvgPrecision, $entireWebAvgPrecision, $blekkoAvgPrecision ) 
{
	$mergedResults = array();
	// Array with numeric index, pointing to array of engine's results; engine's name, and engine's avg precision
	$mergedResults[0]['engine'] = $bing; 
	$mergedResults[0]['engineName'] = "Bing"; 
	$mergedResults[0]['precision'] = $bingAvgPrecision; 
	$mergedResults[1]['engine'] = $entireWeb;
	$mergedResults[1]['engineName'] = "EntireWeb";
	$mergedResults[1]['precision'] = $entireWebAvgPrecision;
	$mergedResults[2]['engine'] = $blekko;
	$mergedResults[2]['engineName'] = "Blekko";
	$mergedResults[2]['precision'] = $blekkoAvgPrecision;

	// Sort by precision value and re-key the array elements
	usort( $mergedResults, 'sortAvgPrecision' );

	// Compile a new unique results list
	$uniqueResults = array();
	foreach ( $mergedResults as $key => $value ) // $key = numeric index
	{
		// loop over each search engine's results list
		foreach ( $value['engine'] as $engine => $value2 )
		{	// if the result is already in the new list
			if ( array_key_exists( $engine , $uniqueResults ) )
			{	// Add a counter and increase the score
				$uniqueResults[$engine]['count'] += 1;
				$uniqueResults[$engine]['score'] += $value2['score'];
			}
			else // If not, add data for the new result found
			{	
				$uniqueResults[$engine]['count'] = 1;
				$uniqueResults[$engine]['title'] = $value2['title'];
				$uniqueResults[$engine]['snippet'] = $value2['snippet'];
				$uniqueResults[$engine]['url'] = $value2['url'];
				$uniqueResults[$engine]['score'] = $value2['score'];
				$uniqueResults[$engine]['engineName'] = $value['engineName'];			
			}
		}
	}
	// calculate CombMNZ (used for aggregated list)
	foreach ( $uniqueResults as $engine => $value )
	{
		$uniqueResults[$engine]['score'] = $value['score'] * $value['count'];
	}	
	return $uniqueResults;
}

// Function to return the aggregated list
function getAggregatedResults( array $uniqueResults )
{
	// Sort array by CombMNZ
	uasort( $uniqueResults, 'sortScore' );
	// Count the docs in the array
	$uniqueResultsDocID = 0;
	foreach ( $uniqueResults as $engine => $value )
	{
		$uniqueResultsDocID++;
		$uniqueResults[$engine]['docID'] = $uniqueResultsDocID;
	}	
	return $uniqueResults; 
}

// Function to return the non-aggregated list
function getNonAggregatedResults( array $uniqueResults )
{
	$uniqueResultsDocID = 0;
	foreach ( $uniqueResults as $engine => $value )
	{
		$uniqueResultsDocID++;
		$uniqueResults[$engine]['docID'] = $uniqueResultsDocID;
	}	
	return $uniqueResults;
}

// Create clustered list
function getClusteredResults( array $nonAggregated, $mapDocToClusterID ) 
{
	$clustered = array();
	// for each docID in this cluster write out the search result
	foreach( $nonAggregated as $click => $value )  
	{
		// only use results with a snippet
		if( $value['snippet'] == null )
			continue;

		// Create a new index for the array of clustered results [$clusterId], and add the relevant information
		$docID = $value['docID'];
		$clusterId = $mapDocToClusterID[$docID];					
		$clustered[$clusterId][$docID]['click'] = $click;
		$clustered[$clusterId][$docID]['title'] = $value['title'];
		$clustered[$clusterId][$docID]['snippet'] = $value['snippet'];
		$clustered[$clusterId][$docID]['url'] = $value['url'];
	}
	// Sort by key clusterID
	ksort($clustered);

	// Display clusters
	$clusteredList .= '<div class="searchresult"><a name="TopOfPage"><h3>Clustered Results</h3><ul>';
	foreach ( $clustered as $clusterID => $value )
	{
		$clusteredList .= "<a href='#$clusterID'>Cluster " . $clusterID . " ( " . count( $clustered[$clusterID] ) . " )</a><br />";
	}	
	foreach ( $clustered as $clusterID => $value )
	{			
		$clusteredList .= "<h4><a name='$clusterID'>Cluster " . $clusterID . " ( " . count( $clustered[$clusterID] ) . " )			
							&nbsp&nbsp&nbsp<a href='#TopOfPage'>Back to top</a></h4>";
		foreach ( $value as $docID => $value2 )
		{
			$clusteredList .= 	'<br /><li><a href=' . $value2['click'] . '>' . $value2['title'] .			 
			'</a><br />' . $value2['snippet'] . '<br/><cite class="resulturl">' . $value2['url'] . '</cite></li><br />';
		}
	}	
	$clusteredList .= '</ul></div>';	
	// Return the clustered results to display
	return $clusteredList;
}
?>