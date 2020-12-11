<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="genericstyle.css">
		<title>Home</title>
	</head>
	<body>
<?php
try {
	require_once('functions.php');
	echo HTMLNAV('index.php');
}
catch (Exception $e) {
	throw new Exception('Error ' . $e->getMessage(), 0, $e);
}
?>
		<main>
			<h1>Home Page</h1>
			<section class="index">
				<p>Welcome to the online bookstore.<br>
				</p>
			</section>
			<div class="aside">
				<aside>
					<h2>Offers:</h2>
				</aside>
				<aside id="offers">
				</aside>
				<aside id="JSONoffers">
				</aside>
			</div>
			<script>
				//getting the paragraph elements
				const html = document.getElementById('offers');
				const json = document.getElementById('JSONoffers');

				function toFetch() {
					//every 5 seconds fetch a new offer
					fetchHTML();
					fetchJSON();
					setTimeout(toFetch, 5000);
				}
				

				//updating the elements using 'getOffers.php'
				function fetchHTML() {
					fetch('getOffers.php')
						.then(
							function(response) {
								return response.text();
							})
						.then(
							function(data) {
								html.innerHTML = data;
							});
				}

				//updating the elements using 'getOffers.php?useJSON'
				function fetchJSON() {
					fetch('getOffers.php?useJSON')
						.then(
							function(response) {
								return response.text();
							})
						.then(
							function(data) {
								let toParse = JSON.parse(data);
								json.innerHTML = '<p>“'.concat(toParse.bookTitle,
															"”<br><span class=\"category\">Category: ",
															toParse.catDesc,
															"</span><br><span class=\"price\">Price: £",
															toParse.bookPrice,
															"</span></p>");
							});
				}
				
				toFetch();
			</script>
		</main>
	</body>
</html>