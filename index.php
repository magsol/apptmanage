<?php
include_once('config.inc.php');

import('user');
import('request');
import('factory');

/******************************************\
* Created on Jul 8, 2008                   *
********************************************
* This is the login page.  If the user is  *
* already logged in - e.g. they have a     *
* valid session cookie - they will be      *
* redirected to home.php.  Otherwise, this *
* page serves as a login page and the      *
* location for links to registration.      *
\******************************************/

// now, the user hasn't logged in...or have they?
$smarty = Factory::getSmarty();
$login = Request::getString('email', 'post');
if ($login) {
  // check the validity
  if (User::checkLogin($login, Request::getString('password', 'post'))) {
    // success!
    $user = Factory::getUser($login);
    $smarty->assign('title', 'Logged In');
    $smarty->assign('message', 'You have successfully logged in.');
    $smarty->assign('url', 'home.php');
    $smarty->assign('urltext', 'Go to Home Page');
    $smarty->display('redirectmsg.tpl');
    exit;
  }
}
  
// ok, user's not logging in...do they have a valid session?
$user = Factory::getUser();
$cfg = Factory::getConfig();
// is the user a guest, or do they have a valid cookie?
if ($user->isGuest()) {
  if (!Request::getString('HTTPS', 'server') && $cfg->getValue('ssl_enabled')) { // we want HTTPS
    Utility::redirect('https://' . Request::getString('SERVER_NAME', 'server') .  
                                   Request::getString('SCRIPT_NAME', 'server'));
  }
} else {
  // forward them on to the home page
  Utility::redirect('home.php');
}

// set up the Smarty template
$smarty->display('index.tpl');

?>