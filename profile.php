<?php

/*  unrequested, but felt it was a nice/needed addition
	so i created this page to edit forename/surname and password
	and to display user details */

try {
	require_once('functions.php');
	getSession();
	if (loggedIn())
	{
		$fnErr = $lnErr = $passErr = $newPassErr = $newCPassErr = $usernameErr = '';
		/* change the name, plus validation */
		function changeName($dbconn) {
			global $fnErr, $lnErr, $passErr, $usernameErr;
			$fn = $ln = $pass = '';
			getSession();

			if (empty($_POST['firstname']))
			{
				$fnErr = 'First name is required.';
			}
			else
			{
				$fn = validate(filter_has_var(INPUT_POST, 'firstname') ? $_POST['firstname'] : null, 'name');
				if ($fn === '')
				{
					$fnErr = 'First name is invalid.';
				}
			}

			if (empty($_POST['lastname']))
			{
				$lnErr = 'Last name is required.';
			}
			else
			{
				$ln = validate(filter_has_var(INPUT_POST, 'lastname') ? $_POST['lastname'] : null, 'name');
				if ($ln === '')
				{
					$lnErr = 'Last name is invalid.';
				}
			}

			if (empty($_POST['password']))
			{
				$passErr = 'Password is required.';
			}
			else
			{
				$pass = validate(filter_has_var(INPUT_POST, 'password') ? $_POST['password'] : null, 'generic');
				if ($pass === '')
				{
					$passErr = 'Password is invalid.';
				}
			}

			if ($passErr === '' && $fnErr === '' && $lnErr === '')
			{
				$sqlQuery = "UPDATE NBL_users
							 SET firstname=?, surname=?
							 WHERE username=?";
				$update = $dbconn->prepare($sqlQuery);
				$sqlQuery = "SELECT passwordHash
							 FROM NBL_users
							 WHERE username=?";
				$passCheck = $dbconn->prepare($sqlQuery);
				if (isset($_SESSION['username']))
				{
					$passCheck->execute([$_SESSION['username']]);
				}
				else
				{
					$usernameErr = 'Invalid username set.';
				}
				$rowCount = $passCheck->rowCount();
				if ($rowCount == 1)
				{
					$fetched = $passCheck->fetchObject();
					$passFetch = $fetched->passwordHash;
					//if the fetched password is not null, or empty string
					if (isset($passFetch) && $passFetch != '')
					{
						$passHash = $passFetch;
					}
					else
					{
						$passErr = 'Wrong password.';
					}
				}
				else if ($rowCount == 0)
				{
					$passErr = 'Wrong password.';
				}
				else
				{
					$passErr = 'Database Error.';
				}
				if ($passErr === '' && $usernameErr === '' && password_verify($pass, $passHash))
				{
					$update->execute([$fn, $ln, $_SESSION['username']]);
					resetSession($_SESSION['username'], $fn, $ln, true);
				}
				else
				{
					$passErr = 'Wrong password.';
				}
			}
		}

		/* change the password, plus validation */
		function changePassword($dbconn) {
			global $passErr, $newPassErr, $newCPassErr, $usernameErr;
			$pass = $newPass = $newCPass = '';
			getSession();

			if (empty($_POST['password']))
			{
				$passErr = 'Password is required.';
			}
			else
			{
				$pass = validate(filter_has_var(INPUT_POST, 'password') ? $_POST['password'] : null, 'generic');
				if ($pass === '')
				{
					$passErr = 'Password is invalid.';
				}
			}

			if (empty($_POST['psw']))
			{
				$newPassErr = 'Password is required.';
			}
			else
			{
				$newPass = validate(filter_has_var(INPUT_POST, 'psw') ? $_POST['psw'] : null, 'generic');
				if ($newPass === '')
				{
					$newPassErr = 'Password is invalid.';
				}
			}

			if (empty($_POST['cpsw']))
			{
				$newCPassErr = 'Password is required.';
			}
			else
			{
				$newCPass = validate(filter_has_var(INPUT_POST, 'cpsw') ? $_POST['cpsw'] : null, 'generic');
				if ($newCPass === '')
				{
					$newCPassErr = 'Password is invalid.';
				}
				else if (!$newPass == $newCPass)
				{
					$newCPassErr = 'Passwords don\'t match.';
				}
			}

			if ($passErr === '' && $newPassErr === '' && $newCPassErr === '')
			{
				$sqlQuery = "UPDATE NBL_users
							 SET passwordHash=?
							 WHERE username=?";
				$update = $dbconn->prepare($sqlQuery);
				$sqlQuery = "SELECT passwordHash
							 FROM NBL_users
							 WHERE username=?";
				$passCheck = $dbconn->prepare($sqlQuery);
				if (isset($_SESSION['username']))
				{
					$passCheck->execute([$_SESSION['username']]);
				}
				else
				{
					$usernameErr = 'Invalid username set.';
				}
				$rowCount = $passCheck->rowCount();
				if ($rowCount == 1)
				{
					$fetched = $passCheck->fetchObject();
					$passFetch = $fetched->passwordHash;
					//if the fetched password is not null, or empty string
					if (isset($passFetch) && $passFetch != '')
					{
						$passHash = $passFetch;
					}
					else
					{
						$passErr = 'Wrong password.';
					}
				}
				else if ($rowCount == 0)
				{
					$passErr = 'Wrong password.';
				}
				else
				{
					$passErr = 'Database Error.';
				}
				if ($passErr === '' && $usernameErr === '' && password_verify($pass, $passHash))
				{
					/* i have not tested this because i don't want to alter the passwordHash, but i did save the exact passwordHash 
					   $2y$10$ seems to be PASSWORD_DEFAULT currently, well it doesn't matter as it'll still work anyhow */
					$update->execute([password_hash($newPass, PASSWORD_DEFAULT), $_SESSION['username']]);
				}
				else
				{
					$passErr = 'Wrong password.';
				}
			}
		}
		//only if a request to change is made
		if ($_SERVER["REQUEST_METHOD"] == "POST")
		{
			$dbconn = getConnection();
			if (isset($_REQUEST['name']))
			{
				changeName($dbconn);
			}
			if (isset($_REQUEST['password']))
			{
				changePassword($dbconn);
			}
		}
		echo HTMLHead('profile.php', 'Profile', $_SESSION['name'].'\'s Profile');
		echo <<<DETAILS

			<fieldset id="profDetails">
				<p>Username: {$_SESSION['username']}<br>Name: {$_SESSION['name']}<br></p>
			</fieldset>
DETAILS;
		echo "<span class=\"error\">{$usernameErr}</span>\n";
		//section for different forms
		getSession();
		//make session variables exist (if they don't) for form content
		setSessVar('firstname');
		setSessVar('lastname');
		//dynamic form content (hidden until needed)
		$changeSessContent = <<<FORM
			<div id="changeName" class="container" style="display: none;">
				<form action="profile.php?name" method="post">
					<fieldset>
						<label for="firstname">First Name</label>
						<input class="green" type="text" id="firstname" name="firstname" value="{$_SESSION['firstname']}" title="Must contain one uppercase at the start and any lowercase letters after." required>
						<span class="error">* {$fnErr}</span><br>
						<div id="messageFirstName">
							<h2>First Names should contain the following:</h2>
							<p id="firstCapital" class="invalid">A starting <b>Capital</b> letter</p>
							<p id="firstLetter" class="invalid">Some trailing <b>Lowercase</b> letters</p>
						</div>
						<label for="lastname">Last Name</label>
						<input class="green" type="text" id="lastname" name="lastname" value="{$_SESSION['lastname']}" title="Must contain one uppercase at the start and any lowercase letters after." required>
						<span class="error">* {$lnErr}</span><br>
						<div id="messageLastName">
							<h2>Last Names should contain the following:</h2>
							<p id="lastCapital" class="invalid">A starting <b>Capital</b> letter</p>
							<p id="lastLetter" class="invalid">Some trailing <b>Lowercase</b> letters</p>
						</div>
						<label for="password">Password</label>
						<input type="password" id="password1" name="password" required>
						<span class="error">* {$passErr}</span><br>
					</fieldset>
					<fieldset>
						<button type="submit" id="submit1" name="submit" value="submit">Submit</button>
						<button type="button" id="back1" name="back" onclick="displayOrHide('changeName')">Back</button>
					</fieldset>
				</form>
			</div>
			<div id="changePassword" class="container" style="display: none;">
				<form action="profile.php?password" method="post">
					<fieldset>
						<label for="password">Old Password</label>
						<input type="password" id="password2" name="password" required>
						<span class="error">* {$passErr}</span><br>
						<label for="psw">New Password</label>
						<input type="password" id="psw" name="psw" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" required>
						<span class="error">* {$newPassErr}</span><br>
						<div id="messagePSW">
							<h3>Password must contain the following:</h3>
							<p id="letter" class="invalid">A <b>lowercase</b> letter</p>
							<p id="capital" class="invalid">A <b>capital (uppercase)</b> letter</p>
							<p id="number" class="invalid">A <b>number</b></p>
							<p id="symbol" class="invalid">A <b>symbol</b></p>
							<p id="length" class="invalid">Minimum <b>8 characters</b></p>
						</div>
						<label for="cpsw">Confirm New Password</label>
						<input type="password" id="cpsw" name="cpsw" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" required>
						<span class="error">* {$newCPassErr}</span><br>

FORM;
		echo $changeSessContent;
	}
	else
	{
		redirect('login.php');
	}
}
catch (Exception $e) {
	throw new Exception('Error ' . $e->getMessage(), 0, $e);
}
?>
						<div id="messageCPSW">
							<h2>Password must be identical:</h2>
							<p id="value" class="invalid">Password is <b> identical</b></p>
						</div>
					</fieldset>
					<fieldset>
						<button type="submit" id="submit2" name="submit" value="submit">Submit</button>
						<button type="button" id="back2" name="back" onclick="displayOrHide('changePassword')">Back</button>
					</fieldset>
				</form>
			</div>
			<!-- section to hide and reveal different forms -->
			<fieldset id="changeForm">
				<button type="button" onclick="displayOrHide('changeName')">Change Name</button>
				<button type="button" onclick="displayOrHide('changePassword')">Change Password</button>
			</fieldset>
			<!-- imports all functions from scripts.js -->
			<script src="scripts.js">
			</script>
			<!-- script for changing name validation etc -->
			<script>
				//define all variables
				const firstInput = document.getElementById("firstname");
				const firstValue = firstInput.value;
				const lastInput = document.getElementById("lastname");
				const lastValue = lastInput.value;
				const firstCapital = document.getElementById("firstCapital");
				const lastCapital = document.getElementById("lastCapital");
				const firstLetter = document.getElementById("firstLetter");
				const lastLetter = document.getElementById("lastLetter");

				//hides message container and reveals when clicking in and out of names
				firstInput.onfocus = function() {displayType("block", "messageFirstName");}
				firstInput.onblur = function() {displayType("none", "messageFirstName");}
				lastInput.onfocus = function() {displayType("block", "messageLastName");}
				lastInput.onblur = function() {displayType("none", "messageLastName");}

				capValidate(firstInput, firstCapital, /[A-Z]/g);
				pswValidate(firstInput, firstLetter, /[a-z]/g);
				capValidate(lastInput, lastCapital, /[A-Z]/g);
				pswValidate(lastInput, lastLetter, /[a-z]/g);

				firstInput.onkeyup = function() {
					//matches against regex
					capValidate(firstInput, firstCapital, /[A-Z]/g);
					pswValidate(firstInput, firstLetter, /[a-z]/g);
					comparison(firstInput, firstValue);
					if (firstCapital.classList.contains("invalid") || firstLetter.classList.contains("invalid"))
					{
						convert(firstInput, "red");
					}
				}
				lastInput.onkeyup = function() {
					//matches against regex
					capValidate(lastInput, lastCapital, /[A-Z]/g);
					pswValidate(lastInput, lastLetter, /[a-z]/g);
					comparison(lastInput, lastValue);
					if (lastCapital.classList.contains("invalid") || lastLetter.classList.contains("invalid"))
					{
						convert(lastInput, "red");
					}
				}
			</script>
			<!-- script for changing password validation etc -->
			<script>
				/*  client side validation will not stop the user from submitting
					since not all possible symbols are listed in the regex below */

				//define all variables
				const pswInput = document.getElementById("psw");
				const cpswInput = document.getElementById("cpsw");
				const letter = document.getElementById("letter");
				const capital = document.getElementById("capital");
				const number = document.getElementById("number");
				const symbol = document.getElementById("symbol");
				const length = document.getElementById("length");
				const value = document.getElementById("value");

				//hides message container and reveals when clicking in and out of psw
				pswInput.onfocus = function() {displayType("block", "messagePSW");}
				pswInput.onblur = function() {displayType("none", "messagePSW");}
				cpswInput.onfocus = function() {displayType("block", "messageCPSW");}
				cpswInput.onblur = function() {displayType("none", "messageCPSW");}

				pswInput.onkeyup = function() {
					//matches against regex
					pswValidate(pswInput, letter, /[a-z]/g);
					pswValidate(pswInput, capital, /[A-Z]/g);
					pswValidate(pswInput, number, /[0-9]/g);
					pswValidate(pswInput, symbol, /[#?!@$%^&*-]/g);

					//no regex no function
					if (pswInput.value.length >= 8)
					{
						length.classList.remove("invalid");
						length.classList.add("valid");
					}
					else
					{
						length.classList.remove("valid");
						length.classList.add("invalid");
					}
					//incase you edit the first password after the second
					if (pswInput.value == cpswInput.value)
					{
						value.classList.remove("invalid");
						value.classList.add("valid");
					}
					else
					{
						value.classList.remove("valid");
						value.classList.add("invalid");
					}
				}
				cpswInput.onkeyup = function() {
					if (pswInput.value == cpswInput.value)
					{
						value.classList.remove("invalid");
						value.classList.add("valid");
					}
					else
					{
						value.classList.remove("valid");
						value.classList.add("invalid");
					}
				}
			</script>
		</main>
	</body>
</html>