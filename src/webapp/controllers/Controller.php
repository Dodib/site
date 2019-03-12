<?php

namespace ttm4135\webapp\controllers;
use ttm4135\webapp\Auth;
class Controller
{
    protected $app;

    function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
    }

    function render($template, $variables = [])
    {     
      if (! Auth::guest()) {
            $user = Auth::user();
            $variables['isLoggedIn'] = true;
            $variables['isAdmin'] = $user->isAdmin();
            $variables['loggedInUsername'] = $user->getUsername();
            $variables['loggedInID'] = $user->getId();
        }
        print $this->app->render($template, $variables);
    }
}
