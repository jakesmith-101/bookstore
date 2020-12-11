<?php
try {
	// include the file for the database connection
	require_once('functions.php');
	// get database connection
	$dbConn = getConnection();

	if (isset($_REQUEST['useJSON'])) {
		// echo what getJSONOffer returns
		echo getJSONOffer($dbConn);
	}

	else {    // otherwise just an html record is required
		// so echo whatever getHTMLOffer returns to the browser or back to the ajax script
		echo getHTMLOffer($dbConn);
	}
}
catch (Exception $e) {
	throw new Exception("Error " . $e->getMessage(), 0, $e);
}

function getHTMLOffer($dbConn) {
	try {
		/* added pound symbol in after "Price: " so it's format is nicer  */
	    // store the sql for a random special offer, the sql wraps things using concat in an html 'wrapper'
	    $sql = "select concat('<p>&#8220;',bookTitle,'&#8221;<br>\n<span class=\"category\">Category: ',catDesc,'</span><br>\n<span class=\"price\">Price: £',bookPrice,'</span></p>') as offer from NBL_special_offers inner join NBL_category on NBL_special_offers.catID = NBL_category.catID order by rand() limit 1";

		// execute the query
		$rsOffer = $dbConn->query($sql);

		// get the one offer returned
		$offer = $rsOffer->fetchObject();

		// return the offer
		return $offer->offer;
	}
	catch (Exception $e) {
		return "Problem: " . $e->getMessage();
	}
}

function getJSONOffer($dbConn) {
	try {
	    $sql = "select bookTitle, catDesc, bookPrice from NBL_special_offers inner join NBL_category on NBL_special_offers.catID = NBL_category.catID order by rand() limit 1";
	   	$rsOffer = $dbConn->query($sql);;
	    $offer = $rsOffer->fetchObject();
	    return json_encode($offer);
	}
	catch (Exception $e) {
		return "Problem: " . $e->getMessage();
	}
}

?>