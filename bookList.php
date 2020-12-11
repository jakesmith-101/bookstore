<?php
try {
	require_once('functions.php');
	echo HTMLHead('bookList.php', 'List of Books', 'List of Books');
}
catch (Exception $e) {
	throw new Exception('Error ' . $e->getMessage(), 0, $e);
}
?>
			<div style="overflow-x:auto;">
				<table>
					<thead>
						<tr>
							<th>ISBN</th>
							<th>Title</th>
							<th>Year</th>
							<th>Category</th>
							<th>Price</th>
						</tr>
					</thead>
					<tbody>
<?php
try {
	if (loggedIn() && isAdmin())
	{
		//get database connection
		$dbconn = getConnection();
		//database query
		$sqlQuery = "SELECT bookISBN, bookTitle, bookYear, catID, bookPrice
					 FROM NBL_books
					 ORDER BY bookTitle";
		$bookQuery = $dbconn->query($sqlQuery);
		$sqlQuery = "SELECT catID, catDesc
					 FROM NBL_category";
		$catQuery = $dbconn->query($sqlQuery);
		$catObj = array();
		while ($rowObj = $catQuery->fetchObject()) {
			$catObj[$rowObj->catID] = $rowObj->catDesc;
		}
		//plan to add css classes, and hyperlink for admin/customers
		while ($rowObj = $bookQuery->fetchObject()) {
			echo <<<ROW
						<tr>
							<td>{$rowObj->bookISBN}</td>
							<td><a href="editBook.php?bookISBN={$rowObj->bookISBN}">{$rowObj->bookTitle}</a></td>
							<td>{$rowObj->bookYear}</td>
							<td>{$catObj[$rowObj->catID]}</td>
							<td>{$rowObj->bookPrice}</td>
						</tr>

ROW;
		}
	}
	else
	{
		//redirect to login page
		redirect('login.php');
	}
}
catch (Exception $e) {
	throw new Exception('Error ' . $e->getMessage(), 0, $e);
}
?>
					</tbody>
				</table>
			</div>
		</main>
	</body>
</html>