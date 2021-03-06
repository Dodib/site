<?php

namespace ttm4135\webapp\controllers;

use ttm4135\webapp\models\User;
use ttm4135\webapp\Auth;

class AdminController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()     
    {
	if (Auth::user() == null) {
	    $this->app->flash('info', 'You do not have access to this resource.');
	    $this->app->redirect('/login');
	}

        if (Auth::isAdmin()) {
            $users = User::all();
	    $sessionid = session_id();
            $this->render('users.twig', ['users' => $users, 'sessionid' => $sessionid]);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function create()
    {
	if(Auth::user() == null){
		$this->app->flash('info', 'You do not have access to this resource');
		$this->app->redirect('/');	
	}    
	if (Auth::isAdmin()) {
          $user = User::makeEmpty();
          $this->render('showuser_admin.twig', [
            'user' => $user
          ]);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }


}
