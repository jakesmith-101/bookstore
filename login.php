<?php
try {
	require_once('functions.php');
	//check login
	//global variables so the form can list certain fails
	$usernameErr = $passwordErr = '';
	$toReturn = array();
	//login details validation
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$username = $password = $passwordHash = '';
		
		//password input check
		if (empty($_POST['password']))
		{
			$passwordErr = "Password is required";
		}
		else
		{
			$password = validate(filter_has_var(INPUT_POST, 'password') ? $_POST['password'] : null, 'generic');
		}
		
		//username input check
		if (empty($_POST['username']))
		{
			$usernameErr = 'Username is required';
		}
		else
		{
			//connection to database is only required at this point, otherwise it is never used
			$dbconn = getConnection();
			//check username exists then get passwordHash
			$username = validate(filter_has_var(INPUT_POST, 'username') ? $_POST['username'] : null, 'generic');
			//sql inject, use validate function to avoid as much as possible
			//brute force method would be to pull all users and compare usernames in php
			$sqlQuery = "SELECT userID, username, passwordHash
						 FROM NBL_users
						 WHERE username=:name";
			$userPrepare = $dbconn->prepare($sqlQuery);
			$userPrepare->execute(['name' => $username]);
			$rowCount = $userPrepare->rowCount();
			//using prepared statements too to mitigate it more
			if ($rowCount == 1)
			{
				$fetched = $userPrepare->fetchObject();
				$userID = $fetched->userID;
				$passFetch = $fetched->passwordHash;
				//if the fetched password is not null, or empty string
				if (isset($passFetch) && $passFetch != '')
				{
					$passwordHash = $passFetch;
				}
				else
				{
					//on the offchance that the database has an empty password hash, but don't specify that the user does exist in database
					$passwordErr = 'Wrong username or password, Database error.';
				}
			}
			else if ($rowCount == 0)
			{
				//if there is no user with that username
				$passwordErr = 'Wrong username or password.';
			}
			else
			{
				$usernameErr = $passwordErr = 'Database Error.';
			}
		}

		//checking passwords if no errors were created
		if ($passwordErr == '' && $usernameErr == '')
		{
			if (password_verify($password, $passwordHash))
			{
				//avoid sql inject by using previous sql statement
				$sqlQuery = "SELECT userID, firstname, surname
							 FROM NBL_users
							 WHERE userID='{$userID}'";
				$userDetails = $dbconn->query($sqlQuery)->fetchObject();
				setSession($username,"{$userDetails->firstname}", "{$userDetails->surname}", true);
				//so i can use this function for profile.php as well as login.php
				$toReturn = array();
				$toReturn[0] = true;
				$toReturn[1] = validate(filter_has_var(INPUT_POST, 'redirect') ? $_POST['redirect'] : null, 'generic');
			}
			else
			{
				//wrong password, but don't specify that the user does exist in database
				$passwordErr = 'Wrong username or password.';
			}
		}
	}
	$toReturn[0] = false;
	//if login form is successful redirect to previous page
	if ($toReturn[0] === true)
	{
		//even if it's not redirecting to admin page, it's supposedly a successful logon
		//so passing the redirect as an admin page just incase
		redirect($toReturn[1], true);
	}
	else
	{
		getSession();
		if (loggedIn())
		{
			//if login redirects back to login page by mistake
			redirect('index.php');
		}
		//display common HTML
		echo HTMLHead('login.php', 'Login', 'Please Login');
		//destroy any session to be recreated on login
		$_SESSION = array();
		session_destroy();
		session_write_close();
		//if redirected to login.php, or click login on a page, remember and redirect back on login
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$webpage = validate($_SERVER['HTTP_REFERER'], 'generic');
		}
		else if (isset($_REQUEST['page']))
		{
			$webpage = validate($_REQUEST['page'], 'generic');
		}
		else
		{
			$webpage = 'index.php';
		}
		//the dynamic login form
		$loginFormContent = <<<FORM

			<form method="post" action="login.php" name="login_form">
				<fieldset>
					<label for="username">Username:</label>
					<input type="text" class="red" name="username" id="username" required />
					<span class="error">* {$usernameErr}</span><br>
					<label for="password">Password:</label>
					<input type="password" name="password" id="password" required />
					<span class="error">* {$passwordErr}</span>
				</fieldset>
				<fieldset>
					<input type="hidden" name="redirect" value="{$webpage}" />

FORM;
		echo $loginFormContent;
	}
}
catch (Exception $e) {
	throw new Exception("Error " . $e->getMessage(), 0, $e);
}
?>
					<button type="submit" name="login" value="login">Login</button>
				</fieldset>
			</form>
			<script src="scripts.js">
				//imports external functions
			</script>
			<script>
				//define all variables
				const userInput = document.getElementById("username");
				userInput.onkeyup = function() {
					//check if empty
					if (isEmpty(userInput.value))
					{
						convert(userInput, "red");
					}
					else
					{
						convert(userInput, "green");
					}
				}
			</script>
		</main>
	</body>
</html>