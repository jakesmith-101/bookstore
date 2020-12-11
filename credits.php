<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="genericstyle.css">
		<title>Credits</title>
	</head>
	<body>
<?php
try {
	require_once('functions.php');
	echo HTMLNAV('credits.php');
}
catch (Exception $e) {
	throw new Exception('Error ' . $e->getMessage(), 0, $e);
}
?>
		<main>
			<p>my university ID, my name</p>
		</main>
	</body>
</html>