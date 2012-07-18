<?php
/**
 * Session.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/auth
 */

class Session
{
	public function __construct()
	{
		global $Config;
		session_name($Config->get("auth.cookie_name"));
		session_start();
		// Create a global User object with the userid obtained from the session data
		if ( $this->is_set('userid') )
		{
			$userid = $this->get('userid');
			try
			{
				$GLOBALS['User'] = new User($userid);
			}catch(CerenkovException $cex)
			{
				$this->destroy();
			}
		}
		else
		{
			$GLOBALS['User'] = NULL;
		}		
	}
	
	public function is_set($key)
	{
		return isset( $_SESSION[$key] );
	}
	
	public function id()
	{
		return session_id();
	}

	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}
	
	public function unsetVar($key)
	{
		unset($_SESSION[$key]);
	}

	public function get($key)
	{
		if ( array_key_exists($key, $_SESSION) )
			return $_SESSION[$key];
		else throw new SessionException('Could not retrieve session variable.');
	}

	public function commit()
	{
		return session_write_close();
	}

	public function destroy()
	{
		return session_destroy();
	}
}

?>
