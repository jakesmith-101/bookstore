<?php

try {
	require_once('functions.php');
	//check book details
	//global variables so the form doesn't spit out errors
	$ISBNErr = $titleErr = $yearErr = $pubErr = $catErr = $priceErr = '';
	getSession();
	//get database connection
	$dbconn = getConnection();

	//book update validation
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$ISBN = $title = $year = $pubID = $catID = $price = '';
		
		//input check
		if (empty($_POST['ISBN']))
		{
			$ISBNErr = "No book selected.";
		}
		else
		{
			$ISBN = validate(filter_has_var(INPUT_POST, 'ISBN') ? $_POST['ISBN'] : null);
			if (isset($_SESSION['edit-book-ISBN']))
			{
				if ($ISBN != validate($_SESSION['edit-book-ISBN']))
				{
					$ISBN = '';
					$ISBNErr = "ISBN altered without permission";
				}
			}
			else
			{
				$ISBN = '';
				$ISBNErr = "ISBN altered without permission";
			}
		}
		//input check if empty
		if (empty($_POST['title']))
		{
			$titleErr = "No title inputted.";
		}
		else
		{
			//will check against regex, if it doesn't exceed, letters, capitals, numbers and punctuation
			$title = validate(filter_has_var(INPUT_POST, 'title') ? $_POST['title'] : null, 'statement');
			if ($title === '')
			{
				$titleErr = "Invalid title inputted.";
			}
		}
		//input check
		if (empty($_POST['year']))
		{
			$yearErr = "No year inputted.";
		}
		else
		{
			$year = validate(filter_has_var(INPUT_POST, 'year') ? $_POST['year'] : null, 'number');
			//book can't be published in the future, ISBN was created 1965/1966, and older books don't have ISBN
			//as such only new editions of old books have ISBN, so i will also restrict it to after 1964
			if (!(intval($year) <= intval(date('Y')) && intval($year) >= 1965))
			{
				$year = '';
			}
			if ($year === '')
			{
				$yearErr = "Invalid year inputted.";
			}
		}
		//input check
		if (empty($_POST['publisher']))
		{
			$pubErr = "No publisher inputted.";
		}
		else
		{
			$pubID = validate(filter_has_var(INPUT_POST, 'publisher') ? $_POST['publisher'] : null, 'generic');
			$sqlQuery = "SELECT pubID
						 FROM NBL_publisher";
			$sqlArray = $dbconn->query($sqlQuery);
			$pubErr = "Category selected does not exist.";
			while($rowObj = $sqlArray->fetchObject()) {
				if ($rowObj->pubID == $pubID)
				{
					$pubErr = '';
				}
			}
		}
		//input check
		if (empty($_POST['category']))
		{
			$catErr = "No category inputted.";
		}
		else
		{
			$catID = validate(filter_has_var(INPUT_POST, 'category') ? $_POST['category'] : null, 'generic');
			$sqlQuery = "SELECT catID
						 FROM NBL_category";
			$sqlArray = $dbconn->query($sqlQuery);
			$catErr = "Category selected does not exist.";
			while($rowObj = $sqlArray->fetchObject()) {
				if ($rowObj->catID == $catID)
				{
					$catErr = '';
				}
			}
		}
		//input check
		if (empty($_POST['price']))
		{
			$priceErr = "No price inputted.";
		}
		else
		{
			$priceArray = explode('.', strval(filter_has_var(INPUT_POST, 'price') ? $_POST['price'] : null));
			if (count($priceArray) === 2)
			{
				$price = validate($priceArray[0], 'number').'.'.validate($priceArray[1], 'number');
			}
			else
			{
				$price = validate($priceArray[0], 'number').'.00';
			}
			//no negative price possible, can be free though?
			if (!(floatval($price) >= 0) || (count($priceArray) > 2))
			{
				$price = '';
			}
			if ($price === '')
			{
				$priceErr = "Invalid price inputted.";
			}
		}
		if ($ISBNErr === '' && $titleErr === '' && $yearErr === '' && $pubErr === '' && $catErr === '' && $priceErr === '')
		{
			$sqlQuery = "UPDATE NBL_books
						 SET booktitle=?, bookYear=?, pubID=?, catID=?, bookPrice=?
						 WHERE bookISBN=?";
			$userPrepare = $dbconn->prepare($sqlQuery);
			$userPrepare->execute([$title, $year, $pubID, $catID, $price, $ISBN]);
			//this will alter the outputted html/php in editBook.php
			$_SESSION['edit-book-ISBN'] = "Authorised.";
		}
	}

	//display common HTML
	echo HTMLHead('editBook.php', 'Book Form', 'Edit Book');
	if (loggedIn() && isAdmin())
	{
		//code called 3 different places in this page but only used once each time
		function generateBookForm($dbconn, $bookISBN) {
			global $ISBNErr, $titleErr, $yearErr, $pubErr, $catErr, $priceErr;
			$_SESSION['edit-book-ISBN'] = $bookISBN;
			//as book ISBN's are just a string of numbers, we can check to see if it's numeric and avoid sql injection
			$sqlQuery = "SELECT bookISBN, bookTitle, bookYear, pubID, catID, bookPrice
						 FROM NBL_books
						 WHERE bookISBN='{$bookISBN}'";
			$bookQuery = $dbconn->query($sqlQuery);
			$dateYear = date("Y");
			$sqlQuery = "SELECT MIN(bookYear)
						 FROM NBL_books";
			$minQuery = $dbconn->query($sqlQuery);
			if ($bookQuery->rowCount() > 0)
			{
				$book = $bookQuery->fetchObject();
				$sqlQuery = "SELECT catID, catDesc
							 FROM NBL_category
							 ORDER BY catID";
				$catQuery = $dbconn->query($sqlQuery);
				$catOptions = '';
				$count = 0;
				while($rowObj = $catQuery->fetchObject()) {
					if ($count === 0)
					{
						$count = $count + 1;
					}
					else
					{
						/* indent correctly */
						$catOptions = "{$catOptions}\n									";
					}
					if ($rowObj->catID == $book->catID)
					{
						$catOptions = "{$catOptions}<option value=\"{$rowObj->catID}\" selected>{$rowObj->catDesc}</option>";
					}
					else
					{
						$catOptions = "{$catOptions}<option value=\"{$rowObj->catID}\">{$rowObj->catDesc}</option>";
					}
				}
				$sqlQuery = "SELECT pubID, pubName
							 FROM NBL_publisher
							 ORDER BY pubID";
				$pubQuery = $dbconn->query($sqlQuery);
				$pubOptions = '';
				$count = 0;
				while($rowObj = $pubQuery->fetchObject()) {
					if ($count === 0)
					{
						$count = $count + 1;
					}
					else
					{
						/* indent correctly */
						$pubOptions = "{$pubOptions}\n									";
					}
					if ($rowObj->pubID == $book->pubID)
					{
						$pubOptions = "{$pubOptions}<option value=\"{$rowObj->pubID}\" selected>{$rowObj->pubName}</option>";
					}
					else
					{
						$pubOptions = "{$pubOptions}<option value=\"{$rowObj->pubID}\">{$rowObj->pubName}</option>";
					}
				}
				$yearOptions = '';
				$count = 0;
				for ($var = $dateYear; $var >= 1965; $var--)
				{
					if ($count === 0)
					{
						$count = $count + 1;
					}
					else
					{
						/* indent correctly */
						$yearOptions = "{$yearOptions}\n									";
					}
					if ($var == $book->bookYear)
					{
						$yearOptions = "{$yearOptions}<option value=\"{$var}\" selected>{$var}</option>";
					}
					else
					{
						$yearOptions = "{$yearOptions}<option value=\"{$var}\">{$var}</option>";
					}
				}
				//need to add an action to this form
				$bookFormContent = <<<FORM

			<form method="post" action="editBook.php" name="book_form">
				<div class="inputForm">
					<fieldset>
						<legend>Book Details</legend>
						<input type="hidden" id="ISBN" name="ISBN" value="{$bookISBN}" />
						<div class="row">
							<div class="leftcolumn">
								<label>Book ISBN:</label>
							</div>
							<div class="rightcolumn">
								<input type="text" name="ISBN" value="{$bookISBN}" readonly />
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<span class="error">*</span>
							</div>
							<div class="rightcolumn">
								<span class="error">{$ISBNErr}</span>
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<label>Title:</label>
							</div>
							<div class="rightcolumn">
								<input type="text" id="title" name="title" value="{$book->bookTitle}" class="green" required />
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<span class="error">*</span>
							</div>
							<div class="rightcolumn">
								<span class="error">{$titleErr}</span>
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<label>Year:</label>
							</div>
							<div class="rightcolumn">
								<select id="year" name="year" class="green">
									{$yearOptions}
								</select>
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<span class="error">*</span>
							</div>
							<div class="rightcolumn">
								<span class="error">{$yearErr}</span>
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<label>Publisher:</label>
							</div>
							<div class="rightcolumn">
								<select id="publisher" name="publisher" class="green">
									{$pubOptions}
								</select>
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<span class="error">*</span>
							</div>
							<div class="rightcolumn">
								<span class="error">{$pubErr}</span>
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<label>Category:</label>
							</div>
							<div class="rightcolumn">
								<select id="category" name="category" class="green">
									{$catOptions}
								</select>
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<span class="error">*</span>
							</div>
							<div class="rightcolumn">
								<span class="error">{$catErr}</span>
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<label>Price:</label>
							</div>
							<div class="rightcolumn">
								<input type="number" id="price" name="price" min="0" step="0.01" value="{$book->bookPrice}" class="green" required />
							</div>
						</div>
						<div class="row">
							<div class="leftcolumn">
								<span class="error">*</span>
							</div>
							<div class="rightcolumn">
								<span class="error">{$priceErr}</span>

FORM;
			}
			else
			{
				$bookFormContent = '<p>Invalid Book ISBN, back to <a href="bookList.php">Book List</a>.</p>';
			}
			
			return $bookFormContent;
		}
		//came from bookList.php
		if (isset($_REQUEST['bookISBN']))
		{
			$bookISBN = validate($_REQUEST['bookISBN']);
			echo generateBookForm($dbconn, $bookISBN);
		}
		//came from NAV
		else if ($_SERVER["REQUEST_METHOD"] == "GET")
		{
			if (empty($_GET['ISBN']))
			{
				echo '<p>Invalid Book ISBN, back to <a href="bookList.php">Book List</a>.</p>';
			}
			else
			{
				$bookISBN = validate(filter_has_var(INPUT_GET, 'ISBN') ? $_GET['ISBN'] : null);
				echo generateBookForm($dbconn, $bookISBN);
			}
		}
		//came back from validation
		else if (isset($_SESSION['edit-book-ISBN']))
		{
			//successful validation
			if ($_SESSION['edit-book-ISBN'] === "Authorised.")
			{
				echo '<p>Successfully updated record, back to <a href="bookList.php">Book List</a>.</p>';
			}
			//failed validation
			else
			{
				$bookISBN = validate($_SESSION['edit-book-ISBN']);
				echo generateBookForm($dbconn, $bookISBN);
			}
		}
		//came without ISBN
		else
		{
			//redirect to book list
			redirect('bookList.php', true);
		}
	}
	else
	{
		//redirect to login page
		try
		{
			redirect('login.php');
		} catch (Exception $e) {
			throw new Exception ('Error '.$e->getMessage(), 0, $e);
		}
	}
}
catch (Exception $e) {
	throw new Exception('Error ' . $e->getMessage(), 0, $e);
}

?>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<div class="button">
							<button type="submit" id="submit" name="submit" value="submit">Submit</button>
						</div>
						<div class="button">
							<input type="reset" id="reset" />
						</div>
					</fieldset>
				</div>
			</form>
			<!-- imports some useful functions -->
			<script src="scripts.js">
			</script>
			<!-- simple visual validation on client side -->
			<script>
				//variables
				const titleInput = document.getElementById("title");
				const titleValue = titleInput.value;

				const yearInput = document.getElementById("year");
				const yearValue = yearInput.value;

				const pubInput = document.getElementById("publisher");
				const pubValue = pubInput.value;

				const catInput = document.getElementById("category");
				const catValue = catInput.value;

				const priceInput = document.getElementById("price");
				//Number() is used otherwise it sets it as string "20.00" not as the number 20
				const priceValue = Number(priceInput.value);

				const resetButton = document.getElementById("reset");

				//changes colours depending on data
				titleInput.onkeyup = function() {comparison(titleInput, titleValue);}
				yearInput.onblur = function() {comparison(yearInput, yearValue);}
				pubInput.onblur = function() {comparison(pubInput, pubValue);}
				catInput.onblur = function() {comparison(catInput, catValue);}
				priceInput.oninput = function() {comparison(priceInput, priceValue);}

				//resets all javascript changes
				resetButton.onclick = function() {
					convert(titleInput, "green");
					convert(yearInput, "green");
					convert(pubInput, "green");
					convert(catInput, "green");
					convert(priceInput, "green");
				}
			</script>
		</main>
	</body>
</html>