<?php
/**
 * Complex Search Ability: Boolean Query
 */
/*
function booleanQuery( array $searchArray )
{	
	foreach ( $searchArray as $key => &$value )
	{
		if ( $value == "AND" || $value == "and" ) 
			//$value = "+"; 
			$value = "AND"; 
		else if ( $value == "OR" || $value == "or" )
			$value = "OR"; 
		else if ( $value == "NOT" || $value == "not" ) 
			$value = "NOT";	// Bing responds correctly tot this
			//$value = "-";   // entireweb responds correctly (even if exploding creates a space)
	}
	
	return $searchArray;
}
*/
function booleanBing( array $searchArray )
{	
	$searchBingArray = array(); 
	$searchBingArray = $searchArray;
	foreach ( $searchBingArray as $key => &$value )
	{
		if ( $value == "AND" || $value == "and" ) 
			$value = "AND"; 
		else if ( $value == "OR" || $value == "or" )
			$value = "OR"; 
		else if ( $value == "NOT" || $value == "not" ) 
			$value = "NOT";	// Bing responds correctly tot this
	}
	// put elements into a string and return it
	$searchBingString = implode( " " , $searchBingArray );

	return $searchBingString;	
}

function booleanEntireWeb( array $searchArray )
{
	$searchEntireWebArray = array(); 
	$searchEntireWebArray = $searchArray;
	foreach ( $searchEntireWebArray as $key => &$value )
	{
		if ( $value == "AND" || $value == "and" ) 
			$value = "AND"; 
		else if ( $value == "OR" || $value == "or" )
			$value = "OR"; 
		else if ( $value == "NOT" || $value == "not" ) 
			$value = "-";   // entireweb responds correctly (even if exploding creates a space)
	}
	// put elements into a string and return it
	$searchEntireWebString = implode( " " , $searchEntireWebArray );

	return $searchEntireWebString;	
}

function booleanBlekko( array $searchArray )
{
	$searchBlekkoArray = array(); 
	$searchBlekkoArray = $searchArray;
	foreach ( $searchBlekkoArray as $key => &$value )
	{
		if ( $value == "AND" || $value == "and" ) 
			$value = "+"; 
		else if ( $value == "OR" || $value == "or" )
			$value = "OR"; 
		else if ( $value == "NOT" || $value == "not" ) 
			$value = "NOT";   
	}
	// put elements into a string and return it
	$searchBlekkoString = implode( " " , $searchBlekkoArray );

	return $searchBlekkoString;	
}

function booleanGoogle( array $searchArray )
{
	$searchGoogleArray = array(); 
	$searchGoogleArray = $searchArray;
	foreach ( $searchGoogleArray as $key => &$value )
	{
		if ( $value == "AND" || $value == "and" ) 
			$value = "+"; 
		else if ( $value == "OR" || $value == "or" )
			$value = " OR "; 
		else if ( $value == "NOT" || $value == "not" ) 
			$value = " -";   
	}
	// put elements into a string and return it
	$searchGoogleString = implode( $searchGoogleArray );

	return $searchGoogleString;	
}
?>