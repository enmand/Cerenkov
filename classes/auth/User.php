<?php
/**
 * User.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/auth
 */

class User
{
	private $userid;
	private $username;
	private $password;
	private $email;
	private $auth_level;
	private $creation_date; // Probably not needed
	private $last_login; // Probably not needed
	private $activated;
	private $activation_code;
	
	/*
	 *	THESE FUNCTIONS ARE PUBLIC STATIC FUNCTIONS
	 */
	
	/**
	 * Activate userid (if they have the correct code), so that they can use their account
	 *
	 * @access public
	 * @static
	 * @param int $userid The user's id number (as calculated by the SQL server)
	 * @param string $code The activation code as sent by the server
	 * @return void
	 **/
	public static function activate($userid, $code)
	{
		global $Config;
		global $DataBase;
		// Check the code against that in the database
		$userid = IOSystem::DBEncode($userid);
		$result = $DataBase->query("SELECT activated,activation_code FROM ". $Config->get("sql.prefix") ."users WHERE userid=$userid");
		$data = $result->getRow();
		if ( $data[1] == 0 )
		{
			// not yet activated
			if ( $data[1] == $code )
			{
				// Code matched, set active
				$DataBase->query("UPDATE ". $Config->get("sql.prefix") ."users SET activated=1 WHERE userid=$userid");
			}
			else
			{
				// Code didn't match
				throw new AuthException('Activation code incorrect.');
			}
		}
		else
		{
			// Already active
			throw new AuthException('Account already active.');
		}
	}
	
	/**
	 * Add a user to the database
	 *
	 * @access public
	 * @static
	 * @param string $username The desired username to be added to the database
	 * @param string $password The user's desired password
	 * @param string $password_check The user's desired password (checked)
	 * @param string $email The user's email address
	 * @return void
	 **/
	public static function addUser($username, $password, $password_check, $email, $hashed = FALSE)
	{
		global $DataBase;
		global $Config;

		// PRIORITY: Hash passwords and lose original
		if($hashed)
		{
			$hpass = $password;
			$chpass = $password_check;
		} else {
			$hpass = IOSystem::hash($password);
			unset($password);
			$chpass = IOSystem::hash($password_check);
			unset($password_check);
		}
		// Check that the password was typed in the same, twice
		if ( $hpass != $chpass )
		{
			throw new AuthException('Passwords do not match.');
		}
		unset($chpass);

		// Create the user entry in the database
		$timestamp = time();
		$username = IOSystem::cleanInput(IOSystem::DBEncode($username));
		$email = IOSystem::cleanInput(IOSystem::DBEncode($email));
		$result = $DataBase->query("SELECT username FROM ". $Config->get("sql.prefix") ."users WHERE username='$username'");
		if($result->numRows())
		{
			throw new AuthException("Username already taken");
		}

		$DataBase->query("INSERT INTO ". $Config->get("sql.prefix") ."users(username,password,email,auth_level,creation_date,last_login,activated,activation_code) VALUES('$username','$hpass','$email',0,$timestamp,0,0,'')");
		// Send an activation e-mail
		User::sendActivationCode($username, $email);
	}
	
	/**
	 * Get the user's id number by their username
	 *
	 * @access public
	 * @static
	 * @param string $username User's username to be checked
	 * @return int The user's ID number
	 **/
	public static function getUserIDByName($username)
	{
		global $Config;
		global $DataBase;
		$username = IOSystem::DBEncode($username);
		$result = $DataBase->query("SELECT userid FROM ". $Config->get("sql.prefix") ."users WHERE username='$username'");
		if ( $result->numRows() != 1 )
			throw new AuthException('Could not obtain unique user lookup.');
		else return $result->getResult(0);
	}

	/**
	 * Get the user's username by their userid
	 *
	 * @access public
	 * @static
	 * @param int $userid The user's ID number
	 * @return string The user's username
	 **/
	public static function getUserNameByID($userid)
	{
		global $Config;
		global $DataBase;
		$result = $DataBase->query("SELECT username FROM ". $Config->get("sql.prefix") ."users WHERE userid=$userid");
		if ( $result->numRows() != 1 )
			throw new AuthException('Could not obtain unique user lookup.');
		else return IOSystem::DBUnencode($result->getResult(0));
	}
	
	/**
	 * Log the user into the system using their username and password
	 *
	 * @access public
	 * @static
	 * @param string $username The username of the user to be logged in
	 * @param string $password The user's password
	 * @return User|void Returns User object if logged in, Exception otherwise
	 **/
	public static function Login($username, $password, $hashed = FALSE)
	{
		global $DataBase;
		global $Config;
		// PRIORITY: Hash password and lose the original
		if($hashed == TRUE)
			$hashed_password = $password;
		else
			$hashed_password = IOSystem::hash($password);
		unset($password);


		// Now find the userid corresponding to this username
		$username = IOSystem::DBEncode($username);
		$result = $DataBase->query("SELECT userid FROM ". $Config->get("sql.prefix") ."users WHERE username='$username'");
		if ( $result->numRows() != 1 )
		{
			throw new AuthException('Error retrieving unique user information.');
		}
		$userid = (int)$result->getResult(0);

		// Construct a User object and verify that the password is correct
		$userObj = new User($userid);
		if ( $userObj->validatePassword($hashed_password) )
		{
			// Set sessions stuff...
			global $Session;
			$Session->set("userid", $userid);
			$Session->set("username", $username);
			$Session->set("auth_level", $userObj->auth_level);
			
			// Update last_login field timestamp...
			$timestamp = time();
			$DataBase->query("UPDATE ". $Config->get("sql.prefix") ."users SET last_login=$timestamp WHERE username='$username'");

			// Return the User object
			return $userObj;
		}
		else
		{
			// Again, a nicer error message here would be good...
			throw new AuthException('Permission denied.');
			unset($userObj);
		}

	}
	
	public static function sendActivationCode($username, $email)
	{
		global $DataBase;
		global $Config;
		// Create a unique code for account activation
		// To do this, mix the current timestamp with a salt and hash
		// using the IOSystem::hash() function
		$timestamp = time();
		$salt = $Config->get('auth.activation_salt');
		$activation_code = IOSystem::hash($timestamp . $salt . $timestamp);

		// Get the userid
		$userid = User::getUserIDByName($username);

		// Set the code in the database and deactivate the account
		$DataBase->query("UPDATE ". $Config->get("sql.prefix") ."users SET activated=0,activation_code='$activation_code' WHERE userid='$userid'");

		// Send an e-mail containing a link to the activation page
		// This needs to be templated or something, but for now we'll just send
		// a link.
		$base_url = $Config->get('auth.activation_page');
		
		if(stristr($Config->get("auth.activate"), 'true'))
		{
			mail($email, 'Activate your Cerenkov account', "To activate your Cerenkov account go to $base_url?userid=$userid&activate=$activation_code");
		}
		else
		{
			$DataBase->query("UPDATE ". $Config->get("sql.prefix") ."users SET activated=1 WHERE userid='$userid'");
		}
	}
	
	# ALTER TABLE `users` AUTO_INCREMENT =1 
	public function __construct($userid)
	{
		global $Config;
		// Construct a User object from a unique userid by querying the database
		global $DataBase;
		if($userid != -1)
		{
			$rs = $DataBase->query("SELECT `username`,`password`,`email`,`auth_level`,`creation_date`,`last_login`,`activated`,`activation_code` FROM `" . $Config->get("sql.prefix") . "users` WHERE userid=$userid");
			$data = $rs->getRow();
			unset($rs);

			if($data == NULL)
				throw new AuthException("Could not find user information");
				
			// Extract the data into member variables
			$this->extract_array($data);
			$this->userid = $userid;
			$this->password = $data['password'];
			unset($data);
		}
	}

	private function extract_array($array)
	{
		global $DataBase;
		// This member function is a bit like the PHP extract() function
		// but extracts to member variables, and performs removal of DB escaped
		// characters
		foreach($array as $key => $value)
		{
			$this->{$key} = IOSystem::DBUnencode($value);
		}
	}

	/**
	 * Remove the user from our Database
	 *
	 * @access public
	 * @param string $username User's username to be deleted
	 * @return boolean True on success, false on failure
	 *
	 */
	public function removeUser()
	{
		global $DataBase;
		global $Config;
		$DataBase->query("DELETE FROM " . $Config->get("sql.prefix") . "`users` WHERE `userid`=" . $this->userid);
	}
	
	public function validatePassword($hashed_password)
	{
		global $DataBase;
		if ( $hashed_password == $this->password && $this->activated )
			return true;
		else return false;
	}

	public function validateLevel($auth_level)
	{
		global $DataBase;
		/*
		 * Auth levels:
		 *	Auth levels can be set to work in one of two ways, simple or additive.
		 *	The auth scheme is set from config.php.
		 *
		 * Simple Auth:
		 * 	If your auth level is >= the required auth level, permission is granted
		 *
		 * Additive Auth:
		 *	Auth levels are individual permission flags OR'd together into an additive
		 *	auth level. Permission is granted only if that specific flag is set in your
		 *	auth flags.
		 */
		global $Config;

		if ( $Config->get('auth.auth_scheme') == 'simple' )
		{
			// SIMPLE AUTH
			if ( $this->auth_level >= $auth_level )
				return true;
			else return false;
		}
		else if ( $Config->get('auth.auth_scheme') == 'additive' )
		{
			// ADDITIVE AUTH
			if ( $this->auth_level & $auth_level )
				return true;
			else return false;
		}
		else
		{
			// Perhaps we should throw an exception and rely on the exception catching mechanism
			// to print a pretty error message here... For now we'll just die, though.
			throw new AuthException('The auth scheme specified is not supported. Please check config.php');
		}
	}

	public function changePassword($old_password, $new_password, $repeat_new)
	{
		global $DataBase;
		if ( $new_password != $repeat_new )
		{
			throw new AuthException('Passwords do not match.');
		}
		$old_hash = IOSystem::hash($old_password);
		unset($old_password);
		$new_hash = IOSystem::hash($new_password);
		unset($new_password);

		if ( $this->validatePasword($old_hash) )
		{
			// Old password matched, set the new one
			$userid = $this->userid;
			$DataBase->query("UPDATE ". $Config->get("sql.prefix") ."users SET password='$new_hash' WHERE userid=$userid");
		}
		else
		{
			throw new AuthException('Permission denied.'); // Move to exceptions...
		}
	}

	public function changeEmail($password, $new_email)
	{
		global $DataBase;
		global $Config;
		$hash_pass = IOSystem::hash($password);
		unset($password);

		if ( $this->validatePassword($hash_pass) )
		{
			// Change e-mail address in the database
			$db_new_email = IOSystem::DBEncode($new_email);
			$DataBase->query("UPDATE ". $Config->get("sql.prefix") ."users SET email='$db_new_email' WHERE userid=$this->userid");

			// Send a new activation e-mail
			User::sendActivationCode($this->username, $new_email);
		}
	}
	
	public function getLevel()
	{
		return $this->auth_level;
	}
	
	public function setLevel($auth_level)
	{
		global $DataBase;
		global $Config;
		$DataBase->query("UPDATE `" . $Config->get("sql.prefix") . "users` SET `auth_level`=$auth_level WHERE `userid`=$this->userid");
	}
}

?>
