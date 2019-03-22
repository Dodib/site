<?php

namespace ttm4135\webapp\controllers;
use ttm4135\webapp\Auth;
use ttm4135\webapp\models\User;

class LoginController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::check()) {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        } else {
	    if (isset($_COOKIE['username'])) {
		$username = $_COOKIE['username'];
	    } else {
		$username = '';
	    }

            $this->render('login.twig', ['title'=>"Login", 'username'=>$username]);
        }
    }

    function login()
    {
        $request = $this->app->request;
        $username = $request->post('username');
	$password = $request->post('password');
	
	if(Auth::block($username) < 5){
        	if ( Auth::checkCredentials($username, $password) ) {
            		setcookie('username', $username, time() + 1209600, '/login/', 'grp14.ttm4135.item.ntnu.no', TRUE, TRUE);
	    		session_regenerate_id(True);

            		$user = User::findByUser($username);
            		$_SESSION['userid'] = $user->getId();
            		$this->app->flashNow('info', "You are now successfully logged in as " . $user->getUsername() . ".");
			self::log_logins($request, 'success');
	    		$this->app->redirect('/');
		}else{
			$this->app->flashNow('info', 'Incorrect username/password combination.');
			self::log_logins($request, 'failed');
        		$this->render('login.twig', []);
		}
	}elseif(Auth::block($username) >= 5){
		# user is blocked
        	$this->app->flashNow('info', 'This user is blocked, please contact the admin');
		self::log_logins($request, 'blocked');
        	$this->render('login.twig', []);
	}else{
		$this->app->flashNow('info', 'Incorrect username/password combination.');
		self::log_logins($request, 'failed');
        	$this->render('login.twig', []);
	}
    }
    function log_logins($request, $type){
	$ip = self::get_client_ip();
	$log_file = fopen("/home/grp14/log.txt", "a") or die ("unable to access file");
	date_default_timezone_set('Europe/Oslo');
	$date = date('d/m/Y h:i:s a', time());
	$text = "";
	if($type == "success"){
		$text = "Login Sucessful";
	}elseif($type == "failed"){
		$text = "Failed Login";
	}elseif($type == "blocked"){
		$text = "Login to blocked account";
	}
	$username = $request->post('username');
	$message = $date . ' | ' . 'IP : ' . self::get_client_ip() . ' : Username : ' . $username . ' | '. $text;
	fwrite($log_file, "\n".$message);
	fclose($log_file);
    }

    // Function to get the client ip address
    function get_client_ip() {
    	$ipaddress = '';
    	if (getenv('HTTP_CLIENT_IP')){
    		$ipaddress = getenv('HTTP_CLIENT_IP');
    	}elseif(getenv('HTTP_X_FORWARDED_FOR')){
    		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    	}else if(getenv('HTTP_X_FORWARDED')){
    		$ipaddress = getenv('HTTP_X_FORWARDED');
	}else if(getenv('HTTP_FORWARDED_FOR')){
    		$ipaddress = getenv('HTTP_FORWARDED_FOR');
	}else if(getenv('HTTP_FORWARDED')){
    		$ipaddress = getenv('HTTP_FORWARDED');
	}else if(getenv('REMOTE_ADDR')){
    		$ipaddress = getenv('REMOTE_ADDR');
	}else{
        	$ipaddress = 'UNKNOWN';
	}                                                                     
	return $ipaddress;
    }

    function logout()
    {   
        Auth::logout();
        $this->app->flashNow('info', 'Logged out successfully!!');
        $this->render('base.twig', []);
        return;
       
    }
}
