<?php

namespace ttm4135\webapp;

use ttm4135\webapp\models\User;

class Auth
{
    static $app;

    function __construct()
    {
    }

    static function checkCredentials($username, $password)
    {
	$user = User::findByUser($username);
	# User does not exist
	if ($user === null) {
        	return false;
        }
	# user found, verify the password is correct
	$id = $user->getId();
        if(password_verify($password, $user->getPassword()))
        {
		# correct login, reset the "blocking"
		$UPDATE_QUERY = self::$app->db->prepare("UPDATE users SET failedattempts = 0 WHERE id = :id");
		$UPDATE_QUERY->bindParam(':id', $id);
		$UPDATE_QUERY->execute();
		return true;
	}
	# login failed increment the "blocking"
	$UPDATE_QUERY = self::$app->db->prepare("UPDATE users SET failedattempts = failedattempts + 1 WHERE id = :id");
	$UPDATE_QUERY->bindParam(':id', $id);
	$UPDATE_QUERY->execute();
        return false;
    }

    static function block($username)
    {
	$user = User::findByUser($username);
	if($user == null){
		return 0;
	}else{
	return $user->getFailedAttempts();
	}
    }
    /**
     * Check if is logged in.
     */
    static function check()
    {
        return isset($_SESSION['userid']);
    }

    /**
     * Check if the person is a guest.
     */
    static function guest()
    {
        return self::check() === false;
    }

    /**
     * Get currently logged in user.
     */
    static function user()
    {
        if (self::check()) {
            return User::findById($_SESSION['userid']);         
        }
    }

    /**
     * Is currently logged in user admin?
     */
    static function isAdmin()
    {
        if (self::check()) {
          return self::user()->isAdmin();	// uses this classes user() method to retrieve the user from sql, then call isadmin on that object.
        }

    }

    /** 
     * Does the logged in user have r/w access to user details identified by $tuserid
     */
    static function userAccess($tuserid) 
    {
        if(self::user()->getId() == $tuserid)   //a user can change their account
        {
          return true;
        }
        if (self::isAdmin() )           //admins can change any account
        {
          return true;
        }
        return false;

    }
    
    static function logout()
    {
        session_unset();
        session_destroy();	
    }
}

Auth::$app = \Slim\Slim::getInstance();
