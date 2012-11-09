<?php
//initialize the session
if (!isset($_SESSION)) 
	session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="styles/bingostyle.css" rel="stylesheet" type="text/css" />
<title>Bingo Meta Search Engine</title>
</head>
<body>
<div id="container">
	<div id="header">
	  <?php require ( 'includes/header.php' ); ?>
	</div><!-- end #header -->
	
	<div id="main">
	
		<div id="form">
			<form method="get" action="search.php" name="formSearch" id="formSearch">
			    <label><input type="radio" name="RadioGroup2" value="stemmingOn" id="stemmingOn" />
			    Stemming On</label>
			    <label><input type="radio" name="RadioGroup2" value="stemmingOff" id="stemmingOff" checked="checked" />
			    Stemming Off</label>
			    <br />    
				<input name="searchText" type="text" id="searchText" value="<?php
			    /*    if( isset ( $_GET['searchText'] ))
				    echo ( $_GET['searchText'] );
			    else 
			    	echo ( 'search' ); */
			    if( !empty( $_SESSION['prev_search'] ))
			    {
					echo ( $_SESSION['prev_search'] );
					//unset ( $_SESSION['prev_search'] );
			    }
			    ?>"/>
			    <input type="submit" value=" Search " name="submit" id="submit" />
			    <br />
			    <label><input type="radio" name="RadioGroup1" value="aggregate" id="aggregate" />
			    Aggregate Results</label>
			    <label><input type="radio" name="RadioGroup1" value="non-aggregate" id="non-aggregate" checked="checked" />
			    Do not Aggregate Results</label>
			    <label><input type="radio" name="RadioGroup1" value="cluster" id="cluster" />
			    Cluster Results</label>	
			    <br />	    
			</form>
		</div><!-- end #form -->	

		 <div id="error">
		<?php
			if ( !empty ( $_SESSION['error'] ))
			{
				echo ( '<h3>Error!</h3>' );
				echo ( $_SESSION['error'] );
				unset ( $_SESSION['error'] );
			}	
		?>	  
		</div><!-- end #error -->

		<div id="pseudoRelevance"  class="leftAligned">
		<?php
			if ( !empty ( $_SESSION['pseudoRelevance'] ))
			{
				echo ( 'Add to your query:<br />' );
				echo ( $_SESSION['pseudoRelevance'] );
				//unset ( $_SESSION['pseudoRelevance'] );
			}	
		?>	  
		</div><!-- end #pseudoRelevance -->

		 <div id="synonyms" class="leftAligned">
		<?php
			if ( !empty ( $_SESSION['synonyms'] ))
			{
				echo ( 'Also try: ' );
				echo ( $_SESSION['synonyms'] );
				//unset ( $_SESSION['synonyms'] );
			}	
		?>	  
		</div><!-- end #synonyms -->

		<div id="stats">
		<?php
			if ( !empty ( $_SESSION['stats'] ))
			{
				echo ( '<h3>Evaluation Metrics</h3>' );
				echo ( $_SESSION['stats'] );
				//unset ( $_SESSION['stats'] );
			}	
		?>	  
		</div><!-- end #stats -->
						 
        <div id="metasearch"> 
           	<?php
			if ( !empty ( $_SESSION['metasearch'] ))
			{
				echo ( $_SESSION['metasearch'] );
				//unset ( $_SESSION['metasearch'] );
			} 
			?>
        </div><!-- end #metasearch -->         

	 <div class="clear"></div>

  	</div><!-- end #main -->	
	
	<div id="footer">
		<?php require('includes/footer.php'); ?> 
	</div><!-- end #footer -->
</div><!-- end #container -->

</body>
</html>
