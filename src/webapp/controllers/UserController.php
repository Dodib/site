<?php

namespace ttm4135\webapp\controllers;

use ttm4135\webapp\models\User;
use ttm4135\webapp\Auth;

class UserController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()     
    {
        if (Auth::guest()) {
            $this->render('newUserForm.twig', []);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function create()		  
    {
	$request = $this->app->request;
	$user = User::makeEmpty();
        $username = $request->post('username');
	$password = $request->post('password');
	$email = $request->post('email');
	$bio = $request->post('bio');

	if(!$this->checkPassword($password)){
		# check pass length > 5	
		$this->app->flash('info', 'Password is too short (minimum 8 characters)');
	}elseif(!$this->checkUsername($username)){
		# check username length > 5
		$this->app->flash('info', 'Username is too short');
	}elseif($this->similarPassword($password, $username, $user->getEmail(), $user->getBio())){
		# check similarity of fields, pass cannot contain pass for example
		$this->app->flash('info', 'Password should not contain similar info as the other cells');
	}elseif(shell_exec('~/pass_sec/check_password '.escapeshellarg($password)) == 1){
		# c-script to check pass is not weak, from list of weak passwords
		$this->app->flash('info', 'The password is weak and easily hackable, please pick another');
    	}elseif( User::findByUser($username) != null) { 
		# check that username is not already taken
		$this->app->flash('info','This username already exists');
	}else{
		# everything fine, so create the user
		$user->setUsername($username);
		$user->setPassword(password_hash($password, PASSWORD_DEFAULT));
		$user->setEmail($email);
		$user->setBio($bio);
        	$user->save();
        	$this->app->flash('info', 'Thanks for creating a user. You may now log in.');
		$this->app->redirect('/login');
	}
	# one of the checks executed therefore redirect to current page
	$this->app->redirect('/register');
    }

    function unblock($tuserid){
    	if(Auth::isAdmin()) {
            $request = $this->app->request;
	    
	    if ($request->get('token') != session_id()) {
                $this->app->flash('info', 'Request cannot originate from another website');
		$this->app->redirect('/admin');
	    } else {
		$user = User::findById($tuserid);
		$user->setFailedAttempts(0);
		$user->save();
		$this->app->flash('info', 'User '.$user->getUsername(). ' unblocked');
		$this->app->redirect('/admin');
	    }
	} else {
	    $this->app->flash('info', 'You do not have acces to this resource');
	    $this->app->redirect('/');
	}
    
    }
    function delete($tuserid)
    {
	$user = Auth::user();

	if ($user == null) {
	    $this->app->flash('info', 'You need to log in to access to this resource');
            $this->app->redirect('/login');   
	}
	    
	if(Auth::isAdmin())
	{
	    $request = $this->app->request;

            if ($request->get('token') != session_id()) {
		// echo "token: " . $request->get('token') . " session: " . session_id();
                // throw new \Exception("CSRF token mismatch.");
		$this->app->flash('info', 'Request cannot originate from other website');
		$this->app->redirect('/admin');
            } else {
                $user = User::findById($tuserid);
                $user->delete();
                $this->app->flash('info', 'User ' . $user->getUsername() . '  with id ' . $tuserid . ' has been deleted.');
                $this->app->redirect('/admin');
            }

            $user = User::findById($tuserid);
            $user->delete();
            $this->app->flash('info', 'User ' . $user->getUsername() . '  with id ' . $tuserid . ' has been deleted.');
            $this->app->redirect('/admin');
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function deleteMultiple()
    {
      if(Auth::isAdmin()){
          $request = $this->app->request;

          if ($request->post('token') != session_id()) {
	      // echo "token: " . $request->get('token') . " session: " . session_id();
              // throw new \Exception("CSRF token mismatch.");
              $this->app->flash('info', 'Request cannot originate from other website');
	      $this->app->redirect('/admin');
	  }

          $userlist = $request->post('userlist'); 
          $deleted = [];

          if($userlist == NULL){
              $this->app->flash('info','No user to be deleted.');
          } else {
               foreach( $userlist as $duserid)
               {
                    $user = User::findById($duserid);
                    if(  $user->delete() == 1) { //1 row affect by delete, as expect..
                      $deleted[] = $user->getId();
                    }
               }
               $this->app->flash('info', 'Users with IDs  ' . implode(',',$deleted) . ' have been deleted.');
          }

          $this->app->redirect('/admin');
      } else {
          $username = Auth::user()->getUserName();
          $this->app->flash('info', 'You do not have access to this resource. You are logged in as ' . $username);
          $this->app->redirect('/');
      }
    }


    function show($tuserid)   
    {
	if(Auth::user() == null){
		$this->app->flash('info', 'You do not have accesss to this resource');
		$this->app->redirect("/");
	}
        if(Auth::isAdmin())
        {
		$user = User::findById($tuserid);
		$sessionid = session_id();
		if($user->isAdmin()){
			if(Auth::user() != $user){
				$this->render('showuser_admin_2.twig', [
		    		'user' => $user,
		    		'sessionid' => $sessionid
				]);
			}else{
				$this->render('showuser_admin.twig', [
		    		'user' => $user,
		    		'sessionid' => $sessionid
				]);
			}
		}else{
			$this->render('showuser_admin.twig', [
		    	'user' => $user,
		    	'sessionid' => $sessionid
	    		]);
		}
	} 
	elseif(Auth::userAccess($tuserid)){
	  	$user = User::findById($tuserid);
		$sessionid = session_id();
	  	$this->render('showuser.twig', [
		    'user' => $user,
		    'sessionid' => $sessionid
	  	]);
    	}else{
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access to this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function show_user()   
    {
	$user = Auth::user();
	if($user == null){
            $this->app->flash('info', 'You do not have access to this resource.');
            $this->app->redirect('/');
	}else{
	    $tuserid = Auth::user()->getId();
	}
        if(Auth::isAdmin())
        {

	  $user = User::findById($tuserid);
	  $sessionid = session_id();
          $this->render('showuser_admin.twig', [
	      'user' => $user,
	      'sessionid' => $sessionid
    ]);
	} 
	elseif(Auth::userAccess($tuserid)){
	  $user = User::findById($tuserid);
	  $sessionid = session_id();
	  $this->render('showuser.twig', [
	      'user' => $user,
	      'sessionid' => $sessionid
     ]);
    	}else{
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access to this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }
    function newuser()
    { 

	$user = User::makeEmpty();

	if (Auth::isAdmin()) {
        	$request = $this->app->request;
        	$username = $request->post('username');
            	$password = $request->post('password');
           	$email = $request->post('email');
            	$bio = $request->post('bio');
            	$isAdmin = ($request->post('isAdmin') != null);
	    	if(!$this->checkPassword($password)){
			# check length of pass > 5
			$this->app->flash('info', 'Password is too short (minimum 8 characters)');
	    	}
	    	else if(!$this->checkUsername($username)){
			# check length of username > 5
			$this->app->flash('info', 'Username is too short');
	    	}
	    	else if($this->similarPassword($password, $username, $email, $bio)){
			# check that password doesnt contain other fields
			$this->app->flash('info', 'Password should not contain similar info as the other cells');
            	}elseif(shell_exec('~/pass_sec/check_password '.escapeshellarg($password)) == 1){
			# c-script to check for weak passwords form a list
			$this->app->flash('info', 'The password is weak and easily hackable, please pick another');
	    	}elseif(User::findByUser($username) != null){
			# check if username already exists
			$this->app->flash('info','This username already exists');

		}else{
			# everythings fine, go ahead create user
            		$user->setUsername($username);
            		$user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            		$user->setBio($bio);
            		$user->setEmail($email);
            		$user->setIsAdmin($isAdmin);
            		$user->save();
			$this->app->flash('info', 'Your profile was successfully saved.');
		}
		# redirect to here for all cases
		$this->app->redirect('/admin/create');

	}else{
		# not admin rights
        	$username = $user->getUserName();
        	$this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            	$this->app->redirect('/');
        }
    }

    function edit_2(){
	    $tuserid = Auth::user()->getId();
	    if ($tuserid != null){
		    $this->edit($tuserid);
	    }else{
		$this->app->flash('info', 'something went wrong, try again');
	    	$thi->app->redirect('/edit');
	    }
    }
    function edit($tuserid)    
    { 
	// if($tuserid == NULL){$tuserid = Auth::user()->getId();}
	$user = User::findById($tuserid);
        if (! $user) {
            throw new \Exception("Unable to fetch logged in user's object from db.");
	}
	if(!Auth::isAdmin() && !(Auth::userAccess($user->getId()))){
		$username = $user->getUserName();
            	$this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            	$this->app->redirect('/');	
	}

	# both user and admin can change these
	$request = $this->app->request;

	$token = $request->post('token');

	if ($token != session_id()) {
	    $this->app->flash('info', 'Request cannot originate from different website');
	    $this->app->redirect('/edit');    
	}

	$username = $request->post('username');
	$oldpassword = $request->post('oldpassword');
        $password = $request->post('password');
        $email = $request->post('email');
	$bio = $request->post('bio');
	if(strlen($password) != 0 && !password_verify($oldpassword, $user->getPassword())){
		$this->app->flash('info', 'Wrong old password');
	}elseif(strlen($password) != 0 && !$this->checkPassword($password)){	
		$this->app->flash('info', 'Password is too short (minimum 8 characters)');
    	}elseif($this->similarPassword($password, $username, $email, $bio)){
		# check that password doesnt contain other fields
		$this->app->flash('info', 'Password should not contain similar info as the other cells');
        }elseif(shell_exec('~/pass_sec/check_password '.escapeshellarg($password)) == 1){
		# c-script to check for weak passwords form a list
		$this->app->flash('info', 'The password is weak and easily hackable, please pick another');
	}else{
		$user->setUsername($user->getUsername());
		if(strlen($password) != 0){
			if(!$user->isAdmin()){
				$user->setPassword(password_hash($password, PASSWORD_DEFAULT));
			}
		}
		$user->setEmail($email);
		$user->setBio($bio);	
		if(Auth::isAdmin()) {
            		$isAdmin = ($request->post('isAdmin') != null);
	    		if($user->getUsername() != $username && !$this->checkUsername($username)){
				$this->app->flash('info', 'Username is too short');
	    			$this->app->redirect("/admin/edit/{$tuserid}");
			}
            		$user->setIsAdmin($isAdmin);
		}
		#common for both admin and users
		$user->save();
		$this->app->flash('info', 'Your profile was successfully saved.');
            	$user = User::findById($tuserid);
	}    
	if(Auth::isAdmin()){
		$this->app->redirect("/admin/edit/{$tuserid}");
	}else{
		$this->app->redirect("/edit");
	}
    }

    function checkPassword($tpassword){
	 return strlen($tpassword) >= 5;
    }

    function checkUsername($tusername){
	return strlen($tusername) >= 5;
    }
    function similarPassword($tpassword, $tusername, $temail, $tbio){
	    $error_1 = false;
	    $error_2 = false;
	    $error_3 = false;
	    if(strlen($tpassword) == 0){
	    	return False;
	    }
	    if(strlen($temail) != 0){
	   	$error_1 = strpos($tpassword, $temail); 
	    }
	    if(strlen($tusername) != 0){
	    	$error_2 = strpos($tpassword, $tusername);
	    }
	    if(strlen($tbio) != 0){
	    	$error_3 = strpos($tpassword, $tbio);
	    }
	return ($error_1 || $error_2 || $error_3);
    }    
}
