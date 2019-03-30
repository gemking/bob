<?php
ini_set('display_errors', '1');
require_once '/home/common/mail.php'; // Add email functionality
require_once '/home/common/dbInterface.php'; // Add database functionality
processPageRequest();
function authenticateUser($username, $password){
	$user = validateUser($username, $password);
	if($user !== null){
		session_start();
		$_SESSION["userId"] = $user["userId"];
		$_SESSION["displayName"] = $user["displayName"];
		$_SESSION["email"] = $user["email"];
		header("Location: ./index.php");
		exit();
	} else {
		displayLoginForm("The entered username and/or password served as invalid input(s). Please try again.");
	}
}
function createAccount($username, $password, $displayName, $email){
	$result = addUser($username, $password, $displayName, $email);
	if($result > 0){
		sendValidationEmail($userId, $displayName, $emailAddress);
	} else {
		displayLoginForm("Account already exists with that username");
	}
}
function displayCreateAccountForm(){
	echo'
	<!DOCTYPE html>
	<html>
		<head>
			<title>Create Account - myMovies Xpress!</title>
		</head>
		<body>
		<h1>myMovies Xpress Create Account Page!</h1>
		<form action="./logon.php" onsubmit="return validateCreateAccountForm();" method="post">
			<p><label for="displayName">Display Name: </label>
			<input type="text" id="displayName" name="displayName" required/></p>
			<p><label for="emailAddress">Email Address: </label>
			<input type="text" id="emailAddress" name="emailAddress" required/></p>
			<p><label for="confirmEmailAddress">Confirm email Address: </label>
			<input type="text" id="confirmEmailAddress" name="confirmEmailAddress" required/></p>
			<p><label for="username">Username: </label>
			<input type="text" id="username" name="username" required/></p>
			<p><label for="password">Password: </label>
			<input type="password" id="password" name="password" required/></p>
			<p><label for="confirmPassword">Confirm Password: </label>
			<input type="password" id="confirmPassword" name="confirmPassword" required/></p>
			<input type="hidden" name="action" value="create" />
			<p><button type="button" name="cancel" value="Cancel" onClick="confirmCancel(\'create\')"></button></p>
			<p><input type="reset" name="reset" value="Clear"></p>
			<p><input type="hidden" id="hiddenId" name="action" value="create"></p>
			<p><input type="submit" name="createAccount" value="Create Account"></p>
		</form>
		</body>
	</html>';
}
function displayForgotPasswordForm() {
	echo'
	<!DOCTYPE html>
	<html>
		<head>
			<title>Login - myMovies Xpress!</title>
		</head>
		<body
			<h1>myMovies Xpress Forgot Password Page!</h1>
			<form action="./logon.php" method="post">
				<p><label for="username">Username: </label>
				<input type="text" name="username" required/></p>
				<input type="hidden" name="action" value="forgot" />
				<p><button type="button" name="cancel" value="Cancel" onClick="confirmCancel(\'forgot\')"></button></p>
				<p><input type="reset" name="reset" value="Clear"></p>
				<p><input type="hidden" id="hiddenId" name="action" value="forgot"></p>
				<p><input type="submit" name="forgotPassword" value="Submit"></p>
			</form>
		</body>
	</html>';
}
function displayLoginForm($message=""){
	echo'
	<!DOCTYPE html>
	<html>
	<head>
	<title>Login - myMovies Xpress!</title>
	<link rel="stylesheet" href="../css/my.css">
	</head>
	<body>
		
	<h1 class = "payment">myMovies Xpress!</h1>
	
	<p>'.$message.'</p>
	<form action="./logon.php" method="post">
	<p> Username: <br>
	<input type="text" name="username" required></p>
	<p> Password: <br>
	<input type="password" name="password" required></p>
	<p><input type="reset" name="reset" value="Clear"></p>
	<p><input type="hidden" id="hiddenId" name="action" value="login"></p>
	<p><input type="submit" name="login" value="Login"></p><br>
	<a href="./logon.php?form=create">Create Account</a><br><br>
	<a href="./logon.php?form=forgot">Forgot Password</a><br><br>
	</form>
    <a href="/~vl456555/index.html" class="hover"> ePortfolio<br></a>
	</body>
	</html>';
}
function displayResetPasswordForm($userId){
	echo'
	<!DOCTYPE html>
	<html>
		<head>
			<title>Login - myMovies Xpress!</title>
		</head>
		<body
			<h1>myMovies Xpress Reset Password Page!</h1>
			<form action="./logon.php" method="post">
				<p><label for="password">Password: </label>
				<input type="password" id="password" name="password" required/></p>
				<p><label for="confirmPassword">Confirm Password: </label>
				<input type="password" id="confirmPassword" name="confirmPassword" required/></p>
				<p><input type="hidden" id="hiddenId" name="action" value="reset"></p>
				<p><input type="hidden" name="user_id" value="' . $userId . '"></p>
				<p><button type="button" name="cancel" value="Cancel" onClick="confirmCancel(\'reset\')"></button></p>
				<p><input type="reset" name="reset" value="Clear"></p>
				<p><input type="submit" name="resetPassword" value="Reset Password"></p>
			</form>
		</body>
	</html>';
}
function processPageRequest(){
	
	if(!empty($_POST)){
		if(isset($_POST['action'])){
			switch($_POST['action']){
				case 'create':
					createAccount($_POST['username'], $_POST['password'], $_POST['displayName'], $_POST['emailAddress']);
					break;
				case 'forgot':
					sendForgotPasswordEmail($_POST['username']);
					break;
				case 'login':
					authenticateUser($_POST['username'], $_POST['password']);
					break;
				case 'reset':
					resetPassword($_POST['userId'], $_POST['password']);
					break;
				default:
					break;
			}
		}
	} else if(!empty($_GET)){
		if(isset($_GET['action'])){
			validateAccount($_GET['userId']);
		} else if(isset($_GET['form'])){
			switch($_GET['form']){
				case 'create':
					displayCreateAccountForm();
					break;
				case 'forgot':
					displayForgotPasswordForm();
					break;
				case 'reset':
					displayResetPasswordForm($_GET['userId']);
					break;
				default:
					break;
			}
		}
	} else {
		displayLoginForm();
	}
}
function resetPassword($userId, $password){
	$result = resetUserPassword($userId, $password);
	displayLoginForm($result ? "The password was successfully resetted" : "The password was unsuccessfully resetted");
}
function sendForgotPasswordEmail($username){
	$user = getUserData($username);
	$email = "n01052448@ospreys.unf.edu"; 
	$displayName = "bobthebuilder";
	$message = '
		<html>
			<head>
				<title>Password Reset</title>
			</head>
			<body>
				<h1>myMovies Xpress Password Reset</h1>
				<p>'. $user['name'] . '</p>
				<p>Please click on the URL Below to complete the password reset for your account</p>
				<p><a href="http://192.168.100.86/~vl456555/project5/logon.php?form=reset&user_id=' . $user['id'] . '">Reset Password</a></p>
			</body>
		</html>
	';
	$result = sendMail('555409699', $email, user['displayName'], user['emailAddress'], 'Password Reset for myMovies Xpress', $message);
}
 
function sendValidationEmail($userId, $displayName, $emailAddress){
	$email = "n01052448@ospreys.unf.edu"; 
	$message = '
	<html>
		<head>
			<title>Validate Account</title>
		</head>
		<body>
			<h1>myMovies Xpress Account Validation</h1>
			<p>'. $displayName . '</p>
			<p>Please Click on the URL to validate your new account</p>
			<p><a href="http://192.168.100.86/~vl456555/project5/logon.php?action=validate&user_id=' . $userId . '">Validate Email</a></p>
		</body>
	</html>
';
$result = sendMail('555409699', $email, $displayName, 'Validate Account', $message);
var_dump($result);
}
function validateAccount($userId){
	if(activateAccount($userId)){
		displayLoginForm("The account activation was successful");
	} else{
		displayLoginForm("The account activation was unsuccessful");
	}
}
 
?>
