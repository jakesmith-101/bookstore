<?php

//connection to database
function getConnection() {
	try {
		$connection = new PDO("mysql:host=localhost;dbname=database_name_here", "username_here", "password_here");
		$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $connection;
	} catch (Exception $e) {
		throw new Exception("Connection error ". $e->getMessage(), 0, $e);
	}
}

//create session if one doesn't exist already
function getSession() {
	if (session_status() == PHP_SESSION_NONE)
	{
		ini_set('session.save_path', 'sessionData');
		session_start();
	}
}
//creates a session and sets it's username and if it's logged in
function setSession($sessUsername, $sessFName, $sessLName, $permission=false) {
	getSession();
	$_SESSION['username'] = $sessUsername;
	$_SESSION['firstname'] = $sessFName;
	$_SESSION['lastname'] = $sessLName;
	$_SESSION['name'] = $sessFName.' '.$sessLName;
	$_SESSION['admin'] = $permission;
	$_SESSION['logged-in'] = true;
}
//creates a session if one doesn't exist and sets it's details if specified
function resetSession($sessUsername, $sessFName, $sessLName, $permission=false) {
	getSession();
	if ($_SESSION['username'] != $sessUsername)
	{
		$_SESSION['username'] = $sessUsername;
	}
	if ($_SESSION['firstname'] != $sessFName)
	{
		$_SESSION['firstname'] = $sessFName;
	}
	if ($_SESSION['lastname'] != $sessLName)
	{
		$_SESSION['lastname'] = $sessLName;
	}
	if ($_SESSION['name'] != $sessFName.' '.$sessLName)
	{
		$_SESSION['name'] = $sessFName.' '.$sessLName;
	}
	if ($_SESSION['admin'] != $permission)
	{
		$_SESSION['admin'] = $permission;
	}
	$_SESSION['logged-in'] = true;
}

//if a session variable is not set it is set if specified (usually blank)
function setSessVar($name, $value='') {
	if (!isset($_SESSION[$name]))
	{
		$_SESSION[$name] = $value;
	}
}

function adminPage($page) {
	if ((strpos($page, 'bookList.php') !== false) || (strpos($page, 'editBook.php') !== false) || (strpos($page, 'profile.php') !== false)) {
		return true;
	}
	return false;
}

//creates a session if one doesn't exist and checks if it's logged in
function loggedIn() {
	getSession();
	if(isset($_SESSION['logged-in']))
	{
		return $_SESSION['logged-in'];
	}
	else
	{
		$_SESSION['logged-in'] = false;
		return false;
	}
}
//checks the session for if the user is an admin, incase there are different types of users
function isAdmin() {
	getSession();
	if(isset($_SESSION['admin']))
	{
		return $_SESSION['admin'];
	}
	else
	{
		$_SESSION['admin'] = false;
		return false;
	}
}

//validate input, TO BE DONE
function validate($data, $type='ISBN') {
	$data = trim($data);
	$data = stripslashes($data);
	//contains spaces, there should be no spaces!!! avoid something?
	if(($type === 'generic') && (strpos($data, ' ') !== false))
	{
		return '';
	}
	if ($type === 'statement')
	{
		$exp = "@^[-a-zA-Z0-9 /_,.():;&\\*]+$@";
		if (preg_match($exp, $data))
		{
			return $data;
		}
		else
		{
			return '';
		}
	}
	else
	{
		$data = htmlspecialchars($data);
	}
	if ($type === 'name')
	{
		if (preg_match("@^[A-Z]+$@", substr($data, 0, 1)) && preg_match("@^[a-z]+$@", substr($data, 1)))
		{
			return $data;
		}
		else
		{
			return '';
		}
	}
	if ($type == 'number' && is_numeric($data) && intval($data) >= 0)
	{
		return $data;
	}
	else if ($type == 'number')
	{
		return '';
	}
	//if it's just ISBN, ISBN since 2017 is 13 digits? but checking if it's 10 digits
	if ($type == 'ISBN' && ((strlen($data) == 10) || (strlen($data) == 13)))
	{
		if (is_numeric($data))
		{
			return $data;
		}
		else if ((substr($data, -1) === 'X') && (is_numeric(substr($data,0,-1))))
		{
			return $data;
		}
		else
		{
			return '';
		}
	}
	//checking if it's ISBN and fails, avoid any errors or sql injection?
	else if ($type == 'ISBN')
	{
		return '';
	}
	return $data;
}

//redirect to certain page, $permission referring to if the redirected site is admin only
function redirect($page, $permission=false) {
	if ($permission)
	{
		getSession();
		//just incase they are urged to log in but already had logged in somehow
		if (loggedIn() && isAdmin())
		{
			header('Location: '.$page);
			die;
		}
		header('Location: login.php');
		die;
	}
	//checks against list of pages if admin is required but not passed through $permission
	else if (adminPage($page))
	{
		getSession();
		//just incase they are urged to log in but already had logged in somehow
		if (loggedIn() && isAdmin())
		{
			header('Location: '.$page);
			die;
		}
		header('Location: login.php');
		die;
	}
	else
	{
		header('Location: '.$page);
		die;
	}
}

//dynamic NAV
function HTMLNAV($page) {
	getSession();
	if ($page === 'login.php')
	{
		$loginPage = ' id="active"';
	}
	else
	{
		$loginPage = '';
	}
	if ($page === 'editBook.php')
	{
		$editBook = ' id="active"';
	}
	else
	{
		$editBook = '';
	}
	$current = 'page='.$page;
	if (isset($_SESSION['name']))
	{
		$name = <<<USER
	<a href="javascript:void(0)" class="dropbtn">{$_SESSION['name']}</a>
					<div class="dropdown-content">
						<a href="profile.php">Profile</a>
						<a href="logout.php?{$current}">Logout</a>
					</div>
USER;
	}
	else
	{
		$name = <<<USER
	<a href="login.php?{$current}"{$loginPage}>Login</a>
USER;
	}
	if (isset($_SESSION['admin']) && $_SESSION['admin'])
	{
		$NAVAdmin = <<<ADMIN
			<li><a href="bookList.php">Book List</a></li>
				<li class="dropdown">
					<a href="javascript:void(0)" class="dropbtn"{$editBook}>Edit Book</a>
					<div class="dropdown-content">
						<form method="get" action="editBook.php" name="book_ISBN" id="book_ISBN">
							<label>Book ISBN:</label>
							<input type="text" name="ISBN" id="ISBN" required />
							<button type="submit" id="submit" name="submit" value="submit">Request</button>
						</form>
					</div>
				</li>
ADMIN;
	}
	else
	{
		$NAVAdmin = '';
	}
	$HTMLNAVContent = <<<NAV
<nav>
			<ul class="nav">
				<li><a href="index.php">Home</a></li>
				<li><a href="orderBooksForm.php">Order Books</a></li>{$NAVAdmin}
				<li><a href="credits.php">Credits</a></li>
				<li class="dropdown" id="profile">
				{$name}
				</li>
			</ul>
		</nav>
NAV;
	//adds class="active" to the <a href=""> that user is currently on
	if ($page !== 'editBook.php' && $page !== 'login.php')
	{
		$HTMLNAVContent = substr($HTMLNAVContent, 0, strpos($HTMLNAVContent, $page) + strlen($page) + 1).' class="active"'.substr($HTMLNAVContent, strpos($HTMLNAVContent, $page) + strlen($page) + 1);
	}
	return $HTMLNAVContent;
}

//repeated content at the beginning
function HTMLHead($page, $title, $head) {
	$HTMLNAVContent = HTMLNAV($page);
	$HTMLHeadContent = <<<HEAD
<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="genericstyle.css">
		<title>{$title}</title>
	</head>
	<body>
		{$HTMLNAVContent}
		<main>
			<h1>{$head}</h1>
HEAD;
	return $HTMLHeadContent;
}

?>