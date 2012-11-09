<?php
/**
 *  Query Pre-processing Module: tokenise, stopword removal and stemming of search string
 */  
// Tokenise query terms by exploding the string search into an array of tokens
function tokeniseQuery( $searchString ) 
{
	$searchArray = array();
	
	//create a pattern to remove everything that is not a letter, and a delimiter for the toens
	$pattern = "/[^a-zA-Z0-9]+/"; // + means more than one
	//$pattern = "/[^a-zA-Z]+/"; // use to replace anon letters to white spaces
	$delimiter = " ";
	
	// clean the string using the pattern, and replace all to lowercase
	$searchTokens = strtolower ( preg_replace ( $pattern, " " , $searchString ));
	// Put tokens in array and return it
	$searchArray = explode( $delimiter , $searchTokens );

	return $searchArray;
}

// Function to remove stopwords
function stopwordRemoval( array $searchArray ) // or results array
{
	$searchUnique = array(); // or resultsUnique
	$searchDiff = array(); // or resultsDiff
	// Put stopwords into array
	$stopwordslist = file ( 'lists/stopwords.txt' ); // it comes with a \n after each entry
	// Remove the newline character after each string, save to new array
	foreach ( $stopwordslist as $value )
	{	// Remove new line and other characters from end of string
		$stopwords[] = rtrim( $value ); 
	}
	
	// Remove duplicate words
	$searchUnique = array_unique( $searchArray ); 
	
	// Subtract the stopwords array from the results array
	$searchDiff = array_diff ( $searchUnique , $stopwords );
	
	return $searchDiff;
}

// Don't use this! Instead Porter stemming algorithm!
function stemQuery( array $searchDiff ) 
{
	$stemsUnique = array();

	for ( $i = 0 ; $i < count( $searchDiff ) -1 ; $i++ )
	{	// iterate over characters in each string
		for ( $char = 0 ; $char < strlen( $searchDiff[$i] ) && $char < strlen($searchDiff[$i+1]) ; $char++ )
		{	// test if a char in current string is same as char in next string
			if ( $searchDiff[$i][$char] == $searchDiff[$i+1][$char] )
			$stemsArray[$i] .= $searchDiff[$i][$char];
		}
		// test
		//$stemsString .= $stems[$i] . "\n";
	}
	$stemsUnique = array_unique( $stemsArray ); 

	return $stemsUnique;
}

// Porter Stemming algorithm: call the class method created by Richard Heyes
function applyPorterStemming( array $searchArray ) 
{
	$stemsUnique = array();
	$searchDiff = stopwordRemoval( $searchArray );

	foreach ( $searchDiff as $word )
	{
		$stems[] .= PorterStemmer::Stem( $word ) . "\n";
	}

	// Remove duplicate words
	$stemsUnique = array_unique( $stems ); 

	// Return stems
	return $stemsUnique;
}
?>